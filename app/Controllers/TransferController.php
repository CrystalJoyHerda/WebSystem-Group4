<?php
namespace App\Controllers;

use App\Models\TransferModel;
use App\Models\InventoryModel;
use App\Models\UserWarehouseModel;
use App\Services\AuthorizationService;

class TransferController extends BaseController
{
    protected $session;
    protected $auth;

    public function __construct()
    {
        $this->session = session();
        $this->auth = new AuthorizationService();
    }

    private function guardApi(): ?\CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not logged in']);
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

    private function isAllowedWarehouse(?int $warehouseId, ?array $allowedWarehouseIds): bool
    {
        if ($warehouseId === null || $warehouseId <= 0) {
            return false;
        }
        if ($allowedWarehouseIds === null) {
            return true;
        }
        return in_array($warehouseId, $allowedWarehouseIds, true);
    }

    public function create()
    {
        // JSON endpoint to perform transfer
        if ($deny = $this->requirePermission('transfers.create')) {
            return $deny;
        }
        $data = $this->request->getJSON(true);
        if (! $data) return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing payload']);

        $allowedWarehouseIds = $this->allowedWarehouseIdsOrNull();
        $role = (string) $this->session->get('role');

        $itemId = (int) ($data['item_id'] ?? 0);
        $from = isset($data['from_warehouse_id']) ? (int)$data['from_warehouse_id'] : null;
        $to = isset($data['to_warehouse_id']) ? (int)$data['to_warehouse_id'] : null;
        $qty = (int) ($data['quantity'] ?? 0);

        if ($itemId <= 0 || $qty <= 0 || ! $from || ! $to) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid parameters']);
        }

        if (! $this->isAllowedWarehouse($from, $allowedWarehouseIds) || ! $this->isAllowedWarehouse($to, $allowedWarehouseIds)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        if ($this->auth->isItAdminRole($role)) {
            $wid = $this->currentWarehouseId();
            if ($wid === null) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Please select a warehouse']);
            }
            if ($from !== $wid && $to !== $wid) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
            }
        }

        $inventory = new InventoryModel();
        $transferModel = new TransferModel();
        $threshold = $inventory->getLowStockThreshold();

        $db = \Config\Database::connect();
        $db->transStart();

        // debit source
        $src = $inventory->builder()->where('id', $itemId)->where('warehouse_id', $from)->get()->getRowArray();
        if (! $src) {
            $db->transComplete();
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Source item not found']);
        }
        if ((int)$src['quantity'] < $qty) {
            $db->transComplete();
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Insufficient stock']);
        }

        // Use the new updateStock method instead of adjustQuantity
        $newSourceQty = (int)$src['quantity'] - $qty;
        $inventory->updateStock($itemId, $newSourceQty, $from);

        // credit destination: try to find an item with same sku in dest warehouse
        $dest = $inventory->builder()->where('sku', $src['sku'])->where('warehouse_id', $to)->get()->getRowArray();
        if ($dest) {
            $newDestQty = (int)$dest['quantity'] + $qty;
            $inventory->updateStock((int)$dest['id'], $newDestQty, $to);
        } else {
            // create a new inventory row in destination with the same sku/name
            $new = [
                'name' => $src['name'],
                'sku' => $src['sku'],
                'category' => $src['category'] ?? null,
                'location' => $src['location'] ?? null,
                'warehouse_id' => $to,
                'quantity' => $qty,
                'status' => $qty <= 0 ? 'out' : ($qty <= $threshold ? 'low' : 'in'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            $inventory->insert($new);
        }

        $transferModel->insert([
            'item_id' => $itemId,
            'from_warehouse_id' => $from,
            'to_warehouse_id' => $to,
            'quantity' => $qty,
            'status' => 'pending',
            'created_by' => session('name') ?? session('email'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Transfer failed']);
        }

        return $this->response->setJSON(['success' => true, 'transfer_id' => $db->insertID()]);
    }

    /**
     * Get transfer history
     */
    public function getHistory()
    {
        if ($deny = $this->requirePermission('transfers.view')) {
            return $deny;
        }
        
        $transferModel = new TransferModel();

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
            $transfers = $transferModel->getTransferHistory(50, $warehouseId, $allowedWarehouseIds);
            return $this->response->setJSON($transfers);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }

    /**
     * Get pending transfers (requiring approval)
     */
    public function getPending()
    {
        if ($deny = $this->requirePermission('transfers.approve')) {
            return $deny;
        }
        
        $transferModel = new TransferModel();

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
            $transfers = $transferModel->getPendingTransfers($warehouseId, $allowedWarehouseIds);
            return $this->response->setJSON($transfers);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }

    /**
     * Approve or reject a transfer
     */
    public function approve($transferId)
    {
        if ($deny = $this->requirePermission('transfers.approve')) {
            return $deny;
        }

        $data = $this->request->getJSON(true);
        $action = $data['action'] ?? 'approve'; // approve or reject
        $notes = $data['notes'] ?? '';

        if (! in_array($action, ['approve', 'reject'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid action']);
        }

        $status = $action === 'approve' ? 'approved' : 'rejected';

        $transferModel = new TransferModel();

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
            $row = $transferModel->find((int) $transferId);
            if (! $row) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Transfer not found']);
            }

            $from = isset($row['from_warehouse_id']) ? (int) $row['from_warehouse_id'] : null;
            $to = isset($row['to_warehouse_id']) ? (int) $row['to_warehouse_id'] : null;
            if ($allowedWarehouseIds !== null) {
                if (($from === null || ! in_array($from, $allowedWarehouseIds, true)) && ($to === null || ! in_array($to, $allowedWarehouseIds, true))) {
                    return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
                }
            }
            if ($warehouseId !== null && $from !== $warehouseId && $to !== $warehouseId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
            }

            $approverId = (int) ($this->session->get('userID') ?? 0);
            $result = $transferModel->updateTransferStatus($transferId, $status, $approverId, $notes);
            
            if ($result) {
                return $this->response->setJSON(['success' => true, 'action' => $action, 'status' => $status]);
            } else {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Transfer not found']);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }

    /**
     * Validate transfer request before processing
     */
    private function validateTransferRequest($itemId, $fromWarehouse, $toWarehouse, $quantity)
    {
        $errors = [];

        if ($itemId <= 0) {
            $errors[] = 'Invalid item ID';
        }

        if ($fromWarehouse <= 0) {
            $errors[] = 'Invalid source warehouse';
        }

        if ($toWarehouse <= 0) {
            $errors[] = 'Invalid destination warehouse';
        }

        if ($fromWarehouse === $toWarehouse) {
            $errors[] = 'Source and destination warehouses cannot be the same';
        }

        if ($quantity <= 0) {
            $errors[] = 'Quantity must be greater than 0';
        }

        return $errors;
    }
}
