<?php
// Script to create receipt tables
$config = [
    'hostname' => 'localhost',
    'username' => 'root', 
    'password' => '',
    'database' => 'warehouse_group4',
];

try {
    $pdo = new PDO(
        "mysql:host={$config['hostname']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Create inbound_receipts table
    $sql = "
    CREATE TABLE IF NOT EXISTS inbound_receipts (
        id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        reference_no VARCHAR(100) NOT NULL UNIQUE,
        supplier_name VARCHAR(255) NOT NULL,
        warehouse_id INT(11) UNSIGNED NULL,
        status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
        total_items INT(11) DEFAULT 0,
        approved_by INT(11) UNSIGNED NULL,
        approved_at DATETIME NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL,
        PRIMARY KEY (id),
        KEY status_idx (status)
    ) DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;
    ";
    $pdo->exec($sql);
    echo "✅ Created inbound_receipts table\n";

    // Create inbound_receipt_items table
    $sql = "
    CREATE TABLE IF NOT EXISTS inbound_receipt_items (
        id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        receipt_id INT(11) UNSIGNED NOT NULL,
        item_id INT(11) UNSIGNED NOT NULL,
        warehouse_id INT(11) UNSIGNED NOT NULL,
        quantity INT(11) NOT NULL,
        unit_cost DECIMAL(10,2) DEFAULT 0.00,
        PRIMARY KEY (id),
        KEY receipt_id (receipt_id)
    ) DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;
    ";
    $pdo->exec($sql);
    echo "✅ Created inbound_receipt_items table\n";

    // Create outbound_receipts table
    $sql = "
    CREATE TABLE IF NOT EXISTS outbound_receipts (
        id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        reference_no VARCHAR(100) NOT NULL UNIQUE,
        customer_name VARCHAR(255) NOT NULL,
        warehouse_id INT(11) UNSIGNED NULL,
        status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
        total_items INT(11) DEFAULT 0,
        approved_by INT(11) UNSIGNED NULL,
        approved_at DATETIME NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL,
        PRIMARY KEY (id),
        KEY status_idx2 (status)
    ) DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;
    ";
    $pdo->exec($sql);
    echo "✅ Created outbound_receipts table\n";

    // Create outbound_receipt_items table
    $sql = "
    CREATE TABLE IF NOT EXISTS outbound_receipt_items (
        id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        receipt_id INT(11) UNSIGNED NOT NULL,
        item_id INT(11) UNSIGNED NOT NULL,
        warehouse_id INT(11) UNSIGNED NOT NULL,
        quantity INT(11) NOT NULL,
        PRIMARY KEY (id),
        KEY receipt_id (receipt_id)
    ) DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;
    ";
    $pdo->exec($sql);
    echo "✅ Created outbound_receipt_items table\n";

    // Insert sample data for testing
    echo "\n📋 Inserting sample data...\n";
    
    // Sample inbound receipts
    $pdo->exec("
        INSERT IGNORE INTO inbound_receipts (reference_no, supplier_name, warehouse_id, total_items, created_at, updated_at) VALUES
        ('PO-1234', 'Construction Materials Ltd', 1, 1, NOW(), NOW()),
        ('PO-1210', 'Paint Solutions Inc', 1, 1, NOW(), NOW()),
        ('PO-1205', 'Pipe & Plumbing Co', 1, 1, NOW(), NOW())
    ");
    
    // Sample inbound receipt items
    $pdo->exec("
        INSERT IGNORE INTO inbound_receipt_items (receipt_id, item_id, warehouse_id, quantity, unit_cost) VALUES
        (1, 3, 1, 50, 25.00),
        (2, 8, 1, 200, 15.50),
        (3, 7, 1, 200, 8.75)
    ");

    // Sample outbound receipts
    $pdo->exec("
        INSERT IGNORE INTO outbound_receipts (reference_no, customer_name, warehouse_id, total_items, created_at, updated_at) VALUES
        ('SO-5678', 'ABC Construction Corp', 1, 1, NOW(), NOW()),
        ('SO-5599', 'Electrical Works Ltd', 1, 1, NOW(), NOW()),
        ('SO-5588', 'Woodwork Construction Ltd', 1, 1, NOW(), NOW())
    ");
    
    // Sample outbound receipt items
    $pdo->exec("
        INSERT IGNORE INTO outbound_receipt_items (receipt_id, item_id, warehouse_id, quantity) VALUES
        (1, 11, 1, 100),
        (2, 6, 1, 40),
        (3, 4, 1, 60)
    ");

    echo "✅ Sample data inserted successfully\n";
    echo "\n🎉 All receipt tables created and populated!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>