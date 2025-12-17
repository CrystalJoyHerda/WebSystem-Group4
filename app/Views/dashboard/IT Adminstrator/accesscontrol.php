<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We Build - IT Administrator Access Control</title>
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
                <i class="fas fa-bell notification-icon"></i>
            </div>
        </div>

        <div class="page-content">
            <div class="page-title">Access Control</div>
            <div class="text-muted small mb-3">Manage permissions per role. Changes take effect immediately for users with that role.</div>

            <div class="row g-3">
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-body">
                            <div class="fw-semibold mb-2">Role</div>
                            <div class="small text-muted mb-2">Select a role to edit its permissions.</div>

                            <select id="roleSelect" class="form-select form-select-sm"></select>

                            <div class="mt-3">
                                <div class="fw-semibold mb-2">Admin Password</div>
                                <input id="adminPassword" type="password" class="form-control form-control-sm" placeholder="Enter your password to save changes">
                                <div class="form-text small text-muted">Required to confirm permission updates.</div>
                            </div>

                            <div class="mt-3 d-flex gap-2">
                                <button id="btnSaveRole" class="btn btn-dark btn-sm">Save Changes</button>
                                <button id="btnReloadRoles" class="btn btn-outline-secondary btn-sm">Reload</button>
                            </div>

                            <div id="saveStatus" class="small mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-body">
                            <div class="fw-semibold mb-2">Permissions</div>
                            <div class="small text-muted mb-3">Tick the permissions this role should have.</div>

                            <input id="permFilter" class="form-control form-control-sm mb-3" placeholder="Search permissions (e.g. inventory, tickets, view, manage)">

                            <div id="permissionsWrap" class="row g-2"></div>

                            <hr>
                            <div class="small text-muted">Security note: IT Administrator role is required to keep <code>admin.dashboard.view</code> and <code>access.view</code> so you cannot lock yourself out.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (() => {
        const WAREHOUSE_API = '<?= site_url('api/admin/warehouses') ?>';
        const SET_WAREHOUSE_API = '<?= site_url('api/admin/current-warehouse') ?>';
        const ROLES_API = '<?= site_url('api/admin/roles') ?>';
        const SAVE_ROLE_API = '<?= site_url('api/admin/roles') ?>';
        const ITADMIN_REQUIRED = ['admin.dashboard.view', 'access.view'];

        let roles = [];
        let permissions = [];

        let permFilter = '';

        initWarehouse();
        initRoleEditor();

        async function initWarehouse() {
            const sel = document.getElementById('warehouseSelect');
            if (!sel) return;

            const res = await fetch(WAREHOUSE_API, { credentials: 'same-origin' });
            if (!res.ok) return;

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
            } else if (warehouses.length > 0) {
                await setWarehouse(Number(warehouses[0].id));
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

        async function initRoleEditor() {
            const roleSelect = document.getElementById('roleSelect');
            const permsWrap = document.getElementById('permissionsWrap');
            const btnSave = document.getElementById('btnSaveRole');
            const btnReload = document.getElementById('btnReloadRoles');
            const status = document.getElementById('saveStatus');
            const filterEl = document.getElementById('permFilter');

            if (!roleSelect || !permsWrap || !btnSave || !btnReload) return;

            btnReload.addEventListener('click', async () => {
                await loadRoles();
                renderRoleSelector();
                renderPermissionChecklist();
            });

            roleSelect.addEventListener('change', () => {
                renderPermissionChecklist();
            });

            if (filterEl) {
                filterEl.addEventListener('input', () => {
                    permFilter = (filterEl.value || '').trim().toLowerCase();
                    renderPermissionChecklist();
                });
            }

            btnSave.addEventListener('click', async () => {
                if (status) status.textContent = '';
                const key = roleSelect.value;
                if (!key) return;

                const adminPassword = document.getElementById('adminPassword')?.value;
                if (!adminPassword) {
                    if (status) {
                        status.textContent = 'Admin password is required.';
                        status.className = 'small mt-2 text-danger';
                    }
                    return;
                }

                const selected = Array.from(document.querySelectorAll('input[data-perm-code]:checked')).map(i => i.getAttribute('data-perm-code'));
                if (key === 'itadministrator') {
                    ITADMIN_REQUIRED.forEach(p => {
                        if (!selected.includes(p)) selected.push(p);
                    });
                }

                try {
                    btnSave.disabled = true;
                    const res = await fetch(`${SAVE_ROLE_API}/${encodeURIComponent(key)}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify({ permissions: selected, admin_password: adminPassword }),
                    });
                    const body = await res.json().catch(() => null);
                    if (!res.ok) {
                        throw new Error((body && body.error) ? body.error : 'Save failed');
                    }

                    if (status) {
                        status.textContent = 'Saved.';
                        status.className = 'small mt-2 text-success';
                    }

                    await loadRoles();
                    renderRoleSelector(key);
                    renderPermissionChecklist();
                } catch (e) {
                    if (status) {
                        status.textContent = e.message || 'Save failed';
                        status.className = 'small mt-2 text-danger';
                    }
                } finally {
                    btnSave.disabled = false;
                }
            });

            await loadRoles();
            renderRoleSelector();
            renderPermissionChecklist();
        }

        async function loadRoles() {
            const res = await fetch(ROLES_API, { credentials: 'same-origin' });
            if (!res.ok) {
                roles = [];
                permissions = [];
                return;
            }
            const data = await res.json();
            roles = Array.isArray(data.roles) ? data.roles : [];
            permissions = Array.isArray(data.permissions) ? data.permissions : [];
        }

        function renderRoleSelector(preferredRoleKey = null) {
            const roleSelect = document.getElementById('roleSelect');
            if (!roleSelect) return;

            roleSelect.innerHTML = '';
            roles.forEach(r => {
                const opt = document.createElement('option');
                opt.value = String(r.role_key);
                opt.textContent = r.label ? `${r.label} (${r.role_key})` : r.role_key;
                roleSelect.appendChild(opt);
            });

            if (preferredRoleKey && roles.some(r => String(r.role_key) === String(preferredRoleKey))) {
                roleSelect.value = String(preferredRoleKey);
            } else if (roles.length > 0) {
                roleSelect.value = String(roles[0].role_key);
            }
        }

        function renderPermissionChecklist() {
            const roleSelect = document.getElementById('roleSelect');
            const permsWrap = document.getElementById('permissionsWrap');
            if (!roleSelect || !permsWrap) return;

            const key = roleSelect.value;
            const role = roles.find(r => String(r.role_key) === String(key));
            const selected = new Set((role && Array.isArray(role.permissions)) ? role.permissions : []);
            const isItAdmin = key === 'itadministrator';

            permsWrap.innerHTML = '';
            if (!Array.isArray(permissions) || permissions.length === 0) {
                permsWrap.innerHTML = '<div class="text-muted">No permissions found. Run RBAC migration first.</div>';
                return;
            }

            const grouped = buildGroupedPermissions(permissions, permFilter);
            const groupKeys = Object.keys(grouped).sort((a, b) => a.localeCompare(b));

            groupKeys.forEach(groupName => {
                const header = document.createElement('div');
                header.className = 'col-12';
                header.innerHTML = `
                    <div class="fw-semibold" style="font-size:12px; letter-spacing:0.6px; text-transform:uppercase; opacity:0.85;">${escapeHtml(groupName)}</div>
                    <hr class="my-2">
                `;
                permsWrap.appendChild(header);

                const items = grouped[groupName] || [];
                items.forEach(p => {
                    const code = p.code;
                    const meta = permissionMeta(code, p.description || '');
                    const id = `perm_${String(code).replace(/[^a-zA-Z0-9_\-]/g, '_')}`;
                    const isRequired = isItAdmin && ITADMIN_REQUIRED.includes(code);
                    const checked = selected.has(code) || isRequired;

                    const col = document.createElement('div');
                    col.className = 'col-12 col-md-6';
                    col.innerHTML = `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="${id}" data-perm-code="${escapeHtml(code)}" ${checked ? 'checked' : ''} ${isRequired ? 'disabled' : ''}>
                            <label class="form-check-label" for="${id}">
                                <div class="fw-semibold">${escapeHtml(meta.label)}</div>
                                <div class="text-muted small">
                                    <code>${escapeHtml(code)}</code>
                                    ${meta.detail ? `&nbsp;&middot;&nbsp;${escapeHtml(meta.detail)}` : ''}
                                </div>
                            </label>
                        </div>
                    `;
                    permsWrap.appendChild(col);
                });
            });
        }

        function buildGroupedPermissions(list, filter) {
            const out = {};
            (Array.isArray(list) ? list : []).forEach(p => {
                const code = String(p.code || '');
                const meta = permissionMeta(code, String(p.description || ''));
                const hay = `${code} ${meta.label} ${meta.detail || ''} ${p.description || ''}`.toLowerCase();
                if (filter && !hay.includes(filter)) return;
                const group = meta.group || 'Other';
                if (!out[group]) out[group] = [];
                out[group].push({ code: code, description: p.description || '' });
            });

            Object.keys(out).forEach(g => {
                out[g].sort((a, b) => {
                    const la = permissionMeta(a.code, a.description).label;
                    const lb = permissionMeta(b.code, b.description).label;
                    return la.localeCompare(lb);
                });
            });

            return out;
        }

        function permissionMeta(code, description) {
            const raw = String(code || '');
            const parts = raw.split('.').filter(Boolean);
            const groupKey = parts[0] || 'other';

            const groupMap = {
                'admin': 'Admin',
                'user': 'Users',
                'access': 'Access Control',
                'logs': 'System Logs',
                'backup': 'Backup & Recovery',
                'config': 'System Configuration',
                'inventory': 'Inventory',
                'asset': 'Assets',
                'inbound': 'Inbound',
                'outbound': 'Outbound',
                'ticket': 'Tickets',
                'transfers': 'Transfers',
                'jobs': 'Jobs',
            };

            const actionMap = {
                'view': 'View',
                'view_all': 'View (All)',
                'view_own': 'View (Own)',
                'manage': 'Manage',
                'create': 'Create',
                'approve': 'Approve',
                'scan': 'Scan',
                'assign': 'Assign',
                'comment': 'Comment',
                'dashboard': 'Dashboard',
            };

            const group = groupMap[groupKey] || titleCase(groupKey);

            let module = parts.length >= 2 ? titleCase(parts[1]) : group;
            let action = parts.length >= 3 ? parts.slice(2).join('_') : (parts[1] || '');

            if (raw === 'admin.dashboard.view') {
                module = 'Dashboard';
                action = 'view';
            }

            const actionLabel = actionMap[action] || titleCase(action.replace(/_/g, ' '));
            const label = `${module}: ${actionLabel}`;
            const detail = description ? String(description) : '';

            return { group, label, detail };
        }

        function titleCase(s) {
            return String(s || '')
                .replace(/[_\-]+/g, ' ')
                .trim()
                .split(/\s+/)
                .filter(Boolean)
                .map(w => w.charAt(0).toUpperCase() + w.slice(1))
                .join(' ');
        }

        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    })();
    </script>
</body>
</html>
