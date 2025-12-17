<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Warehouse Viewer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('css/site.css') ?>" rel="stylesheet">
    <link href="<?= base_url('public/assets/theme.css') ?>" rel="stylesheet">
    <script src="<?= base_url('public/assets/theme.js') ?>" defer></script>
    <style>
        body { font-family: 'Times New Roman', serif; background: #fff; }
        .app-shell { display: flex; min-height: 100vh; }
        /* content leaves room for the fixed sidebar (220px) */
        .main { flex: 1; padding: 24px 32px; margin-left: 220px; }
        .header { display:flex; align-items:center; justify-content:space-between; margin-bottom: 18px; }
        .brand { font-family: 'Georgia', serif; font-size: 28px; }
        .page-title { text-align: center; font-size: 40px; font-weight: 400; letter-spacing: 1px; margin: 8px 0 32px; }
        .card-warehouse { background: #f8f9fa; border: 1px solid #ddd; padding: 20px; margin: 10px; border-radius: 8px; }
        .progress { height: 10px; }
        .stats-card { background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-badge { background: #dc3545; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; }
        .low-stock-item { border-left: 4px solid #ffc107; padding: 8px; margin: 4px 0; background: #fff9c4; }
    </style>
</head>
<body>
    <div class="app-shell">
        <?= view('partials/sidebar') ?>

        <main class="main">
            <div class="header">
                <div class="brand">WeBuild</div>
            </div>

            <div class="page-title">Viewer DASHBOARD</div>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <h3 id="totalItems">-</h3>
                        <small>Total Items</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <h3 id="totalWarehouses">-</h3>
                        <small>Warehouses</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <h3 id="lowStockCount" class="text-warning">-</h3>
                        <small>Low Stock Alerts</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <h3 id="outOfStockCount" class="text-danger">-</h3>
                        <small>Out of Stock</small>
                    </div>
                </div>
            </div>

            <!-- Warehouse Overview -->
            <div class="row mb-4 warehouses" id="warehousesContainer">
                <!-- Warehouses will be loaded dynamically -->
            </div>

            <!-- Recent Activity and Alerts -->
            <div class="row">
                <div class="col-md-6">
                    <div class="stats-card">
                        <h5>Recent Activity <small class="text-muted">(View Only)</small></h5>
                        <div id="recentActivity">
                            <p class="text-muted">Loading recent activities...</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stats-card">
                        <h5>Low Stock Alerts <span id="lowStockBadge" class="alert-badge">0</span></h5>
                        <div id="lowStockItems">
                            <p class="text-muted">Loading low stock items...</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            loadWarehouses();
            loadLowStockItems();
            
            // Refresh data every 30 seconds
            setInterval(loadDashboardData, 30000);
        });

        async function loadDashboardData() {
            try {
                // Load inventory stats
                const response = await fetch('<?= site_url('api/inventory/stats') ?>');
                if (response.ok) {
                    const stats = await response.json();
                    document.getElementById('totalItems').textContent = stats.total || 0;
                    document.getElementById('lowStockCount').textContent = stats.low_stock || 0;
                    document.getElementById('outOfStockCount').textContent = stats.out_of_stock || 0;
                }

                // Load warehouse count
                const warehouseResponse = await fetch('<?= site_url('api/warehouse/list') ?>');
                if (warehouseResponse.ok) {
                    const warehouses = await warehouseResponse.json();
                    document.getElementById('totalWarehouses').textContent = warehouses.length || 0;
                }
            } catch (error) {
                console.warn('Failed to load dashboard stats:', error);
            }
        }

        async function loadWarehouses() {
            try {
                const response = await fetch('<?= site_url('api/warehouse/list') ?>');
                if (response.ok) {
                    const warehouses = await response.json();
                    const container = document.getElementById('warehousesContainer');
                    
                    if (warehouses.length === 0) {
                        container.innerHTML = '<div class="col-12"><p class="text-muted">No warehouses found.</p></div>';
                        return;
                    }

                    container.innerHTML = warehouses.map(warehouse => `
                        <div class="col-md-4">
                            <div class="card-warehouse">
                                <h5>${warehouse.name || 'Unnamed Warehouse'}</h5>
                                <div class="d-flex justify-content-between">
                                    <small>Location</small>
                                    <small>${warehouse.location || 'Not specified'}</small>
                                </div>
                                <div class="d-flex gap-3 mt-3">
                                    <div style="background:#f1f1f1;padding:10px;border-radius:8px;min-width:90px">
                                        Items<br><strong id="warehouse-${warehouse.id}-items">-</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');

                    // Load item counts for each warehouse
                    for (const warehouse of warehouses) {
                        loadWarehouseItemCount(warehouse.id);
                    }
                }
            } catch (error) {
                console.warn('Failed to load warehouses:', error);
            }
        }

        async function loadWarehouseItemCount(warehouseId) {
            try {
                const response = await fetch(`<?= site_url('api/inventory/by-warehouse/') ?>${warehouseId}`);
                if (response.ok) {
                    const items = await response.json();
                    const element = document.getElementById(`warehouse-${warehouseId}-items`);
                    if (element) {
                        element.textContent = items.length || 0;
                    }
                }
            } catch (error) {
                console.warn(`Failed to load items for warehouse ${warehouseId}:`, error);
            }
        }

        async function loadLowStockItems() {
            try {
                const response = await fetch('<?= site_url('api/inventory/low-stock') ?>');
                if (response.ok) {
                    const items = await response.json();
                    const container = document.getElementById('lowStockItems');
                    const badge = document.getElementById('lowStockBadge');
                    
                    badge.textContent = items.length;
                    
                    if (items.length === 0) {
                        container.innerHTML = '<p class="text-success">All items are well stocked!</p>';
                        return;
                    }

                    container.innerHTML = items.slice(0, 5).map(item => `
                        <div class="low-stock-item">
                            <strong>${item.name}</strong> (${item.sku})<br>
                            <small>Quantity: ${item.quantity} | Location: ${item.location || 'Not specified'}</small>
                        </div>
                    `).join('');

                    if (items.length > 5) {
                        container.innerHTML += `<p class="text-muted mt-2">... and ${items.length - 5} more items</p>`;
                    }
                }
            } catch (error) {
                console.warn('Failed to load low stock items:', error);
                document.getElementById('lowStockItems').innerHTML = '<p class="text-muted">Unable to load low stock items</p>';
            }
        }
    </script>
</body>
</html>