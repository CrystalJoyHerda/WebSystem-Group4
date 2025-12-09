<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\StockMovementModel;
use App\Models\StaffTaskModel;
use App\Models\InventoryModel;
use App\Models\InboundReceiptModel;
use App\Models\OutboundReceiptModel;

class stockmovements extends Controller 
{
    protected $stockMovementModel;
    protected $staffTaskModel;
    protected $inventoryModel;
    protected $inboundReceiptModel;
    protected $outboundReceiptModel;

    public function __construct()
    {
        $this->stockMovementModel = new StockMovementModel();
        $this->staffTaskModel = new StaffTaskModel();
        $this->inventoryModel = new InventoryModel();
        $this->inboundReceiptModel = new InboundReceiptModel();
        $this->outboundReceiptModel = new OutboundReceiptModel();
    }

    /**
     * Approve an inbound receipt by receipt ID
     * Creates stock movement record and staff task for barcode scanning
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function approveInboundReceipt($receiptId = null)
    {
        // Check authentication and permissions
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        if (session('role') !== 'manager') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Manager access required']);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        
        // Support both receipt ID from URL parameter and request data
        if (!$receiptId && isset($data['receipt_id'])) {
            $receiptId = $data['receipt_id'];
        }
        
        // Also support legacy reference_no approach for backward compatibility
        if (!$receiptId && isset($data['reference_no'])) {
            $receipt = $this->inboundReceiptModel->where('reference_no', $data['reference_no'])->first();
            $receiptId = $receipt['id'] ?? null;
        }

        if (!$receiptId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Receipt ID or reference number required']);
        }

        $managerId = session('userID');
        
        // Validate user ID from session
        if (!$managerId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Manager ID not found in session']);
        }

        try {
            // Get receipt with items
            $receiptWithItems = $this->inboundReceiptModel->getReceiptWithItems($receiptId);
            
            if (!$receiptWithItems) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Receipt not found']);
            }

            if ($receiptWithItems['status'] !== 'Pending') {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Receipt is not pending approval']);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            $createdTasks = [];
            $referenceNo = $receiptWithItems['reference_no'];
            $itemData = $receiptWithItems['items'];

            $managerWarehouse = session('warehouse_id') ?: null;
            foreach ($itemData as $item) {
                // 1. Create stock movement record
                $movementData = [
                    'transaction_number' => 'TXN-' . time() . '-' . rand(100, 999),
                    'order_number' => $referenceNo,
                    'id' => $item['item_id'], // inventory item id
                    'quantity' => $item['quantity'],
                    'movement_type' => 'in', // inbound
                    'company_name' => $receiptWithItems['supplier_name'] ?? 'External Supplier',
                    'location' => $item['warehouse_name'] ?? 'Warehouse',
                    'status' => 'approved', // Manager approved
                    'items_in_progress' => 1,
                    'warehouse_id' => $managerWarehouse ?? ($item['warehouse_id'] ?? null)
                ];

                $movementId = $this->stockMovementModel->createMovement($movementData);

                if (!$movementId) {
                    throw new \Exception("Failed to create stock movement for item {$item['item_name']}");
                }

                // 2. Create staff task for barcode scanning
                $taskData = [
                    'movement_id' => $movementId,
                    'reference_no' => $referenceNo,
                    'warehouse_id' => $managerWarehouse ?? ($item['warehouse_id'] ?? null),
                    'item_id' => $item['item_id'],
                    'item_name' => $item['item_name'],
                    'item_sku' => $item['item_sku'] ?? '',
                    'quantity' => $item['quantity'],
                    'movement_type' => 'IN', // Staff needs to scan IN
                    'assigned_by' => $managerId,
                    'notes' => "Inbound receipt approved - scan items to confirm receipt"
                ];

                $taskId = $this->staffTaskModel->createTask($taskData);

                if (!$taskId) {
                    throw new \Exception("Failed to create staff task for item {$item['item_name']}");
                }

                $createdTasks[] = [
                    'movement_id' => $movementId,
                    'task_id' => $taskId,
                    'item_name' => $item['item_name']
                ];
            }

            // 3. Mark receipt as approved
            $this->inboundReceiptModel->approveReceipt($receiptId, $managerId);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception("Transaction failed");
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Inbound receipt {$referenceNo} approved successfully",
                'created_tasks' => $createdTasks,
                'tasks_count' => count($createdTasks),
                'receipt_id' => $receiptId
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Inbound receipt approval failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Failed to approve inbound receipt: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Approve an outbound receipt by receipt ID
     * Creates stock movement record and staff task for barcode scanning
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function approveOutboundReceipt($receiptId = null)
    {
        // Check authentication and permissions
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        if (session('role') !== 'manager') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Manager access required']);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        
        // Support both receipt ID from URL parameter and request data
        if (!$receiptId && isset($data['receipt_id'])) {
            $receiptId = $data['receipt_id'];
        }
        
        // Also support legacy reference_no approach for backward compatibility
        if (!$receiptId && isset($data['reference_no'])) {
            $receipt = $this->outboundReceiptModel->where('reference_no', $data['reference_no'])->first();
            $receiptId = $receipt['id'] ?? null;
        }

        if (!$receiptId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Receipt ID or reference number required']);
        }

        $managerId = session('userID');
        
        // Validate user ID from session
        if (!$managerId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Manager ID not found in session']);
        }

        try {
            // Get receipt with items
            $receiptWithItems = $this->outboundReceiptModel->getReceiptWithItems($receiptId);
            
            if (!$receiptWithItems) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Receipt not found']);
            }

            if ($receiptWithItems['status'] !== 'Pending') {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Receipt is not pending approval']);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            $createdTasks = [];
            $referenceNo = $receiptWithItems['reference_no'];
            $itemData = $receiptWithItems['items'];

            $managerWarehouse = session('warehouse_id') ?: null;
            foreach ($itemData as $item) {
                // Validate stock availability
                $inventoryItem = $this->inventoryModel->find($item['item_id']);
                if (!$inventoryItem || $inventoryItem['quantity'] < $item['quantity']) {
                    throw new \Exception("Insufficient stock for item {$item['item_name']}. Available: " . ($inventoryItem['quantity'] ?? 0));
                }

                // 1. Create stock movement record
                $movementData = [
                    'transaction_number' => 'TXN-' . time() . '-' . rand(100, 999),
                    'order_number' => $referenceNo,
                    'id' => $item['item_id'], // inventory item id
                    'quantity' => $item['quantity'],
                    'movement_type' => 'out', // outbound
                    'company_name' => $receiptWithItems['customer_name'] ?? 'External Customer',
                    'location' => $item['warehouse_name'] ?? 'Warehouse',
                    'status' => 'approved', // Manager approved
                    'items_in_progress' => 1,
                    'warehouse_id' => $managerWarehouse ?? ($item['warehouse_id'] ?? null)
                ];

                $movementId = $this->stockMovementModel->createMovement($movementData);

                if (!$movementId) {
                    throw new \Exception("Failed to create stock movement for item {$item['item_name']}");
                }

                // 2. Create staff task for barcode scanning
                $taskData = [
                    'movement_id' => $movementId,
                    'reference_no' => $referenceNo,
                    'warehouse_id' => $managerWarehouse ?? ($item['warehouse_id'] ?? null),
                    'item_id' => $item['item_id'],
                    'item_name' => $item['item_name'],
                    'item_sku' => $item['item_sku'] ?? '',
                    'quantity' => $item['quantity'],
                    'movement_type' => 'OUT', // Staff needs to scan OUT
                    'assigned_by' => $managerId,
                    'notes' => "Outbound shipment approved - scan items to confirm dispatch"
                ];

                $taskId = $this->staffTaskModel->createTask($taskData);

                if (!$taskId) {
                    throw new \Exception("Failed to create staff task for item {$item['item_name']}");
                }

                $createdTasks[] = [
                    'movement_id' => $movementId,
                    'task_id' => $taskId,
                    'item_name' => $item['item_name']
                ];
            }

            // 3. Mark receipt as approved
            $this->outboundReceiptModel->approveReceipt($receiptId, $managerId);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception("Transaction failed");
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Outbound shipment {$referenceNo} approved successfully",
                'created_tasks' => $createdTasks,
                'tasks_count' => count($createdTasks),
                'receipt_id' => $receiptId
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Outbound receipt approval failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Failed to approve outbound shipment: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get movement history for display
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getMovementHistory()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        try {
            $warehouseId = session('warehouse_id') ?: null;
            $movements = $this->stockMovementModel->getMovementHistoryByWarehouse(100, $warehouseId);
            return $this->response->setJSON($movements);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get movement history: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }

    /**
     * Get pending movements
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getPendingMovements()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        try {
            $warehouseId = session('warehouse_id') ?: null;
            $movements = $this->stockMovementModel->getByTypeAndStatusAndWarehouse(null, 'pending', $warehouseId);
            return $this->response->setJSON($movements);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get pending movements: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }

    /**
     * Get pending inbound receipts
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getPendingInboundReceipts()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        if (session('role') !== 'manager') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Manager access required']);
        }

        try {
            $receipts = $this->inboundReceiptModel->getPendingReceipts();
            
            // Enrich with item details
            foreach ($receipts as &$receipt) {
                $receiptWithItems = $this->inboundReceiptModel->getReceiptWithItems($receipt['id']);
                $receipt['items'] = $receiptWithItems['items'] ?? [];
                $receipt['items_summary'] = $this->generateItemsSummary($receiptWithItems['items'] ?? []);
            }
            
            return $this->response->setJSON($receipts);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get pending inbound receipts: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }

    /**
     * Get pending outbound receipts
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getPendingOutboundReceipts()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        if (session('role') !== 'manager') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Manager access required']);
        }

        try {
            $receipts = $this->outboundReceiptModel->getPendingReceipts();
            
            // Enrich with item details
            foreach ($receipts as &$receipt) {
                $receiptWithItems = $this->outboundReceiptModel->getReceiptWithItems($receipt['id']);
                $receipt['items'] = $receiptWithItems['items'] ?? [];
                $receipt['items_summary'] = $this->generateItemsSummary($receiptWithItems['items'] ?? []);
            }
            
            return $this->response->setJSON($receipts);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get pending outbound receipts: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }

    /**
     * Create an outbound movement (from manager modal) and create staff task
     */
    public function createOutbound()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        if (session('role') !== 'manager') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Manager access required']);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        // Required fields
        $required = ['product_id', 'quantity', 'destination'];
        foreach ($required as $f) {
            if (empty($data[$f])) {
                return $this->response->setStatusCode(400)->setJSON(['error' => "Missing required field: {$f}"]);
            }
        }

