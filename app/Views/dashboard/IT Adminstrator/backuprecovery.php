<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We Build - Backup & Recovery</title>
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
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-icon {
            font-size: 20px;
            color: #666;
            cursor: pointer;
        }

        .page-content {
            padding: 30px 40px;
            overflow: auto;
        }

        .page-title {
            font-size: 28px;
            font-weight: 400;
            color: #333;
            margin-bottom: 10px;
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
                <i class="fas fa-bell notification-icon"></i>
            </div>
        </div>

        <div class="page-content">
            <div class="page-title">Backup &amp; Recovery</div>
            <div class="text-muted small mb-3">Create a database backup, download existing backups, or restore from a SQL file. Restore is destructive and will overwrite data.</div>

            <div class="row g-3">
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-body">
                            <div class="fw-semibold mb-2">Create Backup</div>
                            <div class="small text-muted mb-2">Generates a <code>.sql</code> dump stored on the server and available for download.</div>

                            <div class="mb-2">
                                <label class="form-label small">Admin Password</label>
                                <input id="adminPasswordCreate" type="password" class="form-control form-control-sm" placeholder="Enter your password">
                            </div>

                            <div class="d-flex gap-2">
                                <button id="btnCreateBackup" class="btn btn-dark btn-sm">Create Backup</button>
                                <button id="btnRefresh" class="btn btn-outline-secondary btn-sm">Refresh List</button>
                            </div>

                            <div id="createStatus" class="small mt-2"></div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="fw-semibold mb-2">Restore Database</div>
                            <div class="small text-muted mb-2">Upload a <code>.sql</code> file to restore. This will run SQL statements on your database.</div>

                            <div class="mb-2">
                                <label class="form-label small">SQL File</label>
                                <input id="restoreFile" type="file" class="form-control form-control-sm" accept=".sql">
                            </div>

                            <div class="mb-2">
                                <label class="form-label small">Admin Password</label>
                                <input id="adminPasswordRestore" type="password" class="form-control form-control-sm" placeholder="Enter your password">
                            </div>

                            <div class="mb-2">
                                <label class="form-label small">Type <code>RESTORE</code> to confirm</label>
                                <input id="restoreConfirm" type="text" class="form-control form-control-sm" placeholder="RESTORE">
                            </div>

                            <button id="btnRestore" class="btn btn-danger btn-sm">Restore</button>
                            <div id="restoreStatus" class="small mt-2"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-body">
                            <div class="fw-semibold mb-2">Backups</div>
                            <div class="small text-muted mb-3">Download a backup or restore by uploading a file.</div>

                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>File</th>
                                            <th class="text-end">Size</th>
                                            <th>Created</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="backupTableBody">
                                        <tr><td colspan="4" class="text-muted">Loading...</td></tr>
                                    </tbody>
                                </table>
                            </div>
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
        const LIST_API = '<?= site_url('api/admin/backups') ?>';
        const CREATE_API = '<?= site_url('api/admin/backups') ?>';
        const DOWNLOAD_API = '<?= site_url('api/admin/backups') ?>';
        const RESTORE_API = '<?= site_url('api/admin/backups/restore') ?>';

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
            document.getElementById('btnRefresh')?.addEventListener('click', async () => {
                await loadBackups();
            });

            document.getElementById('btnCreateBackup')?.addEventListener('click', async () => {
                const status = document.getElementById('createStatus');
                if (status) { status.textContent = ''; status.className = 'small mt-2'; }

                const admin_password = document.getElementById('adminPasswordCreate')?.value || '';
                if (!admin_password) {
                    if (status) { status.textContent = 'Admin password is required.'; status.className = 'small mt-2 text-danger'; }
                    return;
                }

                try {
                    const btn = document.getElementById('btnCreateBackup');
                    if (btn) btn.disabled = true;

                    const res = await fetch(CREATE_API, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify({ admin_password }),
                    });
                    const body = await res.json().catch(() => null);
                    if (!res.ok) throw new Error((body && body.error) ? body.error : 'Create failed');

                    if (status) { status.textContent = 'Backup created.'; status.className = 'small mt-2 text-success'; }
                    await loadBackups();
                } catch (e) {
                    if (status) { status.textContent = e.message || 'Create failed'; status.className = 'small mt-2 text-danger'; }
                } finally {
                    const btn = document.getElementById('btnCreateBackup');
                    if (btn) btn.disabled = false;
                }
            });

            document.getElementById('btnRestore')?.addEventListener('click', async () => {
                const status = document.getElementById('restoreStatus');
                if (status) { status.textContent = ''; status.className = 'small mt-2'; }

                const file = document.getElementById('restoreFile')?.files?.[0];
                const adminPassword = document.getElementById('adminPasswordRestore')?.value || '';
                const confirm = (document.getElementById('restoreConfirm')?.value || '').trim();

                if (!file) {
                    if (status) { status.textContent = 'Select a .sql file first.'; status.className = 'small mt-2 text-danger'; }
                    return;
                }
                if (!adminPassword) {
                    if (status) { status.textContent = 'Admin password is required.'; status.className = 'small mt-2 text-danger'; }
                    return;
                }
                if (confirm !== 'RESTORE') {
                    if (status) { status.textContent = 'Type RESTORE to confirm.'; status.className = 'small mt-2 text-danger'; }
                    return;
                }

                const fd = new FormData();
                fd.append('backup', file);
                fd.append('admin_password', adminPassword);
                fd.append('confirm', confirm);

                try {
                    const btn = document.getElementById('btnRestore');
                    if (btn) btn.disabled = true;

                    const res = await fetch(RESTORE_API, {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: fd,
                    });
                    const body = await res.json().catch(() => null);
                    if (!res.ok) throw new Error((body && body.error) ? body.error : 'Restore failed');

                    if (status) { status.textContent = 'Restore completed.'; status.className = 'small mt-2 text-success'; }
                    await loadBackups();
                } catch (e) {
                    if (status) { status.textContent = e.message || 'Restore failed'; status.className = 'small mt-2 text-danger'; }
                } finally {
                    const btn = document.getElementById('btnRestore');
                    if (btn) btn.disabled = false;
                }
            });

            loadBackups();
        }

        async function loadBackups() {
            const tbody = document.getElementById('backupTableBody');
            if (!tbody) return;

            tbody.innerHTML = '<tr><td colspan="4" class="text-muted">Loading...</td></tr>';
            const res = await fetch(LIST_API, { credentials: 'same-origin' });
            if (!res.ok) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-danger">Failed to load backups.</td></tr>';
                return;
            }

            const data = await res.json();
            const backups = Array.isArray(data.backups) ? data.backups : [];

            if (backups.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-muted">No backups found.</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            backups.forEach(b => {
                const tr = document.createElement('tr');
                const size = formatBytes(b.size || 0);
                const created = b.created_at || '';
                tr.innerHTML = `
                    <td><code>${escapeHtml(b.name)}</code></td>
                    <td class="text-end">${escapeHtml(size)}</td>
                    <td>${escapeHtml(created)}</td>
                    <td class="text-end">
                        <a class="btn btn-outline-primary btn-sm" href="${DOWNLOAD_API}/${encodeURIComponent(b.name)}/download">Download</a>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function formatBytes(bytes) {
            const units = ['B', 'KB', 'MB', 'GB'];
            let v = Number(bytes) || 0;
            let i = 0;
            while (v >= 1024 && i < units.length - 1) {
                v = v / 1024;
                i++;
            }
            return `${v.toFixed(i === 0 ? 0 : 1)} ${units[i]}`;
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
