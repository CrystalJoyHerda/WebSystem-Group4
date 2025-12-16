<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We Build - IT Administrator System Logs</title>
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
            <div class="toolbar">
                <div>
                    <div class="page-title">System Logs</div>
                    <div class="text-muted small">Audit trail of actions performed in the system.</div>
                    <div class="text-muted small" id="currentTime" style="margin-top:2px;"></div>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <input id="logSearch" class="form-control form-control-sm" style="min-width:240px" placeholder="Search action/entity/user">
                    <div class="form-check form-check-inline small ms-1">
                        <input class="form-check-input" type="checkbox" id="allWarehouses">
                        <label class="form-check-label" for="allWarehouses">All warehouses</label>
                    </div>
                    <select id="entityFilter" class="form-select form-select-sm" style="min-width:180px">
                        <option value="">All Entities</option>
                        <option value="user">User</option>
                    </select>
                    <select id="actionFilter" class="form-select form-select-sm" style="min-width:180px">
                        <option value="">All Actions</option>
                        <option value="create">Create</option>
                        <option value="update">Update</option>
                        <option value="delete">Delete</option>
                        <option value="status">Status</option>
                        <option value="reset_password">Reset Password</option>
                    </select>
                </div>
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
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody id="logsTbody">
                                <tr><td colspan="7" class="text-center text-muted p-4">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (() => {
        const API_BASE = '<?= site_url('api/admin/audit-logs') ?>';
        const WAREHOUSE_API = '<?= site_url('api/admin/warehouses') ?>';
        const SET_WAREHOUSE_API = '<?= site_url('api/admin/current-warehouse') ?>';
        let debounceTimer = null;
        let liveTimer = null;
        let clockTimer = null;

        document.getElementById('logSearch').addEventListener('input', scheduleLoad);
        document.getElementById('entityFilter').addEventListener('change', loadLogs);
        document.getElementById('actionFilter').addEventListener('change', loadLogs);

        initWarehouse();

        startClock();

        function scheduleLoad() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(loadLogs, 250);
        }

        function buildUrl() {
            const q = (document.getElementById('logSearch').value || '').trim();
            const entity = document.getElementById('entityFilter').value;
            const action = document.getElementById('actionFilter').value;
            const all = document.getElementById('allWarehouses').checked;

            const params = new URLSearchParams();
            if (q) params.set('q', q);
            if (entity) params.set('entity_type', entity);
            if (action) params.set('action', action);
            if (all) params.set('all', '1');
            params.set('limit', '100');

            return `${API_BASE}?${params.toString()}`;
        }

        async function initWarehouse() {
            const sel = document.getElementById('warehouseSelect');
            if (!sel) {
                loadLogs();
                return;
            }

            const res = await fetch(WAREHOUSE_API, { credentials: 'same-origin' });
            if (!res.ok) {
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
                loadLogs();
            } else if (warehouses.length > 0) {
                await setWarehouse(Number(warehouses[0].id));
            } else {
                loadLogs();
            }

            sel.addEventListener('change', async () => {
                const id = Number(sel.value);
                if (!id) return;
                await setWarehouse(id);
            });

            document.getElementById('allWarehouses').addEventListener('change', loadLogs);
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

        async function loadLogs() {
            const tbody = document.getElementById('logsTbody');
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted p-4">Loading...</td></tr>';

            const res = await fetch(buildUrl(), { credentials: 'same-origin' });
            if (!res.ok) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted p-4">Failed to load</td></tr>';
                return;
            }

            const payload = await res.json();
            const logs = (payload && Array.isArray(payload.logs)) ? payload.logs : payload;
            tbody.innerHTML = '';
            if (!Array.isArray(logs) || logs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted p-4">No logs</td></tr>';
                return;
            }

            logs.forEach(l => {
                const tr = document.createElement('tr');
                const whenAbs = formatAbsoluteTime(l.created_at || '');
                tr.innerHTML = `
                    <td>${escapeHtml(l.id)}</td>
                    <td class="js-live-time" data-ts="${escapeHtml(l.created_at || '')}" title="${escapeHtml(formatRelativeTime(l.created_at || ''))}">${escapeHtml(whenAbs)}</td>
                    <td>${escapeHtml(l.actor_name || 'System')}</td>
                    <td>${escapeHtml(prettyAction(l.action || ''))}</td>
                    <td>${escapeHtml(l.entity_type || '')}</td>
                    <td>${escapeHtml(l.entity_id || '')}</td>
                    <td class="text-muted small">${escapeHtml(l.summary || '')}</td>
                `;
                tbody.appendChild(tr);
            });

            refreshLiveTimes();
            if (!liveTimer) {
                liveTimer = setInterval(refreshLiveTimes, 30000);
            }
        }

        function refreshLiveTimes() {
            document.querySelectorAll('.js-live-time').forEach(td => {
                const ts = td.getAttribute('data-ts') || '';
                td.title = formatRelativeTime(ts);
            });
        }

        function parseMysqlDateTime(s) {
            const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2}):(\d{2})/);
            if (!m) return null;
            const y = Number(m[1]);
            const mo = Number(m[2]) - 1;
            const d = Number(m[3]);
            const hh = Number(m[4]);
            const mm = Number(m[5]);
            const ss = Number(m[6]);
            const dt = new Date(y, mo, d, hh, mm, ss);
            return isNaN(dt.getTime()) ? null : dt;
        }

        function pad2(n) {
            const v = Number(n);
            return v < 10 ? `0${v}` : String(v);
        }

        function formatAbsoluteTime(s) {
            const dt = parseMysqlDateTime(s);
            if (!dt) return String(s || '');
            const y = dt.getFullYear();
            const mo = pad2(dt.getMonth() + 1);
            const d = pad2(dt.getDate());
            const hh = pad2(dt.getHours());
            const mm = pad2(dt.getMinutes());
            const ss = pad2(dt.getSeconds());
            return `${y}-${mo}-${d} ${hh}:${mm}:${ss}`;
        }

        function startClock() {
            const el = document.getElementById('currentTime');
            if (!el) return;

            const update = () => {
                const now = new Date();
                const y = now.getFullYear();
                const mo = pad2(now.getMonth() + 1);
                const d = pad2(now.getDate());
                const hh = pad2(now.getHours());
                const mm = pad2(now.getMinutes());
                const ss = pad2(now.getSeconds());
                el.textContent = `Current time: ${y}-${mo}-${d} ${hh}:${mm}:${ss}`;
            };

            update();
            if (!clockTimer) {
                clockTimer = setInterval(update, 1000);
            }
        }

        function formatRelativeTime(s) {
            const dt = parseMysqlDateTime(s);
            if (!dt) return String(s || '');

            let diffMs = Date.now() - dt.getTime();
            if (diffMs < 0) diffMs = 0;

            const sec = Math.floor(diffMs / 1000);
            if (sec < 10) return 'Just now';
            if (sec < 60) return `${sec}s ago`;

            const min = Math.floor(sec / 60);
            if (min < 60) return `${min}m ago`;

            const hr = Math.floor(min / 60);
            if (hr < 24) return `${hr}h ago`;

            const day = Math.floor(hr / 24);
            if (day < 7) return `${day}d ago`;

            return dt.toLocaleString();
        }

        function prettyAction(a) {
            const map = {
                'create': 'Create',
                'update': 'Update',
                'delete': 'Delete',
                'status': 'Status Change',
                'reset_password': 'Reset Password'
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
