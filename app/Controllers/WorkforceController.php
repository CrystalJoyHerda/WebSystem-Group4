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

        // log incoming payload for debugging role persistence issues
        if (function_exists('log_message')) {
            log_message('debug', '[WorkforceController::create] incoming: ' . json_encode($data));
        }

        // allowed role keys (UI-facing)
        $allowedRoles = [
            'manager','staff','inventory_auditor','procurement_officer',
            'accounts_payable','accounts_receivable','it_administrator','topmanagement','admin'
        ];

        // mapping from UI key -> DB enum value (migration currently uses space-separated labels)
        $roleMap = [
            'manager' => 'manager',
            'staff' => 'staff',
            'inventory_auditor' => 'inventory auditor',
            'procurement_officer' => 'procurement officer',
            'accounts_payable' => 'accounts payable',
            'accounts_receivable' => 'accounts receivable',
            'it_administrator' => 'IT administrator',
            'topmanagement' => 'topmanagement',
            'admin' => 'admin'
        ];

        $model = new UserModel();

        $uiRole = $data['role'] ?? 'staff';
        if (! in_array($uiRole, $allowedRoles, true)) {
            $uiRole = 'staff';
        }

        $insertData = [
            'name'  => $data['name'],
            'email' => $data['email'],
            // map UI key to DB label to match existing enum values
            'role'  => $roleMap[$uiRole] ?? 'staff',
        ];

        if (! empty($data['password'])) {
            $insertData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $insertId = $model->insert($insertData);

        if ($insertId === false) {
            if (function_exists('log_message')) {
                log_message('error', '[WorkforceController::create] insert failed: ' . json_encode($model->errors()));
            }
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Insert failed']);
        }

        // return created record for client-side verification
        $created = $model->find($insertId);
        return $this->response->setJSON(['success' => true, 'id' => $insertId, 'user' => $created]);
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

        // allowed role keys (UI-facing) and mapping to DB-stored enum values
        $allowedRoles = [
            'manager','staff','inventory_auditor','procurement_officer',
            'accounts_payable','accounts_receivable','it_administrator','topmanagement','admin'
        ];

        $roleMap = [
            'manager' => 'manager',
            'staff' => 'staff',
            'inventory_auditor' => 'inventory auditor',
            'procurement_officer' => 'procurement officer',
            'accounts_payable' => 'accounts payable',
            'accounts_receivable' => 'accounts receivable',
            'it_administrator' => 'IT administrator',
            'topmanagement' => 'topmanagement',
            'admin' => 'admin'
        ];

        $updateData = [];
        if (isset($data['name'])) { $updateData['name'] = $data['name']; }
        if (isset($data['email'])) { $updateData['email'] = $data['email']; }
        if (isset($data['role']))  {
            $uiRole = $data['role'];
            if (! in_array($uiRole, $allowedRoles, true)) { $uiRole = 'staff'; }
            $updateData['role'] = $roleMap[$uiRole] ?? 'staff';
        }

        if (! empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $model = new UserModel();
        // log incoming payload and computed update for debugging
        if (function_exists('log_message')) {
            $payload = $this->request->getJSON(true);
            log_message('debug', '[WorkforceController::update] id=' . $id . ' payload=' . json_encode($payload) . ' updateData=' . json_encode($updateData));
        }

        $ok = $model->update($id, $updateData);

        if ($ok === false) {
            if (function_exists('log_message')) {
                log_message('error', '[WorkforceController::update] update failed for id=' . $id . ' errors=' . json_encode($model->errors()));
            }
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Update failed']);
        }

        // return updated record for verification
        $updated = $model->find($id);
        return $this->response->setJSON(['success' => true, 'user' => $updated]);
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