<?php
namespace App\Controllers;

use App\Models\TransferModel;
use App\Models\InventoryModel;

class TransferController extends BaseController
{
    public function create()
    {
        // JSON endpoint to perform transfer
        if (! session()->get('isLoggedIn')) return $this->response->setStatusCode(401);
        $data = $this->request->getJSON(true);
        if (! $data) return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing payload']);

        // only staff and manager allowed
        $role = session('role');
        if (! in_array($role, ['manager','staff'])) return $this->response->setStatusCode(403);

        $itemId = (int) ($data['item_id'] ?? 0);
        $from = isset($data['from_warehouse_id']) ? (int)$data['from_warehouse_id'] : null;
        $to = isset($data['to_warehouse_id']) ? (int)$data['to_warehouse_id'] : null;
        $qty = (int) ($data['quantity'] ?? 0);

        if ($itemId <= 0 || $qty <= 0 || ! $from || ! $to) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid parameters']);
        }

        $inventory = new InventoryModel();
        $transferModel = new TransferModel();

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
                'status' => $qty <= 0 ? 'out' : ($qty < 10 ? 'low' : 'in'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            $inventory->insert($new);
        }

        $transferModel->insert([
            'item_id' => $itemId,
            'from_warehouse_id' => $from,
            'to_warehouse_id' => $to,
            'quantity' => $qty,
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
        if (! session()->get('isLoggedIn')) return $this->response->setStatusCode(401);
        
        $transferModel = new TransferModel();
        
        try {
            $transfers = $transferModel->getTransferHistory();
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
        if (! session()->get('isLoggedIn')) return $this->response->setStatusCode(401);
        
        // Only managers can view pending transfers
        if (session('role') !== 'manager') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Manager access required']);
        }
        
        $transferModel = new TransferModel();
        
        try {
            $transfers = $transferModel->getPendingTransfers();
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
        if (! session()->get('isLoggedIn')) return $this->response->setStatusCode(401);
        
        // Only managers can approve transfers
        if (session('role') !== 'manager') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Manager access required']);
        }

        $data = $this->request->getJSON(true);
        $action = $data['action'] ?? 'approve'; // approve or reject
        $notes = $data['notes'] ?? '';

        if (! in_array($action, ['approve', 'reject'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid action']);
        }

        $transferModel = new TransferModel();
        
        try {
            $result = $transferModel->updateTransferStatus($transferId, $action, session('user_id'), $notes);
            
            if ($result) {
                return $this->response->setJSON(['success' => true, 'action' => $action]);
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
