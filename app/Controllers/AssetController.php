<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AssetModel;
use App\Models\AssetAssignmentModel;
use App\Models\UserModel;
use App\Services\AuthorizationService;

class AssetController extends Controller
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

    private function requirePermission(string $permission): ?\CodeIgniter\HTTP\ResponseInterface
    {
        if ($deny = $this->guardApi()) {
            return $deny;
        }

        if (! $this->auth->hasPermission($this->session->get('role'), $permission)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
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

    public function index()
    {
        if ($deny = $this->guardApi()) {
            return $deny;
        }

        $role = $this->session->get('role');
        $canAll = $this->auth->hasPermission($role, 'asset.view_all');
        $canOwn = $this->auth->hasPermission($role, 'asset.view_own');
        if (! $canAll && ! $canOwn) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        $q = trim((string) ($this->request->getGet('q') ?? ''));
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $type = trim((string) ($this->request->getGet('type') ?? ''));

        $db = \Config\Database::connect();
        $builder = $db->table('assets a')->select('a.*');

        if ($this->auth->isItAdminRole($role) && $db->fieldExists('warehouse_id', 'assets')) {
            $wid = $this->session->get('currentWarehouseId');
            if ($wid === null || $wid === '' || ! is_numeric($wid) || (int) $wid <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Please select a warehouse']);
            }
            $builder->where('a.warehouse_id', (int) $wid);
        }

        if ($q !== '') {
            $builder->groupStart()
                ->like('a.asset_tag', $q)
                ->orLike('a.serial_no', $q)
                ->orLike('a.brand', $q)
                ->orLike('a.model', $q)
                ->groupEnd();
        }
        if ($status !== '') {
            $builder->where('a.status', $status);
        }
        if ($type !== '') {
            $builder->where('a.type', $type);
        }

        if (! $canAll) {
            $uid = (int) ($this->session->get('userID') ?? 0);
            $builder->join('asset_assignments aa', 'aa.asset_id = a.id AND aa.returned_at IS NULL', 'inner');
            $builder->where('aa.user_id', $uid);
        }

        $rows = $builder->orderBy('a.id', 'DESC')->get()->getResultArray();
        return $this->response->setJSON($rows);
    }

    public function create()
    {
        if ($deny = $this->requirePermission('asset.manage')) {
            return $deny;
        }

        $data = $this->request->getJSON(true);
        if (! $data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        }

        if ($resp = $this->validateJson($data, [
            'asset_tag' => 'required|min_length[2]|max_length[50]',
            'type' => 'permit_empty|max_length[50]',
            'brand' => 'permit_empty|max_length[50]',
            'model' => 'permit_empty|max_length[50]',
            'serial_no' => 'permit_empty|max_length[80]',
            'status' => 'permit_empty|max_length[30]',
        ])) {
            return $resp;
        }

        $model = new AssetModel();
        $exists = $model->where('asset_tag', $data['asset_tag'])->first();
        if ($exists) {
            return $this->response->setStatusCode(422)->setJSON([
                'error' => 'Validation failed',
                'errors' => ['asset_tag' => 'Asset tag already exists.'],
            ]);
        }

        $insert = [
            'asset_tag' => $data['asset_tag'],
            'warehouse_id' => null,
            'type' => $data['type'] ?? null,
            'brand' => $data['brand'] ?? null,
            'model' => $data['model'] ?? null,
            'serial_no' => $data['serial_no'] ?? null,
            'status' => $data['status'] ?? 'available',
            'purchased_at' => $data['purchased_at'] ?? null,
        ];

        $db = \Config\Database::connect();
        if ($this->auth->isItAdminRole($this->session->get('role')) && $db->fieldExists('warehouse_id', 'assets')) {
            $wid = $this->session->get('currentWarehouseId');
            if ($wid === null || $wid === '' || ! is_numeric($wid) || (int) $wid <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Please select a warehouse']);
            }
            $insert['warehouse_id'] = (int) $wid;
        } else {
            unset($insert['warehouse_id']);
        }

        $id = $model->insert($insert);
        if ($id === false) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Create failed', 'details' => $model->errors()]);
        }

        return $this->response->setJSON(['success' => true, 'asset' => $model->find($id)]);
    }

    public function update($id = null)
    {
        if ($deny = $this->requirePermission('asset.manage')) {
            return $deny;
        }

        if (empty($id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing id']);
        }

        $model = new AssetModel();
        $existing = $model->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Asset not found']);
        }

        $data = $this->request->getJSON(true);
        if (! $data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        }

        $blockedStatuses = ['lost', 'retired', 'disposed'];
        if (array_key_exists('status', $data)) {
            $nextStatus = strtolower(trim((string) $data['status']));
            if (in_array($nextStatus, $blockedStatuses, true)) {
                if ($deny = $this->requireAdminPassword($data)) {
                    return $deny;
                }
            }
        }

        if ($resp = $this->validateJson($data, [
            'type' => 'permit_empty|max_length[50]',
            'brand' => 'permit_empty|max_length[50]',
            'model' => 'permit_empty|max_length[50]',
            'serial_no' => 'permit_empty|max_length[80]',
            'status' => 'permit_empty|max_length[30]',
            'purchased_at' => 'permit_empty',
        ])) {
            return $resp;
        }

        $update = [];
        foreach (['type','brand','model','serial_no','status','purchased_at'] as $k) {
            if (array_key_exists($k, $data)) {
                $update[$k] = $data[$k];
            }
        }

        if ($update === []) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'No changes provided']);
        }

        $ok = $model->update($id, $update);
        if ($ok === false) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Update failed', 'details' => $model->errors()]);
        }

        return $this->response->setJSON(['success' => true, 'asset' => $model->find($id)]);
    }

    public function assign($id = null)
    {
        if ($deny = $this->requirePermission('asset.assign')) {
            return $deny;
        }

        if (empty($id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing id']);
        }

        $data = $this->request->getJSON(true);
        if (! $data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        }

        if ($deny = $this->requireAdminPassword($data)) {
            return $deny;
        }

        if ($resp = $this->validateJson($data, [
            'user_id' => 'required|integer',
            'notes' => 'permit_empty|max_length[2000]',
        ])) {
            return $resp;
        }

        $assetModel = new AssetModel();
        $asset = $assetModel->find($id);
        if (! $asset) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Asset not found']);
        }

        $db = \Config\Database::connect();
        if ($this->auth->isItAdminRole($this->session->get('role')) && $db->fieldExists('warehouse_id', 'assets')) {
            $wid = $this->session->get('currentWarehouseId');
            if ($wid === null || $wid === '' || ! is_numeric($wid) || (int) $wid <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Please select a warehouse']);
            }
            if (isset($asset['warehouse_id']) && $asset['warehouse_id'] !== null && (int) $asset['warehouse_id'] !== (int) $wid) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
            }
        }

        $blocked = ['lost', 'retired', 'disposed'];
        $status = strtolower(trim((string) ($asset['status'] ?? '')));
        if (in_array($status, $blocked, true)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Asset cannot be assigned in its current status']);
        }

        $assignmentModel = new AssetAssignmentModel();
        $active = $assignmentModel->where('asset_id', (int) $id)->where('returned_at', null)->first();
        if ($active) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Asset is already assigned']);
        }

        $assignmentId = $assignmentModel->insert([
            'asset_id' => (int) $id,
            'user_id' => (int) $data['user_id'],
            'assigned_by' => $this->session->get('userID') ? (int) $this->session->get('userID') : null,
            'assigned_at' => date('Y-m-d H:i:s'),
            'returned_at' => null,
            'notes' => $data['notes'] ?? null,
        ]);

        if ($assignmentId === false) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Assign failed', 'details' => $assignmentModel->errors()]);
        }

        $assetModel->update($id, ['status' => 'assigned']);

        return $this->response->setJSON(['success' => true]);
    }

    public function returnAsset($id = null)
    {
        if ($deny = $this->requirePermission('asset.assign')) {
            return $deny;
        }

        if (empty($id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing id']);
        }

        $assignmentModel = new AssetAssignmentModel();
        $active = $assignmentModel->where('asset_id', (int) $id)->where('returned_at', null)->first();
        if (! $active) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'No active assignment']);
        }

        $assetModel = new AssetModel();
        $asset = $assetModel->find($id);
        if (! $asset) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Asset not found']);
        }

        $db = \Config\Database::connect();
        if ($this->auth->isItAdminRole($this->session->get('role')) && $db->fieldExists('warehouse_id', 'assets')) {
            $wid = $this->session->get('currentWarehouseId');
            if ($wid === null || $wid === '' || ! is_numeric($wid) || (int) $wid <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Please select a warehouse']);
            }
            if (isset($asset['warehouse_id']) && $asset['warehouse_id'] !== null && (int) $asset['warehouse_id'] !== (int) $wid) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
            }
        }

        $data = $this->request->getJSON(true) ?? [];

        if ($deny = $this->requireAdminPassword($data)) {
            return $deny;
        }
        $notes = $data['notes'] ?? null;

        $assignmentModel->update((int) $active['id'], [
            'returned_at' => date('Y-m-d H:i:s'),
            'notes' => $notes !== null ? $notes : ($active['notes'] ?? null),
        ]);

        $assetModel->update($id, ['status' => 'available']);

        return $this->response->setJSON(['success' => true]);
    }

    public function history($id = null)
    {
        if ($deny = $this->guardApi()) {
            return $deny;
        }

        if (empty($id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing id']);
        }

        $role = $this->session->get('role');
        $canAll = $this->auth->hasPermission($role, 'asset.view_all');
        $canOwn = $this->auth->hasPermission($role, 'asset.view_own');
        if (! $canAll && ! $canOwn) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        $assignmentModel = new AssetAssignmentModel();
        $rows = $assignmentModel->where('asset_id', (int) $id)->orderBy('id', 'DESC')->findAll();

        $db = \Config\Database::connect();
        if ($this->auth->isItAdminRole($role) && $db->fieldExists('warehouse_id', 'assets')) {
            $wid = $this->session->get('currentWarehouseId');
            if ($wid === null || $wid === '' || ! is_numeric($wid) || (int) $wid <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Please select a warehouse']);
            }

            $assetModel = new AssetModel();
            $asset = $assetModel->find($id);
            if ($asset && isset($asset['warehouse_id']) && $asset['warehouse_id'] !== null && (int) $asset['warehouse_id'] !== (int) $wid) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
            }
        }

        if (! $canAll) {
            $uid = (int) ($this->session->get('userID') ?? 0);
            $mine = false;
            foreach ($rows as $r) {
                if ((int) ($r['user_id'] ?? 0) === $uid) {
                    $mine = true;
                    break;
                }
            }
            if (! $mine) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
            }
        }

        return $this->response->setJSON($rows);
    }
}
