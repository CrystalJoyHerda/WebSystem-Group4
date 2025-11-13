<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=warehouse_group4', 'root', '');
    $tables = $pdo->query('SHOW TABLES LIKE "%receipt%"')->fetchAll(PDO::FETCH_COLUMN);
    echo "Receipt tables: " . implode(', ', $tables) . "\n";
    
    if (in_array('inbound_receipts', $tables)) {
        echo "inbound_receipts structure:\n";
        $columns = $pdo->query('DESCRIBE inbound_receipts')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo "  {$col['Field']} - {$col['Type']}\n";
        }
    }
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>