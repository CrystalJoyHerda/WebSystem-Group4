<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We Build - IT Administrator Profile</title>
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
            <div class="user-avatar"><i class="fas fa-user"></i></div>
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
            <a href="<?= site_url('profile') ?>" class="menu-item <?= ($path === 'profile') ? 'active' : '' ?>">My Profile</a>
        </div>
        <button class="logout-btn" onclick="window.location.href='<?= site_url('logout') ?>'">Logout</button>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="logo-section"><h1>WeBuild</h1></div>
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
            <div class="page-title">My Profile</div>
            <div class="text-muted small mb-3">Update your information. Saving requires your current password.</div>

            <div class="card" style="max-width:720px;">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Name</label>
                            <input id="pfName" class="form-control form-control-sm" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Email</label>
                            <input id="pfEmail" type="email" class="form-control form-control-sm" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">New Password (optional)</label>
                            <input id="pfNewPassword" type="password" class="form-control form-control-sm" autocomplete="new-password" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Confirm New Password</label>
                            <input id="pfConfirmPassword" type="password" class="form-control form-control-sm" autocomplete="new-password" />
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Current Password (required to save)</label>
                            <input id="pfCurrentPassword" type="password" class="form-control form-control-sm" autocomplete="current-password" />
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button id="pfSave" class="btn btn-sm btn-dark">Save Changes</button>
                        <button id="pfReload" class="btn btn-sm btn-outline-secondary">Reload</button>
                    </div>

                    <div id="pfStatus" class="small mt-2"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function fetchJson(url, opts) {
            const res = await fetch(url, Object.assign({ headers: { 'Accept': 'application/json' }, credentials: 'same-origin' }, opts || {}));
            const data = await res.json().catch(() => null);
            if (!res.ok) {
                const msg = data && data.error ? data.error : `Request failed (${res.status})`;
                throw new Error(msg);
            }
            return data;
        }

        async function loadProfile() {
            const data = await fetchJson('<?= site_url('api/admin/profile') ?>');
            const u = data && data.user ? data.user : {};
            document.getElementById('pfName').value = u.name || '';
            document.getElementById('pfEmail').value = u.email || '';
            document.getElementById('pfNewPassword').value = '';
            document.getElementById('pfConfirmPassword').value = '';
            document.getElementById('pfCurrentPassword').value = '';
        }

        async function saveProfile() {
            const payload = {
                name: document.getElementById('pfName').value,
                email: document.getElementById('pfEmail').value,
                new_password: document.getElementById('pfNewPassword').value,
                confirm_password: document.getElementById('pfConfirmPassword').value,
                current_password: document.getElementById('pfCurrentPassword').value,
            };

            return fetchJson('<?= site_url('api/admin/profile') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
        }

        function setStatus(msg, cls) {
            const el = document.getElementById('pfStatus');
            if (!el) return;
            el.textContent = msg;
            el.className = 'small mt-2 ' + (cls || '');
        }

        (async function init() {
            try {
                await loadProfile();
                document.getElementById('pfReload').addEventListener('click', async () => {
                    setStatus('', '');
                    await loadProfile();
                });

                document.getElementById('pfSave').addEventListener('click', async () => {
                    try {
                        setStatus('', '');
                        const btn = document.getElementById('pfSave');
                        btn.disabled = true;
                        await saveProfile();
                        setStatus('Saved.', 'text-success');
                        await loadProfile();
                    } catch (e) {
                        setStatus(e && e.message ? e.message : String(e), 'text-danger');
                    } finally {
                        document.getElementById('pfSave').disabled = false;
                    }
                });
            } catch (e) {
                setStatus(e && e.message ? e.message : String(e), 'text-danger');
            }
        })();
    </script>
</body>
</html>
