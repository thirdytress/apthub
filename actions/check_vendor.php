<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

echo "=== VENDOR FOLDER CONTENTS ===\n\n";

$vendorDir = __DIR__ . '/../vendor';
echo "Vendor directory: $vendorDir\n";
echo "Exists: " . (is_dir($vendorDir) ? 'YES' : 'NO') . "\n\n";

if (is_dir($vendorDir)) {
    echo "Contents of vendor/:\n";
    $items = scandir($vendorDir);
    foreach ($items as $item) {
        if ($item != '.' && $item != '..') {
            $fullPath = $vendorDir . '/' . $item;
            $type = is_dir($fullPath) ? 'DIR' : 'FILE';
            echo "  [$type] $item\n";
        }
    }
    
    echo "\n=== CHECKING PHPMAILER ===\n";
    $phpmailerDir = $vendorDir . '/phpmailer';
    echo "PHPMailer directory: $phpmailerDir\n";
    echo "Exists: " . (is_dir($phpmailerDir) ? 'YES' : 'NO') . "\n\n";
    
    if (is_dir($phpmailerDir)) {
        echo "Contents of vendor/phpmailer/:\n";
        $items = scandir($phpmailerDir);
        foreach ($items as $item) {
            if ($item != '.' && $item != '..') {
                $fullPath = $phpmailerDir . '/' . $item;
                $type = is_dir($fullPath) ? 'DIR' : 'FILE';
                echo "  [$type] $item\n";
            }
        }
        
        $phpmailerSubDir = $phpmailerDir . '/phpmailer';
        if (is_dir($phpmailerSubDir)) {
            echo "\nContents of vendor/phpmailer/phpmailer/:\n";
            $items = scandir($phpmailerSubDir);
            foreach ($items as $item) {
                if ($item != '.' && $item != '..') {
                    $fullPath = $phpmailerSubDir . '/' . $item;
                    $type = is_dir($fullPath) ? 'DIR' : 'FILE';
                    echo "  [$type] $item\n";
                }
            }
            
            $srcDir = $phpmailerSubDir . '/src';
            if (is_dir($srcDir)) {
                echo "\nContents of vendor/phpmailer/phpmailer/src/:\n";
                $items = scandir($srcDir);
                foreach ($items as $item) {
                    if ($item != '.' && $item != '..') {
                        echo "  - $item\n";
                    }
                }
            }
        }
    }
    
    echo "\n=== CHECKING COMPOSER ===\n";
    $composerDir = $vendorDir . '/composer';
    echo "Composer directory: $composerDir\n";
    echo "Exists: " . (is_dir($composerDir) ? 'YES' : 'NO') . "\n";
    
    $installedJson = $composerDir . '/installed.json';
    if (file_exists($installedJson)) {
        echo "\ninstalled.json exists!\n";
        $installed = json_decode(file_get_contents($installedJson), true);
        if (isset($installed['packages'])) {
            echo "Installed packages:\n";
            foreach ($installed['packages'] as $package) {
                echo "  - " . $package['name'] . " v" . $package['version'] . "\n";
            }
        }
    }
}

echo "\n=== TRYING MANUAL PHPMAILER LOAD ===\n";
$phpmailerFile = __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
echo "PHPMailer.php path: $phpmailerFile\n";
echo "File exists: " . (file_exists($phpmailerFile) ? 'YES' : 'NO') . "\n";

if (file_exists($phpmailerFile)) {
    try {
        require_once $phpmailerFile;
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
        echo "Manual load successful!\n";
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        echo "PHPMailer instance created successfully!\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
