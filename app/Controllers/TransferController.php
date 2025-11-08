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

        $inventory->adjustQuantity($itemId, -$qty);

        // credit destination: try to find an item with same sku in dest warehouse
        $dest = $inventory->builder()->where('sku', $src['sku'])->where('warehouse_id', $to)->get()->getRowArray();
        if ($dest) {
            $inventory->adjustQuantity((int)$dest['id'], $qty);
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

        return $this->response->setJSON(['success' => true]);
    }
}
