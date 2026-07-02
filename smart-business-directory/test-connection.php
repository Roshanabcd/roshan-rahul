<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Testing MySQL connection...\n";

// Test 1: Direct TCP connection
echo "\n=== Test 1: Direct TCP connection ===\n";
$test1 = @mysqli_connect('127.0.0.1', 'businessdir', 'BusinessDir@2026!', 'business_directory', 3306);
if ($test1) {
    echo "✓ TCP Connection successful!\n";
    mysqli_close($test1);
} else {
    echo "✗ TCP Connection failed: " . mysqli_connect_error() . "\n";
}

// Test 2: Using localhost
echo "\n=== Test 2: Localhost connection ===\n";
ini_set('mysqli.default_socket', '');
$test2 = @mysqli_connect('localhost', 'businessdir', 'BusinessDir@2026!', 'business_directory', 3306);
if ($test2) {
    echo "✓ Localhost connection successful!\n";
    mysqli_close($test2);
} else {
    echo "✗ Localhost connection failed: " . mysqli_connect_error() . "\n";
}

// Test 3: Check PHP version and mysqli
echo "\n=== Test 3: System Info ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "MySQLi Version: " . phpversion('mysqli') . "\n";
echo "Default MySQL Port: " . ini_get('mysqli.default_port') . "\n";
echo "Default MySQL Socket: " . ini_get('mysqli.default_socket') . "\n";

echo "\n</pre>";
?>
