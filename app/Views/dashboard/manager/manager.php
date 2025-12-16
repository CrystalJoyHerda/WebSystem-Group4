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
        /* content leaves room for the fixed sidebar (220px) */
        .main { flex: 1; padding: 24px 32px; margin-left: 220px; }
        .header { display:flex; align-items:center; justify-content:space-between; margin-bottom: 18px; }
        .brand { font-family: 'Georgia', serif; font-size: 28px; }
        .page-title { text-align:center; font-size: 34px; margin-top: 6px; margin-bottom: 14px; }
        .card-warehouse { border-radius: 8px; border:1px solid #dcdcdc; padding: 18px; }
        .stat-card { border-radius: 16px; border:1px solid #dcdcdc; padding: 28px; text-align:center; }
        .stat-card h3 { font-size: 48px; margin:0; }
        .warehouses { display:flex; gap:16px; }
        /* clickable stat card link */
        .stat-link { display:block; color:inherit; text-decoration:none; }
        .stat-link:focus, .stat-link:hover { text-decoration:none; }
        @media (max-width: 900px) { .warehouses { flex-direction:column; } .sidebar{display:none;} .main{margin-left:0;padding:16px;} }
    </style>
</head>
<body>
    <div class="app-shell">
        <?= view('partials/sidebar') ?>

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
                <?php
// ensure $items is set — if controller didn't provide it, load from the Inventory model
if (empty($items) || ! is_array($items)) {
    try {
        $inventoryModel = new \App\Models\InventoryModel();
        $items = $inventoryModel->findAll();
    } catch (\Throwable $e) {
        $items = [];
    }
}

// compute alert count robustly
$alertCount = 0;
foreach ($items as $it) {
    // normalize status
    $statusRaw = '';
    if (! empty($it['status'])) {
        $statusRaw = $it['status'];
    } elseif (! empty($it['stock_status'])) {
        $statusRaw = $it['stock_status'];
    }
    $status = strtolower((string) $statusRaw);

    if ($status !== '' && (strpos($status, 'low') !== false || strpos($status, 'out') !== false)) {
        $alertCount++;
        continue;
    }

    // fallback: numeric checks (quantity vs min/reorder level)
    $qty = isset($it['quantity']) ? (int) $it['quantity'] : null;
    if ($qty !== null) {
        $minLevel = null;
        if (isset($it['min_level'])) {
            $minLevel = (int) $it['min_level'];
        } elseif (isset($it['reorder_level'])) {
            $minLevel = (int) $it['reorder_level'];
        }

        if ($minLevel !== null) {
            if ($qty <= $minLevel) {
                $alertCount++;
                continue;
            }
        } else {
            if ($qty <= 0) {
                $alertCount++;
                continue;
            }
        }
    }
}
?>
                <div class="col-md-4">
                    <a class="stat-link" href="<?= site_url('dashboard/manager/stockmovement') ?>">
                        <div class="stat-card" role="button" aria-label="View pending approvals">
                            <h6>Pending Approvals</h6>
                            <h3 id="pendingApprovals">5</h3>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a class="stat-link" href="<?= site_url('inventory') ?>?filter=alert">
                        <div class="stat-card" role="button" aria-label="View alert stocks">
                            <h6>Alert Stocks</h6>
                            <h3 id="alertStocks"><?= intval($alertCount) ?></h3>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Real-time Stock Alerts Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">🚨 Stock Alerts</h5>
                                <button id="refreshAlerts" class="btn btn-sm btn-outline-primary">Refresh</button>
                            </div>
                            <div id="stockAlertsList">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <span class="ms-2">Loading stock alerts...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warehouse Status Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">📊 Warehouse Usage Analytics</h5>
                                <small class="text-muted">Last updated: <span id="lastUpdated">-</span></small>
                            </div>
                            <div id="warehouseAnalytics">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <span class="ms-2">Loading analytics...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/site.js') ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadStockAlerts();
            loadWarehouseAnalytics();
            
            // Set up refresh button
            document.getElementById('refreshAlerts').addEventListener('click', loadStockAlerts);
            
            // Auto-refresh every 2 minutes
            setInterval(function() {
                loadStockAlerts();
                loadWarehouseAnalytics();
            }, 120000);
        });

        async function loadStockAlerts() {
            try {
                const response = await fetch('<?= site_url('api/inventory/low-stock') ?>');
                if (response.ok) {
                    const alerts = await response.json();
                    displayStockAlerts(alerts);
                    
                    // Update alert count in stat card
                    document.getElementById('alertStocks').textContent = alerts.length;
                } else {
                    document.getElementById('stockAlertsList').innerHTML = 
                        '<div class="alert alert-warning">Unable to load stock alerts</div>';
                }
            } catch (error) {
                console.error('Error loading stock alerts:', error);
                document.getElementById('stockAlertsList').innerHTML = 
                    '<div class="alert alert-danger">Error loading stock alerts</div>';
            }
        }

        function displayStockAlerts(alerts) {
            const container = document.getElementById('stockAlertsList');
            
            if (alerts.length === 0) {
                container.innerHTML = '<div class="alert alert-success">✅ All items are adequately stocked!</div>';
                return;
            }

            const alertsHtml = alerts.slice(0, 10).map(item => {
                const statusClass = item.quantity <= 0 ? 'danger' : 'warning';
                const statusText = item.quantity <= 0 ? 'OUT OF STOCK' : 'LOW STOCK';
                
                return `
                    <div class="alert alert-${statusClass} d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>${item.name}</strong> (${item.sku || 'No SKU'})
                            <br><small>Location: ${item.location || 'Not specified'} | Current: ${item.quantity} units</small>
                        </div>
                        <div>
                            <span class="badge bg-${statusClass}">${statusText}</span>
                        </div>
                    </div>
                `;
            }).join('');

            if (alerts.length > 10) {
                container.innerHTML = alertsHtml + 
                    `<div class="text-center mt-2">
                        <small class="text-muted">... and ${alerts.length - 10} more items</small>
                        <br><a href="<?= site_url('inventory') ?>?filter=low" class="btn btn-sm btn-outline-primary mt-2">View All Alerts</a>
                    </div>`;
            } else {
                container.innerHTML = alertsHtml;
            }
        }

        async function loadWarehouseAnalytics() {
            try {
                const response = await fetch('<?= site_url('api/warehouse/analytics') ?>');
                if (response.ok) {
                    const analytics = await response.json();
                    displayWarehouseAnalytics(analytics);
                    
                    // Update last updated time
                    document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString();
                } else {
                    document.getElementById('warehouseAnalytics').innerHTML = 
                        '<div class="alert alert-warning">Unable to load warehouse analytics</div>';
                }
            } catch (error) {
                console.error('Error loading warehouse analytics:', error);
                document.getElementById('warehouseAnalytics').innerHTML = 
                    '<div class="alert alert-danger">Error loading warehouse analytics</div>';
            }
        }

        function displayWarehouseAnalytics(analytics) {
            const container = document.getElementById('warehouseAnalytics');
            
            if (!analytics || analytics.length === 0) {
                container.innerHTML = '<div class="alert alert-info">No warehouse data available</div>';
                return;
            }

            // Filter out warehouses with 0 items and 0 usage
            const filteredAnalytics = analytics.filter(warehouse => 
                warehouse.total_items > 0 || warehouse.total_quantity > 0
            );

            if (filteredAnalytics.length === 0) {
                container.innerHTML = '<div class="alert alert-info">No warehouse data available</div>';
                return;
            }

            // Custom warehouse names for display
            const warehouseNames = ['Warehouse A', 'Warehouse B'];

            const analyticsHtml = filteredAnalytics.map((warehouse, index) => {
                const totalCapacity = 10000; // This should come from warehouse configuration
                const usagePercent = Math.min((warehouse.total_quantity / totalCapacity) * 100, 100);
                const progressClass = usagePercent > 90 ? 'danger' : usagePercent > 70 ? 'warning' : 'success';
                
                // Use custom warehouse name if available, otherwise use the original name
                const displayName = warehouseNames[index] || warehouse.warehouse_name || 'Unknown Warehouse';
                
                return `
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">${displayName}</h6>
                                <div class="mb-2">
                                    <small class="text-muted">Usage: ${usagePercent.toFixed(1)}%</small>
                                    <div class="progress mt-1" style="height: 8px;">
                                        <div class="progress-bar bg-${progressClass}" 
                                             style="width: ${usagePercent}%"></div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <small>Items: <strong>${warehouse.total_items || 0}</strong></small>
                                    </div>
                                    <div>
                                        <small>Stock: <strong>${warehouse.total_quantity || 0}</strong></small>
                                    </div>
                                </div>
                                ${warehouse.low_stock_count > 0 ? 
                                    `<div class="mt-2">
                                        <span class="badge bg-warning">${warehouse.low_stock_count} Low Stock</span>
                                    </div>` : ''
                                }
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = `<div class="row">${analyticsHtml}</div>`;
        }
    </script>
</body>
</html>
