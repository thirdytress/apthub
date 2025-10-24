<?php
header('Content-Type: text/plain');

echo "=== DETAILED PATH CHECK ===\n\n";

$vendorPath = __DIR__ . '/../vendor';
$phpmailerSrc = $vendorPath . '/phpmailer/phpmailer/src';
$phpmailerFile = $phpmailerSrc . '/PHPMailer.php';

echo "Vendor path: " . realpath($vendorPath) . "\n";
echo "Vendor exists: " . (is_dir($vendorPath) ? 'YES' : 'NO') . "\n\n";

echo "PHPMailer src path: $phpmailerSrc\n";
echo "Real path: " . (realpath($phpmailerSrc) ?: 'NOT RESOLVED') . "\n";
echo "Is directory: " . (is_dir($phpmailerSrc) ? 'YES' : 'NO') . "\n";
echo "Is file: " . (is_file($phpmailerSrc) ? 'YES' : 'NO') . "\n";
echo "Readable: " . (is_readable($phpmailerSrc) ? 'YES' : 'NO') . "\n\n";

if (is_dir($phpmailerSrc)) {
    echo "Contents of src directory:\n";
    $files = scandir($phpmailerSrc);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $fullPath = $phpmailerSrc . '/' . $file;
            $type = is_file($fullPath) ? 'FILE' : (is_dir($fullPath) ? 'DIR' : 'UNKNOWN');
            $size = is_file($fullPath) ? filesize($fullPath) : 0;
            echo "  [$type] $file";
            if ($type === 'FILE') {
                echo " ($size bytes)";
            }
            echo "\n";
        }
    }
} else {
    echo "src is NOT a directory!\n";
}

echo "\n=== CHECKING PHPMailer.php ===\n";
echo "Path: $phpmailerFile\n";
echo "File exists: " . (file_exists($phpmailerFile) ? 'YES' : 'NO') . "\n";
echo "Is file: " . (is_file($phpmailerFile) ? 'YES' : 'NO') . "\n";
echo "Real path: " . (realpath($phpmailerFile) ?: 'NOT RESOLVED') . "\n";
if (file_exists($phpmailerFile)) {
    echo "File size: " . filesize($phpmailerFile) . " bytes\n";
    echo "Readable: " . (is_readable($phpmailerFile) ? 'YES' : 'NO') . "\n";
}

echo "\n=== TRYING TO REQUIRE ===\n";
try {
    if (file_exists($phpmailerFile)) {
        require_once $phpmailerFile;
        echo "PHPMailer.php loaded successfully!\n";
        
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            echo "PHPMailer class found!\n";
        } else {
            echo "PHPMailer class NOT found after loading file!\n";
        }
    } else {
        echo "Cannot load - file does not exist!\n";
    }
} catch (Exception $e) {
    echo "Error loading PHPMailer: " . $e->getMessage() . "\n";
}

echo "\n=== CHECKING AUTOLOAD ===\n";
$autoloadPath = $vendorPath . '/autoload.php';
echo "Autoload path: $autoloadPath\n";
echo "Autoload exists: " . (file_exists($autoloadPath) ? 'YES' : 'NO') . "\n";

try {
    require_once $autoloadPath;
    echo "Autoload loaded!\n";
    
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        echo "✓ PHPMailer class available via autoload!\n";
    } else {
        echo "✗ PHPMailer class NOT available via autoload!\n";
    }
} catch (Exception $e) {
    echo "Error with autoload: " . $e->getMessage() . "\n";
}

echo "\n=== CHECKING COMPOSER FILES ===\n";
$autoloadPsr4 = $vendorPath . '/composer/autoload_psr4.php';
if (file_exists($autoloadPsr4)) {
    echo "autoload_psr4.php contents:\n";
    $psr4 = include $autoloadPsr4;
    print_r($psr4);
}

echo "\n=== END ===\n";
