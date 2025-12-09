<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\StaffTaskModel;
use App\Models\StockMovementModel;
use App\Models\InventoryModel;
use App\Models\RecentScanModel;
use App\Models\WarehouseModel;

class StaffTaskController extends Controller
{
    protected $staffTaskModel;
    protected $stockMovementModel;
    protected $inventoryModel;
    protected $recentScanModel;
    protected $warehouseModel;

    public function __construct()
    {
        $this->staffTaskModel = new StaffTaskModel();
        $this->stockMovementModel = new StockMovementModel();
        $this->inventoryModel = new InventoryModel();
        $this->recentScanModel = new RecentScanModel();
        $this->warehouseModel = new WarehouseModel();
    }

    /**
     * Get pending tasks for staff dashboard (excludes scanned tasks)
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getPendingTasks()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        // Allow both staff and managers to view tasks
        $role = session('role');
        if (!in_array($role, ['staff', 'manager'])) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Insufficient permissions']);
        }

        try {
            $warehouseId = $this->request->getGet('warehouse_id');
            // Default to session warehouse if not provided by request
            if (empty($warehouseId)) {
                $warehouseId = session('warehouse_id') ?: null;
            }
            
            // Get only truly pending tasks (not scanned or completed)
            $tasks = $this->staffTaskModel->where('status', 'Pending');
            
            if ($warehouseId) {
                $tasks = $tasks->where('warehouse_id', (int)$warehouseId);
            }
            
            $tasks = $tasks->orderBy('created_at', 'ASC')->findAll();
            
            // Enrich with item and warehouse information
            foreach ($tasks as &$task) {
                $item = $this->inventoryModel->find($task['item_id']);
                if ($item) {
                    $task['item_name'] = $item['name'];
                    $task['item_sku'] = $item['sku'];
                    $task['current_stock'] = $item['quantity'];
                }
                
                // Get warehouse info if available
                if ($task['warehouse_id']) {
                    $warehouse = $this->warehouseModel->find($task['warehouse_id']);
                    if ($warehouse) {
                        $task['warehouse_name'] = $warehouse['name'];
                    }
                }
            }
            
            return $this->response->setJSON([
                'success' => true,
                'tasks' => $tasks,
                'total_pending' => count($tasks)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get pending tasks: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }

    /**
     * Complete a staff task when item is scanned
     * 
     * @param int $taskId Task ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function completeTask($taskId = null)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        if (session('role') !== 'staff') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Staff access required']);
        }

        if (!$taskId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Task ID required']);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        $staffId = session('userID');

        // Validate user ID from session
        if (!$staffId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'User ID not found in session']);
        }

        try {
            $db = \Config\Database::connect();
            $db->transStart();

            // Get task details
            $task = $this->staffTaskModel->find($taskId);
            if (!$task) {
                throw new \Exception('Task not found');
            }

            if ($task['status'] !== 'Pending') {
                throw new \Exception('Task is not pending');
            }

            // Get inventory item details
            $inventoryItem = $this->inventoryModel->find($task['item_id']);
            if (!$inventoryItem) {
                throw new \Exception('Inventory item not found');
            }

            // Calculate new stock quantity based on movement type
            $currentStock = $inventoryItem['quantity'];
            $taskQuantity = $task['quantity'];
            $newStock = $currentStock;

            if ($task['movement_type'] === 'IN') {
                // Inbound - add to stock
                $newStock = $currentStock + $taskQuantity;
            } elseif ($task['movement_type'] === 'OUT') {
                // Outbound - remove from stock
                $newStock = $currentStock - $taskQuantity;
                
                if ($newStock < 0) {
                    throw new \Exception('Insufficient stock for outbound operation');
                }
            }

            // Update inventory stock
            $this->inventoryModel->updateStock($task['item_id'], $newStock, $task['warehouse_id']);

            // Update stock movement status
            if ($task['movement_id']) {
                $this->stockMovementModel->updateMovementStatus($task['movement_id'], 'completed');
            }

            // Complete the task
            $notes = $data['notes'] ?? "Task completed via barcode scanning";
            $result = $this->staffTaskModel->completeTask($taskId, $staffId, $notes);

            if (!$result) {
                throw new \Exception('Failed to complete task');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Task completed successfully',
                'task_id' => $taskId,
                'old_stock' => $currentStock,
                'new_stock' => $newStock,
                'movement_type' => $task['movement_type']
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Task completion failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Failed to complete task: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get task by barcode scan
     * Find pending task for scanned item
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getTaskByBarcode()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        if (session('role') !== 'staff') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Staff access required']);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        
        if (!isset($data['barcode']) || !isset($data['warehouse_id'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Barcode and warehouse ID required']);
        }

        $barcode = $data['barcode'];
        $warehouseId = $data['warehouse_id'];

        try {
            // Find inventory item by SKU/barcode - try with warehouse filter first
            $inventoryItem = $this->inventoryModel->findBySkuAndWarehouse($barcode, $warehouseId);
            
            // If not found with warehouse filter, try without warehouse filter as fallback
            if (!$inventoryItem) {
                $inventoryItem = $this->inventoryModel->where('sku', $barcode)->first();
                
                if (!$inventoryItem) {
                    // Also try searching by name (partial match)
                    $inventoryItem = $this->inventoryModel->like('name', $barcode, 'both')->first();
                }
                
                if (!$inventoryItem) {
                    return $this->response->setStatusCode(404)->setJSON([
                        'error' => 'Item not found in inventory',
                        'searched_sku' => $barcode,
                        'warehouse_id' => $warehouseId
                    ]);
                }
            }

            // Find pending task for this item in the specified warehouse
            $tasks = $this->staffTaskModel->where('item_id', $inventoryItem['id'])
                                         ->where('warehouse_id', $warehouseId)
                                         ->where('status', 'Pending')
                                         ->orderBy('created_at', 'ASC')
                                         ->findAll();

            // If no tasks found for this warehouse, try finding tasks for this item in any warehouse
            if (empty($tasks)) {
                $tasks = $this->staffTaskModel->where('item_id', $inventoryItem['id'])
                                             ->where('status', 'Pending')
                                             ->orderBy('created_at', 'ASC')
                                             ->findAll();
            }

            if (empty($tasks)) {
                return $this->response->setStatusCode(404)->setJSON([
                    'error' => 'No pending tasks found for this item',
                    'item' => $inventoryItem,
                    'searched_sku' => $barcode,
                    'warehouse_id' => $warehouseId
                ]);
            }

            // Return the first pending task
            $task = $tasks[0];
            
            return $this->response->setJSON([
                'success' => true,
                'task' => $task,
                'item' => $inventoryItem,
                'message' => "Found pending {$task['movement_type']} task for {$inventoryItem['name']}"
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Task lookup by barcode failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Internal server error',
                'debug_info' => [
                    'barcode' => $barcode,
                    'warehouse_id' => $warehouseId,
                    'error_message' => $e->getMessage()
                ]
            ]);
        }
    }

    /**
     * Get task statistics for dashboard
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getTaskStats()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        try {
            $warehouseId = session('warehouse_id') ?: null;
            $stats = $this->staffTaskModel->getTaskStats($warehouseId);
            return $this->response->setJSON($stats);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get task statistics: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }

    /**
     * Scan item from To-Do list and move to Recent Scans
     * POST api/staff-tasks/scan-item
     */
    public function scanTaskItem()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        if (session('role') !== 'staff') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Staff access required']);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        $userId = session('userID');

