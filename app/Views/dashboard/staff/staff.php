
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
    <style>
        /* Ensure main content leaves room for the fixed sidebar (layout only) */
        body { font-family: 'Times New Roman', serif; background: #fff; }
        .app-shell { display: flex; min-height: 100vh; }
        /* Keep sidebar fixed width at 220px (matches partials/sidebar) and shift main content */
        .main { flex: 1; padding: 24px 32px; margin-left: 220px; }
        .page-title { text-align:center; font-size: 34px; margin-top: 6px; margin-bottom: 14px; }

        @media (max-width: 991px) {
            .sidebar { position: relative; width: 100%; }
            .main { margin-left: 0; padding: 16px; }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <?= view('partials/sidebar') ?>

        <main class="main">
            <div class="header d-flex align-items-center justify-content-between mb-3">
                <div class="brand">WeBuild</div>
            </div>

            <div class="page-title">Warehouse DASHBOARD</div>

            <div class="container-fluid mt-3">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Recent Activity</h5>
                                <ul class="list-unstyled small">
                                    <li>Completed pick for Order #SO-2024-0123 - 2:15 PM</li>
                                    <li>Updated stock count for SKU BP-PRE-001 - 1:45 PM</li>
                                    <li>Put-away completed in Zone A-1-01 - 1:20 PM</li>
                                    <li>Completed pick for Order #SO-2024-0123 - 2:15 PM</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-body">
                                <h5 class="card-title">My Tasks <span class="badge bg-secondary ms-2">3</span></h5>
                                <div class="list-group list-group-flush mt-2">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>Pick Order</div>
                                        <small class="text-muted">View task</small>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>Put-away Inbound</div>
                                        <small class="text-muted">View task</small>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>Cycle Count</div>
                                        <small class="text-muted">View task</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card mb-3 text-center">
                            <div class="card-body">
                                <h6>Today's Progress</h6>
                                <h3>22 / 25</h3>
                                <div class="progress my-2" style="height:10px"><div class="progress-bar" role="progressbar" style="width:72%"></div></div>
                                <small class="text-muted">72.0% of daily target</small>
                            </div>
                        </div>

                        <div class="card mb-3 text-center">
                            <div class="card-body">
                                <h6>Weekly Progress</h6>
                                <h3>92 / 125</h3>
                                <div class="progress my-2" style="height:10px"><div class="progress-bar bg-dark" role="progressbar" style="width:73.6%"></div></div>
                                <small class="text-muted">73.6% of weekly target</small>
                            </div>
                        </div>

                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Efficiency</h6>
                                <h3 style="color:#1b63ff">87%</h3>
                                <small class="text-muted">Above team average</small>
                            </div>
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
