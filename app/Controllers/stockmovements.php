<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\StockMovementModel;
use App\Models\StaffTaskModel;
use App\Models\InventoryModel;
use App\Models\InboundReceiptModel;
use App\Models\OutboundReceiptModel;
use App\Models\UserWarehouseModel;
use App\Services\AuthorizationService;

class stockmovements extends Controller 
{
    protected $stockMovementModel;
    protected $staffTaskModel;
    protected $inventoryModel;
    protected $inboundReceiptModel;
    protected $outboundReceiptModel;
    protected $session;
    protected $auth;

    public function __construct()
    {
        $this->session = session();
        $this->auth = new AuthorizationService();
        $this->stockMovementModel = new StockMovementModel();
        $this->staffTaskModel = new StaffTaskModel();
        $this->inventoryModel = new InventoryModel();
        $this->inboundReceiptModel = new InboundReceiptModel();
        $this->outboundReceiptModel = new OutboundReceiptModel();
    }

    private function guardApi(): ?\CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }
        return null;
    }

    private function requirePermission(string $permission): ?\CodeIgniter\HTTP\ResponseInterface
    {
        if ($deny = $this->guardApi()) {
            return $deny;
        }
        if (! $this->auth->hasPermission($this->session->get('role'), $permission)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }
        return null;
    }

    private function currentWarehouseId(): ?int
    {
        $id = $this->session->get('currentWarehouseId');
        if ($id === null || $id === '' || ! is_numeric($id)) {
            return null;
        }
        $id = (int) $id;
        return $id > 0 ? $id : null;
    }

    private function allowedWarehouseIdsOrNull(): ?array
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('user_warehouses')) {
            return null;
        }

        $userId = (int) ($this->session->get('userID') ?? 0);
        if ($userId <= 0) {
            return null;
        }

        $uw = new UserWarehouseModel();
        $rows = $uw->select('warehouse_id')->where('user_id', $userId)->findAll();
        if (! $rows) {
            return null;
        }

        $ids = [];
        foreach ($rows as $r) {
            $wid = (int) ($r['warehouse_id'] ?? 0);
            if ($wid > 0) {
                $ids[] = $wid;
            }
        }
        $ids = array_values(array_unique($ids));
        return $ids !== [] ? $ids : null;
    }

    /**
     * Approve an inbound receipt by receipt ID
     * Creates stock movement record and staff task for barcode scanning
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function approveInboundReceipt($receiptId = null)
    {
        if ($deny = $this->requirePermission('inbound.approve')) {
            return $deny;
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

        $managerId = $this->session->get('userID');
        
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

            $allowedWarehouseIds = $this->allowedWarehouseIdsOrNull();
            $role = (string) $this->session->get('role');
            $receiptWarehouseId = isset($receiptWithItems['warehouse_id']) ? (int) $receiptWithItems['warehouse_id'] : null;

            if ($allowedWarehouseIds !== null && ($receiptWarehouseId === null || ! in_array($receiptWarehouseId, $allowedWarehouseIds, true))) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
            }

            if ($this->auth->isItAdminRole($role)) {
                $wid = $this->currentWarehouseId();
                if ($wid === null) {
                    return $this->response->setStatusCode(400)->setJSON(['error' => 'Please select a warehouse']);
                }
                if ($receiptWarehouseId !== null && $receiptWarehouseId !== $wid) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
            }

            $db = \Config\Database::connect();
            $db->transStart();

            $createdTasks = [];
            $referenceNo = $receiptWithItems['reference_no'];
            $itemData = $receiptWithItems['items'];

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
        if ($deny = $this->requirePermission('outbound.approve')) {
            return $deny;
        }

        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        if (!$receiptId && isset($data['receipt_id'])) {
            $receiptId = $data['receipt_id'];
        }

        if (!$receiptId && isset($data['reference_no'])) {
            $receipt = $this->outboundReceiptModel->where('reference_no', $data['reference_no'])->first();
            $receiptId = $receipt['id'] ?? null;
        }

        if (!$receiptId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Receipt ID or reference number required']);
        }

        $managerId = $this->session->get('userID');
        if (!$managerId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Manager ID not found in session']);
        }

        try {
            $receiptWithItems = $this->outboundReceiptModel->getReceiptWithItems($receiptId);
            if (!$receiptWithItems) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Receipt not found']);
            }
            if ($receiptWithItems['status'] !== 'Pending') {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Receipt is not pending approval']);
            }

            $allowedWarehouseIds = $this->allowedWarehouseIdsOrNull();
            $role = (string) $this->session->get('role');
            $receiptWarehouseId = isset($receiptWithItems['warehouse_id']) ? (int) $receiptWithItems['warehouse_id'] : null;

            if ($allowedWarehouseIds !== null && ($receiptWarehouseId === null || ! in_array($receiptWarehouseId, $allowedWarehouseIds, true))) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
            }

            if ($this->auth->isItAdminRole($role)) {
                $wid = $this->currentWarehouseId();
                if ($wid === null) {
                    return $this->response->setStatusCode(400)->setJSON(['error' => 'Please select a warehouse']);
                }
                if ($receiptWarehouseId !== null && $receiptWarehouseId !== $wid) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
            }

            $db = \Config\Database::connect();
            $db->transStart();

            $createdTasks = [];
            $referenceNo = $receiptWithItems['reference_no'];
            $itemData = $receiptWithItems['items'];

            foreach ($itemData as $item) {
                $inventoryItem = $this->inventoryModel->find($item['item_id']);
                if (!$inventoryItem || (int) $inventoryItem['quantity'] < (int) $item['quantity']) {
                    throw new \Exception("Insufficient stock for item {$item['item_name']}. Available: " . ((int) ($inventoryItem['quantity'] ?? 0)));
                }

                $movementData = [
                    'transaction_number' => 'TXN-' . time() . '-' . rand(100, 999),
                    'order_number' => $referenceNo,
                    'id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'movement_type' => 'out',
                    'company_name' => $receiptWithItems['customer_name'] ?? 'External Customer',
                    'location' => $item['warehouse_name'] ?? 'Warehouse',
                    'status' => 'approved',
                    'items_in_progress' => 1
                ];

                $movementId = $this->stockMovementModel->createMovement($movementData);
                if (!$movementId) {
                    throw new \Exception("Failed to create stock movement for item {$item['item_name']}");
                }

                $taskData = [
                    'movement_id' => $movementId,
                    'reference_no' => $referenceNo,
                    'warehouse_id' => $item['warehouse_id'],
                    'item_id' => $item['item_id'],
                    'item_name' => $item['item_name'],
                    'item_sku' => $item['item_sku'] ?? '',
                    'quantity' => $item['quantity'],
                    'movement_type' => 'OUT',
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

            $this->outboundReceiptModel->approveReceipt($receiptId, $managerId);
            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
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

    public function getMovementHistory()
    {
        if ($deny = $this->guardApi()) {
            return $deny;
        }

        try {
            $movements = $this->stockMovementModel->getMovementHistory(100);
            return $this->response->setJSON($movements);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get movement history: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }

    public function getPendingMovements()
    {
        if ($deny = $this->guardApi()) {
            return $deny;
        }

        try {
            $movements = $this->stockMovementModel->getPendingMovements();
            return $this->response->setJSON($movements);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get pending movements: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }

    public function getPendingInboundReceipts()
    {
        if ($deny = $this->requirePermission('inbound.approve')) {
            return $deny;
        }

        $allowedWarehouseIds = $this->allowedWarehouseIdsOrNull();
        $role = (string) $this->session->get('role');
        $warehouseId = null;
        if ($this->auth->isItAdminRole($role)) {
            $warehouseId = $this->currentWarehouseId();
            if ($warehouseId === null) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Please select a warehouse']);
            }
        }

        try {
            $receipts = $this->inboundReceiptModel->getPendingReceipts($warehouseId, $allowedWarehouseIds);
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

    public function getPendingOutboundReceipts()
    {
        if ($deny = $this->requirePermission('outbound.approve')) {
            return $deny;
        }

        $allowedWarehouseIds = $this->allowedWarehouseIdsOrNull();
        $role = (string) $this->session->get('role');
        $warehouseId = null;
        if ($this->auth->isItAdminRole($role)) {
            $warehouseId = $this->currentWarehouseId();
            if ($warehouseId === null) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Please select a warehouse']);
            }
        }

        try {
            $receipts = $this->outboundReceiptModel->getPendingReceipts($warehouseId, $allowedWarehouseIds);
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