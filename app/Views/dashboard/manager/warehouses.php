<?php $role = session() ? session()->get('role') ?? 'User' : 'User'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Warehouses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('css/site.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/manager.css') ?>" rel="stylesheet">
    <link href="<?= base_url('public/assets/theme.css') ?>" rel="stylesheet">
    <script src="<?= base_url('public/assets/theme.js') ?>" defer></script>
    <style>
        body { font-family: 'Times New Roman', serif; background: #fff; }
        .app-shell { display: flex; min-height: 100vh; }
        .main { margin-left: 220px; flex: 1; padding: 28px; }
        .page-title { text-align:center; font-size: 34px; margin-top: 6px; margin-bottom: 14px; }
    </style>
</head>
<body>
    <div class="app-shell">
        <?= view('partials/sidebar') ?>

        <main class="main">
            <div class="header">
                <div class="brand">WeBuild</div>
            </div>

            <div class="page-title">Warehouses</div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Warehouse Management</h5>
                        <a href="<?= site_url('warehouses/seed-test-data') ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-database"></i> Generate Test Data
                        </a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Contact</th>
                                    <th>Capacity</th>
                                    <th>Current Usage</th>
                                    <th>Usage %</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (! empty($warehouses) && is_array($warehouses)): ?>
                                    <?php foreach ($warehouses as $w): ?>
                                        <?php 
                                            $capacity = $w['capacity'] ?? 0;
                                            $currentUsage = $w['current_usage'] ?? 0;
                                            $usagePercent = $capacity > 0 ? round(($currentUsage / $capacity) * 100, 1) : 0;
                                            $status = $w['status'] ?? 'active';
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary"><?= esc($w['id']) ?></span>
                                            </td>
                                            <td>
                                                <strong><?= esc($w['name']) ?></strong>
                                            </td>
                                            <td>
                                                <i class="fas fa-map-marker-alt text-muted me-1"></i>
                                                <?= esc($w['location']) ?>
                                            </td>
                                            <td>
                                                <i class="fas fa-phone text-muted me-1"></i>
                                                <?= esc($w['contact_info'] ?? 'N/A') ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= number_format($capacity) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?= number_format($currentUsage) ?></span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px; width: 80px;">
                                                    <div class="progress-bar <?= $usagePercent > 80 ? 'bg-danger' : ($usagePercent > 60 ? 'bg-warning' : 'bg-success') ?>" 
                                                         style="width: <?= $usagePercent ?>%">
                                                        <?= $usagePercent ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?= $status === 'active' ? 'bg-success' : ($status === 'maintenance' ? 'bg-warning' : 'bg-danger') ?>">
                                                    <i class="fas fa-<?= $status === 'active' ? 'check-circle' : ($status === 'maintenance' ? 'tools' : 'times-circle') ?> me-1"></i>
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-info" onclick="viewWarehouse(<?= $w['id'] ?>)" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-warning" onclick="editWarehouse(<?= $w['id'] ?>)" title="Edit Warehouse">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-primary" onclick="viewInventory(<?= $w['id'] ?>)" title="View Inventory">
                                                        <i class="fas fa-boxes"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="9" class="text-center text-muted py-5">
                                        <i class="fas fa-warehouse fa-3x mb-3 text-muted"></i>
                                        <p class="mb-0">No warehouses found</p>
                                        <small>Click "Generate Test Data" to create sample warehouses</small>
                                    </td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewWarehouse(id) {
            // TODO: Implement warehouse view modal or redirect
            alert('View warehouse details for ID: ' + id);
        }

        function editWarehouse(id) {
            // TODO: Implement warehouse edit modal or redirect
            alert('Edit warehouse for ID: ' + id);
        }

        function viewInventory(id) {
            // Redirect to inventory page filtered by warehouse
            window.location.href = '<?= site_url('dashboard/manager/inventory') ?>?warehouse_id=' + id;
        }

        // Auto-refresh data every 30 seconds for real-time updates
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