        // Validate user ID from session
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'User ID not found in session']);
        }

        if (!isset($data['barcode']) || !isset($data['warehouse_id'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'barcode and warehouse_id required']);
        }

        $barcode = $data['barcode'];
        $warehouseId = (int)$data['warehouse_id'];

        try {
            // First, try to find a pending task for this barcode
            $inventoryItem = $this->inventoryModel->findBySkuAndWarehouse($barcode, $warehouseId);
            if (!$inventoryItem) {
                $inventoryItem = $this->inventoryModel->where('sku', $barcode)->first();
            }

            if (!$inventoryItem) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Item not found in inventory']);
            }

            // Find pending task for this item
            $task = $this->staffTaskModel->where('item_id', $inventoryItem['id'])
                                        ->where('warehouse_id', $warehouseId)
                                        ->where('status', 'Pending')
                                        ->orderBy('created_at', 'ASC')
                                        ->first();

            if (!$task) {
                // No pending task found - just add to recent scans as manual scan
                $movementType = isset($data['movement_type']) ? strtoupper($data['movement_type']) : 'IN';
                $qty = isset($data['quantity']) ? (int)$data['quantity'] : 1;
                
                $scan = $this->recentScanModel->upsertScan($userId, $warehouseId, $barcode, $inventoryItem['name'], $movementType, $qty, $inventoryItem['id']);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Item added to Recent Scans (no pending task found)',
                    'scan' => $scan,
                    'type' => 'manual_scan'
                ]);
            }

            // If this task is an OUT (export) task, validate available stock now
            $taskQty = isset($task['quantity']) ? (int)$task['quantity'] : 1;
            $movementType = strtoupper($task['movement_type'] ?? 'IN');

            if ($movementType === 'OUT') {
                $available = isset($inventoryItem['quantity']) ? (int)$inventoryItem['quantity'] : 0;
                if ($available < $taskQty) {
                    // Insufficient stock: mark task as RED STOCK and mark related stock movement (if any)
                    try {
                        $this->staffTaskModel->update($task['id'], ['status' => 'RED STOCK']);
                        if (!empty($task['movement_id'])) {
                            $this->stockMovementModel->updateMovementStatus($task['movement_id'], 'red_stock');
                        }
                    } catch (\Exception $e) {
                        log_message('error', 'Failed to mark RED STOCK: ' . $e->getMessage());
                    }

                    return $this->response->setStatusCode(409)->setJSON([
                        'success' => false,
                        'error' => 'Insufficient stock',
                        'message' => 'Insufficient stock for export - task marked as RED STOCK',
                        'task_id' => $task['id'] ?? null,
                        'movement_id' => $task['movement_id'] ?? null,
                    ]);
                }
            }

            // Stock is sufficient or task is inbound: move to recent scans without updating inventory yet
            $scan = $this->recentScanModel->upsertScan(
                $userId,
                $warehouseId,
                $barcode,
                $inventoryItem['name'],
                $task['movement_type'],
                $task['quantity'],
                $inventoryItem['id']
            );

            // Mark the task as "scanned" but not completed
            $this->staffTaskModel->update($task['id'], ['status' => 'Scanned']);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Item moved from To-Do to Recent Scans',
                'scan' => $scan,
                'task' => $task,
                'type' => 'task_scan'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to scan task item: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to scan item: ' . $e->getMessage()]);
        }
    }

    /**
     * Add a scanned item to Recent Scans (staging area) - for manual scanning
     * POST api/recent-scans/add
     */
    public function addRecentScan()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        if (session('role') !== 'staff') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Staff access required']);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        $userId = session('userID');

        // Validate user ID from session
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'User ID not found in session']);
        }

        if (!isset($data['barcode']) || !isset($data['warehouse_id']) || !isset($data['movement_type'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'barcode, warehouse_id and movement_type required']);
        }

        $barcode = $data['barcode'];
        $warehouseId = (int)$data['warehouse_id'];
        $movementType = strtoupper($data['movement_type']);
        $qty = isset($data['quantity']) ? (int)$data['quantity'] : 1;

        // Try to resolve inventory item
        $inventoryItem = $this->inventoryModel->findBySkuAndWarehouse($barcode, $warehouseId);
        if (!$inventoryItem) {
            $inventoryItem = $this->inventoryModel->where('sku', $barcode)->first();
        }

        if (!$inventoryItem) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Item not found in inventory']);
        }

        $itemId = $inventoryItem['id'];
        $itemName = $inventoryItem['name'];

        try {
            $scan = $this->recentScanModel->upsertScan($userId, $warehouseId, $barcode, $itemName, $movementType, $qty, $itemId);
            return $this->response->setJSON(['success' => true, 'scan' => $scan]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to add recent scan: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to add recent scan']);
        }
    }

    /**
     * List recent scans for the logged-in user
     * GET api/recent-scans/list
     */
    public function listRecentScans()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $userId = session('userID');

        // Validate user ID from session
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'User ID not found in session']);
        }

        try {
            $scans = $this->recentScanModel->listForUser($userId);
            return $this->response->setJSON(['success' => true, 'scans' => $scans]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to list recent scans: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to list recent scans']);
        }
    }

    /**
     * Remove a recent scan entry
     * DELETE api/recent-scans/remove/(:num)
     */
    public function removeRecentScan($id = null)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $userId = session('userID');
        
        // Validate user ID from session
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'User ID not found in session']);
        }
        
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Scan ID required']);
        }

        $scan = $this->recentScanModel->find($id);
        if (!$scan || $scan['user_id'] != $userId) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Scan not found']);
        }

        try {
            $this->recentScanModel->remove($id);
            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to remove recent scan: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to remove recent scan']);
        }
    }

    /**
     * Save and Update: process pending recent scans for the user
     * This updates inventory and completes associated tasks
     * POST api/recent-scans/save
     */
    public function saveAndUpdateScans()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $userId = session('userID');
        
        // Validate user ID from session
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'User ID not found in session']);
        }
        
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        // Optional: allow selecting specific scan ids to process
        $scanIds = isset($data['scan_ids']) ? (array)$data['scan_ids'] : null;

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $scans = $this->recentScanModel->listForUser($userId);
            if ($scanIds) {
                $scans = array_filter($scans, function($s) use ($scanIds) { return in_array($s['id'], $scanIds); });
            }

            if (empty($scans)) {
                return $this->response->setJSON(['success' => true, 'processed' => [], 'message' => 'No scans to process']);
            }

            $processed = [];
            $taskUpdates = [];

            foreach ($scans as $scan) {
                // Check for duplicate processing
                if ($scan['status'] !== 'Pending') {
                    continue; // Skip already processed scans
                }

                // Resolve inventory item
                $itemId = $scan['item_id'];
                if (!$itemId) {
                    $inv = $this->inventoryModel->where('sku', $scan['item_sku'])->first();
                    $itemId = $inv['id'] ?? null;
                } else {
                    $inv = $this->inventoryModel->find($itemId);
                }

                if (!$inv) {
                    log_message('warning', "Item not found for scan ID {$scan['id']}: {$scan['item_sku']}");
                    continue;
                }

                $currentQty = (float)$inv['quantity'];
                $qty = (int)$scan['quantity'];
                $movement = strtolower($scan['movement_type']); // in/out

                // Calculate new stock quantity
                if ($movement === 'in') {
                    $newQty = $currentQty + $qty;
                } else {
                    $newQty = $currentQty - $qty;
                    if ($newQty < 0) {
                        throw new \Exception("Insufficient stock for item {$scan['item_sku']}. Current: {$currentQty}, Requested: {$qty}");
                    }
                }

                // Update inventory stock
                $this->inventoryModel->updateStock($itemId, $newQty, $scan['warehouse_id']);

                // Create stock movement record
                $movementData = [
                    'transaction_number' => 'SCAN-' . $userId . '-' . time() . '-' . $scan['id'],
                    'order_number' => 'MANUAL-SCAN',
                    'id' => $itemId,
                    'quantity' => $qty,
                    'items_in_progress' => 1,
                    'company_name' => 'WeBuild Construction',
                    'movement_type' => $movement,
                    'location' => 'Warehouse',
                    'status' => 'completed',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $this->stockMovementModel->insert($movementData);

                // Check if this scan was from a task and complete the task
                $relatedTask = $this->staffTaskModel->where('item_id', $itemId)
                                                   ->where('warehouse_id', $scan['warehouse_id'])
                                                   ->where('status', 'Scanned')
                                                   ->where('movement_type', strtoupper($movement))
                                                   ->where('quantity', $qty)
                                                   ->first();

                if ($relatedTask) {
                    // Complete the related staff task
                    $this->staffTaskModel->update($relatedTask['id'], [
                        'status' => 'Completed',
                        'completed_by' => $userId,
                        'completed_at' => date('Y-m-d H:i:s'),
                        'notes' => 'Completed via barcode scanning workflow'
                    ]);

                    // Update movement status if associated
                    if ($relatedTask['movement_id']) {
                        $this->stockMovementModel->updateMovementStatus($relatedTask['movement_id'], 'completed');
                    }

                    $taskUpdates[] = [
                        'task_id' => $relatedTask['id'],
                        'reference_no' => $relatedTask['reference_no']
                    ];
                }

                // Mark scan as processed and remove it
                $this->recentScanModel->update($scan['id'], ['status' => 'Processed']);
                $this->recentScanModel->remove($scan['id']);

                $processed[] = [
                    'scan_id' => $scan['id'],
                    'sku' => $scan['item_sku'],
                    'item_name' => $scan['item_name'],
                    'qty' => $qty,
                    'movement' => $movement,
                    'old_stock' => $currentQty,
                    'new_stock' => $newQty,
                    'task_completed' => !empty($relatedTask)
                ];
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed during saveAndUpdate');
            }

            return $this->response->setJSON([
                'success' => true,
                'processed' => $processed,
                'task_updates' => $taskUpdates,
                'message' => count($processed) . ' item(s) processed and inventory updated'
            ]);

        } catch (\Exception $e) {
            $db->transComplete();
            log_message('error', 'Save and update scans failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to process scans: ' . $e->getMessage()]);
        }
    }

    /**
     * Get staff task history
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getTaskHistory()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        if (session('role') !== 'staff') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Staff access required']);
        }

        try {
            $staffId = session('userID');
            
            // Validate user ID from session
            if (!$staffId) {
                return $this->response->setStatusCode(401)->setJSON(['error' => 'User ID not found in session']);
            }
            
            $limit = $this->request->getGet('limit') ?? 20;
            
            $history = $this->staffTaskModel->getStaffTaskHistory($staffId, $limit);
            
            return $this->response->setJSON([
                'success' => true,
                'history' => $history,
                'total' => count($history)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get task history: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }
}
