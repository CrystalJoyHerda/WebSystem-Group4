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

            <!-- Warehouse Cards Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">📊 Warehouse Overview</h5>
                                <div>
                                    <button class="btn btn-primary btn-sm me-2" onclick="loadWarehouseData()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                    <a href="<?= site_url('warehouses/seed-test-data') ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-database"></i> Generate Test Data
                                    </a>
                                </div>
                            </div>
                            <div id="warehouseCards">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <span class="ms-2">Loading warehouse data...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Warehouse Table -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Warehouse Management</h5>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Total Items</th>
                                    <th>Total Stock</th>
                                    <th>Low Stock Items</th>
                                    <th>Usage %</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="warehouseTableBody">
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <div class="spinner-border spinner-border-sm" role="status"></div>
                                        <span class="ms-2">Loading warehouse data...</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Custom warehouse names
        const warehouseNames = ['Warehouse A', 'Warehouse B'];

        // Load warehouse data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadWarehouseData();
            
            // Auto-refresh every 2 minutes
            setInterval(loadWarehouseData, 120000);
        });

        async function loadWarehouseData() {
            try {
                const response = await fetch('<?= site_url('api/warehouse/analytics') ?>');
                if (response.ok) {
                    const warehouses = await response.json();
                    
                    // Filter out warehouses with 0 items and 0 usage
                    const filteredWarehouses = warehouses.filter(warehouse => 
                        warehouse.total_items > 0 || warehouse.total_quantity > 0
                    );
                    
                    displayWarehouseCards(filteredWarehouses);
                    displayWarehouseTable(filteredWarehouses);
                } else {
                    showError();
                }
            } catch (error) {
                console.error('Error loading warehouse data:', error);
                showError();
            }
        }

        function displayWarehouseCards(warehouses) {
            const container = document.getElementById('warehouseCards');
            
            if (!warehouses || warehouses.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info text-center">
                        <i class="fas fa-warehouse fa-3x mb-3"></i>
                        <p class="mb-0">No warehouse data available</p>
                        <small>Click "Generate Test Data" to create sample warehouses</small>
                    </div>`;
                return;
            }

            const cardsHtml = warehouses.map((warehouse, index) => {
                const totalCapacity = 10000; // This should come from warehouse configuration
                const usagePercent = Math.min((warehouse.total_quantity / totalCapacity) * 100, 100);
                const progressClass = usagePercent > 90 ? 'danger' : usagePercent > 70 ? 'warning' : 'success';
                
                // Use custom warehouse name if available
                const displayName = warehouseNames[index] || warehouse.warehouse_name || 'Unknown Warehouse';
                
                return `
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="fas fa-warehouse text-primary me-2"></i>${displayName}
                                </h6>
                                <div class="mb-3">
                                    <small class="text-muted">Usage: ${usagePercent.toFixed(1)}%</small>
                                    <div class="progress mt-1" style="height: 10px;">
                                        <div class="progress-bar bg-${progressClass}" 
                                             style="width: ${usagePercent}%"></div>
                                    </div>
                                </div>
                                <div class="row text-center mb-2">
                                    <div class="col-6">
                                        <div class="p-2 bg-light rounded">
                                            <small class="text-muted d-block">Items</small>
                                            <strong class="fs-5">${warehouse.total_items || 0}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 bg-light rounded">
                                            <small class="text-muted d-block">Stock</small>
                                            <strong class="fs-5">${warehouse.total_quantity || 0}</strong>
                                        </div>
                                    </div>
                                </div>
                                ${warehouse.low_stock_count > 0 ? 
                                    `<div class="mt-2">
                                        <span class="badge bg-warning">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            ${warehouse.low_stock_count} Low Stock
                                        </span>
                                    </div>` : 
                                    `<div class="mt-2">
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            All Stocked
                                        </span>
                                    </div>`
                                }
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-primary w-100" onclick="viewInventory(${warehouse.id})">
                                        <i class="fas fa-boxes me-1"></i> View Inventory
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = `<div class="row">${cardsHtml}</div>`;
        }

        function displayWarehouseTable(warehouses) {
            const tbody = document.getElementById('warehouseTableBody');
            
            if (!warehouses || warehouses.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-warehouse fa-3x mb-3 text-muted"></i>
                            <p class="mb-0">No warehouses found</p>
                            <small>Click "Generate Test Data" to create sample warehouses</small>
                        </td>
                    </tr>`;
                return;
            }

            const rowsHtml = warehouses.map((warehouse, index) => {
                const totalCapacity = 10000;
                const usagePercent = Math.min((warehouse.total_quantity / totalCapacity) * 100, 100).toFixed(1);
                const progressClass = usagePercent > 80 ? 'bg-danger' : (usagePercent > 60 ? 'bg-warning' : 'bg-success');
                
                // Use custom warehouse name if available
                const displayName = warehouseNames[index] || warehouse.warehouse_name || 'Unknown Warehouse';
                
                return `
                    <tr>
                        <td>
                            <span class="badge bg-primary">${warehouse.id}</span>
                        </td>
                        <td>
                            <strong><i class="fas fa-warehouse me-2 text-primary"></i>${displayName}</strong>
                        </td>
                        <td>
                            <i class="fas fa-map-marker-alt text-muted me-1"></i>
                            ${warehouse.location || 'N/A'}
                        </td>
                        <td>
                            <span class="badge bg-info">${warehouse.total_items || 0}</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">${warehouse.total_quantity || 0}</span>
                        </td>
                        <td>
                            ${warehouse.low_stock_count > 0 ? 
                                `<span class="badge bg-warning">${warehouse.low_stock_count}</span>` :
                                `<span class="badge bg-success">0</span>`
                            }
                        </td>
                        <td>
                            <div class="progress" style="height: 20px; width: 100px;">
                                <div class="progress-bar ${progressClass}" 
                                     style="width: ${usagePercent}%">
                                    ${usagePercent}%
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-info" onclick="viewWarehouse(${warehouse.id})" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="viewInventory(${warehouse.id})" title="View Inventory">
                                    <i class="fas fa-boxes"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            tbody.innerHTML = rowsHtml;
        }

        function showError() {
            document.getElementById('warehouseCards').innerHTML = 
                '<div class="alert alert-danger">Error loading warehouse data. Please try again.</div>';
            document.getElementById('warehouseTableBody').innerHTML = 
                '<tr><td colspan="8" class="text-center"><div class="alert alert-danger">Error loading warehouse data</div></td></tr>';
        }

        function viewWarehouse(id) {
            // TODO: Implement warehouse view modal or redirect
            alert('View warehouse details for ID: ' + id);
        }

        function viewInventory(id) {
            // Redirect to inventory page filtered by warehouse
            window.location.href = '<?= site_url('dashboard/manager/inventory') ?>?warehouse_id=' + id;
        }
    </script>
</body>
</html>
