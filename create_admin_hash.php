<?php
// Generate proper password hash for admin user
$password = 'password';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Password: {$password}\n";
echo "Hash: {$hash}\n";
