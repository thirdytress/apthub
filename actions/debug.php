<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

echo "=== REGISTRATION DEBUG ===\n\n";

echo "PHP Version: " . phpversion() . "\n";
echo "Current Directory: " . __DIR__ . "\n";
echo "Parent Directory: " . dirname(__DIR__) . "\n\n";

echo "=== FILE CHECKS ===\n";
$vendorPath = __DIR__ . '/../vendor/autoload.php';
echo "Vendor Path: $vendorPath\n";
echo "Vendor Exists: " . (file_exists($vendorPath) ? 'YES' : 'NO') . "\n";

$dbPath = __DIR__ . '/../classes/database.php';
echo "Database Path: $dbPath\n";
echo "Database Exists: " . (file_exists($dbPath) ? 'YES' : 'NO') . "\n\n";

echo "=== TRYING TO LOAD VENDOR ===\n";
if (file_exists($vendorPath)) {
    try {
        require $vendorPath;
        echo "Autoload loaded successfully!\n";
        
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            echo "PHPMailer class found!\n";
        } else {
            echo "PHPMailer class NOT found!\n";
        }
    } catch (Exception $e) {
        echo "Error loading autoload: " . $e->getMessage() . "\n";
    }
} else {
    echo "VENDOR FOLDER NOT FOUND!\n";
    echo "Expected at: $vendorPath\n";
    
    // List what's actually in the parent directory
    echo "\n=== CONTENTS OF PARENT DIRECTORY ===\n";
    $parentDir = dirname(__DIR__);
    if (is_dir($parentDir)) {
        $files = scandir($parentDir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "- $file\n";
            }
        }
    }
}

echo "\n=== TRYING TO LOAD DATABASE ===\n";
if (file_exists($dbPath)) {
    try {
        require $dbPath;
        echo "Database class loaded successfully!\n";
        
        $db = new Database();
        echo "Database instance created!\n";
        
        $conn = $db->connect();
        echo "Database connected successfully!\n";
    } catch (Exception $e) {
        echo "Database error: " . $e->getMessage() . "\n";
    }
} else {
    echo "DATABASE CLASS NOT FOUND!\n";
}

echo "\n=== POST DATA ===\n";
echo "POST data: " . print_r($_POST, true) . "\n";

echo "\n=== END DEBUG ===\n";
?>
