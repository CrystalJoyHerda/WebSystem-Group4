<?php
// Test script to check pending receipts API
try {
    echo "🧪 Testing pending receipts API...\n\n";

    // Test inbound receipts
    echo "1. Testing inbound receipts endpoint:\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/WebSystem-Group4/public/api/receipts/inbound/pending');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Status: {$httpCode}\n";
    echo "Response: {$response}\n\n";

    // Test outbound receipts
    echo "2. Testing outbound receipts endpoint:\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/WebSystem-Group4/public/api/receipts/outbound/pending');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Status: {$httpCode}\n";
    echo "Response: {$response}\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>