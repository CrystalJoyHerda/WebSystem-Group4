
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Warehouse Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('css/site.css') ?>" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', serif; background: #fff; }
        .app-shell { display: flex; min-height: 100vh; }
    .sidebar { width: 220px; background: #ebeaea; padding: 20px; border-right: 1px solid #ddd; }
    .sidebar .profile { text-align: center; margin-bottom: 20px; }
    .sidebar .nav-link { color: #000; padding: 12px 8px; display:block; }
        .main { flex: 1; padding: 24px 32px; }
        .header { display:flex; align-items:center; justify-content:space-between; margin-bottom: 18px; }
        .brand { font-family: 'Georgia', serif; font-size: 28px; }
        .page-title { text-align:center; font-size: 34px; margin-top: 6px; margin-bottom: 14px; }
        .card-warehouse { border-radius: 8px; border:1px solid #dcdcdc; padding: 18px; }
        .stat-card { border-radius: 16px; border:1px solid #dcdcdc; padding: 28px; text-align:center; }
        .stat-card h3 { font-size: 48px; margin:0; }
        .warehouses { display:flex; gap:16px; }
        @media (max-width: 900px) { .warehouses { flex-direction:column; } .sidebar{display:none;} }
    </style>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <?php
            // Determine a friendly role label from session
            $roleLabel = 'Guest';
            if (function_exists('session')) {
                $sess = session();
                if ($sess->has('role')) {
                    $r = $sess->get('role');
                    if ($r === 'manager') {
                        $roleLabel = 'Warehouse Manager';
                    } elseif ($r === 'staff') {
                        $roleLabel = 'Warehouse Staff';
                    } else {
                        $roleLabel = ucfirst($r);
                    }
                }
            }
            ?>
            <div class="profile">
                <div style="width:80px;height:80px;border-radius:50%;background:#ccc;margin:0 auto 8px"></div>
                <div><?= esc($roleLabel) ?></div>
            </div>
            <nav>
                <a class="nav-link" href="<?= site_url('dashboard/manager') ?>">Dashboard</a>
                <a class="nav-link" href="<?= site_url('inventory') ?>">Inventory</a>
                <a class="nav-link" href="#">Stock Movements</a>
            </nav>
        </aside>

            <div style="position:absolute;left:18px;bottom:18px;">
                <a href="<?= site_url('logout') ?>" class="btn btn-sm btn-outline-dark">Logout</a>
            </div>

        <main class="main">
            <div class="header">
                <div class="brand">WeBuild</div>
            </div>

            <div class="page-title">Warehouse DASHBOARD</div>

            <div class="row mb-4 warehouses">
                <div class="col card-warehouse">
                    <h5>Warehouse A</h5>
                    <div class="d-flex justify-content-between"><small>Capacity</small><small>75.0%</small></div>
                    <div class="progress my-2" style="height:10px"><div class="progress-bar" role="progressbar" style="width:75%"></div></div>
                    <div class="d-flex gap-3 mt-3">
                        <div style="background:#f1f1f1;padding:10px;border-radius:8px;min-width:90px">Items<br><strong>2,340</strong></div>
                        <div style="background:#f1f1f1;padding:10px;border-radius:8px;min-width:90px">Staff<br><strong>12</strong></div>
                    </div>
                    <div class="mt-3"><small class="text-success">Inbound shipment completed 2h ago</small></div>
                </div>

                <div class="col card-warehouse">
                    <h5>Warehouse B</h5>
                    <div class="d-flex justify-content-between"><small>Capacity</small><small>87.70%</small></div>
                    <div class="progress my-2" style="height:10px"><div class="progress-bar bg-dark" role="progressbar" style="width:87.7%"></div></div>
                    <div class="d-flex gap-3 mt-3">
                        <div style="background:#f1f1f1;padding:10px;border-radius:8px;min-width:90px">Items<br><strong>1,890</strong></div>
                        <div style="background:#f1f1f1;padding:10px;border-radius:8px;min-width:90px">Staff<br><strong>8</strong></div>
                    </div>
                    <div class="mt-3"><small class="text-info">Stock transfer in progress</small></div>
                </div>

                <div class="col card-warehouse">
                    <h5>Warehouse C</h5>
                    <div class="d-flex justify-content-between"><small>Capacity</small><small>95.8%</small></div>
                    <div class="progress my-2" style="height:10px"><div class="progress-bar bg-dark" role="progressbar" style="width:95.8%"></div></div>
                    <div class="d-flex gap-3 mt-3">
                        <div style="background:#f1f1f1;padding:10px;border-radius:8px;min-width:90px">Items<br><strong>3,120</strong></div>
                        <div style="background:#f1f1f1;padding:10px;border-radius:8px;min-width:90px">Staff<br><strong>15</strong></div>
                    </div>
                    <div class="mt-3"><small class="text-info">Stock transfer in progress</small></div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <h6>Pending Approvals</h6>
                        <h3>5</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <h6>Alert Stocks</h6>
                        <h3>3</h3>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/site.js') ?>"></script>
</body>
</html>
