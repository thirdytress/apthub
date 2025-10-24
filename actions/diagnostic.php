<?php
header('Content-Type: application/json');

$diagnostics = [
    'php_version' => phpversion(),
    'vendor_exists' => file_exists(__DIR__ . '/../vendor/autoload.php'),
    'vendor_path' => realpath(__DIR__ . '/../vendor/autoload.php'),
    'database_class_exists' => file_exists(__DIR__ . '/../classes/database.php'),
    'phpmailer_path' => __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php',
    'phpmailer_exists' => file_exists(__DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php'),
    'current_dir' => __DIR__,
    'parent_dir' => dirname(__DIR__),
    'post_data' => $_POST,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
];

// Try to load autoload
try {
    if ($diagnostics['vendor_exists']) {
        require __DIR__ . '/../vendor/autoload.php';
        $diagnostics['autoload_loaded'] = true;
        
        // Try to instantiate PHPMailer
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $diagnostics['phpmailer_works'] = true;
        } catch (Exception $e) {
            $diagnostics['phpmailer_works'] = false;
            $diagnostics['phpmailer_error'] = $e->getMessage();
        }
    } else {
        $diagnostics['autoload_loaded'] = false;
        $diagnostics['error'] = 'Vendor autoload file not found';
    }
} catch (Exception $e) {
    $diagnostics['autoload_loaded'] = false;
    $diagnostics['autoload_error'] = $e->getMessage();
}

// Try to connect to database
try {
    require_once __DIR__ . '/../classes/database.php';
    $db = new Database();
    $conn = $db->connect();
    $diagnostics['database_works'] = true;
} catch (Exception $e) {
    $diagnostics['database_works'] = false;
    $diagnostics['database_error'] = $e->getMessage();
}

echo json_encode($diagnostics, JSON_PRETTY_PRINT);
?>
