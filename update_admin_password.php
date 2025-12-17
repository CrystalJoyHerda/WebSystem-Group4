<?php
// Update admin user password
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Config/Paths.php';
$paths = new Config\Paths();
require __DIR__ . '/system/bootstrap.php';

$db = \Config\Database::connect();
$hash = password_hash('password', PASSWORD_DEFAULT);

$result = $db->table('users')
    ->where('email', 'admin@warehouse.com')
    ->update(['password' => $hash]);

if ($result) {
    echo "Admin password updated successfully!\n";
    echo "Email: admin@warehouse.com\n";
    echo "Password: password\n";
    
    // Verify
    $user = $db->table('users')
        ->where('email', 'admin@warehouse.com')
        ->get()
        ->getRowArray();
    
    if ($user && password_verify('password', $user['password'])) {
        echo "Password verification: SUCCESS\n";
    } else {
        echo "Password verification: FAILED\n";
    }
} else {
    echo "Failed to update password\n";
}
