<?php
namespace App\Controllers;

use App\Models\InventoryModel;

class BarcodeController extends BaseController
{
    // POST /api/barcode/scan
    public function scan()
    {
        if (! session()->get('isLoggedIn')) return $this->response->setStatusCode(401);
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        $code = $data['code'] ?? $this->request->getPost('code');
        $warehouse = isset($data['warehouse_id']) ? (int)$data['warehouse_id'] : null;
        if (empty($code)) return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing code']);

        $inv = new InventoryModel();
        $item = $inv->findBySkuAndWarehouse($code, $warehouse);
        if (! $item) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Item not found', 'code' => $code]);
        }

        return $this->response->setJSON(['item' => $item]);
    }
}
