<?php
namespace App\Controllers;

use App\Models\InventoryModel;

class Inventory extends BaseController
{
    public function index()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $model = new InventoryModel();
        // Show only items for the manager's warehouse (separation by warehouse)
        $warehouseId = session('warehouse_id') ?: null;
        if ($warehouseId) {
            $items = $model->getByWarehouse((int)$warehouseId);
        } else {
            // Fallback to all items with warehouse info if no session warehouse
            $items = $model->getItemsWithWarehouse();
        }

        // Updated path to new location
        return view('dashboard/manager/inventory', ['items' => $items]);
    }

    public function create()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $model = new InventoryModel();
        $threshold = $model->getLowStockThreshold();

        $data = [
            'name'     => $this->request->getPost('name'),
            'sku'      => $this->request->getPost('sku'),
            'category' => $this->request->getPost('category'),
            'location' => $this->request->getPost('location'),
            'warehouse_id' => (int) $this->request->getPost('warehouse_id') ?: null,
            'quantity' => (int) $this->request->getPost('quantity'),
            // Status will be derived from quantity to keep consistency
            // 0 => out, 1-9 => low, >=10 => in
            'status'   => null,
            'expiry'   => $this->request->getPost('expiry') ?: null,
            'version'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // derive status from quantity
        $qty = (int) $data['quantity'];
        if ($qty <= 0) {
            $data['status'] = 'out';
        } elseif ($qty <= $threshold) {
            $data['status'] = 'low';
        } else {
            $data['status'] = 'in';
        }

        $inserted = $model->insert($data);
        if ($inserted === false) {
            session()->setFlashdata('error', 'Unable to add item.');
        } else {
            // Try to include the item name in the flash message
            $itemName = $data['name'] ?? 'Item';
            session()->setFlashdata('success', sprintf('"%s" added to inventory.', $itemName));
        }

        // Redirect to the inventory route using an app-relative URI
        return redirect()->to('/inventory');
    }

    public function delete($id = null)
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $model = new InventoryModel();

        if (empty($id) || ! is_numeric($id)) {
            session()->setFlashdata('error', 'Invalid item id');
            return redirect()->to('/inventory');
        }

        $exists = $model->find($id);
        if (! $exists) {
            session()->setFlashdata('error', 'Item not found');
            return redirect()->to('/inventory');
        }

        $model->delete($id);
        session()->setFlashdata('success', 'Item deleted');
        return redirect()->to('/inventory');
    }

    public function update($id = null)
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $model = new InventoryModel();
        $threshold = $model->getLowStockThreshold();

        if (empty($id) || ! is_numeric($id)) {
            session()->setFlashdata('error', 'Invalid item id');
            return redirect()->to('/inventory');
        }

        $exists = $model->find($id);
        if (! $exists) {
            session()->setFlashdata('error', 'Item not found');
            return redirect()->to('/inventory');
        }

        $data = [
            'name'     => $this->request->getPost('name'),
            'sku'      => $this->request->getPost('sku'),
            'category' => $this->request->getPost('category'),
            'location' => $this->request->getPost('location'),
            'warehouse_id' => (int) $this->request->getPost('warehouse_id') ?: null,
            'quantity' => (int) $this->request->getPost('quantity'),
            'status'   => null,
            'expiry'   => $this->request->getPost('expiry') ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // derive status from quantity (same rules as create)
        $qty = (int) $data['quantity'];
        if ($qty <= 0) {
            $data['status'] = 'out';
        } elseif ($qty <= $threshold) {
            $data['status'] = 'low';
        } else {
            $data['status'] = 'in';
        }

        $updated = $model->update($id, $data);
        if ($updated === false) {
            session()->setFlashdata('error', 'Unable to update item.');
        } else {
            session()->setFlashdata('success', 'Item updated.');
        }

        return redirect()->to('/inventory');
    }

    /**
     * API endpoint to get inventory statistics
     */
    public function getStats()
    {
        if (! session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $model = new InventoryModel();
        $warehouseId = session('warehouse_id') ?: null;

        try {
            if ($warehouseId) {
                $total = count($model->getByWarehouse((int)$warehouseId));
                $lowStock = count($model->getLowStockItems((int)$warehouseId));
                $outOfStock = $model->where('warehouse_id', (int)$warehouseId)->where('quantity <=', 0)->countAllResults();
            } else {
                $total = $model->countAll();
                $lowStock = count($model->getLowStockItems());
                $outOfStock = $model->where('quantity <=', 0)->countAllResults();
            }

            return $this->response->setJSON([
                'total' => $total,
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }

    /**
     * API endpoint to get inventory by warehouse
     */
    public function getByWarehouse($warehouseId)
    {
        if (! session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        if (! is_numeric($warehouseId)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid warehouse ID']);
        }

        $model = new InventoryModel();
        
        try {
            $items = $model->getByWarehouse($warehouseId);
            return $this->response->setJSON($items);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }

    /**
     * API endpoint to get low stock items
     */
    public function getLowStock()
    {
        if (! session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $model = new InventoryModel();
        $warehouseId = session('warehouse_id') ?: null;

        try {
            $items = $model->getLowStockItems($warehouseId !== null ? (int)$warehouseId : null);
            return $this->response->setJSON($items);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }

    /**
     * API endpoint to get all items with warehouse information
     */
    public function getAllWithWarehouse()
    {
        if (! session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $model = new InventoryModel();
        $warehouseId = session('warehouse_id') ?: null;

        try {
            if ($warehouseId) {
                $items = $model->getByWarehouse((int)$warehouseId);
            } else {
                $items = $model->getItemsWithWarehouse();
            }
            return $this->response->setJSON($items);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }

    /**
     * API endpoint to update stock quantity (for barcode scanning)
     */
    public function updateStock()
    {
        if (! session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        // Only allow staff and managers to update stock
        $role = session('role');
        if (! in_array($role, ['manager', 'staff'])) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Insufficient permissions']);
        }

        $data = $this->request->getJSON(true);
        
        if (! $data || ! isset($data['item_id']) || ! isset($data['new_quantity'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing required fields']);
        }

        $itemId = (int) $data['item_id'];
        $newQuantity = (int) $data['new_quantity'];
        $warehouseId = isset($data['warehouse_id']) ? (int) $data['warehouse_id'] : null;
        $scanType = $data['scan_type'] ?? 'manual';
        $quantityChanged = (int) ($data['quantity_changed'] ?? 0);

        if ($newQuantity < 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Quantity cannot be negative']);
        }

        $model = new InventoryModel();
        
        try {
            // Verify item exists
            $item = $model->find($itemId);
            if (! $item) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Item not found']);
            }

            // Update stock using the model method
            $updated = $model->updateStock($itemId, $newQuantity, $warehouseId);
            
            if ($updated) {
                // Log the stock movement (optional - could be implemented later)
                // $this->logStockMovement($itemId, $item['quantity'], $newQuantity, $scanType);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Stock updated successfully',
                    'item_id' => $itemId,
                    'old_quantity' => $item['quantity'],
                    'new_quantity' => $newQuantity,
                    'change' => $quantityChanged,
                    'scan_type' => $scanType
                ]);
            } else {
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to update stock']);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error: ' . $e->getMessage()]);
        }
    }
}
