<?php
// Test script to simulate receipt approval
try {

    echo "🧪 Testing inbound receipt approval...\n\n";

    // Simulate approval API call
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/WebSystem-Group4/public/api/receipts/inbound/1/approve');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Cookie: ci_session=' . session_id()
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Status: {$httpCode}\n";
    echo "Response: {$response}\n\n";

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if ($result && isset($result['success']) && $result['success']) {
            echo "✅ Approval successful!\n";
            echo "Message: {$result['message']}\n";
            if (isset($result['tasks_count'])) {
                echo "Staff tasks created: {$result['tasks_count']}\n";
            }
        } else {
            echo "❌ Approval failed: " . ($result['error'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "❌ HTTP Error: {$httpCode}\n";
    }

    // Check if receipt was approved
    echo "\n📋 Checking receipt status after approval...\n";
    $pdo = new PDO('mysql:host=localhost;dbname=warehouse_group4', 'root', '');
    $stmt = $pdo->prepare("SELECT reference_no, status, approved_by, approved_at FROM inbound_receipts WHERE id = 1");
    $stmt->execute();
    $receipt = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($receipt) {
        echo "Receipt {$receipt['reference_no']}: Status = {$receipt['status']}\n";
        if ($receipt['approved_by']) {
            echo "Approved by user ID: {$receipt['approved_by']} at {$receipt['approved_at']}\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>