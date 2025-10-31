<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class WorkforceController extends BaseController
{
    public function index()
    {
        return view('dashboard/manager/workforcemanagement');
    }

    public function listUsers()
    {
        $model = new UserModel();
        $users = $model->select('id, name, email, role, created_at, updated_at')->orderBy('id', 'ASC')->findAll();
        // ensure password not returned
        return $this->response->setJSON($users);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        if (! $data || empty($data['name']) || empty($data['email'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing name or email']);
        }

        $model = new UserModel();

        $insertData = [
            'name'  => $data['name'],
            'email' => $data['email'],
            'role'  => $data['role'] ?? 'staff',
        ];

        if (! empty($data['password'])) {
            $insertData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $insertId = $model->insert($insertData);

        if ($insertId === false) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Insert failed']);
        }

        return $this->response->setJSON(['success' => true, 'id' => $insertId]);
    }

    public function update($id = null)
    {
        if (empty($id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing id']);
        }

        $data = $this->request->getJSON(true);
        if (! $data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        }

        $updateData = [];
        if (isset($data['name'])) { $updateData['name'] = $data['name']; }
        if (isset($data['email'])) { $updateData['email'] = $data['email']; }
        if (isset($data['role']))  { $updateData['role'] = $data['role']; }

        if (! empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $model = new UserModel();
        $ok = $model->update($id, $updateData);

        if ($ok === false) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Update failed']);
        }

        return $this->response->setJSON(['success' => true]);
    }

    public function delete($id = null)
    {
        if (empty($id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing id']);
        }

        $model = new UserModel();
        $model->delete($id);

        return $this->response->setJSON(['success' => true]);
    }
}