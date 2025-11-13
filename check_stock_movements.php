<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=warehouse_group4', 'root', '');
    echo "Stock_movements table structure:\n";
    $columns = $pdo->query('DESCRIBE stock_movements')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>