<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AuditLogModel;
use App\Models\UserModel;
use App\Models\WarehouseModel;
use App\Models\InventoryModel;
use App\Models\TransferModel;
use App\Services\AuthorizationService;

class TopManagement extends BaseController
{
    protected $session;
    protected $auth;

    public function __construct()
    {
        helper(['url', 'form']);
        $this->session = session();
        $this->auth = new AuthorizationService();
    }

    private function writeAuditLog(string $action, string $entityType, $entityId = null, $before = null, $after = null, ?int $warehouseIdOverride = null): void
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('audit_logs')) {
            return;
        }

        $warehouseId = $warehouseIdOverride;
        if ($warehouseId === null && ! $db->fieldExists('warehouse_id', 'audit_logs')) {
            $warehouseId = null;
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

    private function guardPagePermission(string $permission): ?\CodeIgniter\HTTP\RedirectResponse
    {
        if (! $this->session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $role = $this->session->get('role');
        if (! $this->isTopManagementRole($role)) {
            return redirect()->to('/login');
        }

        if (! $this->auth->hasPermission($role, $permission)) {
            return redirect()->to('/dashboard');
        }

        return null;
    }

    private function guardApiPermission(string $permission): ?\CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->session->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not logged in']);
        }

        $role = $this->session->get('role');
        if (! $this->isTopManagementRole($role)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        if (! $this->auth->hasPermission($role, $permission)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        return null;
    }

    private function normalizeRole($role): string
    {
        $role = (string) ($role ?? '');
        $role = strtolower(trim($role));
        $role = preg_replace('/\s+/', '', $role);
        $role = str_replace('_', '', $role);
        return $role;
    }

    private function isTopManagementRole($role): bool
    {
        return $this->normalizeRole($role) === 'topmanagement';
    }

    private function viewData(array $extra = []): array
    {
        return array_merge([
            'permissions' => $this->auth->permissionsForRole($this->session->get('role')),
        ], $extra);
    }

    private function readWarehouseIdFromRequest(): ?int
    {
        $val = $this->request->getGet('warehouse_id');
        if ($val === null || $val === '' || ! is_numeric($val)) {
            return null;
        }
        $id = (int) $val;
        return $id > 0 ? $id : null;
    }

    public function index()
    {
        if ($redirect = $this->guardPagePermission('top.dashboard.view')) {
            return $redirect;
        }

        return view('dashboard/Top Management/dashboard', $this->viewData([
            'title' => 'System Overview',
            'active' => 'top-management',
        ]));
    }

    public function inventory()
    {
        if ($redirect = $this->guardPagePermission('inventory.view')) {
            return $redirect;
        }

        return view('dashboard/Top Management/inventory', $this->viewData([
            'title' => 'Inventory Oversight',
            'active' => 'top-management/inventory',
        ]));
    }

    public function profile()
    {
        if ($redirect = $this->guardPagePermission('top.dashboard.view')) {
            return $redirect;
        }

        return view('dashboard/Top Management/profile', $this->viewData([
            'title' => 'My Profile',
            'active' => 'top-management/profile',
        ]));
    }

    public function getProfile()
    {
        if ($deny = $this->guardApiPermission('top.dashboard.view')) {
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

    public function updateProfile()
    {
        if ($deny = $this->guardApiPermission('top.dashboard.view')) {
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

        if ($resp = $this->passwordRateLimitCheck('top_profile')) {
            return $resp;
        }

        $model = new UserModel();
        $existing = $model->find($userId);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }

        $hash = (string) ($existing['password'] ?? '');
        if ($hash === '' || ! password_verify($currentPassword, $hash)) {
            $this->passwordRateLimitFail('top_profile');
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Invalid password']);
        }

        $this->passwordRateLimitClear('top_profile');

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

    public function transfers()
    {
        if ($redirect = $this->guardPagePermission('transfers.view')) {
            return $redirect;
        }

        return view('dashboard/Top Management/transfers', $this->viewData([
            'title' => 'Transfers',
            'active' => 'top-management/transfers',
        ]));
    }

    public function approvals()
    {
        if ($redirect = $this->guardPagePermission('transfers.approve')) {
            return $redirect;
        }

        return view('dashboard/Top Management/approvals', $this->viewData([
            'title' => 'Approvals Center',
            'active' => 'top-management/approvals',
        ]));
    }

    public function finance()
    {
        if ($redirect = $this->guardPagePermission('finance.view')) {
            return $redirect;
        }

        return view('dashboard/Top Management/finance', $this->viewData([
            'title' => 'Finance Overview',
            'active' => 'top-management/finance',
        ]));
    }

    public function reports()
    {
        if ($redirect = $this->guardPagePermission('reports.view')) {
            return $redirect;
        }

        return view('dashboard/Top Management/reports', $this->viewData([
            'title' => 'Reports',
            'active' => 'top-management/reports',
        ]));
    }

    public function audit()
    {
        if ($redirect = $this->guardPagePermission('logs.view')) {
            return $redirect;
        }

        return view('dashboard/Top Management/audit', $this->viewData([
            'title' => 'Audit & Compliance',
            'active' => 'top-management/audit',
        ]));
    }

    public function warehouses()
    {
        if ($deny = $this->guardApiPermission('top.dashboard.view')) {
            return $deny;
        }

        $db = \Config\Database::connect();
        $rows = [];
        try {
            if ($db->tableExists('warehouses')) {
                $rows = (new WarehouseModel())->orderBy('name', 'ASC')->findAll();
            }
        } catch (\Throwable $e) {
            $rows = [];
        }

        return $this->response->setJSON(['warehouses' => $rows]);
    }

    public function notificationsApi()
    {
        if ($deny = $this->guardApiPermission('top.dashboard.view')) {
            return $deny;
        }

        $warehouseId = $this->readWarehouseIdFromRequest();
        $db = \Config\Database::connect();
        $items = [];

        // Low stock
        try {
            if ($db->tableExists('inventory')) {
                $inv = new InventoryModel();
                $threshold = $inv->getLowStockThreshold();
                $builder = $db->table('inventory i');
                if ($warehouseId !== null) {
                    $builder->where('i.warehouse_id', $warehouseId);
                }
                $low = (int) $builder->where('i.quantity <=', (int) $threshold)->countAllResults();
                if ($low > 0) {
                    $items[] = [
                        'type' => 'warning',
                        'title' => 'Low stock',
                        'message' => $low . ' item(s) at or below threshold (' . (int) $threshold . ')',
                        'created_at' => date('Y-m-d H:i:s'),
                        'link' => site_url('top-management/inventory'),
                    ];
                }
            }
        } catch (\Throwable $e) {
        }

        // Pending approvals (transfers)
        try {
            if ($db->tableExists('transfers')) {
                $builder = $db->table('transfers t')->where('t.status', 'pending');
                if ($warehouseId !== null) {
                    $builder->groupStart()->where('t.from_warehouse_id', $warehouseId)->orWhere('t.to_warehouse_id', $warehouseId)->groupEnd();
                }
                $pending = (int) $builder->countAllResults();
                if ($pending > 0) {
                    $items[] = [
                        'type' => 'info',
                        'title' => 'Transfer approvals',
                        'message' => $pending . ' pending transfer(s) awaiting decision',
                        'created_at' => date('Y-m-d H:i:s'),
                        'link' => site_url('top-management/approvals'),
                    ];
                }
            }
        } catch (\Throwable $e) {
        }

        // Pending approvals (purchase orders)
        try {
            if ($db->tableExists('purchase_orders')) {
                $builder = $db->table('purchase_orders po')->where('po.status', 'pending');
                if ($warehouseId !== null && $db->tableExists('inventory')) {
                    $builder->join('inventory i', 'i.id = po.id', 'left')->where('i.warehouse_id', $warehouseId);
                }
                $poPending = (int) $builder->countAllResults();
                if ($poPending > 0) {
                    $items[] = [
                        'type' => 'info',
                        'title' => 'Purchase orders',
                        'message' => $poPending . ' pending purchase order(s) awaiting decision',
                        'created_at' => date('Y-m-d H:i:s'),
                        'link' => site_url('top-management/approvals'),
                    ];
                }
            }
        } catch (\Throwable $e) {
        }

        // Recent audit activity
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
                        'message' => $this->simpleAuditSummary($r),
                        'created_at' => $r['created_at'] ?? null,
                        'link' => site_url('top-management/audit'),
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

    public function inventoryOverview()
    {
        if ($deny = $this->guardApiPermission('inventory.view')) {
            return $deny;
        }

        $warehouseId = $this->readWarehouseIdFromRequest();
        $db = \Config\Database::connect();

        $metrics = [
            'total_items' => 0,
            'total_quantity' => 0,
            'low_stock_count' => 0,
        ];
        $byCategory = [];

        if (! $db->tableExists('inventory')) {
            return $this->response->setJSON(['metrics' => $metrics, 'by_category' => [], 'threshold' => 10]);
        }

        $inv = new InventoryModel();
        $threshold = $inv->getLowStockThreshold();

        $builder = $db->table('inventory i');
        if ($warehouseId !== null) {
            $builder->where('i.warehouse_id', $warehouseId);
        }

        $row = $builder->select(
            'COUNT(i.id) as total_items, COALESCE(SUM(i.quantity),0) as total_qty, COALESCE(SUM(CASE WHEN i.quantity <= ' . (int) $threshold . ' THEN 1 ELSE 0 END),0) as low_count'
        )->get()->getRowArray();

        $metrics['total_items'] = (int) ($row['total_items'] ?? 0);
        $metrics['total_quantity'] = (int) ($row['total_qty'] ?? 0);
        $metrics['low_stock_count'] = (int) ($row['low_count'] ?? 0);

        $catBuilder = $db->table('inventory i')->select('i.category, COUNT(i.id) as items, COALESCE(SUM(i.quantity),0) as qty')
            ->groupBy('i.category')
            ->orderBy('qty', 'DESC');
        if ($warehouseId !== null) {
            $catBuilder->where('i.warehouse_id', $warehouseId);
        }
        $byCategory = $catBuilder->get()->getResultArray();

        return $this->response->setJSON([
            'metrics' => $metrics,
            'by_category' => $byCategory,
            'threshold' => $threshold,
        ]);
    }

    public function inventoryLowStock()
    {
        if ($deny = $this->guardApiPermission('inventory.view')) {
            return $deny;
        }

        $warehouseId = $this->readWarehouseIdFromRequest();
        $limit = (int) ($this->request->getGet('limit') ?? 50);
        if ($limit <= 0) {
            $limit = 50;
        }
        if ($limit > 200) {
            $limit = 200;
        }

        $db = \Config\Database::connect();
        if (! $db->tableExists('inventory')) {
            return $this->response->setJSON(['items' => []]);
        }

        $inv = new InventoryModel();
        $threshold = $inv->getLowStockThreshold();
        $builder = $db->table('inventory i')
            ->select('i.id, i.name, i.sku, i.category, i.quantity, i.status, i.warehouse_id, w.name as warehouse_name')
            ->join('warehouses w', 'w.id = i.warehouse_id', 'left')
            ->where('i.quantity <=', $threshold)
            ->orderBy('i.quantity', 'ASC')
            ->limit($limit);
        if ($warehouseId !== null) {
            $builder->where('i.warehouse_id', $warehouseId);
        }
        $rows = $builder->get()->getResultArray();

        return $this->response->setJSON(['items' => $rows, 'threshold' => $threshold]);
    }

    public function transferHistory()
    {
        if ($deny = $this->guardApiPermission('transfers.view')) {
            return $deny;
        }

        $warehouseId = $this->readWarehouseIdFromRequest();
        $limit = (int) ($this->request->getGet('limit') ?? 50);
        if ($limit <= 0) {
            $limit = 50;
        }
        if ($limit > 200) {
            $limit = 200;
        }

        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $db = \Config\Database::connect();
        if (! $db->tableExists('transfers')) {
            return $this->response->setJSON(['transfers' => []]);
        }

        $builder = $db->table('transfers t')
            ->select('t.*, i.name as item_name, i.sku as item_sku, w1.name as from_warehouse_name, w2.name as to_warehouse_name')
            ->join('inventory i', 'i.id = t.item_id', 'left')
            ->join('warehouses w1', 'w1.id = t.from_warehouse_id', 'left')
            ->join('warehouses w2', 'w2.id = t.to_warehouse_id', 'left')
            ->orderBy('t.created_at', 'DESC')
            ->limit($limit);

        if ($status !== '') {
            $builder->where('t.status', $status);
        }
        if ($warehouseId !== null) {
            $builder->groupStart()->where('t.from_warehouse_id', $warehouseId)->orWhere('t.to_warehouse_id', $warehouseId)->groupEnd();
        }

        $rows = $builder->get()->getResultArray();
        return $this->response->setJSON(['transfers' => $rows]);
    }

    public function pendingPurchaseOrders()
    {
        if ($deny = $this->guardApiPermission('po.view')) {
            return $deny;
        }

        $warehouseId = $this->readWarehouseIdFromRequest();
        $limit = (int) ($this->request->getGet('limit') ?? 50);
        if ($limit <= 0) {
            $limit = 50;
        }
        if ($limit > 200) {
            $limit = 200;
        }

        $db = \Config\Database::connect();
        if (! $db->tableExists('purchase_orders')) {
            return $this->response->setJSON(['purchase_orders' => []]);
        }

        $builder = $db->table('purchase_orders po')
            ->select('po.purchase_order_id, po.order_number, po.id as inventory_id, i.name as item_name, i.sku as item_sku, i.warehouse_id, w.name as warehouse_name, po.vendor, po.quantity, po.price, po.total_amount, po.order_date, po.status')
            ->join('inventory i', 'i.id = po.id', 'left')
            ->join('warehouses w', 'w.id = i.warehouse_id', 'left')
            ->where('po.status', 'pending')
            ->orderBy('po.order_date', 'DESC')
            ->limit($limit);

        if ($warehouseId !== null) {
            $builder->where('i.warehouse_id', $warehouseId);
        }

        $rows = $builder->get()->getResultArray();
        return $this->response->setJSON(['purchase_orders' => $rows]);
    }

    public function decidePurchaseOrder($purchaseOrderId = null)
    {
        if ($deny = $this->guardApiPermission('po.approve')) {
            return $deny;
        }

        if (empty($purchaseOrderId) || ! is_numeric($purchaseOrderId)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid purchase order id']);
        }

        $db = \Config\Database::connect();
        if (! $db->tableExists('purchase_orders')) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'purchase_orders table not found']);
        }

        $data = $this->request->getJSON(true) ?? [];
        $action = (string) ($data['action'] ?? 'approve');
        if (! in_array($action, ['approve', 'reject'], true)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid action']);
        }

        // purchase_orders.status enum: pending, approved, received, canceled
        $status = $action === 'approve' ? 'approved' : 'canceled';

        $row = $db->table('purchase_orders')->where('purchase_order_id', (int) $purchaseOrderId)->get()->getRowArray();
        if (! $row) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Purchase order not found']);
        }

        $warehouseId = null;
        try {
            if ($db->tableExists('inventory') && isset($row['id'])) {
                $invRow = $db->table('inventory')->select('warehouse_id')->where('id', (int) $row['id'])->get()->getRowArray();
                if ($invRow && isset($invRow['warehouse_id']) && is_numeric($invRow['warehouse_id'])) {
                    $warehouseId = (int) $invRow['warehouse_id'];
                }
            }
        } catch (\Throwable $e) {
            $warehouseId = null;
        }

        $ok = $db->table('purchase_orders')->where('purchase_order_id', (int) $purchaseOrderId)->update(['status' => $status]);
        if (! $ok) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to update purchase order']);
        }

        $after = $row;
        $after['status'] = $status;
        $this->writeAuditLog(
            $action === 'approve' ? 'approve' : 'reject',
            'purchase_order',
            (int) $purchaseOrderId,
            $row,
            $after,
            $warehouseId
        );

        return $this->response->setJSON(['success' => true, 'status' => $status]);
    }

    public function financeSummary()
    {
        if ($deny = $this->guardApiPermission('finance.view')) {
            return $deny;
        }

        $db = \Config\Database::connect();
        $out = [
            'ap' => ['pending' => 0, 'approved' => 0, 'paid' => 0, 'overdue' => 0, 'total_amount' => 0],
            'ar' => ['unpaid' => 0, 'paid' => 0, 'overdue' => 0, 'total_amount_due' => 0],
            'po' => ['pending' => 0, 'approved' => 0, 'received' => 0, 'canceled' => 0, 'total_amount' => 0],
        ];

        try {
            if ($db->tableExists('invoices')) {
                $rows = $db->table('invoices')->select('status, COALESCE(SUM(amount),0) as amt, COUNT(*) as cnt')->groupBy('status')->get()->getResultArray();
                $sum = 0;
                foreach ($rows as $r) {
                    $status = (string) ($r['status'] ?? '');
                    $cnt = (int) ($r['cnt'] ?? 0);
                    $amt = (float) ($r['amt'] ?? 0);
                    if (isset($out['ap'][$status])) {
                        $out['ap'][$status] = $cnt;
                    }
                    $sum += $amt;
                }
                $out['ap']['total_amount'] = $sum;
            }
        } catch (\Throwable $e) {
        }

        try {
            if ($db->tableExists('ar_invoices')) {
                $rows = $db->table('ar_invoices')->select('status, COALESCE(SUM(amount_due),0) as amt, COUNT(*) as cnt')->groupBy('status')->get()->getResultArray();
                $sum = 0;
                foreach ($rows as $r) {
                    $status = (string) ($r['status'] ?? '');
                    $cnt = (int) ($r['cnt'] ?? 0);
                    $amt = (float) ($r['amt'] ?? 0);
                    if (isset($out['ar'][$status])) {
                        $out['ar'][$status] = $cnt;
                    }
                    $sum += $amt;
                }
                $out['ar']['total_amount_due'] = $sum;
            }
        } catch (\Throwable $e) {
        }

        try {
            if ($db->tableExists('purchase_orders')) {
                $rows = $db->table('purchase_orders')->select('status, COALESCE(SUM(total_amount),0) as amt, COUNT(*) as cnt')->groupBy('status')->get()->getResultArray();
                $sum = 0;
                foreach ($rows as $r) {
                    $status = (string) ($r['status'] ?? '');
                    $cnt = (int) ($r['cnt'] ?? 0);
                    $amt = (float) ($r['amt'] ?? 0);
                    if (isset($out['po'][$status])) {
                        $out['po'][$status] = $cnt;
                    }
                    $sum += $amt;
                }
                $out['po']['total_amount'] = $sum;
            }
        } catch (\Throwable $e) {
        }

        return $this->response->setJSON($out);
    }

    public function overview()
    {
        if ($deny = $this->guardApiPermission('top.dashboard.view')) {
            return $deny;
        }

        $warehouseId = $this->readWarehouseIdFromRequest();
        $db = \Config\Database::connect();

        $metrics = [
            'inventory_total_quantity' => 0,
            'inventory_total_items' => 0,
            'low_stock_count' => 0,
            'inbound_qty' => 0,
            'outbound_qty' => 0,
            'discrepancies_open' => 0,
            'open_tickets' => 0,
            'pending_approvals' => 0,
        ];

        $comparison = [];
        $topItems = [];
        $alerts = [];

        try {
            if ($db->tableExists('inventory')) {
                $inv = new InventoryModel();
                $threshold = $inv->getLowStockThreshold();

                $builder = $db->table('inventory i');
                if ($warehouseId !== null) {
                    $builder->where('i.warehouse_id', $warehouseId);
                }

                $row = $builder->select(
                    'COUNT(i.id) as total_items, COALESCE(SUM(i.quantity),0) as total_qty, COALESCE(SUM(CASE WHEN i.quantity <= ' . (int) $threshold . ' THEN 1 ELSE 0 END),0) as low_count'
                )->get()->getRowArray();

                $metrics['inventory_total_items'] = (int) ($row['total_items'] ?? 0);
                $metrics['inventory_total_quantity'] = (int) ($row['total_qty'] ?? 0);
                $metrics['low_stock_count'] = (int) ($row['low_count'] ?? 0);
            }
        } catch (\Throwable $e) {
        }

        try {
            if ($db->tableExists('transfers')) {
                $tBuilder = $db->table('transfers t');
                if ($warehouseId !== null) {
                    $tBuilder->groupStart()->where('t.from_warehouse_id', $warehouseId)->orWhere('t.to_warehouse_id', $warehouseId)->groupEnd();
                }

                $pending = (int) $tBuilder->where('t.status', 'pending')->countAllResults(false);
                $metrics['pending_approvals'] = $pending;

                // inbound/outbound for the selected warehouse (counts transferred qty)
                if ($warehouseId !== null) {
                    $inRow = $db->table('transfers')->select('COALESCE(SUM(quantity),0) as qty')
                        ->where('to_warehouse_id', $warehouseId)
                        ->get()->getRowArray();
                    $outRow = $db->table('transfers')->select('COALESCE(SUM(quantity),0) as qty')
                        ->where('from_warehouse_id', $warehouseId)
                        ->get()->getRowArray();
                    $metrics['inbound_qty'] = (int) ($inRow['qty'] ?? 0);
                    $metrics['outbound_qty'] = (int) ($outRow['qty'] ?? 0);
                } else {
                    $inTotal = $db->table('transfers')->select('COALESCE(SUM(quantity),0) as qty')->where('to_warehouse_id IS NOT NULL', null, false)->get()->getRowArray();
                    $outTotal = $db->table('transfers')->select('COALESCE(SUM(quantity),0) as qty')->where('from_warehouse_id IS NOT NULL', null, false)->get()->getRowArray();
                    $metrics['inbound_qty'] = (int) ($inTotal['qty'] ?? 0);
                    $metrics['outbound_qty'] = (int) ($outTotal['qty'] ?? 0);
                }

                // top moving items from transfers
                $topBuilder = $db->table('transfers t')
                    ->select('t.item_id, COALESCE(SUM(t.quantity),0) as moved_qty, i.name as item_name, i.sku as item_sku')
                    ->join('inventory i', 'i.id = t.item_id', 'left')
                    ->groupBy('t.item_id, i.name, i.sku')
                    ->orderBy('moved_qty', 'DESC')
                    ->limit(10);
                if ($warehouseId !== null) {
                    $topBuilder->groupStart()->where('t.from_warehouse_id', $warehouseId)->orWhere('t.to_warehouse_id', $warehouseId)->groupEnd();
                }
                $topItems = $topBuilder->get()->getResultArray();
            }
        } catch (\Throwable $e) {
        }

        try {
            if ($db->tableExists('tickets')) {
                $ticketBuilder = $db->table('tickets');
                if ($warehouseId !== null) {
                    $ticketBuilder->where('warehouse_id', $warehouseId);
                }
                $metrics['open_tickets'] = (int) $ticketBuilder->where('status !=', 'closed')->countAllResults();
            }
        } catch (\Throwable $e) {
        }

        try {
            if ($db->tableExists('decrepancy') && $db->tableExists('inventory')) {
                $decBuilder = $db->table('decrepancy d')->join('inventory i', 'i.id = d.id', 'left');
                if ($warehouseId !== null) {
                    $decBuilder->where('i.warehouse_id', $warehouseId);
                }
                $metrics['discrepancies_open'] = (int) $decBuilder->where('d.status !=', 'resolved')->where('d.status !=', 'closed')->countAllResults();
            }
        } catch (\Throwable $e) {
        }

        try {
            if ($db->tableExists('warehouses')) {
                $wModel = new WarehouseModel();
                $comparison = $wModel->getAllWithCounts();
            }
        } catch (\Throwable $e) {
            $comparison = [];
        }

        // alerts
        if ($metrics['low_stock_count'] > 0) {
            $alerts[] = ['type' => 'warning', 'message' => 'Low stock critical'];
        }
        if ($metrics['discrepancies_open'] > 0) {
            $alerts[] = ['type' => 'warning', 'message' => 'Large variance detected'];
        }
        if ($metrics['pending_approvals'] > 0) {
            $alerts[] = ['type' => 'info', 'message' => 'Pending approvals require attention'];
        }

        // transfer delays: pending older than 2 days
        try {
            if ($db->tableExists('transfers')) {
                $delayBuilder = $db->table('transfers t')->where('t.status', 'pending')->where('t.created_at <', date('Y-m-d H:i:s', time() - 2 * 86400));
                if ($warehouseId !== null) {
                    $delayBuilder->groupStart()->where('t.from_warehouse_id', $warehouseId)->orWhere('t.to_warehouse_id', $warehouseId)->groupEnd();
                }
                $delays = (int) $delayBuilder->countAllResults();
                if ($delays > 0) {
                    $alerts[] = ['type' => 'warning', 'message' => 'Transfer delays'];
                }
            }
        } catch (\Throwable $e) {
        }

        // over-capacity
        try {
            if ($db->tableExists('warehouses') && $db->fieldExists('capacity', 'warehouses') && $db->fieldExists('current_usage', 'warehouses')) {
                $capBuilder = $db->table('warehouses');
                if ($warehouseId !== null) {
                    $capBuilder->where('id', $warehouseId);
                }
                $rows = $capBuilder->select('id, name, capacity, current_usage')->get()->getResultArray();
                foreach ($rows as $r) {
                    $cap = (float) ($r['capacity'] ?? 0);
                    $usage = (float) ($r['current_usage'] ?? 0);
                    if ($cap > 0 && ($usage / $cap) >= 0.9) {
                        $alerts[] = ['type' => 'warning', 'message' => 'Over-capacity risk'];
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        return $this->response->setJSON([
            'metrics' => $metrics,
            'comparison' => $comparison,
            'top_items' => $topItems,
            'alerts' => $alerts,
        ]);
    }

    public function pendingTransfers()
    {
        if ($deny = $this->guardApiPermission('transfers.approve')) {
            return $deny;
        }

        $warehouseId = $this->readWarehouseIdFromRequest();
        $model = new TransferModel();

        try {
            $rows = $model->getPendingTransfers($warehouseId, null);
        } catch (\Throwable $e) {
            $rows = [];
        }

        return $this->response->setJSON(['transfers' => $rows]);
    }

    public function decideTransfer($transferId = null)
    {
        if ($deny = $this->guardApiPermission('transfers.approve')) {
            return $deny;
        }

        if (empty($transferId) || ! is_numeric($transferId)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid transfer id']);
        }

        $data = $this->request->getJSON(true) ?? [];
        $action = (string) ($data['action'] ?? 'approve');
        $notes = (string) ($data['notes'] ?? '');

        if (! in_array($action, ['approve', 'reject'], true)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid action']);
        }

        $status = $action === 'approve' ? 'approved' : 'rejected';
        $approverId = (int) ($this->session->get('userID') ?? 0);

        $model = new TransferModel();
        $row = $model->find((int) $transferId);
        if (! $row) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Transfer not found']);
        }

        $before = $row;
        $warehouseId = null;
        if (isset($row['from_warehouse_id']) && is_numeric($row['from_warehouse_id'])) {
            $warehouseId = (int) $row['from_warehouse_id'];
        } elseif (isset($row['to_warehouse_id']) && is_numeric($row['to_warehouse_id'])) {
            $warehouseId = (int) $row['to_warehouse_id'];
        }

        $ok = $model->updateTransferStatus((int) $transferId, $status, $approverId, $notes);
        if (! $ok) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to update transfer']);
        }

        $after = $before;
        $after['status'] = $status;
        $after['approved_by'] = $approverId;
        $after['approval_notes'] = $notes;
        $this->writeAuditLog(
            $action === 'approve' ? 'approve' : 'reject',
            'transfer',
            (int) $transferId,
            $before,
            $after,
            $warehouseId
        );

        return $this->response->setJSON(['success' => true, 'status' => $status]);
    }

    public function auditLogs()
    {
        if ($deny = $this->guardApiPermission('logs.view')) {
            return $deny;
        }

        $warehouseId = $this->readWarehouseIdFromRequest();
        $q = trim((string) ($this->request->getGet('q') ?? ''));
        $from = trim((string) ($this->request->getGet('from') ?? ''));
        $to = trim((string) ($this->request->getGet('to') ?? ''));
        $limit = (int) ($this->request->getGet('limit') ?? 10);
        if ($limit <= 0) {
            $limit = 10;
        }
        if ($limit > 100) {
            $limit = 100;
        }

        $db = \Config\Database::connect();
        if (! $db->tableExists('audit_logs')) {
            return $this->response->setJSON(['logs' => []]);
        }

        $builder = $db->table('audit_logs a')
            ->select('a.id, a.warehouse_id, a.action, a.entity_type, a.entity_id, a.created_at, u.name as actor_name, u.email as actor_email')
            ->join('users u', 'u.id = a.actor_user_id', 'left')
            ->orderBy('a.id', 'DESC')
            ->limit($limit);

        if ($warehouseId !== null && $db->fieldExists('warehouse_id', 'audit_logs')) {
            $builder->groupStart()->where('a.warehouse_id', $warehouseId)->orWhere('a.warehouse_id', null)->groupEnd();
        }

        if ($q !== '') {
            $builder->groupStart()
                ->like('a.action', $q)
                ->orLike('a.entity_type', $q)
                ->orLike('u.name', $q)
                ->orLike('u.email', $q)
                ->groupEnd();
        }

        if ($from !== '') {
            // Accept YYYY-MM-DD or YYYY-MM-DD HH:MM:SS
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
                $from .= ' 00:00:00';
            }
            $builder->where('a.created_at >=', $from);
        }
        if ($to !== '') {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
                $to .= ' 23:59:59';
            }
            $builder->where('a.created_at <=', $to);
        }

        $rows = $builder->get()->getResultArray();
        foreach ($rows as &$r) {
            $r['summary'] = $this->simpleAuditSummary($r);
        }
        unset($r);

        return $this->response->setJSON(['logs' => $rows]);
    }

    private function csvResponse(string $filename, array $rows): \CodeIgniter\HTTP\ResponseInterface
    {
        $fh = fopen('php://temp', 'w+');
        if ($rows !== []) {
            fputcsv($fh, array_keys($rows[0]));
            foreach ($rows as $r) {
                fputcsv($fh, $r);
            }
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    public function reportInventory()
    {
        if ($deny = $this->guardApiPermission('reports.view')) {
            return $deny;
        }

        $warehouseId = $this->readWarehouseIdFromRequest();
        $db = \Config\Database::connect();
        if (! $db->tableExists('inventory')) {
            return $this->csvResponse('inventory.csv', []);
        }

        $builder = $db->table('inventory i')
            ->select('i.id, i.name, i.sku, i.category, i.quantity, i.status, i.warehouse_id, w.name as warehouse_name')
            ->join('warehouses w', 'w.id = i.warehouse_id', 'left')
            ->orderBy('i.name', 'ASC');
        if ($warehouseId !== null) {
            $builder->where('i.warehouse_id', $warehouseId);
        }

        $rows = $builder->get()->getResultArray();
        return $this->csvResponse('inventory.csv', $rows);
    }

    public function reportTransfers()
    {
        if ($deny = $this->guardApiPermission('reports.view')) {
            return $deny;
        }

        $warehouseId = $this->readWarehouseIdFromRequest();
        $db = \Config\Database::connect();
        if (! $db->tableExists('transfers')) {
            return $this->csvResponse('transfers.csv', []);
        }

        $builder = $db->table('transfers t')
            ->select('t.id, t.status, t.quantity, t.created_at, t.approved_at, t.created_by, i.name as item_name, i.sku as item_sku, w1.name as from_warehouse, w2.name as to_warehouse')
            ->join('inventory i', 'i.id = t.item_id', 'left')
            ->join('warehouses w1', 'w1.id = t.from_warehouse_id', 'left')
            ->join('warehouses w2', 'w2.id = t.to_warehouse_id', 'left')
            ->orderBy('t.created_at', 'DESC');
        if ($warehouseId !== null) {
            $builder->groupStart()->where('t.from_warehouse_id', $warehouseId)->orWhere('t.to_warehouse_id', $warehouseId)->groupEnd();
        }
        $rows = $builder->get()->getResultArray();
        return $this->csvResponse('transfers.csv', $rows);
    }

    public function reportApprovals()
    {
        if ($deny = $this->guardApiPermission('reports.view')) {
            return $deny;
        }

        $warehouseId = $this->readWarehouseIdFromRequest();
        $db = \Config\Database::connect();
        $rows = [];

        if ($db->tableExists('transfers')) {
            $t = $db->table('transfers t')
                ->select("'transfer' as type, t.id as ref_id, t.status, t.quantity, t.created_at, i.name as item_name, w1.name as from_warehouse, w2.name as to_warehouse")
                ->join('inventory i', 'i.id = t.item_id', 'left')
                ->join('warehouses w1', 'w1.id = t.from_warehouse_id', 'left')
                ->join('warehouses w2', 'w2.id = t.to_warehouse_id', 'left')
                ->where('t.status', 'pending');
            if ($warehouseId !== null) {
                $t->groupStart()->where('t.from_warehouse_id', $warehouseId)->orWhere('t.to_warehouse_id', $warehouseId)->groupEnd();
            }
            $rows = array_merge($rows, $t->get()->getResultArray());
        }

        if ($db->tableExists('purchase_orders')) {
            $po = $db->table('purchase_orders po')
                ->select("'purchase_order' as type, po.purchase_order_id as ref_id, po.status, po.quantity, po.order_date as created_at, i.name as item_name, w.name as from_warehouse, '' as to_warehouse")
                ->join('inventory i', 'i.id = po.id', 'left')
                ->join('warehouses w', 'w.id = i.warehouse_id', 'left')
                ->where('po.status', 'pending');
            if ($warehouseId !== null) {
                $po->where('i.warehouse_id', $warehouseId);
            }
            $rows = array_merge($rows, $po->get()->getResultArray());
        }

        return $this->csvResponse('approvals.csv', $rows);
    }

    public function reportAuditLogs()
    {
        if ($deny = $this->guardApiPermission('reports.view')) {
            return $deny;
        }

        $warehouseId = $this->readWarehouseIdFromRequest();
        $from = trim((string) ($this->request->getGet('from') ?? ''));
        $to = trim((string) ($this->request->getGet('to') ?? ''));
        $db = \Config\Database::connect();
        if (! $db->tableExists('audit_logs')) {
            return $this->csvResponse('audit_logs.csv', []);
        }

        $builder = $db->table('audit_logs a')
            ->select('a.id, a.warehouse_id, a.action, a.entity_type, a.entity_id, a.created_at, u.name as actor_name, u.email as actor_email')
            ->join('users u', 'u.id = a.actor_user_id', 'left')
            ->orderBy('a.id', 'DESC');
        if ($warehouseId !== null && $db->fieldExists('warehouse_id', 'audit_logs')) {
            $builder->groupStart()->where('a.warehouse_id', $warehouseId)->orWhere('a.warehouse_id', null)->groupEnd();
        }

        if ($from !== '') {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
                $from .= ' 00:00:00';
            }
            $builder->where('a.created_at >=', $from);
        }
        if ($to !== '') {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
                $to .= ' 23:59:59';
            }
            $builder->where('a.created_at <=', $to);
        }
        $rows = $builder->get()->getResultArray();
        return $this->csvResponse('audit_logs.csv', $rows);
    }

    private function simpleAuditSummary(array $row): string
    {
        $action = (string) ($row['action'] ?? '');
        $entity = (string) ($row['entity_type'] ?? '');
        $id = $row['entity_id'] ?? null;
        $base = trim(ucfirst($action) . ' ' . $entity);
        if ($base === '') {
            return '';
        }
        if ($id !== null && $id !== '') {
            return $base . ' #' . $id;
        }
        return $base;
    }
}
