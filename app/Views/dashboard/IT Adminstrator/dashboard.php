<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We Build - IT Administrator Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            background: #34495e;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }

        .sidebar-menu {
            flex: 1;
            padding: 20px 0;
            overflow-y: auto;
        }

        .sidebar-menu::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
            font-size: 14px;
        }

        .menu-item:hover {
            background: #34495e;
            color: white;
            padding-left: 30px;
        }

        .menu-item.active {
            background: #34495e;
            border-left: 4px solid #3498db;
            color: white;
        }

        .menu-item i {
            width: 25px;
            margin-right: 15px;
            font-size: 16px;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Header Styles */
        .header {
            background: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid #e0e0e0;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-section img {
            width: 50px;
            height: auto;
        }

        .logo-section h1 {
            font-size: 32px;
            font-weight: 300;
            color: #333;
            margin: 0;
            font-style: italic;
            font-family: 'Times New Roman', Times, serif;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #34495e;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .user-details h4 {
            font-size: 14px;
            margin: 0;
            color: #333;
        }

        .user-details p {
            font-size: 12px;
            margin: 0;
            color: #666;
        }

        .settings-icon {
            font-size: 24px;
            color: #333;
            cursor: pointer;
            transition: all 0.3s;
        }

        .settings-icon:hover {
            color: #143449ff;
            transform: rotate(90deg);
        }

        /* Dashboard Content */
        .dashboard-content {
            flex: 1;
            padding: 30px 40px;
            overflow-y: auto;
            background: #f5f5f5;
        }

        .dashboard-title {
            font-size: 32px;
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            letter-spacing: 2px;
        }

        /* Quick Actions */
        .quick-actions {
            margin-bottom: 30px;
        }

        .quick-actions h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 12px 25px;
            background: #e0e0e0;
            border: none;
            border-radius: 5px;
            color: #333;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: #d0d0d0;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background: white;
            border: 2px solid #333;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .stat-card .subtitle {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .header {
                padding: 15px 20px;
            }

            .dashboard-content {
                padding: 20px;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>IT Administrator</h3>
        </div>
        <div class="sidebar-menu">
            <a href="<?= site_url('dashboard/admin') ?>" class="menu-item active">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?= site_url('user-management') ?>" class="menu-item">
                <i class="fas fa-users"></i>
                <span>User Management</span>
            </a>
            <a href="<?= site_url('access-control') ?>" class="menu-item">
                <i class="fas fa-lock"></i>
                <span>Access Control</span>
            </a>
            <a href="<?= site_url('system-logs') ?>" class="menu-item">
                <i class="fas fa-history"></i>
                <span>System Logs / Audit Trail</span>
            </a>
            <a href="<?= site_url('backup-recovery') ?>" class="menu-item">
                <i class="fas fa-sync-alt"></i>
                <span>Backup & Recovery</span>
            </a>
            <a href="<?= site_url('system-configuration') ?>" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>System Configuration</span>
            </a>
            <a href="<?= site_url('reports') ?>" class="menu-item">
                <i class="fas fa-file-alt"></i>
                <span>Reports</span>
            </a>
            <a href="<?= site_url('notifications') ?>" class="menu-item">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>
            <a href="<?= site_url('profile') ?>" class="menu-item">
                <i class="fas fa-user-circle"></i>
                <span>Profile / Account Settings</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <img src="<?= base_url('public/assets/llogo.png') ?>" alt="We Build Logo">
                <h1>We Build</h1>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <h4><?= esc(session()->get('name') ?? 'IT Administrator') ?></h4>
                        <p>Role: <?= esc(session()->get('role') ?? 'Admin') ?></p>
                    </div>
                </div>
                <a href="<?= site_url('logout') ?>" style="text-decoration: none;">
                    <i class="fas fa-sign-out-alt settings-icon" title="Logout"></i>
                </a>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <h2 class="dashboard-title">DASHBOARD</h2>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="action-buttons">
                    <button class="action-btn" onclick="window.location.href='<?= site_url('system-configuration') ?>'">System Maintenance</button>
                    <button class="action-btn" onclick="window.location.href='<?= site_url('access-control') ?>'">Security</button>
                    <button class="action-btn" onclick="window.location.href='<?= site_url('reports') ?>'">System Reports</button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Active Users</h3>
                    <div class="number">100</div>
                </div>
                <div class="stat-card">
                    <h3>System Uptime</h3>
                    <div class="number">99.97%</div>
                    <div class="subtitle">Last restart: 3 days ago</div>
                </div>
                <div class="stat-card">
                    <h3>Open Tickets</h3>
                    <div class="number">5</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>