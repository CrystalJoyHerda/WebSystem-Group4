<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;
use App\Models\AuditLogModel;
use App\Models\SystemSettingModel;
use App\Models\WarehouseModel;
use App\Services\AuthorizationService;
use CodeIgniter\HTTP\Files\UploadedFile;

class Admin extends Controller
{
    protected $session;
    protected $auth;

    public function __construct()
    {
        helper(['url', 'form']);
        $this->session = session();
        $this->auth = new AuthorizationService();
    }

    // ... (rest of the code remains the same)

    private function guardPage(): ?\CodeIgniter\HTTP\RedirectResponse
    {
        if (! $this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $role = $this->session->get('role');
        if (! $this->auth->isItAdminRole($role)) {
            return redirect()->to('/login');
        }

        return null;
    }

    private function guardApi(): ?\CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not logged in']);
        }

        $role = $this->session->get('role');
        if (! $this->auth->isItAdminRole($role)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        return null;
    }

    private function guardPagePermission(string $permission): ?\CodeIgniter\HTTP\RedirectResponse
    {
        if ($redirect = $this->guardPage()) {
            return $redirect;
        }

        $role = $this->session->get('role');
        if (! $this->auth->hasPermission($role, $permission)) {
            return redirect()->to('/dashboard');
        }

        return null;
    }

    private function guardApiPermission(string $permission): ?\CodeIgniter\HTTP\ResponseInterface
    {
        if ($deny = $this->guardApi()) {
            return $deny;
        }

        $role = $this->session->get('role');
        if (! $this->auth->hasPermission($role, $permission)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        return null;
    }

    private function viewData(array $extra = []): array
    {
        return array_merge([
            'permissions' => $this->auth->permissionsForRole($this->session->get('role')),
        ], $extra);
    }

    private function currentWarehouseId(): ?int
    {
        $val = $this->session->get('currentWarehouseId');
        if ($val === null || $val === '' || ! is_numeric($val)) {
            return null;
        }
        $id = (int) $val;
        return $id > 0 ? $id : null;
    }

    private function roleMap(): array
    {
        return [
            'manager' => 'manager',
            'staff' => 'staff',
            'viewer' => 'viewer',
            'inventory_auditor' => 'inventory auditor',
            'procurement_officer' => 'procurement officer',
            'accounts_payable' => 'accounts payable',
            'accounts_receivable' => 'accounts receivable',
            'it_administrator' => 'IT administrator',
            'topmanagement' => 'topmanagement',
        ];
    }

    private function passwordRateLimitCheck(string $scope): ?\CodeIgniter\HTTP\ResponseInterface
    {
        $untilKey = $scope . '_lock_until';
        $until = (int) ($this->session->get($untilKey) ?? 0);
        if ($until > time()) {
            $remaining = $until - time();
            if ($remaining < 1) {
                $remaining = 1;
            }
            return $this->response->setStatusCode(429)->setJSON([
                'error' => 'Too many password attempts. Please try again later.',
                'retry_after' => $remaining,
            ]);
        }
        return null;
    }

    private function passwordRateLimitFail(string $scope, int $maxAttempts = 5, int $lockSeconds = 600): void
    {
        $countKey = $scope . '_fail_count';
        $untilKey = $scope . '_lock_until';

        $count = (int) ($this->session->get($countKey) ?? 0);
        $count++;

        $set = [$countKey => $count];
        if ($count >= $maxAttempts) {
            $set[$untilKey] = time() + $lockSeconds;
        }
        $this->session->set($set);
    }

    private function passwordRateLimitClear(string $scope): void
    {
        $countKey = $scope . '_fail_count';
        $untilKey = $scope . '_lock_until';
        $this->session->set([
            $countKey => 0,
            $untilKey => 0,
        ]);
    }

    private function requireAdminPasswordConfirm($adminPassword): ?\CodeIgniter\HTTP\ResponseInterface
    {
        $adminPassword = trim((string) ($adminPassword ?? ''));
        if ($adminPassword === '') {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Admin password is required']);
        }

        if ($resp = $this->passwordRateLimitCheck('admin_confirm')) {
            return $resp;
        }

        $adminId = $this->session->get('userID') ? (int) $this->session->get('userID') : 0;
        if ($adminId <= 0) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not logged in']);
        }

        $model = new UserModel();
        $row = $model->select('id, password, role')->find($adminId);
        if (! $row) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not logged in']);
        }

        if (! $this->auth->isItAdminRole($row['role'] ?? null)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        $hash = (string) ($row['password'] ?? '');
        if ($hash === '' || ! password_verify($adminPassword, $hash)) {
            $this->passwordRateLimitFail('admin_confirm');
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Invalid password']);
        }

        $this->passwordRateLimitClear('admin_confirm');

        return null;
    }

