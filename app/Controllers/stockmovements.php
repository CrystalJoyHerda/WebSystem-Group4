<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\StockMovementModel;
use App\Models\StaffTaskModel;
use App\Models\InventoryModel;

class stockmovements extends Controller 
{
    protected $stockMovementModel;
    protected $staffTaskModel;
    protected $inventoryModel;

    public function __construct()
    {
        $this->stockMovementModel = new StockMovementModel();
        $this->staffTaskModel = new StaffTaskModel();
        $this->inventoryModel = new InventoryModel();
    }

    /**
     * Approve an inbound receipt
     * Creates stock movement record and staff task for barcode scanning
     * 
     * @param string $receiptId Receipt reference (e.g., PO-1234)
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function approveInboundReceipt()
    {
        // Check authentication and permissions
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        if (session('role') !== 'manager') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Manager access required']);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        
        // Validate required fields
        if (!isset($data['reference_no']) || !isset($data['item_data'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing required fields']);
        }

        $referenceNo = $data['reference_no']; // e.g., PO-1234
        $itemData = $data['item_data']; // Array of items in the receipt
        $managerId = session('user_id');

        try {
            $db = \Config\Database::connect();
            $db->transStart();

            $createdTasks = [];

            foreach ($itemData as $item) {
                // 1. Create stock movement record
                $movementData = [
                    'transaction_number' => 'TXN-' . time() . '-' . rand(100, 999),
                    'order_number' => $referenceNo,
                    'id' => $item['item_id'], // inventory item id
                    'quantity' => $item['quantity'],
                    'movement_type' => 'in', // inbound
                    'company_name' => $item['supplier'] ?? 'External Supplier',
                    'location' => $item['warehouse_name'] ?? 'Warehouse',
                    'status' => 'approved', // Manager approved
                    'items_in_progress' => 1
                ];

                $movementId = $this->stockMovementModel->createMovement($movementData);

                if (!$movementId) {
                    throw new \Exception("Failed to create stock movement for item {$item['item_name']}");
                }

                // 2. Create staff task for barcode scanning
                $taskData = [
                    'movement_id' => $movementId,
                    'reference_no' => $referenceNo,
                    'warehouse_id' => $item['warehouse_id'],
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

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception("Transaction failed");
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Inbound receipt {$referenceNo} approved successfully",
                'created_tasks' => $createdTasks,
                'tasks_count' => count($createdTasks)
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Inbound receipt approval failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Failed to approve inbound receipt: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Approve an outbound receipt
     * Creates stock movement record and staff task for barcode scanning
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function approveOutboundReceipt()
    {
        // Check authentication and permissions
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        if (session('role') !== 'manager') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Manager access required']);
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        
        // Validate required fields
        if (!isset($data['reference_no']) || !isset($data['item_data'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing required fields']);
        }

        $referenceNo = $data['reference_no']; // e.g., SO-5678
        $itemData = $data['item_data']; // Array of items in the shipment
        $managerId = session('user_id');

        try {
            $db = \Config\Database::connect();
            $db->transStart();

            $createdTasks = [];

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
                    'company_name' => $item['customer'] ?? 'External Customer',
                    'location' => $item['warehouse_name'] ?? 'Warehouse',
                    'status' => 'approved', // Manager approved
                    'items_in_progress' => 1
                ];

                $movementId = $this->stockMovementModel->createMovement($movementData);

                if (!$movementId) {
                    throw new \Exception("Failed to create stock movement for item {$item['item_name']}");
                }

                // 2. Create staff task for barcode scanning
                $taskData = [
                    'movement_id' => $movementId,
                    'reference_no' => $referenceNo,
                    'warehouse_id' => $item['warehouse_id'],
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

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception("Transaction failed");
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Outbound shipment {$referenceNo} approved successfully",
                'created_tasks' => $createdTasks,
                'tasks_count' => count($createdTasks)
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
            $movements = $this->stockMovementModel->getMovementHistory(100);
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
            $movements = $this->stockMovementModel->getPendingMovements();
            return $this->response->setJSON($movements);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get pending movements: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }
}