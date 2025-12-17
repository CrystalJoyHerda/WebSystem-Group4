<?php
$session = session();
$role = $session ? $session->get('role') ?? 'guest' : 'guest';
$userName = $session ? $session->get('name') ?? 'Guest User' : 'Guest User';
$roleLabel = 'Guest';

if ($role === 'manager') $roleLabel = 'Warehouse Manager';
elseif ($role === 'staff') $roleLabel = 'Warehouse Staff';
elseif ($role === 'viewer') $roleLabel = 'Warehouse Viewer';
elseif ($role === 'accounts_payable') $roleLabel = 'Accounts Payable Clerk';
elseif ($role === 'accounts_receivable') $roleLabel = 'Accounts Receivable Clerk';
elseif ($role === 'procurement_officer') $roleLabel = 'Procurement Officer';
elseif ($role === 'inventory_auditor') $roleLabel = 'Inventory Auditor';
elseif ($role === 'it_administrator') $roleLabel = 'IT Administrator';
elseif ($role === 'topmanagement') $roleLabel = 'Top Management';

$profileUrl = 'profile';
if ($role === 'topmanagement') {
    $profileUrl = 'top-management/profile';
}
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
            <a class="nav-link" href="<?= site_url('dashboard/viewer') ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a class="nav-link" href="<?= site_url('inventory') ?>">
                <i class="fas fa-boxes me-2"></i>View Inventory
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/manager/warehouses') ?>">
                <i class="fas fa-warehouse me-2"></i>View Warehouses
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/manager/stockmovement') ?>">
                <i class="fas fa-exchange-alt me-2"></i>View Stock Movements
            </a>
            
        <?php elseif ($role === 'accounts_payable'): ?>
            <a class="nav-link" href="<?= site_url('dashboard/accounts_payable/dashboard') ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/accounts_payable/invoices') ?>">
                <i class="fas fa-file-invoice-dollar me-2"></i>Invoice Management
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/accounts_payable/vendors') ?>">
                <i class="fas fa-building me-2"></i>Vendor Management
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/accounts_payable/payments') ?>">
                <i class="fas fa-money-check-alt me-2"></i>Payment Processing
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/accounts_payable/reports') ?>">
                <i class="fas fa-chart-bar me-2"></i>Reports
            </a>
            <hr style="margin: 10px 0;">
            <div class="sidebar-section-title" style="font-size: 12px; color: #666; padding: 5px 8px; text-transform: uppercase;">
                Quick Actions
            </div>
            <a class="nav-link" href="<?= site_url('dashboard/accounts_payable/invoices/create') ?>">
                <i class="fas fa-plus-circle me-2"></i>Create Invoice
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/accounts_payable/vendors/create') ?>">
                <i class="fas fa-user-plus me-2"></i>Add Vendor
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/accounts_payable/payments/create') ?>">
                <i class="fas fa-money-check me-2"></i>Process Payment
            </a>
            
        <?php elseif ($role === 'accounts_receivable'): ?>
            <a class="nav-link" href="<?= site_url('dashboard/accounts_receivable') ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/accounts_receivable/invoices') ?>">
                <i class="fas fa-file-invoice me-2"></i>Customer Invoices
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/accounts_receivable/customers') ?>">
                <i class="fas fa-user-tie me-2"></i>Customer Management
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/accounts_receivable/receipts') ?>">
                <i class="fas fa-receipt me-2"></i>Payment Receipts
            </a>
            
        <?php elseif ($role === 'procurement_officer'): ?>
            <a class="nav-link" href="<?= site_url('dashboard/procurement') ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/procurement/purchase-orders') ?>">
                <i class="fas fa-clipboard-list me-2"></i>Purchase Orders
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/procurement/suppliers') ?>">
                <i class="fas fa-truck me-2"></i>Supplier Management
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/procurement/requests') ?>">
                <i class="fas fa-shopping-cart me-2"></i>Purchase Requests
            </a>
            
        <?php elseif ($role === 'inventory_auditor'): ?>
            <a class="nav-link" href="<?= site_url('dashboard/auditor') ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/auditor/audits') ?>">
                <i class="fas fa-clipboard-check me-2"></i>Audit Management
            </a>
            <a class="nav-link" href="<?= site_url('inventory') ?>">
                <i class="fas fa-boxes me-2"></i>Inventory Review
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/auditor/reports') ?>">
                <i class="fas fa-chart-line me-2"></i>Audit Reports
            </a>
            
        <?php elseif ($role === 'it_administrator'): ?>
            <a class="nav-link" href="<?= site_url('dashboard/admin') ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a class="nav-link" href="<?= site_url('user-management') ?>">
                <i class="fas fa-users-cog me-2"></i>User Management
            </a>
            <a class="nav-link" href="<?= site_url('access-control') ?>">
                <i class="fas fa-shield-alt me-2"></i>Access Control
            </a>
            <a class="nav-link" href="<?= site_url('system-logs') ?>">
                <i class="fas fa-clipboard-list me-2"></i>System Logs
            </a>
            <a class="nav-link" href="<?= site_url('backup-recovery') ?>">
                <i class="fas fa-database me-2"></i>Backup & Recovery
            </a>
            <a class="nav-link" href="<?= site_url('system-configuration') ?>">
                <i class="fas fa-cogs me-2"></i>System Configuration
            </a>
            
        <?php elseif ($role === 'topmanagement'): ?>
            <a class="nav-link" href="<?= site_url('top-management') ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Executive Dashboard
            </a>
            
        <?php else: ?>
            <a class="nav-link" href="<?= site_url('dashboard') ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a class="nav-link" href="<?= site_url('login') ?>">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </a>
            <a class="nav-link" href="<?= site_url('register') ?>">
                <i class="fas fa-user-plus me-2"></i>Register
            </a>
        <?php endif; ?>
        
        <!-- Common links for all authenticated users -->
        <?php if ($role !== 'guest'): ?>
            <hr style="margin: 10px 0;">
            <div class="sidebar-section-title" style="font-size: 12px; color: #666; padding: 5px 8px; text-transform: uppercase;">
                General
            </div>
            <a class="nav-link" href="<?= site_url($profileUrl) ?>">
                <i class="fas fa-user me-2"></i>My Profile
            </a>
            <a class="nav-link" href="<?= site_url('notifications') ?>">
                <i class="fas fa-bell me-2"></i>Notifications
                <?php if (isset($notificationCount) && $notificationCount > 0): ?>
                    <span class="badge bg-danger float-end"><?= $notificationCount ?></span>
                <?php endif; ?>
            </a>
            <a class="nav-link" href="<?= site_url('help') ?>">
                <i class="fas fa-question-circle me-2"></i>Help & Support
            </a>
        <?php endif; ?>
    </nav>
    <div class="logout-dock" style="position:absolute;left:18px;bottom:18px">
        <?php if ($role !== 'guest'): ?>
            <a href="<?= site_url('logout') ?>" class="btn btn-sm btn-outline-dark">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
        <?php else: ?>
            <a href="<?= site_url('login') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-sign-in-alt me-1"></i>Login
            </a>
        <?php endif; ?>
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