        $productId = (int) $data['product_id'];
        $quantity = (int) $data['quantity'];
        $destination = trim($data['destination']);
        $productCode = $data['product_code'] ?? '';
        $date = $data['date'] ?? date('Y-m-d');
        $remarks = $data['remarks'] ?? null;

        $managerId = session('userID');

        try {
            $db = \Config\Database::connect();
            $db->transStart();

            // Verify product exists in inventory
            $item = $this->inventoryModel->find($productId);
            if (! $item) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Product not found']);
            }

            // Build a reference/order number for this outbound (manager-created)
            $referenceNo = 'OUT-' . date('Ymd') . '-' . rand(1000, 9999);

            // Create stock movement
            $movementData = [
                'transaction_number' => 'TXN-' . time() . '-' . rand(100, 999),
                'order_number' => $referenceNo,
                'id' => $productId,
                'quantity' => $quantity,
                'movement_type' => 'out',
                'company_name' => $destination,
                'location' => $destination,
                'status' => 'pending',
                'items_in_progress' => 1
            ];

            $movementId = $this->stockMovementModel->createMovement($movementData);
            if (! $movementId) throw new \Exception('Failed to create stock movement');

            // Create staff task for outbound packing
            $taskData = [
                'movement_id' => $movementId,
                'reference_no' => $referenceNo,
                'warehouse_id' => $item['warehouse_id'] ?? null,
                'item_id' => $productId,
                'item_name' => $item['name'] ?? $item['item_name'] ?? ('Item ' . $productId),
                'item_sku' => $productCode ?: ($item['sku'] ?? ''),
                'quantity' => $quantity,
                'movement_type' => 'OUT',
                'assigned_by' => $managerId,
                'notes' => $remarks ?? 'Created via manager outbound form'
            ];

            $taskId = $this->staffTaskModel->createTask($taskData);
            if (! $taskId) throw new \Exception('Failed to create staff task');

            $db->transComplete();
            if ($db->transStatus() === false) throw new \Exception('Transaction failed');

            // Return created movement and task so frontends can refresh immediately
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Outbound created and task assigned',
                'movement_id' => $movementId,
                'task_id' => $taskId,
                'reference_no' => $referenceNo
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Create outbound failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to create outbound: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate a summary of items for display
     */
    private function generateItemsSummary($items)
    {
        if (empty($items)) return 'No items';
        
        if (count($items) === 1) {
            $item = $items[0];
            return "{$item['item_name']} — qty {$item['quantity']}";
        }
        
        return count($items) . ' items, total qty: ' . array_sum(array_column($items, 'quantity'));
    }
}