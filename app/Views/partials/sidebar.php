<?php
$session = session();
$role = $session ? $session->get('role') ?? 'guest' : 'guest';
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
?>
<link href="<?= base_url('public/assets/theme.css') ?>" rel="stylesheet">
<script src="<?= base_url('public/assets/theme.js') ?>" defer></script>
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
    .sidebar .nav-link { color: #000 !important; padding: 12px 8px; display:block; }
<<<<<<< HEAD
    .sidebar .nav-link.active { color: #000 !important; font-weight: 600; }
    .sidebar .nav-link:hover { color: #000 !important; text-decoration: none; }

    @media (max-width: 991px) {
        .sidebar {
            position: relative;
            width: 100%;
            height: auto;
        }
        .sidebar .logout-dock {
            position: static !important;
            left: auto !important;
            bottom: auto !important;
            margin-top: 12px;
        }
=======
    .sidebar .nav-link.active { color: #000 !important; font-weight: 600; background-color: rgba(0,0,0,0.05); border-radius: 4px; }
    .sidebar .nav-link:hover { color: #000 !important; text-decoration: none; background-color: rgba(0,0,0,0.03); border-radius: 4px; }
    .sidebar .role-badge {
        display: inline-block;
        padding: 2px 8px;
        background: #2c3e50;
        color: white;
        border-radius: 12px;
        font-size: 12px;
        margin-top: 5px;
>>>>>>> 3ba9caf0451f5b53307c188eaea4609feaf8ea62
    }
</style>
<aside class="sidebar">
    <div class="profile">
        <div style="width:80px;height:80px;border-radius:50%;background:#ccc;margin:0 auto 8px; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-user" style="font-size: 36px; color: #666;"></i>
        </div>
        <div style="text-align:center; font-weight: 600; margin-bottom: 5px;"><?= esc(session()->get('name') ?? 'User') ?></div>
        <div class="role-badge"><?= esc($roleLabel) ?></div>
    </div>
    <nav class="nav flex-column">
        <?php if ($role === 'manager'): ?>
            <a class="nav-link" href="<?= site_url('dashboard/manager') ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a class="nav-link" href="<?= site_url('inventory') ?>">
                <i class="fas fa-boxes me-2"></i>Inventory
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/manager/stockmovement') ?>">
                <i class="fas fa-exchange-alt me-2"></i>Stock Movements
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/manager/workforce') ?>">
                <i class="fas fa-users me-2"></i>Workforce Management
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/manager/warehouses') ?>">
                <i class="fas fa-warehouse me-2"></i>Warehouses
            </a>
            
        <?php elseif ($role === 'staff'): ?>
            <a class="nav-link" href="<?= site_url('dashboard/staff') ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/staff/barcode') ?>">
                <i class="fas fa-barcode me-2"></i>Barcode Scanning
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/staff/stock') ?>">
                <i class="fas fa-exchange-alt me-2"></i>Stock Movements
            </a>
            <a class="nav-link" href="<?= site_url('inventory') ?>">
                <i class="fas fa-boxes me-2"></i>Inventory
            </a>
            
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
            <a class="nav-link" href="<?= site_url('dashboard/executive') ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Executive Dashboard
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/executive/financial') ?>">
                <i class="fas fa-chart-line me-2"></i>Financial Overview
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/executive/inventory') ?>">
                <i class="fas fa-boxes me-2"></i>Inventory Summary
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/executive/performance') ?>">
                <i class="fas fa-chart-bar me-2"></i>Performance Metrics
            </a>
            <a class="nav-link" href="<?= site_url('dashboard/executive/reports') ?>">
                <i class="fas fa-file-alt me-2"></i>Executive Reports
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
            <a class="nav-link" href="<?= site_url('profile') ?>">
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
<<<<<<< HEAD
    <div class="logout-dock" style="position:absolute;left:18px;bottom:18px">
        <a href="<?= site_url('logout') ?>" class="btn btn-sm btn-outline-dark">Logout</a>
=======
    <div style="position:absolute;left:18px;bottom:18px">
        <?php if ($role !== 'guest'): ?>
            <a href="<?= site_url('logout') ?>" class="btn btn-sm btn-outline-dark">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
        <?php else: ?>
            <a href="<?= site_url('login') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-sign-in-alt me-1"></i>Login
            </a>
        <?php endif; ?>
>>>>>>> 3ba9caf0451f5b53307c188eaea4609feaf8ea62
    </div>
</aside>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
    // mark active link based on current path
    (function(){
        try {
            var links = document.querySelectorAll('.sidebar .nav-link');
            var path = window.location.pathname.replace(/\/$/, '');
            var currentHref = '';
            
            links.forEach(function(a){
                try {
                    var href = new URL(a.href).pathname.replace(/\/$/, '');
                    
                    // Remove active class from all links first
                    a.classList.remove('active');
                    
                    // Check if this link's path matches current path
                    if (path === href) {
                        a.classList.add('active');
                        currentHref = href;
                    }
                } catch (e) { /* ignore */ }
            });
            
            // If no exact match found, check for partial matches
            if (!currentHref) {
                links.forEach(function(a){
                    try {
                        var href = new URL(a.href).pathname.replace(/\/$/, '');
                        if (href && path.startsWith(href) && href !== '/') {
                            a.classList.add('active');
                        }
                    } catch (e) { /* ignore */ }
                });
            }
            
            // Special case for dashboard home
            if (path === '' || path === '/dashboard') {
                links.forEach(function(a){
                    if (a.href.includes('/dashboard') && !a.href.includes('/dashboard/')) {
                        a.classList.add('active');
                    }
                });
            }
        } catch (e) { 
            console.error('Sidebar navigation error:', e);
        }
    })();
</script>