<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\TicketModel;
use App\Models\TicketCommentModel;
use App\Models\TicketHistoryModel;
use App\Models\UserModel;
use App\Services\AuthorizationService;

class TicketController extends Controller
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

    private function canViewTicket(array $ticket): bool
    {
        $db = \Config\Database::connect();
        if ($this->auth->isItAdminRole($this->session->get('role')) && $db->fieldExists('warehouse_id', 'tickets')) {
            $wid = $this->session->get('currentWarehouseId');
            if ($wid === null || $wid === '' || ! is_numeric($wid)) {
                return false;
            }
            $wid = (int) $wid;
            if ($wid > 0 && isset($ticket['warehouse_id']) && $ticket['warehouse_id'] !== null && (int) $ticket['warehouse_id'] !== $wid) {
                return false;
            }
        }

        if ($this->auth->hasPermission($this->session->get('role'), 'ticket.view_all')) {
            return true;
        }

        $uid = (int) ($this->session->get('userID') ?? 0);
        return ((int) ($ticket['requester_id'] ?? 0) === $uid) || ((int) ($ticket['assignee_id'] ?? 0) === $uid);
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

    private function writeTicketHistory(int $ticketId, string $action, $before = null, $after = null): void
    {
        $model = new TicketHistoryModel();
        $model->insert([
            'ticket_id' => $ticketId,
            'actor_user_id' => $this->session->get('userID') ? (int) $this->session->get('userID') : null,
            'action' => $action,
            'before_json' => $before !== null ? json_encode($before) : null,
            'after_json' => $after !== null ? json_encode($after) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function index()
    {
        if ($deny = $this->guardApi()) {
            return $deny;
        }

        $role = $this->session->get('role');
        $canAll = $this->auth->hasPermission($role, 'ticket.view_all');
        $canOwn = $this->auth->hasPermission($role, 'ticket.view_own') || $this->auth->hasPermission($role, 'ticket.create');
        if (! $canAll && ! $canOwn) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        $q = trim((string) ($this->request->getGet('q') ?? ''));
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $priority = trim((string) ($this->request->getGet('priority') ?? ''));

        $model = new TicketModel();
        $builder = $model->builder()->select('id, warehouse_id, title, priority, status, requester_id, assignee_id, created_at, updated_at');

        $db = \Config\Database::connect();
        if ($this->auth->isItAdminRole($role) && $db->fieldExists('warehouse_id', 'tickets')) {
            $wid = $this->session->get('currentWarehouseId');
            if ($wid === null || $wid === '' || ! is_numeric($wid) || (int) $wid <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Please select a warehouse']);
            }
            $builder->where('warehouse_id', (int) $wid);
        }

        if ($q !== '') {
            $builder->groupStart()
                ->like('title', $q)
                ->orLike('description', $q)
                ->groupEnd();
        }
        if ($status !== '') {
            $builder->where('status', $status);
        }
        if ($priority !== '') {
            $builder->where('priority', $priority);
        }

        if (! $canAll) {
            $uid = (int) ($this->session->get('userID') ?? 0);
            $builder->groupStart()
                ->where('requester_id', $uid)
                ->orWhere('assignee_id', $uid)
                ->groupEnd();
        }

        $rows = $builder->orderBy('id', 'DESC')->get()->getResultArray();
        return $this->response->setJSON($rows);
    }

    public function create()
    {
        if ($deny = $this->requirePermission('ticket.create')) {
            return $deny;
        }

        $data = $this->request->getJSON(true);
        if (! $data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        }

        if ($resp = $this->validateJson($data, [
            'title' => 'required|min_length[3]|max_length[200]',
            'description' => 'permit_empty|max_length[5000]',
            'priority' => 'permit_empty|max_length[20]',
        ])) {
            return $resp;
        }

        $insert = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'] ?? 'normal',
            'status' => 'open',
            'requester_id' => (int) $this->session->get('userID'),
            'assignee_id' => null,
        ];

        $db = \Config\Database::connect();
        if ($this->auth->isItAdminRole($this->session->get('role')) && $db->fieldExists('warehouse_id', 'tickets')) {
            $wid = $this->session->get('currentWarehouseId');
            if ($wid === null || $wid === '' || ! is_numeric($wid) || (int) $wid <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Please select a warehouse']);
            }
            $insert['warehouse_id'] = (int) $wid;
        }

        $model = new TicketModel();
        $id = $model->insert($insert);
        if ($id === false) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Create failed', 'details' => $model->errors()]);
        }

        $ticket = $model->find($id);
        $this->writeTicketHistory((int) $id, 'create', null, $ticket);
        return $this->response->setJSON(['success' => true, 'ticket' => $ticket]);
    }

    public function show($id = null)
    {
        if ($deny = $this->guardApi()) {
            return $deny;
        }

        if (empty($id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing id']);
        }

        $model = new TicketModel();
        $ticket = $model->find($id);
        if (! $ticket) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Ticket not found']);
        }

        if (! $this->canViewTicket($ticket)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        $commentModel = new TicketCommentModel();
        $comments = $commentModel->where('ticket_id', (int) $id)->orderBy('id', 'ASC')->findAll();

        return $this->response->setJSON(['ticket' => $ticket, 'comments' => $comments]);
    }

    public function update($id = null)
    {
        if ($deny = $this->guardApi()) {
            return $deny;
        }

        if (empty($id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing id']);
        }

        $model = new TicketModel();
        $existing = $model->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Ticket not found']);
        }

        $db = \Config\Database::connect();
        if ($this->auth->isItAdminRole($this->session->get('role')) && $db->fieldExists('warehouse_id', 'tickets')) {
            $wid = $this->session->get('currentWarehouseId');
            if ($wid === null || $wid === '' || ! is_numeric($wid) || (int) $wid <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Please select a warehouse']);
            }
        }

        if (! $this->canViewTicket($existing)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        $uid = (int) ($this->session->get('userID') ?? 0);
        $canManage = $this->auth->hasPermission($this->session->get('role'), 'ticket.manage');
        if (! $canManage && (int) $existing['requester_id'] !== $uid) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        $data = $this->request->getJSON(true);
        if (! $data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        }

        if ($resp = $this->validateJson($data, [
            'title' => 'permit_empty|min_length[3]|max_length[200]',
            'description' => 'permit_empty|max_length[5000]',
            'priority' => 'permit_empty|max_length[20]',
        ])) {
            return $resp;
        }

        $update = [];
        if (array_key_exists('title', $data)) {
            $update['title'] = $data['title'];
        }
        if (array_key_exists('description', $data)) {
            $update['description'] = $data['description'];
        }
        if (array_key_exists('priority', $data) && $data['priority'] !== '') {
            $update['priority'] = $data['priority'];
        }

        if ($update === []) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'No changes provided']);
        }

        $ok = $model->update($id, $update);
        if ($ok === false) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Update failed', 'details' => $model->errors()]);
        }

        $after = $model->find($id);
        $this->writeTicketHistory((int) $id, 'update', $existing, $after);
        return $this->response->setJSON(['success' => true, 'ticket' => $after]);
    }

    public function assign($id = null)
    {
        if ($deny = $this->requirePermission('ticket.assign')) {
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
            'assignee_id' => 'required|integer',
        ])) {
            return $resp;
        }

        $model = new TicketModel();
        $existing = $model->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Ticket not found']);
        }

        $db = \Config\Database::connect();
        if ($this->auth->isItAdminRole($this->session->get('role')) && $db->fieldExists('warehouse_id', 'tickets')) {
            $wid = $this->session->get('currentWarehouseId');
            if ($wid === null || $wid === '' || ! is_numeric($wid) || (int) $wid <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Please select a warehouse']);
            }
        }

        if (! $this->canViewTicket($existing)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        $update = [
            'assignee_id' => (int) $data['assignee_id'],
        ];
        if (($existing['status'] ?? '') === 'open') {
            $update['status'] = 'in_progress';
        }

        $ok = $model->update($id, $update);
        if ($ok === false) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Assign failed', 'details' => $model->errors()]);
        }

        $after = $model->find($id);
        $this->writeTicketHistory((int) $id, 'assign', $existing, $after);
        return $this->response->setJSON(['success' => true, 'ticket' => $after]);
    }

    public function setStatus($id = null)
    {
        if ($deny = $this->guardApi()) {
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
            'status' => 'required|max_length[20]',
        ])) {
            return $resp;
        }

        $model = new TicketModel();
        $existing = $model->find($id);
        if (! $existing) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Ticket not found']);
        }

        $db = \Config\Database::connect();
        if ($this->auth->isItAdminRole($this->session->get('role')) && $db->fieldExists('warehouse_id', 'tickets')) {
            $wid = $this->session->get('currentWarehouseId');
            if ($wid === null || $wid === '' || ! is_numeric($wid) || (int) $wid <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Please select a warehouse']);
            }
        }

        if (! $this->canViewTicket($existing)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        $uid = (int) ($this->session->get('userID') ?? 0);
        $newStatus = (string) $data['status'];

        $canManage = $this->auth->hasPermission($this->session->get('role'), 'ticket.manage');
        if (! $canManage) {
            if ((int) ($existing['assignee_id'] ?? 0) !== $uid) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
            }
            if (! in_array($newStatus, ['in_progress', 'resolved'], true)) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
            }
        }

        $ok = $model->update($id, ['status' => $newStatus]);
        if ($ok === false) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Status update failed', 'details' => $model->errors()]);
        }

        $after = $model->find($id);
        $this->writeTicketHistory((int) $id, 'status', $existing, $after);
        return $this->response->setJSON(['success' => true, 'ticket' => $after]);
    }

    public function comment($id = null)
    {
        if ($deny = $this->requirePermission('ticket.comment')) {
            return $deny;
        }

        if (empty($id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing id']);
        }

        $model = new TicketModel();
        $ticket = $model->find($id);
        if (! $ticket) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Ticket not found']);
        }

        if (! $this->canViewTicket($ticket)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        $data = $this->request->getJSON(true);
        if (! $data) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing data']);
        }

        if ($resp = $this->validateJson($data, [
            'comment' => 'required|min_length[1]|max_length[5000]',
        ])) {
            return $resp;
        }

        $commentModel = new TicketCommentModel();
        $commentId = $commentModel->insert([
            'ticket_id' => (int) $id,
            'user_id' => $this->session->get('userID') ? (int) $this->session->get('userID') : null,
            'comment' => $data['comment'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($commentId === false) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Comment failed', 'details' => $commentModel->errors()]);
        }

        $this->writeTicketHistory((int) $id, 'comment', null, ['comment_id' => $commentId]);
        return $this->response->setJSON(['success' => true]);
    }
}
