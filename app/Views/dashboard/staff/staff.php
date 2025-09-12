
<!DOCTYPE html>
<html>
<head>
    <title>Warehouse Staff Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('css/site.css') ?>" rel="stylesheet">
</head>
<body>
    <div class="container dashboard-container bg-white p-4 rounded shadow">
        <div style="float:left;margin-right:20px">
            <nav>
                <a class="nav-link" href="<?= site_url('dashboard/staff') ?>">Dashboard</a>
                <a class="nav-link" href="<?= site_url('inventory') ?>">Inventory</a>
                <a class="nav-link" href="#">Stock Movements</a>
            </nav>
        </div>
        <div style="position:fixed;left:18px;bottom:18px">
            <a href="<?= site_url('logout') ?>" class="btn btn-sm btn-outline-dark">Logout</a>
        </div>
        <h2 class="text-center mb-4">Warehouse Staff Dashboard</h2>
        <div class="alert alert-warning text-center mb-3">Role: Staff</div>
        <div class="mb-3 text-center">
            <span class="fw-bold">Welcome,</span> <?= esc(session('name')) ?>!
        </div>
        <p class="mb-4 text-center">Here you can update inventory, process orders, and view assigned tasks.</p>
        <div class="text-center">
            <a href="<?= site_url('logout') ?>" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/site.js') ?>"></script>
</body>
</html>
