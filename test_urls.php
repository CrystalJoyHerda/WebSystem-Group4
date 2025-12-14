<?php
// Check site_url output
define('BASEPATH', __DIR__ . '/system/');
$_SERVER['REQUEST_URI'] = '/WebSystem-Group4/public/test';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';

require_once 'app/Config/App.php';
require_once 'system/bootstrap.php';

try {
    $config = new \Config\App();
    echo "Base URL from config: " . $config->baseURL . "\n";
    
    // Try to determine the correct URL structure
    $testUrls = [
        'http://localhost/WebSystem-Group4/public/api/inventory/stats',
        'http://localhost/WebSystem-Group4/api/inventory/stats',
        'http://localhost/api/inventory/stats'
    ];
    
    foreach ($testUrls as $url) {
        echo "\nTesting URL: {$url}\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "Status: {$httpCode}\n";
        if ($httpCode != 404) {
            echo "✅ This URL structure works!\n";
            break;
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>