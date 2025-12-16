<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\JobModel;
use App\Models\UserModel;
use App\Services\AuthorizationService;

class JobController extends Controller
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

        if (! $this->auth->hasPermission($this->session->get('role'), 'jobs.manage')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        return null;
    }

    private function requireAdminPassword(array $data, string $missingMessage = 'Admin password is required')
    {
        if (! $this->auth->isItAdminRole($this->session->get('role'))) {
            return null;
        }

        $adminPassword = $data['admin_password'] ?? null;
        if (! $adminPassword) {
            return $this->response->setStatusCode(400)->setJSON(['error' => $missingMessage]);
        }

        $adminId = (int) ($this->session->get('userID') ?? 0);
        if ($adminId <= 0) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not logged in']);
        }

        $userModel = new UserModel();
        $adminRow = $userModel->select('id, password')->find($adminId);
        if (! $adminRow || empty($adminRow['password']) || ! password_verify($adminPassword, $adminRow['password'])) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Invalid admin password']);
        }

        return null;
    }

    private function validateJson(array $data, array $rules)
    {
        $validation = \Config\Services::validation();
        $validation->setRules($rules);

        if (! $validation->run($data)) {
            return $this->response->setStatusCode(422)->setJSON([
                'error' => 'Validation failed',
                'errors' => $validation->getErrors(),
            ]);
        }

        return null;
    }

    public function enqueue()
    {
        if ($deny = $this->guardApi()) {
            return $deny;
        }

        $data = $this->request->getJSON(true);
        if (! $data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        }

        if ($deny = $this->requireAdminPassword($data)) {
            return $deny;
        }

        if ($resp = $this->validateJson($data, [
            'type' => 'required|max_length[50]',
        ])) {
            return $resp;
        }

        $payload = $data['payload'] ?? null;
        $availableAt = $data['available_at'] ?? null;

        $model = new JobModel();
        $id = $model->insert([
            'type' => $data['type'],
            'payload_json' => $payload !== null ? json_encode($payload) : null,
            'status' => 'pending',
            'attempts' => 0,
            'available_at' => $availableAt,
            'last_error' => null,
        ]);

        if ($id === false) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Enqueue failed', 'details' => $model->errors()]);
        }

        return $this->response->setJSON(['success' => true, 'id' => $id]);
    }

    public function index()
    {
        if ($deny = $this->guardApi()) {
            return $deny;
        }

        $limit = (int) ($this->request->getGet('limit') ?? 100);
        if ($limit <= 0 || $limit > 500) {
            $limit = 100;
        }

        $model = new JobModel();
        $rows = $model->orderBy('id', 'DESC')->findAll($limit);
        return $this->response->setJSON($rows);
    }
}
