<?php
header('Content-Type: text/plain');

echo "=== SIMPLE PHPMAILER TEST ===\n\n";

$phpmailerPath = __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';

echo "Looking for: $phpmailerPath\n";
echo "File exists: " . (file_exists($phpmailerPath) ? 'YES' : 'NO') . "\n";
echo "Is readable: " . (is_readable($phpmailerPath) ? 'YES' : 'NO') . "\n";

if (file_exists($phpmailerPath)) {
    echo "File size: " . filesize($phpmailerPath) . " bytes\n";
    echo "File permissions: " . substr(sprintf('%o', fileperms($phpmailerPath)), -4) . "\n";
}

echo "\n=== TRYING AUTOLOAD ===\n";
require_once __DIR__ . '/../vendor/autoload.php';

echo "Checking class_exists...\n";
if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    echo "✓ SUCCESS! PHPMailer class is available!\n";
    
    // Try to instantiate
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        echo "✓ PHPMailer object created successfully!\n";
        echo "PHPMailer Version: " . $mail::VERSION . "\n";
    } catch (Exception $e) {
        echo "✗ Error creating PHPMailer object: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ PHPMailer class NOT found\n";
    echo "\nRegistered autoload functions:\n";
    print_r(spl_autoload_functions());
}

echo "\n=== END TEST ===\n";
