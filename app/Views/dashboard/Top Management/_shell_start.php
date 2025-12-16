<?php
    $title = $title ?? 'Top Management';
    $active = $active ?? '';
    $path = service('uri')->getPath();
    if ($active === '') {
        $active = $path;
    }

    $isActive = function (string $key) use ($active): bool {
        if ($active === $key) return true;
        if ($active === trim($key, '/')) return true;
        return false;
    };
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?></title>
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

        @media (max-width: 900px) {
            .sidebar{display:none;}
            .dashboard-content { padding: 24px 16px; }
            .systems-grid, .alerts-grid { grid-template-columns: 1fr; }
        }

        .badge-soft { background: rgba(13,110,253,0.08); color:#0d6efd; border: 1px solid rgba(13,110,253,0.2); }
        .badge-soft-warn { background: rgba(255,193,7,0.12); color:#b58100; border: 1px solid rgba(255,193,7,0.35); }
        .badge-soft-danger { background: rgba(220,53,69,0.12); color:#b02a37; border: 1px solid rgba(220,53,69,0.25); }

        .status-message {
            font-size: 13px;
            color: #4a90e2;
            margin-top: 8px;
        }
        .status-message.success { color: #27ae60; }
        .status-message.warning { color: #f39c12; }
        .status-message.danger { color: #b02a37; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h3>Top Management</h3>
        </div>
        <div class="sidebar-menu">
            <a href="<?= site_url('top-management') ?>" class="menu-item <?= $isActive('top-management') ? 'active' : '' ?>">Dashboard</a>
            <a href="<?= site_url('top-management/inventory') ?>" class="menu-item <?= $isActive('top-management/inventory') ? 'active' : '' ?>">Inventory Oversight</a>
            <a href="<?= site_url('top-management/transfers') ?>" class="menu-item <?= $isActive('top-management/transfers') ? 'active' : '' ?>">Transfers</a>
            <a href="<?= site_url('top-management/approvals') ?>" class="menu-item <?= $isActive('top-management/approvals') ? 'active' : '' ?>">Approvals</a>
            <a href="<?= site_url('top-management/finance') ?>" class="menu-item <?= $isActive('top-management/finance') ? 'active' : '' ?>">Finance</a>
            <a href="<?= site_url('top-management/reports') ?>" class="menu-item <?= $isActive('top-management/reports') ? 'active' : '' ?>">Reports</a>
            <a href="<?= site_url('top-management/audit') ?>" class="menu-item <?= $isActive('top-management/audit') ? 'active' : '' ?>">Audit & Compliance</a>
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
            <h2 class="dashboard-title"><?= esc($title) ?></h2>
