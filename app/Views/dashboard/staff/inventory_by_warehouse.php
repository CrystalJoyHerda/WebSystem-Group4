<?php $session = session(); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventory by Warehouse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('css/site.css') ?>" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', serif; background: #fff; }
        .main { margin-left: 220px; padding: 28px; }
        .inventory-item { border: 1px solid #ddd; padding: 15px; margin: 8px 0; border-radius: 8px; background: #fff; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; color: white; font-weight: bold; }
        .status-in { background: #28a745; }
        .status-low { background: #ffc107; color: #000; }
        .status-out { background: #dc3545; }
        .search-box { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .loading { text-align: center; padding: 40px; color: #6c757d; }
    </style>
</head>
<body>
    <?= view('partials/sidebar') ?>
    <div class="main">
        <h2>Inventory by Warehouse</h2>
        
        <div class="search-box">
            <div class="row">
                <div class="col-md-6">
                    <label for="warehouseSelect" class="form-label">Select Warehouse</label>
                    <select id="warehouseSelect" class="form-select">
                        <option value="">All Warehouses</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="searchInput" class="form-label">Search Items</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by name or SKU...">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-4">
                    <label for="statusFilter" class="form-label">Filter by Status</label>
                    <select id="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="in">In Stock</option>
                        <option value="low">Low Stock</option>
                        <option value="out">Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="categoryFilter" class="form-label">Filter by Category</label>
                    <select id="categoryFilter" class="form-select">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button id="refreshBtn" class="btn btn-primary me-2">Refresh</button>
                    <button id="exportBtn" class="btn btn-outline-secondary">Export</button>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 id="totalItems">-</h4>
                        <small>Total Items</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 id="inStockItems" class="text-success">-</h4>
                        <small>In Stock</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 id="lowStockItems" class="text-warning">-</h4>
                        <small>Low Stock</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 id="outStockItems" class="text-danger">-</h4>
                        <small>Out of Stock</small>
                    </div>
                </div>
            </div>
        </div>

        <div id="itemsList">
            <div class="loading">Loading inventory items...</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let allItems = [];
        let allWarehouses = [];
        let allCategories = new Set();

        document.addEventListener('DOMContentLoaded', async () => {
            await loadWarehouses();
            await loadAllItems();
            
            // Set up event listeners
            document.getElementById('warehouseSelect').addEventListener('change', filterItems);
            document.getElementById('searchInput').addEventListener('input', filterItems);
            document.getElementById('statusFilter').addEventListener('change', filterItems);
            document.getElementById('categoryFilter').addEventListener('change', filterItems);
            document.getElementById('refreshBtn').addEventListener('click', refreshData);
            document.getElementById('exportBtn').addEventListener('click', exportData);
        });

        async function loadWarehouses() {
            try {
                const response = await fetch('<?= site_url('api/warehouse/list') ?>');
                if (response.ok) {
                    allWarehouses = await response.json();
                    const select = document.getElementById('warehouseSelect');
                    
                    allWarehouses.forEach(warehouse => {
                        const option = document.createElement('option');
                        option.value = warehouse.id;
                        option.textContent = warehouse.name;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.warn('Failed to load warehouses:', error);
            }
        }

        async function loadAllItems() {
            try {
                // Load all inventory items with warehouse information
                const response = await fetch('<?= site_url('api/inventory/all-with-warehouse') ?>');
                if (response.ok) {
                    allItems = await response.json();
                    
                    // Extract unique categories
                    allCategories.clear();
                    allItems.forEach(item => {
                        if (item.category) {
                            allCategories.add(item.category);
                        }
                    });
                    
                    // Populate category filter
                    const categorySelect = document.getElementById('categoryFilter');
                    categorySelect.innerHTML = '<option value="">All Categories</option>';
                    Array.from(allCategories).sort().forEach(category => {
                        const option = document.createElement('option');
                        option.value = category;
                        option.textContent = category;
                        categorySelect.appendChild(option);
                    });
                    
                    filterItems(); // Initial display
                } else {
                    document.getElementById('itemsList').innerHTML = '<div class="alert alert-warning">Failed to load inventory items.</div>';
                }
            } catch (error) {
                console.warn('Failed to load items:', error);
                document.getElementById('itemsList').innerHTML = '<div class="alert alert-danger">Error loading inventory items.</div>';
            }
        }

        function filterItems() {
            const warehouseId = document.getElementById('warehouseSelect').value;
            const searchText = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const categoryFilter = document.getElementById('categoryFilter').value;

            let filteredItems = allItems.filter(item => {
                // Warehouse filter
                if (warehouseId && item.warehouse_id != warehouseId) return false;
                
                // Search filter
                if (searchText && !item.name.toLowerCase().includes(searchText) && 
                    !item.sku?.toLowerCase().includes(searchText)) return false;
                
                // Status filter
                if (statusFilter && item.status !== statusFilter) return false;
                
                // Category filter
                if (categoryFilter && item.category !== categoryFilter) return false;
                
                return true;
            });

            displayItems(filteredItems);
            updateStats(filteredItems);
        }

        function displayItems(items) {
            const container = document.getElementById('itemsList');
            
            if (items.length === 0) {
                container.innerHTML = '<div class="alert alert-info">No items found matching your filters.</div>';
                return;
            }

            container.innerHTML = items.map(item => `
                <div class="inventory-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5>${item.name}</h5>
                            <p class="mb-1"><strong>SKU:</strong> ${item.sku || 'N/A'}</p>
                            <p class="mb-1"><strong>Category:</strong> ${item.category || 'Uncategorized'}</p>
                            <p class="mb-1"><strong>Location:</strong> ${item.location || 'Not specified'}</p>
                            <p class="mb-1"><strong>Warehouse:</strong> ${item.warehouse_name || 'Not assigned'}</p>
                            ${item.expiry ? `<p class="mb-1"><strong>Expiry:</strong> ${item.expiry}</p>` : ''}
                        </div>
                        <div class="text-end">
                            <div class="status-badge status-${item.status}">${item.status?.toUpperCase() || 'UNKNOWN'}</div>
                            <h4 class="mt-2">${item.quantity}</h4>
                            <small>units</small>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function updateStats(items) {
            const total = items.length;
            const inStock = items.filter(item => item.status === 'in').length;
            const lowStock = items.filter(item => item.status === 'low').length;
            const outStock = items.filter(item => item.status === 'out').length;

            document.getElementById('totalItems').textContent = total;
            document.getElementById('inStockItems').textContent = inStock;
            document.getElementById('lowStockItems').textContent = lowStock;
            document.getElementById('outStockItems').textContent = outStock;
        }

        async function refreshData() {
            document.getElementById('itemsList').innerHTML = '<div class="loading">Refreshing data...</div>';
            await loadAllItems();
        }

        function exportData() {
            const warehouseId = document.getElementById('warehouseSelect').value;
            const warehouseName = warehouseId ? 
                allWarehouses.find(w => w.id == warehouseId)?.name || 'Unknown' : 
                'All Warehouses';
            
            let filteredItems = allItems.filter(item => {
                return !warehouseId || item.warehouse_id == warehouseId;
            });

            const csvContent = [
                ['Name', 'SKU', 'Category', 'Location', 'Warehouse', 'Quantity', 'Status', 'Expiry'],
                ...filteredItems.map(item => [
                    item.name,
                    item.sku || '',
                    item.category || '',
                    item.location || '',
                    item.warehouse_name || '',
                    item.quantity,
                    item.status || '',
                    item.expiry || ''
                ])
            ].map(row => row.map(field => `"${field}"`).join(',')).join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `inventory_${warehouseName.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>
</body>
</html>
