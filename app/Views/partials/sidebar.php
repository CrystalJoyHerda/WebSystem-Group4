<?php
$session = session();
$role = $session ? $session->get('role') ?? 'guest' : 'guest';
$userName = $session ? $session->get('name') ?? 'Guest User' : 'Guest User';
$roleLabel = 'Guest';
if ($role === 'admin') $roleLabel = 'IT Administrator';
elseif ($role === 'manager') $roleLabel = 'Warehouse Manager';
elseif ($role === 'staff') $roleLabel = 'Warehouse Staff';
elseif ($role === 'viewer') $roleLabel = 'Warehouse Viewer';
?>
<style>
    /* Sidebar base layout (match manager dashboard spacing) */
    .sidebar {
        width: 220px;
        background: #ebeaea;
        padding: 20px;
        border-right: 1px solid #ddd;
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        overflow: auto;
        z-index: 10;
        color: #000;
    }
    .sidebar .profile { text-align: center; margin-bottom: 20px; }
    .sidebar .profile .user-name { font-weight: 600; font-size: 16px; margin-bottom: 4px; }
    .sidebar .profile .user-role { font-size: 13px; color: #666; }
    .sidebar .nav-link { color: #000 !important; padding: 12px 8px; display:block; }
    .sidebar .nav-link.active { color: #000 !important; font-weight: 600; }
    .sidebar .nav-link:hover { color: #000 !important; text-decoration: none; }
</style>
<aside class="sidebar">
    <div class="profile">
        <div style="width:80px;height:80px;border-radius:50%;background:#ccc;margin:0 auto 8px"></div>
        <div class="user-name"><?= esc($userName) ?></div>
        <div class="user-role"><?= esc($roleLabel) ?></div>
    </div>
    <nav class="nav flex-column">
        <?php if ($role === 'admin'): ?>
            <a class="nav-link" href="<?= site_url('dashboard/admin') ?>">Dashboard</a>
            <a class="nav-link" href="<?= site_url('user-management') ?>">User Management</a>
            <a class="nav-link" href="<?= site_url('system-logs') ?>">System Logs</a>
            <a class="nav-link" href="<?= site_url('system-configuration') ?>">Configuration</a>
        <?php elseif ($role === 'manager'): ?>
            <a class="nav-link" href="<?= site_url('dashboard/manager') ?>">Dashboard</a>
            <a class="nav-link" href="<?= site_url('inventory') ?>">Inventory</a>
            <a class="nav-link" href="<?= site_url('dashboard/manager/stockmovement') ?>">Stock Movements</a>
            <a class="nav-link" href="<?= site_url('dashboard/manager/workforce') ?>">Workforce Management</a>
            <a class="nav-link" href="<?= site_url('dashboard/manager/warehouses') ?>">Warehouses</a>
        <?php elseif ($role === 'staff'): ?>
            <a class="nav-link" href="<?= site_url('dashboard/staff') ?>">Dashboard</a>
            <a class="nav-link" href="<?= site_url('dashboard/staff/picking-packing') ?>">Picking & Packing</a>
            <a class="nav-link" href="<?= site_url('dashboard/staff/barcode') ?>">Barcode Scanning</a>
            <a class="nav-link" href="<?= site_url('inventory') ?>">Inventory</a>
        <?php elseif ($role === 'viewer'): ?>
            <a class="nav-link" href="<?= site_url('dashboard/viewer') ?>">Dashboard</a>
            <a class="nav-link" href="<?= site_url('inventory') ?>">View Inventory</a>
            <a class="nav-link" href="<?= site_url('dashboard/manager/warehouses') ?>">View Warehouses</a>
            <a class="nav-link" href="<?= site_url('dashboard/manager/stockmovement') ?>">View Stock Movements</a>
        <?php else: ?>
            <a class="nav-link" href="<?= site_url('dashboard') ?>">Dashboard</a>
        <?php endif; ?>
    </nav>
    <div style="position:absolute;left:18px;bottom:18px">
        <a href="<?= site_url('logout') ?>" class="btn btn-sm btn-outline-dark">Logout</a>
    </div>
</aside>
<script>
    // mark active link based on current path
    (function(){
        try {
            var links = document.querySelectorAll('.sidebar .nav-link');
            var path = window.location.pathname.replace(/\/$/, '');
            links.forEach(function(a){
                try {
                    var href = new URL(a.href).pathname.replace(/\/$/, '');
                    if (path === href || path.indexOf(href) === 0) {
                        a.classList.add('active');
                    } else {
                        a.classList.remove('active');
                    }
                } catch (e) { /* ignore */ }
            });
        } catch (e) { /* ignore */ }
    })();
</script>
