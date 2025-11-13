<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\StaffTaskModel;
use App\Models\StockMovementModel;
use App\Models\InventoryModel;

class StaffTaskController extends Controller
{
    protected $staffTaskModel;
    protected $stockMovementModel;
    protected $inventoryModel;

    public function __construct()
    {
        $this->staffTaskModel = new StaffTaskModel();
        $this->stockMovementModel = new StockMovementModel();
        $this->inventoryModel = new InventoryModel();
    }

    /**
     * Get pending tasks for staff dashboard
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
            $tasks = $this->staffTaskModel->getPendingTasks($warehouseId);
            
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
        $staffId = session('user_id');

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
            // Find inventory item by SKU/barcode
            $inventoryItem = $this->inventoryModel->findBySkuAndWarehouse($barcode, $warehouseId);
            
            if (!$inventoryItem) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Item not found']);
            }

            // Find pending task for this item
            $tasks = $this->staffTaskModel->where('item_id', $inventoryItem['id'])
                                         ->where('warehouse_id', $warehouseId)
                                         ->where('status', 'Pending')
                                         ->orderBy('created_at', 'ASC')
                                         ->findAll();

            if (empty($tasks)) {
                return $this->response->setStatusCode(404)->setJSON([
                    'error' => 'No pending tasks found for this item',
                    'item' => $inventoryItem
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
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
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
            $stats = $this->staffTaskModel->getTaskStats();
            return $this->response->setJSON($stats);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get task statistics: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
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
            $staffId = session('user_id');
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