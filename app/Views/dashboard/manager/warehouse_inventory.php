<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($warehouse['name']) ?> - Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('css/site.css') ?>" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', serif; background: #fff; }
        .app-shell { display: flex; min-height: 100vh; }
        .main { flex: 1; padding: 24px 32px; margin-left: 220px; }
        .header { display:flex; align-items:center; justify-content:space-between; margin-bottom: 18px; }
        .brand { font-family: 'Georgia', serif; font-size: 28px; }
        .page-title { font-size: 32px; margin-bottom: 8px; }
        .breadcrumb { background: transparent; padding: 0; margin-bottom: 20px; }
        .summary-card { border-radius: 8px; border:1px solid #dcdcdc; padding: 20px; text-align: center; }
        .summary-card h3 { font-size: 36px; margin: 0; color: #333; }
        .summary-card p { margin: 0; color: #666; font-size: 14px; }
        .inventory-table { background: white; border-radius: 8px; border: 1px solid #dcdcdc; }
        .table th { background: #f8f9fa; font-weight: 600; }
        .badge-low-stock { background: #dc3545; color: white; }
        .badge-in-stock { background: #28a745; color: white; }
        @media (max-width: 900px) { .sidebar{display:none;} .main{margin-left:0;padding:16px;} }
    </style>
</head>
<body>
    <div class="app-shell">
        <?= view('partials/sidebar') ?>

        <main class="main">
            <div class="header">
                <div class="brand">WeBuild</div>
            </div>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard/manager') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= esc($warehouse['name']) ?> Inventory</li>
                </ol>
            </nav>

            <h1 class="page-title"><?= esc($warehouse['name']) ?> - Inventory</h1>
            <p class="text-muted mb-4"><?= esc($warehouse['location'] ?? 'No location specified') ?></p>

            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="summary-card">
                        <h3><?= number_format($totalItems) ?></h3>
                        <p>Total Items</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card">
                        <h3><?= number_format($totalQuantity) ?></h3>
                        <p>Total Quantity</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card">
                        <h3 class="<?= $lowStockCount > 0 ? 'text-danger' : 'text-success' ?>"><?= $lowStockCount ?></h3>
                        <p>Low Stock Items</p>
                    </div>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="inventory-table">
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Inventory Items</h5>
                        <input type="text" id="searchInput" class="form-control" style="max-width: 300px;" placeholder="Search items...">
                    </div>

                    <?php if (empty($items)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No items found in this warehouse.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="inventoryTable">
                                <thead>
                                    <tr>
                                        <th>SKU</th>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th class="text-end">Quantity</th>
                                        <th>Unit</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td><code><?= esc($item['sku']) ?></code></td>
                                            <td><strong><?= esc($item['name']) ?></strong></td>
                                            <td><?= esc($item['category'] ?? 'N/A') ?></td>
                                            <td class="text-end"><?= number_format($item['quantity']) ?></td>
                                            <td><?= esc($item['unit'] ?? 'pcs') ?></td>
                                            <td>
                                                <?php if ($item['is_low_stock']): ?>
                                                    <span class="badge badge-low-stock">Low Stock</span>
                                                <?php else: ?>
                                                    <span class="badge badge-in-stock">In Stock</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-3">
                <a href="<?= site_url('dashboard/manager') ?>" class="btn btn-secondary">← Back to Dashboard</a>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = document.getElementById('inventoryTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const text = row.textContent.toLowerCase();
                
                if (text.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>