    private function getSystemSettingInt(string $key, int $default = 0): int
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('system_settings')) {
            return $default;
        }

        $model = new SystemSettingModel();
        $row = $model->where('setting_key', $key)->first();
        if (! $row) {
            return $default;
        }

        $val = (string) ($row['setting_value'] ?? '');
        if ($val === '' || ! is_numeric($val)) {
            return $default;
        }
        return (int) $val;
    }

    private function backupDir(): string
    {
        $dir = rtrim(WRITEPATH, '\\/') . DIRECTORY_SEPARATOR . 'backups';
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir;
    }

    private function sanitizeBackupName(string $name): ?string
    {
        $name = basename(trim($name));
        if ($name === '') {
            return null;
        }
        if (preg_match('/[^A-Za-z0-9._-]/', $name)) {
            return null;
        }
        if (strtolower(substr($name, -4)) !== '.sql') {
            return null;
        }
        return $name;
    }

    private function cleanupBackups(): void
    {
        $dir = $this->backupDir();
        $keep = $this->getSystemSettingInt('backup.keep_count', 10);
        if ($keep <= 0) {
            $keep = 10;
        }

        $files = glob($dir . DIRECTORY_SEPARATOR . '*.sql') ?: [];
        usort($files, static function ($a, $b) {
            return strcmp((string) $b, (string) $a);
        });

        $extra = array_slice($files, $keep);
        foreach ($extra as $path) {
            @unlink($path);
        }
    }

    public function index()
    {
        if ($redirect = $this->guardPagePermission('admin.dashboard.view')) {
            return $redirect;
        }

        return view('dashboard/IT Adminstrator/dashboard', $this->viewData());
    }

    public function userManagement()
    {
        if ($redirect = $this->guardPagePermission('user.manage')) {
            return $redirect;
        }

        return view('dashboard/IT Adminstrator/usermanagement', $this->viewData());
    }

    public function accessControl()
    {
        if ($redirect = $this->guardPagePermission('access.view')) {
            return $redirect;
        }

        return view('dashboard/IT Adminstrator/accesscontrol', $this->viewData());
    }

    public function systemLogs()
    {
        if ($redirect = $this->guardPagePermission('logs.view')) {
            return $redirect;
        }

        return view('dashboard/IT Adminstrator/systemlogs', $this->viewData());
    }

    public function backupRecovery()
    {
        if ($redirect = $this->guardPagePermission('backup.view')) {
            return $redirect;
        }

        return view('dashboard/IT Adminstrator/backuprecovery', $this->viewData());
    }

    public function systemConfiguration()
    {
        if ($redirect = $this->guardPagePermission('config.view')) {
            return $redirect;
        }

        return view('dashboard/IT Adminstrator/systemconfiguration', $this->viewData());
    }

    public function reports()
    {
        if ($redirect = $this->guardPagePermission('admin.dashboard.view')) {
            return $redirect;
        }

        return redirect()->to('/admin');
    }

    public function notifications()
    {
        if ($redirect = $this->guardPagePermission('admin.dashboard.view')) {
            return $redirect;
        }

        return redirect()->to('/admin');
    }

    public function profile()
    {
        if ($redirect = $this->guardPage()) {
            return $redirect;
        }
        return view('dashboard/IT Adminstrator/profile', $this->viewData());
    }

    public function getProfile()
    {
        if ($deny = $this->guardApiPermission('admin.dashboard.view')) {
            return $deny;
        }

        $userId = $this->session->get('userID') ? (int) $this->session->get('userID') : 0;
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not logged in']);
        }

        $model = new UserModel();
        $row = $model->select('id, name, email, role, created_at, updated_at')->find($userId);
        if (! $row) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }

        return $this->response->setJSON(['user' => $row]);
    }

    public function updateProfile()
    {
        if ($deny = $this->guardApiPermission('admin.dashboard.view')) {
            return $deny;
        }

        $userId = $this->session->get('userID') ? (int) $this->session->get('userID') : 0;
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not logged in']);
        }

        $data = $this->request->getJSON(true);
        if (! $data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        }

        $currentPassword = (string) ($data['current_password'] ?? '');
        if (trim($currentPassword) === '') {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Current password is required']);
        }

        if ($resp = $this->passwordRateLimitCheck('admin_profile')) {
            return $resp;
        }

        $model = new UserModel();
        $existing = $model->find($userId);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }

        $hash = (string) ($existing['password'] ?? '');
        if ($hash === '' || ! password_verify($currentPassword, $hash)) {
            $this->passwordRateLimitFail('admin_profile');
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Invalid password']);
        }

        $this->passwordRateLimitClear('admin_profile');

        $name = array_key_exists('name', $data) ? trim((string) $data['name']) : null;
        $email = array_key_exists('email', $data) ? trim((string) $data['email']) : null;
        $newPassword = trim((string) ($data['new_password'] ?? ''));
        $confirmPassword = trim((string) ($data['confirm_password'] ?? ''));

        $update = [];
        if ($name !== null && $name !== '' && $name !== (string) ($existing['name'] ?? '')) {
            $update['name'] = $name;
        }
        if ($email !== null && $email !== '' && $email !== (string) ($existing['email'] ?? '')) {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->response->setStatusCode(422)->setJSON(['error' => 'Invalid email']);
            }
            $dup = $model->where('email', $email)->where('id !=', $userId)->first();
            if ($dup) {
                return $this->response->setStatusCode(422)->setJSON(['error' => 'Email already in use']);
            }
            $update['email'] = $email;
        }
        if ($newPassword !== '') {
            if (strlen($newPassword) < 6) {
                return $this->response->setStatusCode(422)->setJSON(['error' => 'New password must be at least 6 characters']);
            }
            if ($confirmPassword === '' || $confirmPassword !== $newPassword) {
                return $this->response->setStatusCode(422)->setJSON(['error' => 'Password confirmation does not match']);
            }
            $update['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        if ($update === []) {
            return $this->response->setJSON(['success' => true]);
        }

        $db = \Config\Database::connect();
        if ($db->fieldExists('updated_by', 'users')) {
            $update['updated_by'] = $userId;
        }

        $ok = $model->update($userId, $update);
        if (! $ok) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to update profile']);
        }

        $after = $model->select('id, name, email, role, created_at, updated_at')->find($userId);
        $this->session->set([
            'name' => $after['name'] ?? $this->session->get('name'),
            'email' => $after['email'] ?? $this->session->get('email'),
        ]);

        $this->writeAuditLog('update', 'profile', $userId, [
            'id' => $existing['id'] ?? $userId,
            'name' => $existing['name'] ?? null,
            'email' => $existing['email'] ?? null,
        ], [
            'id' => $after['id'] ?? $userId,
            'name' => $after['name'] ?? null,
            'email' => $after['email'] ?? null,
        ]);

        return $this->response->setJSON(['success' => true, 'user' => $after]);
    }

    public function notificationsApi()
    {
        if ($deny = $this->guardApiPermission('admin.dashboard.view')) {
            return $deny;
        }

        $db = \Config\Database::connect();
        $items = [];

        $warehouseId = null;
        $reqWarehouseId = $this->request->getGet('warehouse_id');
        if ($reqWarehouseId !== null && $reqWarehouseId !== '' && is_numeric($reqWarehouseId)) {
            $warehouseId = (int) $reqWarehouseId;
            if ($warehouseId <= 0) {
                $warehouseId = null;
            }
        }
        if ($warehouseId === null) {
            $warehouseId = $this->currentWarehouseId();
        }

        try {
            if ($db->tableExists('tickets')) {
                $builder = $db->table('tickets');
                if ($warehouseId !== null && $db->fieldExists('warehouse_id', 'tickets')) {
                    $builder->where('warehouse_id', $warehouseId);
                }
                $open = (int) $builder->where('status !=', 'closed')->countAllResults();
                if ($open > 0) {
                    $items[] = [
                        'type' => 'warning',
                        'title' => 'Open tickets',
                        'message' => $open . ' ticket(s) need attention',
                        'created_at' => date('Y-m-d H:i:s'),
                        'link' => null,
                    ];
                }
            }
        } catch (\Throwable $e) {
        }

        try {
            if ($db->tableExists('audit_logs')) {
                $builder = $db->table('audit_logs a')
                    ->select('a.id, a.warehouse_id, a.action, a.entity_type, a.entity_id, a.created_at, u.name as actor_name, u.email as actor_email')
                    ->join('users u', 'u.id = a.actor_user_id', 'left')
                    ->orderBy('a.id', 'DESC')
                    ->limit(5);

                if ($warehouseId !== null && $db->fieldExists('warehouse_id', 'audit_logs')) {
                    $builder->groupStart()->where('a.warehouse_id', $warehouseId)->orWhere('a.warehouse_id', null)->groupEnd();
                }

                $rows = $builder->get()->getResultArray();
                foreach ($rows as $r) {
                    $items[] = [
                        'type' => 'info',
                        'title' => 'Activity',
                        'message' => $this->buildAuditSummary($r),
                        'created_at' => $r['created_at'] ?? null,
                        'link' => site_url('system-logs'),
                    ];
                }
            }
        } catch (\Throwable $e) {
        }

        return $this->response->setJSON([
            'count' => count($items),
            'notifications' => $items,
        ]);
    }

    public function warehouses()
    {
        if ($deny = $this->guardApiPermission('admin.dashboard.view')) {
            return $deny;
        }

        $model = new WarehouseModel();
        $warehouses = [];
        try {
            $warehouses = $model->orderBy('name', 'ASC')->findAll();
        } catch (\Throwable $e) {
            $warehouses = [];
        }

        return $this->response->setJSON([
            'warehouses' => $warehouses,
            'current_warehouse_id' => $this->currentWarehouseId(),
        ]);
    }

    public function setCurrentWarehouse()
    {
        if ($deny = $this->guardApiPermission('admin.dashboard.view')) {
            return $deny;
        }

        $data = $this->request->getJSON(true);
        if (! $data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        }

        $warehouseId = $data['warehouse_id'] ?? null;
        if (! is_numeric($warehouseId) || (int) $warehouseId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid warehouse id']);
        }

        $warehouseId = (int) $warehouseId;
        $model = new WarehouseModel();
        $row = $model->find($warehouseId);
        if (! $row) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Warehouse not found']);
        }

        $this->session->set('currentWarehouseId', $warehouseId);
        return $this->response->setJSON(['success' => true, 'current_warehouse_id' => $warehouseId]);
    }

    public function listUsers()
    {
        if ($deny = $this->guardApiPermission('user.manage')) {
            return $deny;
        }

        $limit = (int) ($this->request->getGet('limit') ?? 50);
        if ($limit <= 0) {
            $limit = 50;
        }
        if ($limit > 200) {
            $limit = 200;
        }
        $offset = (int) ($this->request->getGet('offset') ?? 0);
        if ($offset < 0) {
            $offset = 0;
        }

        $db = \Config\Database::connect();
        $builder = $db->table('users u')->select('u.id, u.name, u.email, u.role, u.is_active, u.created_at, u.updated_at');

        $q = trim((string) ($this->request->getGet('q') ?? ''));
        if ($q !== '') {
            $builder->groupStart()
                ->like('u.name', $q)
                ->orLike('u.email', $q)
                ->orLike('u.role', $q)
                ->groupEnd();
        }

        $builder->orderBy('u.id', 'ASC');
        $users = $builder->get($limit, $offset)->getResultArray();
        return $this->response->setJSON($users);
    }

    public function createUser()
    {
        if ($deny = $this->guardApiPermission('user.manage')) {
            return $deny;
        }

        $data = $this->request->getJSON(true);
        if (! $data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        }

        if ($resp = $this->requireAdminPasswordConfirm($data['admin_password'] ?? null)) {
            return $resp;
        }

        $roleMap = $this->roleMap();
        $ruleInList = implode(',', array_keys($roleMap));

        if ($resp = $this->validateJson($data, [
            'name' => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|max_length[100]',
            'role' => 'permit_empty|in_list[' . $ruleInList . ']',
            'password' => 'required|min_length[6]|max_length[255]',
            'admin_password' => 'required',
        ])) {
            return $resp;
        }

        $uiRole = $data['role'] ?? 'staff';
        if (! array_key_exists($uiRole, $roleMap)) {
            $uiRole = 'staff';
        }

        $model = new UserModel();
        $exists = $model->where('email', $data['email'])->first();
        if ($exists) {
            return $this->response->setStatusCode(422)->setJSON([
                'error' => 'Validation failed',
                'errors' => ['email' => 'That email is already registered.'],
            ]);
        }

        $db = \Config\Database::connect();
        $actorId = $this->session->get('userID') ? (int) $this->session->get('userID') : null;

        $insertData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $roleMap[$uiRole] ?? 'staff',
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
        ];

        if ($actorId !== null && $db->fieldExists('created_by', 'users')) {
            $insertData['created_by'] = $actorId;
        }
        if ($actorId !== null && $db->fieldExists('updated_by', 'users')) {
            $insertData['updated_by'] = $actorId;
        }
        if ($db->fieldExists('is_active', 'users')) {
            $defaultActive = $this->getSystemSettingInt('users.default_is_active', 1);
            $insertData['is_active'] = $defaultActive ? 1 : 0;
        }

        $insertId = $model->insert($insertData);
        if ($insertId === false) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Insert failed', 'details' => $model->errors()]);
        }

        $created = $model->select('id, name, email, role, created_at, updated_at, is_active')->find($insertId);
        $this->writeAuditLog('create', 'user', $insertId, null, [
            'id' => $created['id'] ?? $insertId,
            'name' => $created['name'] ?? null,
            'email' => $created['email'] ?? null,
            'role' => $created['role'] ?? null,
        ]);
        return $this->response->setJSON(['success' => true, 'id' => $insertId, 'user' => $created]);
    }

    public function updateUser($id = null)
    {
        if ($deny = $this->guardApiPermission('user.manage')) {
            return $deny;
        }

        if (empty($id) || ! is_numeric($id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing id']);
        }

        $data = $this->request->getJSON(true);
        if (! $data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        }

        if ($resp = $this->requireAdminPasswordConfirm($data['admin_password'] ?? null)) {
            return $resp;
        }

        $model = new UserModel();
        $existing = $model->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }

        $adminId = (int) ($this->session->get('userID') ?? 0);
        if ((int) $id === $adminId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'You cannot edit your own account']);
        }

        $roleMap = $this->roleMap();
        $ruleInList = implode(',', array_keys($roleMap));
        if ($resp = $this->validateJson($data, [
            'name' => 'permit_empty|min_length[3]|max_length[100]',
            'email' => 'permit_empty|valid_email|max_length[100]',
            'role' => 'permit_empty|in_list[' . $ruleInList . ']',
            'password' => 'permit_empty|min_length[6]|max_length[255]',
            'admin_password' => 'required',
        ])) {
            return $resp;
        }

        $updateData = [];
        if (array_key_exists('name', $data)) {
            $updateData['name'] = $data['name'];
        }
        if (array_key_exists('email', $data)) {
            $nextEmail = (string) $data['email'];
            if ($nextEmail !== '' && $nextEmail !== (string) ($existing['email'] ?? '')) {
                $emailExists = $model->where('email', $nextEmail)->where('id !=', (int) $id)->first();
                if ($emailExists) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'error' => 'Validation failed',
                        'errors' => ['email' => 'That email is already registered.'],
                    ]);
                }
            }
            $updateData['email'] = $data['email'];
        }
        if (array_key_exists('role', $data) && $data['role'] !== null && $data['role'] !== '') {
            $uiRole = $data['role'];
            if (! array_key_exists($uiRole, $roleMap)) {
                $uiRole = 'staff';
            }
            $updateData['role'] = $roleMap[$uiRole] ?? 'staff';
        }
        if (! empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $db = \Config\Database::connect();
        if ($db->fieldExists('is_active', 'users') && array_key_exists('is_active', $data)) {
            $updateData['is_active'] = ((int) $data['is_active']) ? 1 : 0;
        }
        if ($db->fieldExists('updated_by', 'users')) {
            $updateData['updated_by'] = (int) $this->session->get('userID');
        }

        if ($updateData === []) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'No changes provided']);
        }

        $ok = $model->update($id, $updateData);
        if ($ok === false) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Update failed', 'details' => $model->errors()]);
        }

        $updated = $model->select('id, name, email, role, created_at, updated_at, is_active')->find($id);
        $after = [
            'id' => $updated['id'] ?? (int) $id,
            'name' => $updated['name'] ?? null,
            'email' => $updated['email'] ?? null,
            'role' => $updated['role'] ?? null,
            'is_active' => $updated['is_active'] ?? null,
        ];
        if (! empty($data['password'])) {
            $after['password_changed'] = true;
        }

        $this->writeAuditLog('update', 'user', $id, [
            'id' => $existing['id'] ?? (int) $id,
            'name' => $existing['name'] ?? null,
            'email' => $existing['email'] ?? null,
            'role' => $existing['role'] ?? null,
            'is_active' => $existing['is_active'] ?? null,
        ], $after);

        return $this->response->setJSON(['success' => true, 'user' => $updated]);
    }

    public function deleteUser($id = null)
    {
        if ($deny = $this->guardApiPermission('user.manage')) {
            return $deny;
        }

        if (empty($id) || ! is_numeric($id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing id']);
        }
        if ((int) $id === (int) $this->session->get('userID')) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'You cannot delete your own account']);
        }

        $data = $this->request->getJSON(true) ?? [];
        if ($resp = $this->requireAdminPasswordConfirm($data['admin_password'] ?? null)) {
            return $resp;
        }

        $db = \Config\Database::connect();
        if (! $db->fieldExists('is_active', 'users')) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'is_active field missing on users']);
        }

        $model = new UserModel();
        $existing = $model->select('id, name, email, role, is_active')->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }

        $update = ['is_active' => 0];
        if ($db->fieldExists('updated_by', 'users')) {
            $update['updated_by'] = (int) $this->session->get('userID');
        }

        $ok = $model->update($id, $update);
        if ($ok === false) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Update failed', 'details' => $model->errors()]);
        }

        $after = $model->select('id, name, email, role, is_active')->find($id);
        $this->writeAuditLog('update', 'user_status', $id, $existing, $after);
        return $this->response->setJSON(['success' => true]);
    }

    public function setUserStatus($id = null)
    {
        if ($deny = $this->guardApiPermission('user.manage')) {
            return $deny;
        }

        if (empty($id) || ! is_numeric($id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing id']);
        }
        if ((int) $id === (int) $this->session->get('userID')) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'You cannot change your own account status']);
        }

        $data = $this->request->getJSON(true);
        if (! $data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        }
        if ($resp = $this->requireAdminPasswordConfirm($data['admin_password'] ?? null)) {
            return $resp;
        }

        $status = isset($data['is_active']) ? (int) $data['is_active'] : null;
        if ($status === null) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing is_active']);
        }

        $db = \Config\Database::connect();
        if (! $db->fieldExists('is_active', 'users')) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'is_active field missing on users']);
        }

        $model = new UserModel();
        $existing = $model->select('id, name, email, role, is_active')->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }

        $ok = $model->update($id, ['is_active' => $status ? 1 : 0]);
        if ($ok === false) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Update failed', 'details' => $model->errors()]);
        }
        $updated = $model->select('id, name, email, role, is_active')->find($id);
        $this->writeAuditLog('update', 'user_status', $id, $existing, $updated);
        return $this->response->setJSON(['success' => true]);
    }

    public function resetPassword($id = null)
    {
        if ($deny = $this->guardApiPermission('user.manage')) {
            return $deny;
        }

        if (empty($id) || ! is_numeric($id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing id']);
        }
        if ((int) $id === (int) $this->session->get('userID')) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'You cannot reset your own password here']);
        }

        $data = $this->request->getJSON(true);
        if (! $data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        }
        if ($resp = $this->requireAdminPasswordConfirm($data['admin_password'] ?? null)) {
            return $resp;
        }

        if ($resp = $this->validateJson($data, [
            'new_password' => 'required|min_length[6]|max_length[255]',
            'admin_password' => 'required',
        ])) {
            return $resp;
        }

        $model = new UserModel();
        $existing = $model->select('id, name, email, role')->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }

        $ok = $model->update($id, ['password' => password_hash($data['new_password'], PASSWORD_DEFAULT)]);
        if ($ok === false) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Update failed', 'details' => $model->errors()]);
        }

        $this->writeAuditLog('update', 'user_password', $id, $existing, ['password_changed' => true]);
        return $this->response->setJSON(['success' => true]);
    }

    public function listAuditLogs()
    {
        if ($deny = $this->guardApiPermission('logs.view')) {
            return $deny;
        }

        $db = \Config\Database::connect();
        if (! $db->tableExists('audit_logs')) {
            return $this->response->setJSON(['logs' => [], 'total' => 0]);
        }

        $limit = (int) ($this->request->getGet('limit') ?? 50);
        if ($limit <= 0) {
            $limit = 50;
        }
        if ($limit > 200) {
            $limit = 200;
        }
        $offset = (int) ($this->request->getGet('offset') ?? 0);
        if ($offset < 0) {
            $offset = 0;
        }
        $q = trim((string) ($this->request->getGet('q') ?? ''));

        $entityType = trim((string) ($this->request->getGet('entity_type') ?? ''));
        $action = trim((string) ($this->request->getGet('action') ?? ''));
        $all = (int) ($this->request->getGet('all') ?? 0);

        $builder = $db->table('audit_logs a')
            ->select('a.id, a.warehouse_id, a.actor_user_id, a.action, a.entity_type, a.entity_id, a.before_json, a.after_json, a.ip_address, a.user_agent, a.created_at, u.name as actor_name, u.email as actor_email')
            ->join('users u', 'u.id = a.actor_user_id', 'left')
            ->orderBy('a.id', 'DESC');

        if ($entityType !== '') {
            $builder->where('a.entity_type', $entityType);
        }

        if ($action !== '') {
            $builder->where('a.action', $action);
        }

        if (! $all && $db->fieldExists('warehouse_id', 'audit_logs')) {
            $currentWarehouseId = $this->currentWarehouseId();
            if ($currentWarehouseId !== null) {
                $builder->groupStart()
                    ->where('a.warehouse_id', $currentWarehouseId)
                    ->orWhere('a.warehouse_id', null)
                    ->groupEnd();
            }
        }

        if ($q !== '') {
            $builder->groupStart()
                ->like('a.action', $q)
                ->orLike('a.entity_type', $q)
                ->orLike('u.name', $q)
                ->orLike('u.email', $q)
                ->groupEnd();
        }

        $total = (int) $builder->countAllResults(false);
        $rows = $builder->get($limit, $offset)->getResultArray();

        foreach ($rows as &$row) {
            $row['summary'] = $this->buildAuditSummary($row);
        }
        unset($row);

        return $this->response->setJSON(['logs' => $rows, 'total' => $total]);
    }

    private function buildAuditSummary(array $row): string
    {
        $action = (string) ($row['action'] ?? '');
        $entityType = (string) ($row['entity_type'] ?? '');
        $entityId = $row['entity_id'] ?? null;

        $before = null;
        $after = null;

        if (! empty($row['before_json'])) {
            $decoded = json_decode((string) $row['before_json'], true);
            if (is_array($decoded)) {
                $before = $decoded;
            }
        }
        if (! empty($row['after_json'])) {
            $decoded = json_decode((string) $row['after_json'], true);
            if (is_array($decoded)) {
                $after = $decoded;
            }
        }

        if ($entityType === 'user' || $entityType === 'user_status' || $entityType === 'user_password') {
            $ref = is_array($after) && $after !== [] ? $after : (is_array($before) ? $before : []);
            $email = isset($ref['email']) ? (string) $ref['email'] : '';
            $name = isset($ref['name']) ? (string) $ref['name'] : '';
            $role = isset($ref['role']) ? (string) $ref['role'] : '';
            $who = $email !== '' ? $email : ($name !== '' ? $name : ($entityId !== null ? ('ID ' . $entityId) : ''));

            if ($entityType === 'user_password') {
                return $who !== '' ? 'Reset password for ' . $who : 'Reset user password';
            }
            if ($entityType === 'user_status') {
                return $who !== '' ? 'Changed status for ' . $who : 'Changed user status';
            }

            if ($action === 'create') {
                $suffix = $role !== '' ? ' (' . $role . ')' : '';
                return $who !== '' ? 'Created user ' . $who . $suffix : 'Created user';
            }
            if ($action === 'update') {
                return $who !== '' ? 'Updated user ' . $who : 'Updated user';
            }
            if ($action === 'delete') {
                return $who !== '' ? 'Deleted user ' . $who : 'Deleted user';
            }
        }

        if ($entityType === 'backup') {
            $file = is_array($after) && isset($after['file']) ? (string) $after['file'] : '';
            if ($file !== '') {
                return 'Created backup ' . $file;
            }
            return 'Created backup';
        }

        if ($entityType === 'restore_backup') {
            $file = is_array($after) && isset($after['file']) ? (string) $after['file'] : '';
            if ($file !== '') {
                return 'Restored backup ' . $file;
            }
            return 'Restored backup';
        }

        if ($entityType === 'system_settings') {
            return 'Updated system settings';
        }

        if ($entityType === 'role_permissions') {
            $roleKey = '';
            if (is_array($after) && isset($after['role_key'])) {
                $roleKey = (string) $after['role_key'];
            } elseif (is_array($before) && isset($before['role_key'])) {
                $roleKey = (string) $before['role_key'];
            }
            return $roleKey !== '' ? 'Updated permissions for role ' . $roleKey : 'Updated role permissions';
        }

        $base = trim(ucfirst($action) . ' ' . $entityType);
        if ($base === '') {
            return '';
        }
        if ($entityId !== null && $entityId !== '') {
            return $base . ' #' . $entityId;
        }
        return $base;
    }

    public function overview()
    {
        if ($deny = $this->guardApiPermission('admin.dashboard.view')) {
            return $deny;
        }

        $db = \Config\Database::connect();

        $metrics = [
            'open_tickets' => 0,
            'devices_online' => 0,
            'devices_offline' => 0,
            'pending_approvals' => 0,
            'security_alerts' => 0,
            'assets_assigned' => 0,
        ];

        try {
            if ($db->tableExists('tickets')) {
                $metrics['open_tickets'] = (int) $db->table('tickets')->where('status !=', 'closed')->countAllResults();
            }
        } catch (\Throwable $e) {
        }
        try {
            if ($db->tableExists('asset_assignments')) {
                $metrics['assets_assigned'] = (int) $db->table('asset_assignments')->where('returned_at', null)->countAllResults();
            }
        } catch (\Throwable $e) {
        }

        $activity = [];
        try {
            if ($db->tableExists('audit_logs')) {
                $activity = $db->table('audit_logs a')
                    ->select('a.id, a.action, a.entity_type, a.entity_id, a.created_at, u.name as actor_name, u.email as actor_email')
                    ->join('users u', 'u.id = a.actor_user_id', 'left')
                    ->orderBy('a.id', 'DESC')
                    ->get(15)
                    ->getResultArray();
            }
        } catch (\Throwable $e) {
            $activity = [];
        }

        return $this->response->setJSON(['metrics' => $metrics, 'activity' => $activity]);
    }

    public function listBackups()
    {
        if ($deny = $this->guardApiPermission('backup.view')) {
            return $deny;
        }

        $dir = $this->backupDir();
        $files = glob($dir . DIRECTORY_SEPARATOR . '*.sql') ?: [];

        $out = [];
        foreach ($files as $path) {
            $name = basename($path);
            $out[] = [
                'name' => $name,
                'size' => @filesize($path) ?: 0,
                'created_at' => @date('Y-m-d H:i:s', @filemtime($path) ?: time()),
            ];
        }

        usort($out, static function ($a, $b) {
            return strcmp($b['name'], $a['name']);
        });

        return $this->response->setJSON(['backups' => $out]);
    }

    public function downloadBackup($name = null)
    {
        if ($deny = $this->guardApiPermission('backup.view')) {
            return $deny;
        }

        $name = $this->sanitizeBackupName((string) ($name ?? ''));
        if (! $name) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid name']);
        }

        $path = $this->backupDir() . DIRECTORY_SEPARATOR . $name;
        if (! is_file($path)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        return $this->response->download($path, null)->setFileName($name);
    }

    public function createBackup()
    {
        if ($deny = $this->guardApiPermission('backup.view')) {
            return $deny;
        }

        $data = $this->request->getJSON(true);
        if (! $data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        }

        if ($resp = $this->requireAdminPasswordConfirm($data['admin_password'] ?? null)) {
            return $resp;
        }

        $db = \Config\Database::connect();
        $driver = strtolower((string) ($db->DBDriver ?? ''));
        if (! in_array($driver, ['mysqli', 'mysql', 'pdo'], true)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Unsupported database driver']);
        }

        $tables = [];
        try {
            $tables = $db->listTables();
        } catch (\Throwable $e) {
            $tables = [];
        }

        if ($tables === []) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'No tables found']);
        }

        $now = date('Ymd_His');
        $filename = 'backup_' . $now . '.sql';
        $path = $this->backupDir() . DIRECTORY_SEPARATOR . $filename;

        $fh = @fopen($path, 'wb');
        if (! $fh) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Unable to write backup file']);
        }

        fwrite($fh, "SET FOREIGN_KEY_CHECKS=0;\n");

        foreach ($tables as $table) {
            $table = (string) $table;
            if ($table === '') {
                continue;
            }

            $row = $db->query('SHOW CREATE TABLE `' . str_replace('`', '``', $table) . '`')->getRowArray();
            $createSql = $row['Create Table'] ?? null;
            if ($createSql) {
                fwrite($fh, "\nDROP TABLE IF EXISTS `{$table}`;\n");
                fwrite($fh, $createSql . ";\n");
            }

            $rows = $db->table($table)->get()->getResultArray();
            if (! $rows) {
                continue;
            }

            $cols = array_keys($rows[0]);
            $colSql = '`' . implode('`,`', array_map(static fn($c) => str_replace('`', '``', (string) $c), $cols)) . '`';

            foreach ($rows as $r) {
                $vals = [];
                foreach ($cols as $c) {
                    $v = $r[$c] ?? null;
                    if ($v === null) {
                        $vals[] = 'NULL';
                    } else {
                        $vals[] = $db->escape($v);
                    }
                }
                fwrite($fh, "INSERT INTO `{$table}` ({$colSql}) VALUES (" . implode(',', $vals) . ");\n");
            }
        }

        fwrite($fh, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fh);

        $this->cleanupBackups();

        $this->writeAuditLog('create', 'backup', 0, null, ['file' => $filename]);
        return $this->response->setJSON(['success' => true, 'name' => $filename]);
    }

    public function restoreBackup()
    {
        if ($deny = $this->guardApiPermission('backup.view')) {
            return $deny;
        }

        $adminPassword = $this->request->getPost('admin_password');
        if ($resp = $this->requireAdminPasswordConfirm($adminPassword)) {
            return $resp;
        }

        $confirm = (string) $this->request->getPost('confirm');
        if (trim($confirm) !== 'RESTORE') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Type RESTORE to confirm']);
        }

        $file = $this->request->getFile('backup');
        if (! ($file instanceof UploadedFile) || ! $file->isValid()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing backup file']);
        }

        if (strtolower($file->getExtension()) !== 'sql') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Only .sql files are allowed']);
        }

        $sql = @file_get_contents($file->getTempName());
        if ($sql === false || trim($sql) === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Empty SQL file']);
        }

        $db = \Config\Database::connect();
        $statements = $this->splitSqlStatements($sql);

        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '') {
                continue;
            }
            $db->query($stmt);
        }

        $this->writeAuditLog('update', 'restore_backup', 0, null, ['file' => $file->getName()]);
        return $this->response->setJSON(['success' => true]);
    }

    private function splitSqlStatements(string $sql): array
    {
        $out = [];
        $buf = '';
        $inSingle = false;
        $inDouble = false;
        $inBacktick = false;
        $len = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];
            $next = ($i + 1 < $len) ? $sql[$i + 1] : '';

            if (! $inSingle && ! $inDouble && ! $inBacktick) {
                if ($ch === '-' && $next === '-') {
                    while ($i < $len && $sql[$i] !== "\n") {
                        $i++;
                    }
                    continue;
                }
                if ($ch === '#') {
                    while ($i < $len && $sql[$i] !== "\n") {
                        $i++;
                    }
                    continue;
                }
                if ($ch === '/' && $next === '*') {
                    $i += 2;
                    while ($i < $len - 1 && ! ($sql[$i] === '*' && $sql[$i + 1] === '/')) {
                        $i++;
                    }
                    $i++;
                    continue;
                }
            }

            if ($ch === "'" && ! $inDouble && ! $inBacktick) {
                $escaped = ($i > 0 && $sql[$i - 1] === '\\');
                if (! $escaped) {
                    $inSingle = ! $inSingle;
                }
                $buf .= $ch;
                continue;
            }
            if ($ch === '"' && ! $inSingle && ! $inBacktick) {
                $escaped = ($i > 0 && $sql[$i - 1] === '\\');
                if (! $escaped) {
                    $inDouble = ! $inDouble;
                }
                $buf .= $ch;
                continue;
            }
            if ($ch === '`' && ! $inSingle && ! $inDouble) {
                $inBacktick = ! $inBacktick;
                $buf .= $ch;
                continue;
            }

            if ($ch === ';' && ! $inSingle && ! $inDouble && ! $inBacktick) {
                $out[] = $buf;
                $buf = '';
                continue;
            }

            $buf .= $ch;
        }

        if (trim($buf) !== '') {
            $out[] = $buf;
        }

        return $out;
    }

    public function getSystemSettings()
    {
        if ($deny = $this->guardApiPermission('config.view')) {
            return $deny;
        }

        $db = \Config\Database::connect();
        if (! $db->tableExists('system_settings')) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'System settings table missing. Run migrations first.']);
        }

        $defaults = [
            'inventory.low_stock_threshold' => '10',
            'backup.retention_days' => '30',
            'backup.max_backups' => '50',
            'auth.password_min_length' => '6',
            'auth.lockout_attempts' => '5',
            'auth.lockout_minutes' => '15',
            'auth.session_timeout_minutes' => '30',
            'users.default_is_active' => '1',
        ];

        $rows = $db->table('system_settings')->select('setting_key, setting_value')->get()->getResultArray();
        $settings = $defaults;
        foreach ($rows as $r) {
            if (! empty($r['setting_key'])) {
                $settings[(string) $r['setting_key']] = $r['setting_value'] !== null ? (string) $r['setting_value'] : '';
            }
        }

        return $this->response->setJSON(['settings' => $settings]);
    }

    public function saveSystemSettings()
    {
        if ($deny = $this->guardApiPermission('config.view')) {
            return $deny;
        }

        $data = $this->request->getJSON(true);
        if (! $data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        }

        if ($resp = $this->requireAdminPasswordConfirm($data['admin_password'] ?? null)) {
            return $resp;
        }

        $settingsIn = $data['settings'] ?? null;
        if (! is_array($settingsIn)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing settings']);
        }

        $allowed = [
            'inventory.low_stock_threshold' => ['min' => 0, 'max' => 100000],
            'backup.retention_days' => ['min' => 0, 'max' => 3650],
            'backup.max_backups' => ['min' => 0, 'max' => 10000],
            'auth.password_min_length' => ['min' => 6, 'max' => 255],
            'auth.lockout_attempts' => ['min' => 0, 'max' => 50],
            'auth.lockout_minutes' => ['min' => 0, 'max' => 1440],
            'auth.session_timeout_minutes' => ['min' => 0, 'max' => 1440],
            'users.default_is_active' => ['min' => 0, 'max' => 1],
        ];

        $db = \Config\Database::connect();
        if (! $db->tableExists('system_settings')) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'System settings table missing. Run migrations first.']);
        }

        $now = date('Y-m-d H:i:s');
        $userId = $this->session->get('userID') ? (int) $this->session->get('userID') : null;

        $before = [];
        $after = [];
        $model = new SystemSettingModel();

        foreach ($settingsIn as $k => $v) {
            $key = (string) $k;
            if (! array_key_exists($key, $allowed)) {
                continue;
            }

            $rawVal = is_string($v) || is_numeric($v) ? (string) $v : '';
            $rawVal = trim($rawVal);
            if ($rawVal === '' || ! is_numeric($rawVal)) {
                return $this->response->setStatusCode(422)->setJSON(['error' => 'Invalid value for ' . $key]);
            }

            $intVal = (int) $rawVal;
            if ($intVal < $allowed[$key]['min'] || $intVal > $allowed[$key]['max']) {
                return $this->response->setStatusCode(422)->setJSON(['error' => 'Out of range value for ' . $key]);
            }

            $existing = $db->table('system_settings')->select('id, setting_value')->where('setting_key', $key)->get()->getRowArray();
            $existingVal = $existing ? ($existing['setting_value'] !== null ? (string) $existing['setting_value'] : '') : null;
            $before[$key] = $existingVal;
            $after[$key] = (string) $intVal;

            if ($existing && ! empty($existing['id'])) {
                $update = [
                    'setting_value' => (string) $intVal,
                    'updated_at' => $now,
                ];
                if ($db->fieldExists('updated_by', 'system_settings')) {
                    $update['updated_by'] = $userId;
                }
                $model->update((int) $existing['id'], $update);
            } else {
                $insert = [
                    'setting_key' => $key,
                    'setting_value' => (string) $intVal,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if ($db->fieldExists('updated_by', 'system_settings')) {
                    $insert['updated_by'] = $userId;
                }
                $model->insert($insert);
            }
        }

        $this->writeAuditLog('update', 'system_settings', 0, $before, $after);
        return $this->response->setJSON(['success' => true]);
    }

    public function listRoles()
    {
        if ($deny = $this->guardApiPermission('access.view')) {
            return $deny;
        }

        $db = \Config\Database::connect();
        if (! ($db->tableExists('roles') && $db->tableExists('permissions') && $db->tableExists('role_permissions'))) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'RBAC tables missing. Run migrations first.']);
        }

        $now = date('Y-m-d H:i:s');

        $seedRoles = [
            'itadministrator' => 'IT Administrator',
            'manager' => 'Manager',
            'staff' => 'Staff',
            'viewer' => 'Viewer',
            'topmanagement' => 'Top Management',
            'inventoryauditor' => 'Inventory Auditor',
            'procurementofficer' => 'Procurement Officer',
            'accountspayable' => 'Accounts Payable',
            'accountsreceivable' => 'Accounts Receivable',
        ];

        foreach ($seedRoles as $roleKey => $label) {
            try {
                $existing = $db->table('roles')->select('id')->where('role_key', $roleKey)->get()->getRowArray();
                if (! $existing) {
                    $db->table('roles')->insert([
                        'role_key' => $roleKey,
                        'label' => $label,
                        'created_at' => $now,
                    ]);
                }
            } catch (\Throwable $e) {
            }
        }

        // Seed Top Management defaults only if role exists and has no configured permissions yet.
        // (Avoid overwriting any custom permission setup.)
        $seedRolePerms = [
            'topmanagement' => [
                'top.dashboard.view',
                'inventory.view',
                'transfers.view',
                'transfers.approve',
                'po.view',
                'po.approve',
                'finance.view',
                'reports.view',
                'logs.view',
                'ticket.view_all',
            ],
        ];

        foreach ($seedRolePerms as $roleKey => $codes) {
            try {
                $roleRow = $db->table('roles')->select('id')->where('role_key', $roleKey)->get()->getRowArray();
                if (! $roleRow || empty($roleRow['id'])) {
                    continue;
                }
                $roleId = (int) $roleRow['id'];

                $existingRp = $db->table('role_permissions')->select('role_id')->where('role_id', $roleId)->limit(1)->get()->getRowArray();
                if ($existingRp) {
                    continue;
                }

                foreach ($codes as $code) {
                    $permRow = $db->table('permissions')->select('id')->where('code', $code)->get()->getRowArray();
                    if (! $permRow) {
                        $db->table('permissions')->insert([
                            'code' => $code,
                            'description' => null,
                            'created_at' => $now,
                        ]);
                    }
                }

                foreach ($codes as $code) {
                    $permRow = $db->table('permissions')->select('id')->where('code', $code)->get()->getRowArray();
                    if ($permRow && ! empty($permRow['id'])) {
                        $db->table('role_permissions')->insert([
                            'role_id' => $roleId,
                            'permission_id' => (int) $permRow['id'],
                            'created_at' => $now,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
            }
        }

        $roles = $db->table('roles')->select('id, role_key, label')->orderBy('role_key', 'ASC')->get()->getResultArray();
        $allPerms = $db->table('permissions')->select('id, code, description')->orderBy('code', 'ASC')->get()->getResultArray();

        $roleOut = [];
        foreach ($roles as $r) {
            $rows = $db->table('role_permissions rp')
                ->select('p.code')
                ->join('permissions p', 'p.id = rp.permission_id', 'inner')
                ->where('rp.role_id', (int) $r['id'])
                ->orderBy('p.code', 'ASC')
                ->get()
                ->getResultArray();

            $codes = [];
            foreach ($rows as $row) {
                if (! empty($row['code'])) {
                    $codes[] = (string) $row['code'];
                }
            }
            $codes = array_values(array_unique($codes));

            $roleOut[] = [
                'id' => (int) $r['id'],
                'role_key' => (string) $r['role_key'],
                'label' => $r['label'],
                'permissions' => $codes,
            ];
        }

        return $this->response->setJSON([
            'roles' => $roleOut,
            'permissions' => $allPerms,
        ]);
    }

    public function setRolePermissions($roleKey = null)
    {
        if ($deny = $this->guardApiPermission('access.view')) {
            return $deny;
        }

        $roleKey = trim((string) ($roleKey ?? ''));
        if ($roleKey === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing role']);
        }

        $data = $this->request->getJSON(true);
        if (! $data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        }

        if ($resp = $this->requireAdminPasswordConfirm($data['admin_password'] ?? null)) {
            return $resp;
        }

        $permsIn = $data['permissions'] ?? [];
        if (! is_array($permsIn)) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'permissions must be an array']);
        }

        $codes = [];
        foreach ($permsIn as $p) {
            $p = trim((string) $p);
            if ($p !== '') {
                $codes[] = $p;
            }
        }
        $codes = array_values(array_unique($codes));

        if ($roleKey === 'itadministrator') {
            $mustHave = ['admin.dashboard.view', 'access.view'];
            foreach ($mustHave as $m) {
                if (! in_array($m, $codes, true)) {
                    return $this->response->setStatusCode(422)->setJSON(['error' => 'IT Administrator role must include: ' . $m]);
                }
            }
        }

        $db = \Config\Database::connect();
        if (! ($db->tableExists('roles') && $db->tableExists('permissions') && $db->tableExists('role_permissions'))) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'RBAC tables missing. Run migrations first.']);
        }

        $roleRow = $db->table('roles')->select('id, role_key')->where('role_key', $roleKey)->get()->getRowArray();
        if (! $roleRow) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Role not found']);
        }

        $roleId = (int) $roleRow['id'];
        $beforeRows = $db->table('role_permissions rp')
            ->select('p.code')
            ->join('permissions p', 'p.id = rp.permission_id', 'inner')
            ->where('rp.role_id', $roleId)
            ->orderBy('p.code', 'ASC')
            ->get()
            ->getResultArray();

        $before = [];
        foreach ($beforeRows as $r) {
            if (! empty($r['code'])) {
                $before[] = (string) $r['code'];
            }
        }
        $before = array_values(array_unique($before));

        $now = date('Y-m-d H:i:s');

        $db->transStart();

        foreach ($codes as $code) {
            $permRow = $db->table('permissions')->select('id')->where('code', $code)->get()->getRowArray();
            if (! $permRow) {
                $db->table('permissions')->insert(['code' => $code, 'description' => null, 'created_at' => $now]);
            }
        }

        $db->table('role_permissions')->where('role_id', $roleId)->delete();
        foreach ($codes as $code) {
            $permRow = $db->table('permissions')->select('id')->where('code', $code)->get()->getRowArray();
            if ($permRow && ! empty($permRow['id'])) {
                $db->table('role_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_id' => (int) $permRow['id'],
                    'created_at' => $now,
                ]);
            }
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to save role permissions']);
        }

        $this->writeAuditLog('update', 'role_permissions', $roleId, ['role_key' => $roleKey, 'permissions' => $before], ['role_key' => $roleKey, 'permissions' => $codes]);

        return $this->response->setJSON(['success' => true]);
    }

    private function writeAuditLog(string $action, string $entityType, $entityId = null, $before = null, $after = null)
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('audit_logs')) {
            return;
        }

        $warehouseId = null;
        if ($db->fieldExists('warehouse_id', 'audit_logs')) {
            $warehouseId = $this->currentWarehouseId();
        }

        $actorId = $this->session->get('userID') ? (int) $this->session->get('userID') : null;

        $userAgent = null;
        try {
            $userAgent = (string) $this->request->getUserAgent();
        } catch (\Throwable $e) {
            $userAgent = null;
        }

        $row = [
            'warehouse_id' => $warehouseId,
            'actor_user_id' => $actorId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId !== null ? (int) $entityId : null,
            'before_json' => $before !== null ? json_encode($before) : null,
            'after_json' => $after !== null ? json_encode($after) : null,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $userAgent,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $model = new AuditLogModel();
        $model->insert($row);
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
}