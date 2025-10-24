<?php
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: text/plain');

echo "=== SMTP CONNECTION TEST ===\n\n";

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = 3; // Verbose debug output
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'apthub@apartmenthub.com';
    $mail->Password = 'Thirdy_090803';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->Timeout = 30;
    
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    $mail->setFrom('apthub@apartmenthub.com', 'ApartmentHub');
    $mail->addAddress('test@example.com', 'Test User');
    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test';
    $mail->Body = 'This is a test email.';

    echo "\nAttempting to send email...\n\n";
    
    $result = $mail->send();
    
    echo "\n\n=== RESULT ===\n";
    if ($result) {
        echo "✓ Email sent successfully!\n";
    } else {
        echo "✗ Failed to send email\n";
        echo "Error Info: " . $mail->ErrorInfo . "\n";
    }
    
} catch (Exception $e) {
    echo "\n\n=== ERROR ===\n";
    echo "Exception: " . $e->getMessage() . "\n";
    echo "Error Info: " . $mail->ErrorInfo . "\n";
}

echo "\n=== END TEST ===\n";
