<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We Build - System Configuration</title>
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
                <a href="<?= site_url('backup-recovery') ?>" class="menu-item <?= ($path === 'backup-recovery') ? 'active' : '' ?>">Backup &amp; Recovery</a>
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
            <div class="page-title">System Configuration</div>
            <div class="text-muted small mb-3">Update global system settings. Changes take effect immediately. Saving requires admin password confirmation.</div>

            <div class="row g-3">
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-body">
                            <div class="fw-semibold mb-2">Authentication &amp; Security</div>
                            <div class="small text-muted mb-2">Controls password rules, lockout policy, and session timeout.</div>

                            <div class="mb-3">
                                <label class="form-label small">Password Minimum Length</label>
                                <input id="passwordMinLength" type="number" min="6" step="1" class="form-control form-control-sm" placeholder="6">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small">Lockout Attempts</label>
                                <input id="lockoutAttempts" type="number" min="0" step="1" class="form-control form-control-sm" placeholder="5">
                                <div class="form-text small text-muted">Set to 0 to disable lockout.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small">Lockout Minutes</label>
                                <input id="lockoutMinutes" type="number" min="0" step="1" class="form-control form-control-sm" placeholder="15">
                                <div class="form-text small text-muted">Used when lockout is enabled. Set to 0 to disable timed lockout.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small">Session Timeout (Minutes)</label>
                                <input id="sessionTimeoutMinutes" type="number" min="0" step="1" class="form-control form-control-sm" placeholder="30">
                                <div class="form-text small text-muted">Set to 0 to disable automatic session expiry.</div>
                            </div>

                            <hr>

                            <div class="fw-semibold mb-2">User Defaults</div>
                            <div class="small text-muted mb-2">Controls default behavior when creating new accounts.</div>

                            <div class="mb-3">
                                <label class="form-label small">Default New User Status</label>
                                <select id="defaultUserActive" class="form-select form-select-sm">
                                    <option value="1">Active</option>
                                    <option value="0">Disabled</option>
                                </select>
                            </div>

                            <hr>

                            <div class="fw-semibold mb-2">Inventory</div>
                            <div class="small text-muted mb-2">Controls thresholds used for low stock status and low stock reports.</div>

                            <div class="mb-3">
                                <label class="form-label small">Low Stock Threshold</label>
                                <input id="lowStockThreshold" type="number" min="0" step="1" class="form-control form-control-sm" placeholder="10">
                                <div class="form-text small text-muted">Items with quantity less than or equal to this value are considered low stock.</div>
                            </div>

                            <hr>

                            <div class="fw-semibold mb-2">Backups</div>
                            <div class="small text-muted mb-2">Controls how many backups are retained on the server.</div>

                            <div class="mb-3">
                                <label class="form-label small">Retention Days</label>
                                <input id="backupRetentionDays" type="number" min="0" step="1" class="form-control form-control-sm" placeholder="30">
                                <div class="form-text small text-muted">Backups older than this number of days may be deleted during cleanup. Set to 0 to disable age-based cleanup.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small">Max Backups</label>
                                <input id="backupMaxBackups" type="number" min="0" step="1" class="form-control form-control-sm" placeholder="50">
                                <div class="form-text small text-muted">If there are more than this number of backups, older ones may be deleted. Set to 0 to disable count-based cleanup.</div>
                            </div>

                            <hr>

                            <div class="fw-semibold mb-2">Admin Password</div>
                            <input id="adminPassword" type="password" class="form-control form-control-sm" placeholder="Enter your password to save changes">

                            <div class="mt-3 d-flex gap-2">
                                <button id="btnSave" class="btn btn-dark btn-sm">Save Settings</button>
                                <button id="btnReload" class="btn btn-outline-secondary btn-sm">Reload</button>
                            </div>

                            <div id="status" class="small mt-2"></div>
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
        const GET_API = '<?= site_url('api/admin/system-settings') ?>';
        const SAVE_API = '<?= site_url('api/admin/system-settings') ?>';

        initWarehouse();
        init();

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

        function init() {
            document.getElementById('btnReload')?.addEventListener('click', async () => {
                await loadSettings();
            });

            document.getElementById('btnSave')?.addEventListener('click', async () => {
                const status = document.getElementById('status');
                if (status) { status.textContent = ''; status.className = 'small mt-2'; }

                const admin_password = document.getElementById('adminPassword')?.value || '';
                if (!admin_password) {
                    if (status) { status.textContent = 'Admin password is required.'; status.className = 'small mt-2 text-danger'; }
                    return;
                }

                const lowStockThreshold = document.getElementById('lowStockThreshold')?.value;
                const backupRetentionDays = document.getElementById('backupRetentionDays')?.value;
                const backupMaxBackups = document.getElementById('backupMaxBackups')?.value;
                const passwordMinLength = document.getElementById('passwordMinLength')?.value;
                const lockoutAttempts = document.getElementById('lockoutAttempts')?.value;
                const lockoutMinutes = document.getElementById('lockoutMinutes')?.value;
                const sessionTimeoutMinutes = document.getElementById('sessionTimeoutMinutes')?.value;
                const defaultUserActive = document.getElementById('defaultUserActive')?.value;

                const payload = {
                    admin_password,
                    settings: {
                        'inventory.low_stock_threshold': String(lowStockThreshold ?? ''),
                        'backup.retention_days': String(backupRetentionDays ?? ''),
                        'backup.max_backups': String(backupMaxBackups ?? ''),
                        'auth.password_min_length': String(passwordMinLength ?? ''),
                        'auth.lockout_attempts': String(lockoutAttempts ?? ''),
                        'auth.lockout_minutes': String(lockoutMinutes ?? ''),
                        'auth.session_timeout_minutes': String(sessionTimeoutMinutes ?? ''),
                        'users.default_is_active': String(defaultUserActive ?? ''),
                    }
                };

                try {
                    const btn = document.getElementById('btnSave');
                    if (btn) btn.disabled = true;

                    const res = await fetch(SAVE_API, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload),
                    });
                    const body = await res.json().catch(() => null);
                    if (!res.ok) throw new Error((body && body.error) ? body.error : 'Save failed');

                    if (status) { status.textContent = 'Saved.'; status.className = 'small mt-2 text-success'; }
                    await loadSettings();
                } catch (e) {
                    if (status) { status.textContent = e.message || 'Save failed'; status.className = 'small mt-2 text-danger'; }
                } finally {
                    const btn = document.getElementById('btnSave');
                    if (btn) btn.disabled = false;
                }
            });

            loadSettings();
        }

        async function loadSettings() {
            const status = document.getElementById('status');
            const res = await fetch(GET_API, { credentials: 'same-origin' });
            const body = await res.json().catch(() => null);
            if (!res.ok) {
                if (status) { status.textContent = (body && body.error) ? body.error : 'Failed to load settings'; status.className = 'small mt-2 text-danger'; }
                return;
            }

            const s = (body && body.settings) ? body.settings : {};
            setVal('lowStockThreshold', s['inventory.low_stock_threshold']);
            setVal('backupRetentionDays', s['backup.retention_days']);
            setVal('backupMaxBackups', s['backup.max_backups']);
            setVal('passwordMinLength', s['auth.password_min_length']);
            setVal('lockoutAttempts', s['auth.lockout_attempts']);
            setVal('lockoutMinutes', s['auth.lockout_minutes']);
            setVal('sessionTimeoutMinutes', s['auth.session_timeout_minutes']);
            setVal('defaultUserActive', s['users.default_is_active']);
        }

        function setVal(id, val) {
            const el = document.getElementById(id);
            if (!el) return;
            if (val === null || val === undefined) return;
            el.value = String(val);
        }
    })();
    </script>
</body>
</html>
