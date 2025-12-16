
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Warehouse Staff Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('css/site.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/manager.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/staff.css') ?>" rel="stylesheet">
    <link href="<?= base_url('public/assets/theme.css') ?>" rel="stylesheet">
    <script src="<?= base_url('public/assets/theme.js') ?>" defer></script>
    <style>
        /* small adjustments for staff-specific tweaks if needed */
        .page-title { text-align:center; font-size: 34px; margin-top: 6px; margin-bottom: 14px; }
    </style>
</head>
<body>
    <div class="app-shell">
        <?= view('partials/sidebar') ?>

        <div style="position:absolute;left:18px;bottom:18px;">
            <a href="<?= site_url('logout') ?>" class="btn btn-sm btn-outline-dark">Logout</a>
        </div>

        <main class="main">
            <div class="header">
                <div class="brand">WeBuild</div>
            </div>

            <div class="page-title">Warehouse DASHBOARD</div>

            <div class="main-content">
                <div class="top-columns">
                <div class="left-col">
                    <div class="recent-activity">
                        <h5>Recent Activity</h5>
                        <ul>
                            <li>Completed pick for Order #SO-2024-0123 - 2:15 PM</li>
                            <li>Updated stock count for SKU BP-PRE-001 - 1:45 PM</li>
                            <li>Put-away completed in Zone A-1-01 - 1:20 PM</li>
                            <li>Completed pick for Order #SO-2024-0123 - 2:15 PM</li>
                        </ul>
                    </div>

                    <div class="tasks-list mt-4">
                        <h5>My Tasks</h5>
                        <span class="badge tasks-badge">3 Pendings</span>
                        <div class="task-item mt-3">
                            <div>Pick Order</div>
                            <div class="mini-box">View task</div>
                        </div>
                        <div class="task-item">
                            <div>Put-away Inbound</div>
                            <div class="mini-box">View task</div>
                        </div>
                        <div class="task-item">
                            <div>Cycle Count</div>
                            <div class="mini-box">View task</div>
                        </div>
                    </div>
                </div>

                <div class="right-col">
                    <div class="stat-card">
                        <h6>Today's Progress</h6>
                        <h3>22 / 25</h3>
                        <div class="progress-small mt-3"><div class="bar" style="width:72%"></div></div>
                        <div class="mt-2"><small>72.0% of daily target</small></div>
                    </div>

                    <div class="stat-card">
                        <h6>Weekly Progress</h6>
                        <h3>92 / 125</h3>
                        <div class="progress-small mt-3"><div class="bar" style="width:73.6%"></div></div>
                        <div class="mt-2"><small>73.6% of weekly target</small></div>
                    </div>

                    <div class="stat-card text-center">
                        <h6>Efficiency</h6>
                        <h3 style="color:#1b63ff">87%</h3>
                        <div class="mt-2"><small>Above team average</small></div>
                    </div>
                </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/site.js') ?>"></script>
</body>
</html>
