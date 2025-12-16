<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We Build - IT Administrator Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?= base_url('public/assets/theme.css') ?>" rel="stylesheet">
    <script src="<?= base_url('public/assets/theme.js') ?>" defer></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            background: #f5f5f5;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* sidebar */
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

        /* Header */
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

        /* Dashboard */
        .dashboard-content {
            flex: 1;
            padding: 40px 60px;
            overflow-y: auto;
            background: white;
        }

        .dashboard-title {
            font-size: 28px;
            font-weight: 400;
            color: #333;
            margin-bottom: 40px;
            text-align: center;
            letter-spacing: 3px;
            font-family: 'Georgia', 'Times New Roman', serif;
        }

        /* System Cards Grid */
        .systems-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 30px;
        }

        .system-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .system-card h3 {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            font-family: 'Georgia', 'Times New Roman', serif;
        }

        .capacity-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .capacity-label {
            font-size: 14px;
            color: #666;
        }

        .capacity-value {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .progress-bar-container {
            width: 100%;
            height: 12px;
            background: #e0e0e0;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 25px;
        }

        .progress-bar-fill {
            height: 100%;
            background: #333;
            transition: width 0.3s;
        }

        .progress-bar-fill.blue {
            background: #4a90e2;
        }

        .progress-bar-fill.high {
            background: #333;
        }

        .system-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-box {
            background: #f8f8f8;
            padding: 15px;
            border-radius: 5px;
        }

        .stat-label {
            font-size: 13px;
            color: #666;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }

        .status-message {
            font-size: 13px;
            color: #4a90e2;
            margin-top: 15px;
        }

        .status-message.success {
            color: #27ae60;
        }

        .status-message.warning {
            color: #f39c12;
        }

        /* Alert Cards Grid */
        .alerts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }

        .alert-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .alert-card h3 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            font-family: 'Georgia', 'Times New Roman', serif;
        }

        .alert-number {
            font-size: 72px;
            font-weight: 300;
            color: #333;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .systems-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .systems-grid,
            .alerts-grid {
                grid-template-columns: 1fr;
            }
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
    <!-- Sidebar -->
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

        <div class="dashboard-content">
            <h2 class="dashboard-title">System Overview</h2>

            <!-- System Cards -->
            <div class="systems-grid">
                <!-- Open Tickets -->
                <div class="system-card">
                    <h3>Open Tickets</h3>
                    <div class="alert-number" id="mOpenTickets" style="font-size:56px;">0</div>
                    <div class="status-message warning">Needs attention</div>
                </div>

                <!-- Devices Online -->
                <div class="system-card">
                    <h3>Devices Online</h3>
                    <div class="alert-number" id="mDevicesOnline" style="font-size:56px;">0</div>
                    <div class="status-message success">Healthy</div>
                </div>

                <!-- Devices Offline -->
                <div class="system-card">
                    <h3>Devices Offline</h3>
                    <div class="alert-number" id="mDevicesOffline" style="font-size:56px;">0</div>
                    <div class="status-message warning">Investigate</div>
                </div>

                <!-- Pending Approvals -->
                <div class="system-card">
                    <h3>Pending Approvals</h3>
                    <div class="alert-number" id="mPendingApprovals" style="font-size:56px;">0</div>
                    <div class="status-message warning">Waiting review</div>
                </div>

                <!-- Security Alerts -->
                <div class="system-card">
                    <h3>Security Alerts</h3>
                    <div class="alert-number" id="mSecurityAlerts" style="font-size:56px;">0</div>
                    <div class="status-message success">No critical issues</div>
                </div>

                <!-- Assets Assigned -->
                <div class="system-card">
                    <h3>Assets Assigned</h3>
                    <div class="alert-number" id="mAssetsAssigned" style="font-size:56px;">0</div>
                    <div class="status-message success">Tracked</div>
                </div>
            </div>

            <!-- Alert Cards -->
            <div class="alerts-grid">
                <div class="alert-card">
                    <h3>Quick Actions</h3>
                    <div class="d-flex flex-column gap-2">
                        <a class="btn btn-sm btn-outline-dark" href="<?= site_url('admin/user-management') ?>"><i class="fa-solid fa-user-plus me-1"></i>Add User</a>
                        <a class="btn btn-sm btn-outline-dark" href="<?= site_url('admin/user-management') ?>"><i class="fa-solid fa-key me-1"></i>Reset Password</a>
                    </div>
                </div>
                <div class="alert-card">
                    <h3>Quick Actions</h3>
                    <div class="d-flex flex-column gap-2">
                        <a class="btn btn-sm btn-outline-dark" href="<?= site_url('admin') ?>"><i class="fa-solid fa-ticket me-1"></i>Create Ticket</a>
                        <a class="btn btn-sm btn-outline-dark" href="<?= site_url('admin') ?>"><i class="fa-solid fa-laptop me-1"></i>Assign Asset</a>
                    </div>
                </div>
            </div>

            <?php if ($canLogs): ?>
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <h3 class="mb-0" style="font-size:18px;font-weight:600;">Recent Activity</h3>
                        <input id="auditSearch" class="form-control form-control-sm" style="max-width:320px" placeholder="Search logs (actor/action/entity)">
                    </div>
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:90px">#</th>
                                            <th style="width:170px">When</th>
                                            <th style="width:160px">Actor</th>
                                            <th style="width:140px">Action</th>
                                            <th style="width:120px">Entity</th>
                                            <th style="width:110px">Entity ID</th>
                                            <th>Summary</th>
                                        </tr>
                                    </thead>
                                    <tbody id="auditTbody">
                                        <tr><td colspan="7" class="text-center text-muted p-4">Loading...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            const CAN_LOGS = <?= $canLogs ? 'true' : 'false' ?>;
            const OVERVIEW_API = '<?= site_url('api/admin/overview') ?>';
            const API_BASE = CAN_LOGS ? '<?= site_url('api/admin/audit-logs') ?>' : null;
            const WAREHOUSE_API = '<?= site_url('api/admin/warehouses') ?>';
            const SET_WAREHOUSE_API = '<?= site_url('api/admin/current-warehouse') ?>';
            let debounceTimer = null;

            const searchEl = document.getElementById('auditSearch');
            if (CAN_LOGS && searchEl) {
                searchEl.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(loadLogs, 250);
                });
            }

            initWarehouse();

            async function initWarehouse() {
                const sel = document.getElementById('warehouseSelect');
                if (!sel) {
                    loadOverview();
                    loadLogs();
                    return;
                }

                const res = await fetch(WAREHOUSE_API, { credentials: 'same-origin' });
                if (!res.ok) {
                    loadOverview();
                    loadLogs();
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
                    loadOverview();
                    loadLogs();
                } else if (warehouses.length > 0) {
                    await setWarehouse(Number(warehouses[0].id));
                } else {
                    loadOverview();
                    loadLogs();
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

            async function loadOverview() {
                const res = await fetch(`${OVERVIEW_API}?limit=15`, { credentials: 'same-origin' });
                if (!res.ok) return;
                const data = await res.json();
                const m = (data && data.metrics) ? data.metrics : {};

                setText('mOpenTickets', m.open_tickets);
                setText('mDevicesOnline', m.devices_online);
                setText('mDevicesOffline', m.devices_offline);
                setText('mPendingApprovals', m.pending_approvals);
                setText('mSecurityAlerts', m.security_alerts);
                setText('mAssetsAssigned', m.assets_assigned);
            }

            function setText(id, val) {
                const el = document.getElementById(id);
                if (!el) return;
                el.textContent = (val === null || val === undefined) ? '0' : String(val);
            }

            function buildUrl() {
                if (!CAN_LOGS || !API_BASE) return null;
                const q = (searchEl && searchEl.value) ? searchEl.value.trim() : '';
                const params = new URLSearchParams();
                if (q) params.set('q', q);
                params.set('limit', '15');
                return `${API_BASE}?${params.toString()}`;
            }

            async function loadLogs() {
                if (!CAN_LOGS) return;
                const tbody = document.getElementById('auditTbody');
                if (!tbody) return;
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted p-4">Loading...</td></tr>';

                const url = buildUrl();
                if (!url) return;
                const res = await fetch(url, { credentials: 'same-origin' });
                if (!res.ok) {
                    let msg = `Failed to load (HTTP ${res.status})`;
                    try {
                        const data = await res.json();
                        if (data && data.error) {
                            msg = `${msg}: ${data.error}`;
                        }
                    } catch (e) {
                        try {
                            const txt = await res.text();
                            if (txt) {
                                msg = `${msg}: ${txt}`;
                            }
                        } catch (e2) {}
                    }
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted p-4">${escapeHtml(msg)}</td></tr>`;
                    return;
                }
                const payload = await res.json();
                const logs = (payload && Array.isArray(payload.logs)) ? payload.logs : payload;
                tbody.innerHTML = '';
                if (!Array.isArray(logs) || logs.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted p-4">No recent activity</td></tr>';
                    return;
                }

                logs.forEach(l => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${escapeHtml(l.id)}</td>
                        <td>${escapeHtml(l.created_at || '')}</td>
                        <td>${escapeHtml(l.actor_name || 'System')}</td>
                        <td>${escapeHtml(prettyAction(l.action || ''))}</td>
                        <td>${escapeHtml(l.entity_type || '')}</td>
                        <td>${escapeHtml(l.entity_id || '')}</td>
                        <td class="text-muted small">${escapeHtml(l.summary || '')}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            function prettyAction(a) {
                const map = {
                    'create': 'Create',
                    'update': 'Update',
                    'delete': 'Delete',
                    'status': 'Status Change',
                    'reset_password': 'Reset Password',
                    'login': 'Login',
                    'logout': 'Logout'
                };
                return map[a] || a;
            }

            function escapeHtml(s) {
                if (s === null || s === undefined) return '';
                return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
            }
        })();
    </script>
</body>
</html>