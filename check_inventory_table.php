<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=warehouse_group4', 'root', '');
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables) . "\n";
    
    // Check inventory table structure
    if (in_array('inventory', $tables)) {
        echo "\nInventory table columns:\n";
        $columns = $pdo->query('DESCRIBE inventory')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo "  - {$col['Field']} ({$col['Type']})\n";
        }
    } else {
        echo "\nNo 'inventory' table found\n";
        
        // Look for similar tables
        $inventoryTables = array_filter($tables, function($table) {
            return strpos(strtolower($table), 'item') !== false || 
                   strpos(strtolower($table), 'inventory') !== false ||
                   strpos(strtolower($table), 'product') !== false;
        });
        
        if (!empty($inventoryTables)) {
            echo "Found related tables: " . implode(', ', $inventoryTables) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>