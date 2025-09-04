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
        $items = $model->orderBy('id', 'ASC')->findAll();

        return view('pages/inventory', ['items' => $items]);
    }

    public function create()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $model = new InventoryModel();

        $data = [
            'name'     => $this->request->getPost('name'),
            'sku'      => $this->request->getPost('sku'),
            'category' => $this->request->getPost('category'),
            'location' => $this->request->getPost('location'),
            'quantity' => (int) $this->request->getPost('quantity'),
            // Status will be derived from quantity to keep consistency
            // 0 => out, 1-9 => low, >=10 => in
            'status'   => null,
            'expiry'   => $this->request->getPost('expiry') ?: null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // derive status from quantity
        $qty = (int) $data['quantity'];
        if ($qty <= 0) {
            $data['status'] = 'out';
        } elseif ($qty < 10) {
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
            'quantity' => (int) $this->request->getPost('quantity'),
            'status'   => null,
            'expiry'   => $this->request->getPost('expiry') ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // derive status from quantity (same rules as create)
        $qty = (int) $data['quantity'];
        if ($qty <= 0) {
            $data['status'] = 'out';
        } elseif ($qty < 10) {
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
}
