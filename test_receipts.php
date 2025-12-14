<?php
// Test script to verify receipt system
try {
    $pdo = new PDO('mysql:host=localhost;dbname=warehouse_group4', 'root', '');
    
    echo "📋 Testing receipt system...\n\n";
    
    // Test 1: Check if tables exist and have data
    echo "1. Checking table structure:\n";
    $tables = ['inbound_receipts', 'inbound_receipt_items', 'outbound_receipts', 'outbound_receipt_items'];
    
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
        echo "   - {$table}: {$count} records\n";
    }
    
    // Test 2: Get pending inbound receipts
    echo "\n2. Pending inbound receipts:\n";
    $stmt = $pdo->query("
        SELECT id, reference_no, supplier_name, total_items, status, created_at 
        FROM inbound_receipts 
        WHERE status = 'Pending'
        ORDER BY created_at DESC
    ");
    $inbounds = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($inbounds as $receipt) {
        echo "   - {$receipt['reference_no']} | {$receipt['supplier_name']} | {$receipt['total_items']} items | {$receipt['status']}\n";
        
        // Get items for this receipt
        $itemStmt = $pdo->prepare("
            SELECT ri.quantity, i.name as item_name, i.sku as item_sku 
            FROM inbound_receipt_items ri
            LEFT JOIN inventory i ON ri.item_id = i.id
            WHERE ri.receipt_id = ?
        ");
        $itemStmt->execute([$receipt['id']]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($items as $item) {
            echo "     * {$item['item_name']} ({$item['item_sku']}) - qty: {$item['quantity']}\n";
        }
    }
    
    // Test 3: Get pending outbound receipts
    echo "\n3. Pending outbound receipts:\n";
    $stmt = $pdo->query("
        SELECT id, reference_no, customer_name, total_items, status, created_at 
        FROM outbound_receipts 
        WHERE status = 'Pending'
        ORDER BY created_at DESC
    ");
    $outbounds = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($outbounds as $receipt) {
        echo "   - {$receipt['reference_no']} | {$receipt['customer_name']} | {$receipt['total_items']} items | {$receipt['status']}\n";
        
        // Get items for this receipt
        $itemStmt = $pdo->prepare("
            SELECT ri.quantity, i.name as item_name, i.sku as item_sku 
            FROM outbound_receipt_items ri
            LEFT JOIN inventory i ON ri.item_id = i.id
            WHERE ri.receipt_id = ?
        ");
        $itemStmt->execute([$receipt['id']]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($items as $item) {
            echo "     * {$item['item_name']} ({$item['item_sku']}) - qty: {$item['quantity']}\n";
        }
    }
    
    // Test 4: Check stock_movements table
    echo "\n4. Recent stock movements:\n";
    $stmt = $pdo->query("
        SELECT movement_type, quantity, order_number, company_name, location
        FROM stock_movements 
        ORDER BY movement_id DESC 
        LIMIT 5
    ");
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($movements)) {
        echo "   - No stock movements found\n";
    } else {
        foreach ($movements as $movement) {
            echo "   - {$movement['movement_type']} | {$movement['company_name']} | Qty {$movement['quantity']} | Ref {$movement['order_number']} | Loc {$movement['location']}\n";
        }
    }
    
    echo "\n✅ Receipt system test completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>