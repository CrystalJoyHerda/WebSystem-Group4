<?php
// ---------------- DATA ----------------
$purchaseOrders = [
    ["id" => "PO-001", "vendor" => "ABC Supplies", "amount" => 45000, "status" => "pending"],
    ["id" => "PO-002", "vendor" => "Tech Solutions", "amount" => 250000, "status" => "approved"],
    ["id" => "PO-003", "vendor" => "Furniture World", "amount" => 180000, "status" => "completed"]
];

$suppliers = [
    ["name" => "ABC Supplies", "category" => "Office Supplies"],
    ["name" => "Tech Solutions", "category" => "Technology"]
];

$inventory = [
    ["item" => "A4 Paper", "qty" => 250, "status" => "adequate"],
    ["item" => "Ballpen", "qty" => 45, "status" => "low"]
];

// ---------------- TAB ----------------
$tab = $_GET['tab'] ?? 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Procurement Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen">

<!-- HEADER -->
<header class="bg-blue-600 text-white p-4 shadow">
    <h1 class="text-2xl font-bold">Procurement Management System</h1>
    <p class="text-sm text-blue-100">Officer Dashboard</p>
</header>

<!-- NAV -->
<nav class="bg-white border-b flex">
    <a href="?tab=dashboard" class="p-4 <?= $tab=='dashboard'?'border-b-2 border-blue-600 text-blue-600':'' ?>">Dashboard</a>
    <a href="?tab=orders" class="p-4 <?= $tab=='orders'?'border-b-2 border-blue-600 text-blue-600':'' ?>">Purchase Orders</a>
    <a href="?tab=suppliers" class="p-4 <?= $tab=='suppliers'?'border-b-2 border-blue-600 text-blue-600':'' ?>">Suppliers</a>
    <a href="?tab=inventory" class="p-4 <?= $tab=='inventory'?'border-b-2 border-blue-600 text-blue-600':'' ?>">Inventory</a>
</nav>

<main class="p-6 max-w-7xl mx-auto">

<?php if ($tab == 'dashboard'): ?>
<!-- DASHBOARD -->
<h2 class="text-xl font-bold mb-4">Dashboard Overview</h2>
<div class="grid md:grid-cols-4 gap-4">
    <div class="bg-white p-4 rounded shadow">
        <p class="text-sm text-gray-500">Total POs</p>
        <p class="text-2xl font-bold"><?= count($purchaseOrders) ?></p>
    </div>
    <div class="bg-white p-4 rounded shadow">
        <p class="text-sm text-gray-500">Pending</p>
        <p class="text-2xl font-bold">
            <?= count(array_filter($purchaseOrders, fn($p) => $p['status']=='pending')) ?>
        </p>
    </div>
    <div class="bg-white p-4 rounded shadow">
        <p class="text-sm text-gray-500">Suppliers</p>
        <p class="text-2xl font-bold"><?= count($suppliers) ?></p>
    </div>
    <div class="bg-white p-4 rounded shadow">
        <p class="text-sm text-gray-500">Low Stock</p>
        <p class="text-2xl font-bold">
            <?= count(array_filter($inventory, fn($i) => $i['status']=='low')) ?>
        </p>
    </div>
</div>

<?php elseif ($tab == 'orders'): ?>
<!-- PURCHASE ORDERS -->
<h2 class="text-xl font-bold mb-4">Purchase Orders</h2>

<table class="w-full bg-white rounded shadow">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-3 text-left">PO #</th>
            <th class="p-3">Vendor</th>
            <th class="p-3">Amount</th>
            <th class="p-3">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($purchaseOrders as $po): ?>
        <tr class="border-t">
            <td class="p-3"><?= $po['id'] ?></td>
            <td class="p-3"><?= $po['vendor'] ?></td>
            <td class="p-3">₱<?= number_format($po['amount']) ?></td>
            <td class="p-3"><?= ucfirst($po['status']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php elseif ($tab == 'suppliers'): ?>
<!-- SUPPLIERS -->
<h2 class="text-xl font-bold mb-4">Suppliers</h2>
<div class="grid md:grid-cols-2 gap-4">
    <?php foreach ($suppliers as $s): ?>
    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-bold"><?= $s['name'] ?></h3>
        <p class="text-sm text-gray-600"><?= $s['category'] ?></p>
    </div>
    <?php endforeach; ?>
</div>

<?php elseif ($tab == 'inventory'): ?>
<!-- INVENTORY -->
<h2 class="text-xl font-bold mb-4">Inventory</h2>
<table class="w-full bg-white rounded shadow">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-3 text-left">Item</th>
            <th class="p-3">Qty</th>
            <th class="p-3">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($inventory as $i): ?>
        <tr class="border-t">
            <td class="p-3"><?= $i['item'] ?></td>
            <td class="p-3"><?= $i['qty'] ?></td>
            <td class="p-3"><?= ucfirst($i['status']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

</main>

</body>
</html>
