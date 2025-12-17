<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We Build - IT Administrator User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?= base_url('public/assets/theme.css') ?>" rel="stylesheet">
    <script src="<?= base_url('public/assets/theme.js') ?>" defer></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            background: #f5f5f5;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 220px;
            background: #ecebe9;
            color: #333;
            display: flex;
            flex-direction: column;
            padding: 0;
        }

        .sidebar-header {
            padding: 40px 20px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #b8b8b8;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            margin: 0 auto 15px;
        }

        .sidebar-header h3 {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            letter-spacing: 0.5px;
        }

        .sidebar-menu {
            flex: 1;
            padding: 30px 0;
            overflow-y: auto;
        }

        .menu-item {
            display: block;
            padding: 12px 30px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
        }

        .menu-item:hover {
            background: #ddd;
            color: #000;
        }

        .menu-item.active {
            background: #333;
            color: white;
            font-weight: 600;
        }

        .logout-btn {
            padding: 12px 30px;
            margin: 20px;
            background: white;
            border: 1px solid #333;
            border-radius: 5px;
            color: #333;
            text-align: center;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: #333;
            color: white;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .header {
            background: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e0e0e0;
        }

        .logo-section h1 {
            font-size: 36px;
            font-weight: 400;
            color: #333;
            margin: 0;
            font-family: 'Georgia', 'Times New Roman', serif;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-icon {
            font-size: 24px;
            color: #ff6b35;
            cursor: pointer;
            position: relative;
        }

        .page-content {
            flex: 1;
            padding: 40px 60px;
            overflow-y: auto;
            background: white;
        }

        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: #333;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 18px;
            font-family: 'Georgia', 'Times New Roman', serif;
        }

        .toolbar {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .table td, .table th { vertical-align: middle; }
        .actions .btn {
            width: 34px;
            height: 34px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .sidebar { width: 200px; }
            .page-content { padding: 20px; }
        }
    </style>
</head>
<body>
    <?php
        $permissions = $permissions ?? [];
        $path = service('uri')->getPath();
        $canDashboard = in_array('admin.dashboard.view', $permissions, true);
        $canUsers = in_array('user.manage', $permissions, true);
        $canLogs = in_array('logs.view', $permissions, true);
        $canAccess = in_array('access.view', $permissions, true);
        $canBackup = in_array('backup.view', $permissions, true);
        $canConfig = in_array('config.view', $permissions, true);
    ?>
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h3>IT Administrator</h3>
        </div>
        <div class="sidebar-menu">
            <?php if ($canDashboard): ?>
                <a href="<?= site_url('admin') ?>" class="menu-item <?= ($path === 'admin') ? 'active' : '' ?>">Dashboard</a>
            <?php endif; ?>
            <?php if ($canUsers): ?>
                <a href="<?= site_url('admin/user-management') ?>" class="menu-item <?= ($path === 'admin/user-management') ? 'active' : '' ?>">User Management</a>
            <?php endif; ?>
            <?php if ($canAccess): ?>
                <a href="<?= site_url('admin/access-control') ?>" class="menu-item <?= ($path === 'admin/access-control') ? 'active' : '' ?>">Access Control</a>
            <?php endif; ?>
            <?php if ($canLogs): ?>
                <a href="<?= site_url('system-logs') ?>" class="menu-item <?= ($path === 'system-logs') ? 'active' : '' ?>">System Logs</a>
            <?php endif; ?>
            <?php if ($canBackup): ?>
                <a href="<?= site_url('backup-recovery') ?>" class="menu-item <?= ($path === 'backup-recovery') ? 'active' : '' ?>">Backup & Recovery</a>
            <?php endif; ?>
            <?php if ($canConfig): ?>
                <a href="<?= site_url('system-configuration') ?>" class="menu-item <?= ($path === 'system-configuration') ? 'active' : '' ?>">System Configuration</a>
            <?php endif; ?>
        </div>
        <button class="logout-btn" onclick="window.location.href='<?= site_url('logout') ?>'">Logout</button>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="logo-section">
                <h1>WeBuild</h1>
            </div>
            <div class="header-right">
                <select id="warehouseSelect" class="form-select form-select-sm" style="min-width:200px;display:none"></select>
                <a href="#" class="position-relative" style="display:inline-block;" data-notifications-api="<?= site_url('api/admin/notifications') ?>">
                    <i class="fas fa-bell notification-icon"></i>
                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill" data-notifications-count style="display:none;font-size:10px;">0</span>
                    <div class="card shadow" data-notifications-dropdown style="display:none; position:absolute; right:0; margin-top:10px; width:340px; z-index:2000;">
                        <div class="card-body p-2">
                            <div class="fw-semibold px-1 pb-1">Notifications</div>
                            <div data-notifications-list class="small text-muted">Loading...</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="page-content">
            <div class="toolbar">
                <div>
                    <div class="page-title">User Management</div>
                    <div class="text-muted small">Create, update, reset passwords, and remove users.</div>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <input id="searchBox" class="form-control form-control-sm" style="min-width:240px" placeholder="Search name/email/role">
                    <select id="roleFilter" class="form-select form-select-sm" style="min-width:200px">
                        <option value="">All roles</option>
                        <option value="manager">Manager</option>
                        <option value="staff">Staff</option>
                        <option value="inventory_auditor">Inventory Auditor</option>
                        <option value="procurement_officer">Procurement Officer</option>
                        <option value="accounts_payable">Accounts Payable</option>
                        <option value="accounts_receivable">Accounts Receivable</option>
                        <option value="it_administrator">IT Administrator</option>
                        <option value="topmanagement">Top Management</option>
                    </select>
                    <select id="statusFilter" class="form-select form-select-sm" style="min-width:160px">
                        <option value="">All status</option>
                        <option value="1">Active</option>
                        <option value="0">Disabled</option>
                    </select>
                    <button id="btnAddUser" class="btn btn-dark btn-sm">Add User</button>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:70px">#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th style="width:180px">Role</th>
                                    <th style="width:120px">Status</th>
                                    <th style="width:160px">Created By</th>
                                    <th style="width:160px">Updated By</th>
                                    <th style="width:310px">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTbody">
                                <tr><td colspan="8" class="text-center text-muted p-4">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add / Edit Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="userForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="userId" value="">

                    <div class="mb-2">
                        <label class="form-label small">Full name</label>
                        <input id="userName" class="form-control form-control-sm" required>
                        <div class="invalid-feedback" id="err_userName"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Email</label>
                        <input id="userEmail" type="email" class="form-control form-control-sm" required>
                        <div class="invalid-feedback" id="err_userEmail"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Role</label>
                        <select id="userRole" class="form-select form-select-sm">
                            <option value="manager">Manager</option>
                            <option value="staff">Staff</option>
                            <option value="inventory_auditor">Inventory Auditor</option>
                            <option value="procurement_officer">Procurement Officer</option>
                            <option value="accounts_payable">Accounts Payable</option>
                            <option value="accounts_receivable">Accounts Receivable</option>
                            <option value="it_administrator">IT Administrator</option>
                            <option value="topmanagement">Top Management</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Password</label>
                        <input id="userPassword" type="password" class="form-control form-control-sm" placeholder="">
                        <div id="passwordHelp" class="form-text small text-muted"></div>
                        <div class="invalid-feedback" id="err_userPassword"></div>
                    </div>

                    <div class="mb-2" id="adminPasswordWrap" style="display:none;">
                        <label class="form-label small">Admin Password</label>
                        <input id="adminPassword" type="password" class="form-control form-control-sm" placeholder="Enter your password to save changes">
                        <div class="form-text small text-muted">Required to confirm this action.</div>
                        <div class="invalid-feedback" id="err_adminPassword"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-sm">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="resetForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="resetUserId" value="">
                    <div class="mb-2">
                        <label class="form-label small">New password</label>
                        <input id="resetPassword" type="password" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Admin Password</label>
                        <input id="resetAdminPassword" type="password" class="form-control form-control-sm" placeholder="Enter your password to confirm" required>
                    </div>
                    <div class="small text-muted">This will overwrite the user's current password.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm">Reset</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirm Status Change Modal -->
    <div class="modal fade" id="confirmStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body">
                    <p class="mb-0" id="statusConfirmText">Change user status?</p>
                    <div class="mt-2">
                        <label class="form-label small">Admin Password</label>
                        <input id="statusAdminPassword" type="password" class="form-control form-control-sm" placeholder="Enter your password to confirm" required>
                    </div>
                    <div class="mt-3 text-end">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button id="confirmStatusBtn" type="button" class="btn btn-outline-secondary btn-sm">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (() => {
        const API_BASE = '<?= site_url('api/admin/users') ?>';
        const CURRENT_USER_ID = <?= json_encode(session()->get('userID') ?? null) ?>;
        const WAREHOUSE_API = '<?= site_url('api/admin/warehouses') ?>';
        const SET_WAREHOUSE_API = '<?= site_url('api/admin/current-warehouse') ?>';
        let users = [];
        let statusTargetId = null;
        let statusNextActive = null;
        let debounceTimer = null;

        const userModal = new bootstrap.Modal(document.getElementById('userModal'));
        const resetModal = new bootstrap.Modal(document.getElementById('resetModal'));
        const statusModal = new bootstrap.Modal(document.getElementById('confirmStatusModal'));

        document.getElementById('btnAddUser').addEventListener('click', openAddModal);
        document.getElementById('userForm').addEventListener('submit', onSaveUser);
        document.getElementById('confirmStatusBtn').addEventListener('click', onConfirmStatus);
        document.getElementById('resetForm').addEventListener('submit', onResetPassword);
        document.getElementById('searchBox').addEventListener('input', scheduleLoad);
        document.getElementById('roleFilter').addEventListener('change', loadUsers);
        document.getElementById('statusFilter').addEventListener('change', loadUsers);

        initWarehouse();

        async function initWarehouse() {
            const sel = document.getElementById('warehouseSelect');
            if (!sel) {
                loadUsers();
                return;
            }

            const res = await fetch(WAREHOUSE_API, { credentials: 'same-origin' });
            if (!res.ok) {
                loadUsers();
                return;
            }
            const data = await res.json();
            const warehouses = Array.isArray(data.warehouses) ? data.warehouses : [];
            const currentId = data.current_warehouse_id;

            sel.innerHTML = '';
            warehouses.forEach(w => {
                const opt = document.createElement('option');
                opt.value = String(w.id);
                opt.textContent = w.location ? `${w.name} (${w.location})` : w.name;
                sel.appendChild(opt);
            });

            if (warehouses.length > 0) {
                sel.style.display = '';
            }

            if (currentId) {
                sel.value = String(currentId);
                loadUsers();
            } else if (warehouses.length > 0) {
                await setWarehouse(Number(warehouses[0].id));
            } else {
                loadUsers();
            }

            sel.addEventListener('change', async () => {
                const id = Number(sel.value);
                if (!id) return;
                await setWarehouse(id);
            });
        }

        async function setWarehouse(id) {
            const res = await fetch(SET_WAREHOUSE_API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ warehouse_id: id }),
            });
            if (res.ok) {
                window.location.reload();
            }
        }

        function scheduleLoad() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(loadUsers, 250);
        }

        function buildListUrl() {
            const q = (document.getElementById('searchBox').value || '').trim();
            const role = document.getElementById('roleFilter').value;
            const status = document.getElementById('statusFilter').value;

            const params = new URLSearchParams();
            if (q) params.set('q', q);
            if (role) params.set('role', role);
            if (status !== '') params.set('status', status);
            params.set('limit', '500');

            return `${API_BASE}?${params.toString()}`;
        }

        async function loadUsers() {
            const res = await fetch(buildListUrl(), { credentials: 'same-origin' });
            if (!res.ok) {
                document.getElementById('usersTbody').innerHTML = '<tr><td colspan="8" class="text-center text-muted p-4">Failed to load</td></tr>';
                return;
            }
            users = await res.json();
            if (CURRENT_USER_ID != null) {
                users.sort((a, b) => {
                    const as = Number(a.id) === Number(CURRENT_USER_ID);
                    const bs = Number(b.id) === Number(CURRENT_USER_ID);
                    if (as === bs) return 0;
                    return as ? -1 : 1;
                });
            }
            renderTable();
        }

        function renderTable() {
            const tbody = document.getElementById('usersTbody');
            tbody.innerHTML = '';
            if (!Array.isArray(users) || users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted p-4">No users</td></tr>';
                return;
            }

            users.forEach(u => {
                const tr = document.createElement('tr');
                const isSelf = (CURRENT_USER_ID != null) && (Number(u.id) === Number(CURRENT_USER_ID));
                const isActive = (u.is_active === undefined || u.is_active === null) ? 1 : Number(u.is_active);
                const statusBadge = isActive ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Disabled</span>';
                const toggleLabel = isActive ? 'Disable' : 'Enable';
                const toggleClass = isActive ? 'btn-outline-secondary' : 'btn-outline-success';
                const createdBy = u.created_by_name ? escapeHtml(u.created_by_name) : (u.created_by ? `#${escapeHtml(u.created_by)}` : '');
                const updatedBy = u.updated_by_name ? escapeHtml(u.updated_by_name) : (u.updated_by ? `#${escapeHtml(u.updated_by)}` : '');

                const disableAttr = isSelf ? 'disabled' : '';
                const selfTitle = isSelf ? 'title="Not allowed for your own account"' : '';
                tr.innerHTML = `
                    <td>${u.id}</td>
                    <td>${escapeHtml(u.name)}</td>
                    <td>${escapeHtml(u.email)}</td>
                    <td>${escapeHtml(prettyRole(u.role) || 'Not set')}</td>
                    <td>${statusBadge}</td>
                    <td class="text-muted small">${createdBy}</td>
                    <td class="text-muted small">${updatedBy}</td>
                    <td class="actions">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-sm btn-outline-dark btn-edit" data-id="${u.id}" ${disableAttr} title="Edit" aria-label="Edit" ${isSelf ? 'disabled' : ''}><i class="fa-solid fa-pen"></i></button>
                            <button class="btn btn-sm btn-outline-warning btn-reset" data-id="${u.id}" ${disableAttr} title="Reset password" aria-label="Reset password" ${isSelf ? 'disabled' : ''}><i class="fa-solid fa-key"></i></button>
                            <button class="btn btn-sm ${toggleClass} btn-status" data-id="${u.id}" data-active="${isActive}" ${disableAttr} title="${toggleLabel}" aria-label="${toggleLabel}" ${isSelf ? 'disabled' : ''}>
                                <i class="fa-solid ${isActive ? 'fa-ban' : 'fa-check'}"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        document.getElementById('usersTbody').addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-edit, .btn-reset, .btn-status');
            if (!btn) return;
            if (btn.disabled) return;
            const id = Number(btn.dataset.id);
            if (btn.classList.contains('btn-edit')) {
                openEditModal(id);
            } else if (btn.classList.contains('btn-reset')) {
                openResetModal(id);
            } else if (btn.classList.contains('btn-status')) {
                openStatusConfirmModal(id, Number(btn.dataset.active || '1'));
            }
        });

        function openStatusConfirmModal(id, isActive) {
            statusTargetId = id;
            statusNextActive = isActive ? 0 : 1;
            const text = document.getElementById('statusConfirmText');
            if (text) text.textContent = isActive ? 'Disable this user?' : 'Enable this user?';

            const btn = document.getElementById('confirmStatusBtn');
            if (btn) {
                btn.className = 'btn btn-sm ' + (isActive ? 'btn-outline-secondary' : 'btn-outline-success');
                btn.textContent = isActive ? 'Disable' : 'Enable';
            }

            const pwd = document.getElementById('statusAdminPassword');
            if (pwd) pwd.value = '';
            statusModal.show();
        }

        async function onConfirmStatus() {
            if (statusTargetId == null || statusNextActive == null) return;
            const adminPassword = document.getElementById('statusAdminPassword')?.value;
            if (!adminPassword) {
                alert('Admin password is required');
                return;
            }
            try {
                const res = await fetch(`${API_BASE}/${statusTargetId}/status`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ is_active: statusNextActive, admin_password: adminPassword }),
                    credentials: 'same-origin'
                });
                if (!res.ok) {
                    const j = await res.json().catch(() => null);
                    throw new Error((j && j.error) ? j.error : 'Status update failed');
                }
                await loadUsers();
                statusTargetId = null;
                statusNextActive = null;
                statusModal.hide();
            } catch (err) {
                alert(err.message || 'Status update failed');
            }
        }

        function openAddModal() {
            document.getElementById('userModalTitle').textContent = 'Add User';
            document.getElementById('userId').value = '';
            document.getElementById('userName').value = '';
            document.getElementById('userEmail').value = '';
            const roleSelect = document.getElementById('userRole');
            roleSelect.disabled = false;
            roleSelect.value = 'staff';
            const pwd = document.getElementById('userPassword');
            pwd.value = '';
            pwd.required = true;
            pwd.placeholder = 'Enter password';
            document.getElementById('passwordHelp').textContent = 'Password is required when adding a user.';
            const adminWrap = document.getElementById('adminPasswordWrap');
            const adminPwd = document.getElementById('adminPassword');
            adminPwd.value = '';
            adminPwd.required = true;
            adminWrap.style.display = 'block';
            userModal.show();
        }

        function openEditModal(id) {
            const u = (users || []).find(x => Number(x.id) === Number(id));
            if (!u) return;

            const isSelf = (CURRENT_USER_ID != null) && (Number(u.id) === Number(CURRENT_USER_ID));
            if (isSelf) {
                alert('You cannot edit your own account.');
                return;
            }

            document.getElementById('userModalTitle').textContent = 'Edit User';
            document.getElementById('userId').value = u.id;
            document.getElementById('userName').value = u.name || '';
            document.getElementById('userEmail').value = u.email || '';

            const roleSelect = document.getElementById('userRole');
            let stored = u.role || '';
            let normalized = String(stored).trim().toLowerCase().replace(/\s+/g, '_');
            if (Array.from(roleSelect.options).some(o => o.value === normalized)) {
                roleSelect.value = normalized;
            } else {
                roleSelect.value = 'staff';
            }

            roleSelect.disabled = false;

            const pwd = document.getElementById('userPassword');
            pwd.value = '';
            pwd.required = false;
            pwd.placeholder = 'Leave blank to keep current password';
            document.getElementById('passwordHelp').textContent = 'Leave blank to keep existing password.';

            const adminWrap = document.getElementById('adminPasswordWrap');
            const adminPwd = document.getElementById('adminPassword');
            adminPwd.value = '';
            adminPwd.required = true;
            adminWrap.style.display = 'block';
            userModal.show();
        }

        function openResetModal(id) {
            document.getElementById('resetUserId').value = String(id);
            document.getElementById('resetPassword').value = '';
            const ap = document.getElementById('resetAdminPassword');
            if (ap) ap.value = '';
            resetModal.show();
        }

        async function onSaveUser(ev) {
            ev.preventDefault();
            clearFormErrors();
            const idVal = document.getElementById('userId').value;
            const name = document.getElementById('userName').value.trim();
            const email = document.getElementById('userEmail').value.trim();
            let role = document.getElementById('userRole').value;
            if (!role) role = 'staff';
            const password = document.getElementById('userPassword').value;
            const adminPassword = document.getElementById('adminPassword').value;

            if (!name || !email) { alert('Name and email are required.'); return; }
            if (!idVal && (!password || password.length === 0)) { alert('Password is required when creating a user.'); return; }
            if ((!adminPassword || adminPassword.length === 0)) { alert('Admin password is required to proceed.'); return; }

            const payload = { name, email, role };
            if (password && password.length > 0) payload.password = password;
            payload.admin_password = adminPassword;

            try {
                if (idVal) {
                    const res = await fetch(`${API_BASE}/${idVal}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload),
                        credentials: 'same-origin'
                    });
                    if (!res.ok) {
                        const j = await res.json().catch(() => null);
                        if (j && j.errors) {
                            applyApiErrors(j.errors);
                            return;
                        }
                        throw new Error((j && j.error) ? j.error : 'Update failed');
                    }
                } else {
                    const res = await fetch(API_BASE, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload),
                        credentials: 'same-origin'
                    });
                    if (!res.ok) {
                        const j = await res.json().catch(() => null);
                        if (j && j.errors) {
                            applyApiErrors(j.errors);
                            return;
                        }
                        throw new Error((j && j.error) ? j.error : 'Create failed');
                    }
                }
                await loadUsers();
                userModal.hide();
            } catch (err) {
                alert(err.message || 'Request failed');
            }
        }

        async function onResetPassword(ev) {
            ev.preventDefault();
            const idVal = document.getElementById('resetUserId').value;
            const password = document.getElementById('resetPassword').value;
            const adminPassword = document.getElementById('resetAdminPassword')?.value;
            if (!idVal || !password) return;
            if (!adminPassword) {
                alert('Admin password is required');
                return;
            }

            try {
                const res = await fetch(`${API_BASE}/${idVal}/reset-password`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ password, admin_password: adminPassword }),
                    credentials: 'same-origin'
                });
                if (!res.ok) {
                    const j = await res.json().catch(() => null);
                    throw new Error((j && j.error) ? j.error : 'Reset failed');
                }
                resetModal.hide();
            } catch (err) {
                alert(err.message || 'Reset failed');
            }
        }

        function escapeHtml(s) {
            if (!s) return '';
            return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        }

        function clearFormErrors() {
            ['userName','userEmail','userPassword','adminPassword'].forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                el.classList.remove('is-invalid');
                const err = document.getElementById('err_' + id);
                if (err) err.textContent = '';
            });
        }

        function setFieldError(fieldId, message) {
            const el = document.getElementById(fieldId);
            if (el) el.classList.add('is-invalid');
            const err = document.getElementById('err_' + fieldId);
            if (err) err.textContent = message || 'Invalid value';
        }

        function applyApiErrors(errors) {
            if (!errors) return;
            if (errors.name) setFieldError('userName', errors.name);
            if (errors.email) setFieldError('userEmail', errors.email);
            if (errors.password) setFieldError('userPassword', errors.password);
            if (errors.admin_password) setFieldError('adminPassword', errors.admin_password);
        }

        function prettyRole(role) {
            if (!role) return '';
            const normalized = String(role).trim().toLowerCase().replace(/\s+/g, '_');
            const map = {
                'manager': 'Manager',
                'staff': 'Staff',
                'inventory_auditor': 'Inventory Auditor',
                'procurement_officer': 'Procurement Officer',
                'accounts_payable': 'Accounts Payable',
                'accounts_receivable': 'Accounts Receivable',
                'it_administrator': 'IT Administrator',
                'topmanagement': 'Top Management',
                'admin': 'Admin'
            };
            if (map[normalized]) return map[normalized];
            return String(role).trim().split(/\s+/).map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
        }
    })();
    </script>
</body>
</html>
