<?php
namespace App\Controllers;

use App\Models\WarehouseModel;

class WarehouseController extends BaseController
{
    public function index()
    {
        if (! session()->get('isLoggedIn')) return redirect()->to('/login');
        if (session('role') !== 'manager') return redirect()->to('/login');

        $model = new WarehouseModel();
        $warehouses = $model->orderBy('id','ASC')->findAll();
        return view('dashboard/manager/warehouses', ['warehouses' => $warehouses]);
    }

    // JSON endpoint to list warehouses (manager/staff)
    public function list()
    {
        if (! session()->get('isLoggedIn')) return $this->response->setStatusCode(401);
        $model = new WarehouseModel();
        return $this->response->setJSON($model->orderBy('id','ASC')->findAll());
    }

    public function create()
    {
        if (! session()->get('isLoggedIn') || session('role') !== 'manager') return $this->response->setStatusCode(403);
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        if (empty($data['name'])) return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing name']);
        $model = new WarehouseModel();
        $id = $model->insert(['name' => $data['name'], 'location' => $data['location'] ?? null, 'created_at' => date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['success' => true, 'id' => $id]);
    }
}
