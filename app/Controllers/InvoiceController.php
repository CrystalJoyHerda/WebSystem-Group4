<?php
namespace App\Controllers;

use App\Models\InvoiceModel;

class InvoiceController extends BaseController
{
    public function list()
    {
        if (! session()->get('isLoggedIn')) return $this->response->setStatusCode(401);
        $model = new InvoiceModel();
        return $this->response->setJSON($model->orderBy('id','DESC')->findAll());
    }

    public function create()
    {
        if (! session()->get('isLoggedIn') || session('role') !== 'manager') return $this->response->setStatusCode(403);
        $data = $this->request->getJSON(true);
        if (! $data) return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        $model = new InvoiceModel();
        $model->insert([
            'reference' => $data['reference'] ?? 'INV-'.time(),
            'amount' => $data['amount'] ?? 0,
            'payable' => $data['payable'] ?? 1,
            'vendor_client' => $data['vendor_client'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'status' => 'OPEN',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return $this->response->setJSON(['success' => true]);
    }
}
