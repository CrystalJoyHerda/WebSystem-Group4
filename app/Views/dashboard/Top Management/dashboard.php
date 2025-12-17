 <!DOCTYPE html>
 <html lang="en">
 <head>
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1">
     <title>We Build - Top Management Dashboard</title>
     <script>
         (function () {
             try {
                 var lockedTheme = document.documentElement.getAttribute('data-theme-lock');
                 if (lockedTheme === 'dark' || lockedTheme === 'light') {
                     document.documentElement.setAttribute('data-theme', lockedTheme);
                     document.documentElement.setAttribute('data-bs-theme', lockedTheme);
                     return;
                 }
                 var t = null;
                 try { t = localStorage.getItem('theme'); } catch (e) { t = null; }
                 if (!t) {
                     t = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
                 }
                 document.documentElement.setAttribute('data-theme', t);
                 document.documentElement.setAttribute('data-bs-theme', t);
             } catch (e) {}
         })();
     </script>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <link href="<?= base_url('css/site.css') ?>" rel="stylesheet">
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

         /* sidebar (match IT Administrator layout) */
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
             gap: 12px;
         }
 
         .notification-icon {
             font-size: 24px;
             color: #ff6b35;
             cursor: pointer;
             position: relative;
         }
 
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
 
         .alert-number {
             font-size: 56px;
             font-weight: 300;
             color: #333;
         }
 
         .status-message {
             font-size: 13px;
             color: #4a90e2;
             margin-top: 15px;
         }
         .status-message.success { color: #27ae60; }
         .status-message.warning { color: #f39c12; }
         .status-message.danger { color: #b02a37; }
 
         .alerts-grid {
             display: grid;
             grid-template-columns: repeat(2, 1fr);
             gap: 30px;
             margin-bottom: 30px;
         }
 
         .alert-card {
             background: white;
             border: 1px solid #ddd;
             border-radius: 8px;
             padding: 30px;
             box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
         }
 
         .alert-card h3 {
             font-size: 18px;
             font-weight: 600;
             color: #333;
             margin-bottom: 16px;
             font-family: 'Georgia', 'Times New Roman', serif;
         }
 
         .badge-soft { background: rgba(13,110,253,0.08); color:#0d6efd; border: 1px solid rgba(13,110,253,0.2); }
         .badge-soft-warn { background: rgba(255,193,7,0.12); color:#b58100; border: 1px solid rgba(255,193,7,0.35); }
         .badge-soft-danger { background: rgba(220,53,69,0.12); color:#b02a37; border: 1px solid rgba(220,53,69,0.25); }
 
         @media (max-width: 1200px) {
             .systems-grid { grid-template-columns: repeat(2, 1fr); }
         }
 
         @media (max-width: 900px) {
             .sidebar{display:none;}
             .dashboard-content { padding: 24px 16px; }
             .systems-grid, .alerts-grid { grid-template-columns: 1fr; }
         }
     </style>
 </head>
 <body>
     <?php
         $path = service('uri')->getPath();
     ?>

     <div class="sidebar">
         <div class="sidebar-header">
             <div class="user-avatar">
                 <i class="fas fa-user"></i>
             </div>
             <h3>Top Management</h3>
         </div>
         <div class="sidebar-menu">
            <a href="<?= site_url('top-management') ?>" class="menu-item <?= ($path === 'top-management') ? 'active' : '' ?>">Dashboard</a>
            <a href="<?= site_url('top-management/inventory') ?>" class="menu-item <?= ($path === 'top-management/inventory') ? 'active' : '' ?>">Inventory Oversight</a>
            <a href="<?= site_url('top-management/transfers') ?>" class="menu-item <?= ($path === 'top-management/transfers') ? 'active' : '' ?>">Transfers</a>
            <a href="<?= site_url('top-management/approvals') ?>" class="menu-item <?= ($path === 'top-management/approvals') ? 'active' : '' ?>">Approvals</a>
            <a href="<?= site_url('top-management/finance') ?>" class="menu-item <?= ($path === 'top-management/finance') ? 'active' : '' ?>">Finance</a>
            <a href="<?= site_url('top-management/reports') ?>" class="menu-item <?= ($path === 'top-management/reports') ? 'active' : '' ?>">Reports</a>
            <a href="<?= site_url('top-management/audit') ?>" class="menu-item <?= ($path === 'top-management/audit') ? 'active' : '' ?>">Audit & Compliance</a>
            <a href="<?= site_url('top-management/profile') ?>" class="menu-item <?= ($path === 'top-management/profile') ? 'active' : '' ?>">My Profile</a>
        </div>
         <button class="logout-btn" onclick="window.location.href='<?= site_url('logout') ?>'">Logout</button>
     </div>
 
     <div class="main-content">
         <div class="header">
             <div class="logo-section">
                 <h1>WeBuild</h1>
             </div>
             <div class="header-right">
                <select id="warehouseSelect" class="form-select form-select-sm" style="min-width:220px;">
                    <option value="">All Warehouses</option>
                </select>
                <button id="btnRefresh" class="btn btn-sm btn-outline-dark">Refresh</button>
                <a href="#" class="position-relative" style="display:inline-block;" data-notifications-api="<?= site_url('api/top/notifications') ?>">
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
 
             <div class="systems-grid">
                 <div class="system-card">
                     <h3>Total Inventory Quantity</h3>
                     <div class="alert-number" id="kpiTotalQty">0</div>
                     <div class="status-message success">Tracked</div>
                 </div>
 
                 <div class="system-card">
                     <h3>Low/Critical Stock</h3>
                     <div class="alert-number" id="kpiLowStock">0</div>
                     <div class="status-message warning">Needs attention</div>
                 </div>
 
                 <div class="system-card">
                     <h3>Pending Approvals</h3>
                     <div class="alert-number" id="kpiPending">0</div>
                     <div class="status-message warning">Waiting review</div>
                 </div>
 
                 <div class="system-card">
                     <h3>Open Tickets</h3>
                     <div class="alert-number" id="kpiTickets">0</div>
                     <div class="status-message warning">Needs attention</div>
                 </div>
 
                 <div class="system-card">
                     <h3>Inbound Volume</h3>
                     <div class="alert-number" id="kpiInbound">0</div>
                     <div class="status-message success">Monitored</div>
                 </div>
 
                 <div class="system-card">
                     <h3>Outbound Volume</h3>
                     <div class="alert-number" id="kpiOutbound">0</div>
                     <div class="status-message success">Monitored</div>
                 </div>
             </div>
 
             <div class="alerts-grid">
                 <div class="alert-card">
                     <h3>Alerts</h3>
                     <div id="alerts">
                         <div class="status-message success">No critical issues</div>
                     </div>
                 </div>
                 <div class="alert-card">
                     <h3>Warehouse Comparison</h3>
                     <div class="table-responsive">
                         <table class="table table-hover mb-0">
                             <thead class="table-light">
                                 <tr>
                                     <th>Warehouse</th>
                                     <th style="width:100px" class="text-end">Items</th>
                                     <th style="width:100px" class="text-end">Qty</th>
                                     <th style="width:90px" class="text-end">Low</th>
                                 </tr>
                             </thead>
                             <tbody id="comparisonBody">
                                 <tr><td colspan="4" class="text-center text-muted p-3">Loading...</td></tr>
                             </tbody>
                         </table>
                     </div>
                 </div>
             </div>
 
             <div class="mt-4">
                 <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                     <h3 class="mb-0" style="font-size:18px;font-weight:600;">Pending Transfer Approvals</h3>
                     <div class="small text-muted" id="pendingCountLabel"></div>
                 </div>
                 <div class="card">
                     <div class="card-body p-0">
                         <div class="table-responsive">
                             <table class="table table-hover mb-0">
                                 <thead class="table-light">
                                     <tr>
                                         <th style="width:90px">ID</th>
                                         <th>Item</th>
                                         <th style="width:160px">From</th>
                                         <th style="width:160px">To</th>
                                         <th style="width:100px" class="text-end">Qty</th>
                                         <th style="width:130px">Status</th>
                                         <th style="width:190px">Action</th>
                                     </tr>
                                 </thead>
                                 <tbody id="pendingBody">
                                     <tr><td colspan="7" class="text-center text-muted p-4">Loading...</td></tr>
                                 </tbody>
                             </table>
                         </div>
                     </div>
                 </div>
                 <div class="small text-muted" id="pendingHint"></div>
             </div>
 
             <div class="mt-4">
                 <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                     <h3 class="mb-0" style="font-size:18px;font-weight:600;">Recent Activity</h3>
                 </div>
                 <div class="card">
                     <div class="card-body p-0">
                         <div class="table-responsive">
                             <table class="table table-hover mb-0">
                                 <thead class="table-light">
                                     <tr>
                                         <th style="width:170px">When</th>
                                         <th style="width:160px">Actor</th>
                                         <th>Summary</th>
                                     </tr>
                                 </thead>
                                 <tbody id="logsBody">
                                     <tr><td colspan="3" class="text-center text-muted p-4">Loading...</td></tr>
                                 </tbody>
                             </table>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 
 <script>
     function escapeHtml(str) {
         return String(str ?? '').replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;'}[m]));
     }
 
     function formatAbsoluteTime(s) {
         if (!s) return '';
         const d = new Date(String(s).replace(' ', 'T'));
         if (isNaN(d.getTime())) return String(s);
         const pad = (n) => String(n).padStart(2, '0');
         return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
     }
 
     function badgeForStatus(status) {
         const s = String(status || '').toLowerCase();
         if (s === 'pending') return '<span class="badge badge-soft-warn">Pending</span>';
         if (s === 'approved') return '<span class="badge badge-soft">Approved</span>';
         if (s === 'rejected') return '<span class="badge badge-soft-danger">Rejected</span>';
         if (s === 'completed') return '<span class="badge badge-soft">Completed</span>';
         return `<span class="badge bg-light text-dark">${escapeHtml(status)}</span>`;
     }
 
     function warehouseQueryParam() {
         const wid = document.getElementById('warehouseSelect')?.value || '';
         return wid ? `warehouse_id=${encodeURIComponent(wid)}` : '';
     }
 
     async function fetchJson(url) {
         const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
         const data = await res.json().catch(() => null);
         if (!res.ok) {
             const msg = data && data.error ? data.error : `Request failed (${res.status})`;
             throw new Error(msg);
         }
         return data;
     }
 
     function setAlerts(alerts) {
         const el = document.getElementById('alerts');
         if (!el) return;
         el.innerHTML = '';
         const list = Array.isArray(alerts) ? alerts : [];
         if (!list.length) {
             const div = document.createElement('div');
             div.className = 'status-message success';
             div.textContent = 'No critical issues';
             el.appendChild(div);
             return;
         }

         list.forEach(a => {
             const type = (a && a.type) ? String(a.type) : 'info';
             const msg = (a && a.message) ? String(a.message) : '';
             const div = document.createElement('div');
             if (type === 'warning') {
                 div.className = 'status-message warning';
             } else if (type === 'danger') {
                 div.className = 'status-message danger';
             } else if (type === 'success') {
                 div.className = 'status-message success';
             } else {
                 div.className = 'status-message';
             }
             div.textContent = msg;
             el.appendChild(div);
         });
     }
 
     async function loadWarehouses() {
         const select = document.getElementById('warehouseSelect');
         if (!select) return;
 
         const payload = await fetchJson('<?= site_url('api/top/warehouses') ?>');
         const warehouses = (payload && Array.isArray(payload.warehouses)) ? payload.warehouses : [];
 
         const existing = Array.from(select.querySelectorAll('option')).slice(1);
         existing.forEach(o => o.remove());
 
         warehouses.forEach(w => {
             const opt = document.createElement('option');
             opt.value = w.id;
             opt.textContent = w.name || (`Warehouse #${w.id}`);
             select.appendChild(opt);
         });
     }
 
     async function loadOverview() {
         const qp = warehouseQueryParam();
         const url = qp ? `<?= site_url('api/top/overview') ?>?${qp}` : `<?= site_url('api/top/overview') ?>`;
         const payload = await fetchJson(url);
 
         const m = payload && payload.metrics ? payload.metrics : {};
         document.getElementById('kpiTotalQty').textContent = String(m.inventory_total_quantity ?? 0);
         document.getElementById('kpiLowStock').textContent = String(m.low_stock_count ?? 0);
         document.getElementById('kpiPending').textContent = String(m.pending_approvals ?? 0);
         document.getElementById('kpiTickets').textContent = String(m.open_tickets ?? 0);
         document.getElementById('kpiInbound').textContent = String(m.inbound_qty ?? 0);
         document.getElementById('kpiOutbound').textContent = String(m.outbound_qty ?? 0);
 
         setAlerts(payload.alerts || []);
 
         const comparison = Array.isArray(payload.comparison) ? payload.comparison : [];
         const cBody = document.getElementById('comparisonBody');
         cBody.innerHTML = '';
         if (!comparison.length) {
             cBody.innerHTML = '<tr><td colspan="4" class="text-muted">No data</td></tr>';
         } else {
             comparison.forEach(r => {
                 const tr = document.createElement('tr');
                 tr.innerHTML = `
                     <td>${escapeHtml(r.name || '')}</td>
                     <td class="text-end">${escapeHtml(r.total_items || 0)}</td>
                     <td class="text-end">${escapeHtml(r.total_quantity || 0)}</td>
                     <td class="text-end">${escapeHtml(r.low_stock_count || 0)}</td>
                 `;
                 cBody.appendChild(tr);
             });
         }
     }
 
     async function loadPendingTransfers() {
         const qp = warehouseQueryParam();
         const url = qp ? `<?= site_url('api/top/pending-transfers') ?>?${qp}` : `<?= site_url('api/top/pending-transfers') ?>`;
         const payload = await fetchJson(url);
         const transfers = (payload && Array.isArray(payload.transfers)) ? payload.transfers : [];
 
         const body = document.getElementById('pendingBody');
         const label = document.getElementById('pendingCountLabel');
         body.innerHTML = '';
         label.textContent = transfers.length ? `${transfers.length} pending` : '';
 
         if (!transfers.length) {
             body.innerHTML = '<tr><td colspan="7" class="text-muted">No pending transfers</td></tr>';
             return;
         }
 
         transfers.forEach(t => {
             const tr = document.createElement('tr');
             tr.innerHTML = `
                 <td>${escapeHtml(t.id)}</td>
                 <td>
                     <div class="fw-semibold">${escapeHtml(t.item_name || 'Item')}</div>
                     <div class="small text-muted">${escapeHtml(t.item_sku || '')}</div>
                 </td>
                 <td>${escapeHtml(t.from_warehouse_name || t.from_warehouse_id || '')}</td>
                 <td>${escapeHtml(t.to_warehouse_name || t.to_warehouse_id || '')}</td>
                 <td class="text-end">${escapeHtml(t.quantity || 0)}</td>
                 <td>${badgeForStatus(t.status)}</td>
                 <td>
                     <div class="d-flex gap-1">
                         <button class="btn btn-sm btn-outline-success" data-action="approve" data-id="${escapeHtml(t.id)}">Approve</button>
                         <button class="btn btn-sm btn-outline-danger" data-action="reject" data-id="${escapeHtml(t.id)}">Reject</button>
                     </div>
                 </td>
             `;
             body.appendChild(tr);
         });
     }
 
     async function loadAuditLogs() {
         const qp = warehouseQueryParam();
         const url = qp ? `<?= site_url('api/top/audit-logs') ?>?${qp}&limit=10` : `<?= site_url('api/top/audit-logs') ?>?limit=10`;
         const payload = await fetchJson(url);
         const logs = (payload && Array.isArray(payload.logs)) ? payload.logs : [];
 
         const body = document.getElementById('logsBody');
         body.innerHTML = '';
         if (!logs.length) {
             body.innerHTML = '<tr><td colspan="3" class="text-muted">No logs</td></tr>';
             return;
         }
 
         logs.forEach(l => {
             const tr = document.createElement('tr');
             tr.innerHTML = `
                 <td class="small">${escapeHtml(formatAbsoluteTime(l.created_at || ''))}</td>
                 <td class="small">${escapeHtml(l.actor_name || 'System')}</td>
                 <td class="small">${escapeHtml(l.summary || '')}</td>
             `;
             body.appendChild(tr);
         });
     }
 
     async function decideTransfer(id, action) {
         const notes = prompt('Optional notes:', '') ?? '';
         const res = await fetch(`<?= site_url('api/top/transfers') ?>/${encodeURIComponent(id)}/decide`, {
             method: 'POST',
             headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
             body: JSON.stringify({ action, notes })
         });
         const data = await res.json().catch(() => null);
         if (!res.ok) {
             throw new Error(data && data.error ? data.error : `Request failed (${res.status})`);
         }
         return data;
     }
 
     function attachHandlers() {
         const select = document.getElementById('warehouseSelect');
         const btn = document.getElementById('btnRefresh');
         if (select) {
             select.addEventListener('change', () => reloadAll());
         }
         if (btn) {
             btn.addEventListener('click', () => reloadAll());
         }
 
         const pendingBody = document.getElementById('pendingBody');
         pendingBody.addEventListener('click', async (e) => {
             const target = e.target;
             if (!target || !target.dataset) return;
             const action = target.dataset.action;
             const id = target.dataset.id;
             if (!action || !id) return;
 
             if (!confirm(`${action === 'approve' ? 'Approve' : 'Reject'} transfer #${id}?`)) return;
             try {
                 target.disabled = true;
                 await decideTransfer(id, action);
                 await loadOverview();
                 await loadPendingTransfers();
                 await loadAuditLogs();
             } catch (err) {
                 alert(err && err.message ? err.message : String(err));
             } finally {
                 target.disabled = false;
             }
         });
     }
 
     async function reloadAll() {
         try {
             await Promise.all([loadOverview(), loadPendingTransfers(), loadAuditLogs()]);
         } catch (err) {
             alert(err && err.message ? err.message : String(err));
         }
     }
 
     (async function init() {
         try {
             await loadWarehouses();
             attachHandlers();
             await reloadAll();
         } catch (err) {
             alert(err && err.message ? err.message : String(err));
         }
     })();
 </script>
 </body>
 </html>
