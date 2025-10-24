<?php
session_start();
require_once "../classes/database.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

// ==========================
// CONFIGURATION
// ==========================
error_reporting(E_ALL);
ini_set('display_errors', 0); // Prevent HTML error output

date_default_timezone_set('Asia/Manila');

// Always return JSON
header('Content-Type: application/json');

// ==========================
// GLOBAL ERROR HANDLERS
// ==========================
set_exception_handler(function ($e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
});

set_error_handler(function ($severity, $message, $file, $line) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "PHP Error: $message in $file:$line"]);
    exit;
});

// ==========================
// MAIN LOGIC
// ==========================
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        throw new Exception('Email is required.');
    }

    // Connect to database
    $db = new Database();
    $conn = $db->connect();

    // Check if email exists
    $stmt = $conn->prepare("SELECT tenant_id, email, firstname FROM tenants WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('No account found with that email address.');
    }

    // Generate secure reset token (valid for 24 hours)
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // Store token in database
    $stmt = $conn->prepare("UPDATE tenants SET reset_token = ?, reset_token_expiry = ? WHERE tenant_id = ?");
    if (!$stmt->execute([$token, $expiry, $user['tenant_id']])) {
        throw new Exception('Failed to store reset token in database.');
    }

    // ==========================
    // EMAIL SENDING (PHPMailer)
    // ==========================a
    $mail = new PHPMailer(true);

    // SMTP settings
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'apthub@apartmenthub.online';
    $mail->Password = 'Thirdy_090803';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Optional: for local dev environments
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    // Email details
    $mail->setFrom('apthub@apartmenthub.online', 'ApartmentHub');
    $mail->addAddress($user['email'], $user['firstname']);
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request - ApartmentHub';

    // Reset link
    // Change this to your live URL when deployed
    $resetLink = "https://apartmenthub.online/reset_password.php?token=" . urlencode($token);

    // Email HTML body
    $mail->Body = "
    <html>
    <head>
        <style>
            body {
                font-family: 'Poppins', Arial, sans-serif;
                background-color: #f9f9f9;
                padding: 30px;
            }
            .container {
                background: #fff;
                max-width: 600px;
                margin: auto;
                border-radius: 12px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                overflow: hidden;
            }
            .header {
                background: linear-gradient(135deg, #1a252f, #2c3e50);
                color: white;
                text-align: center;
                padding: 30px;
                font-size: 22px;
                border-bottom: 4px solid #d4af37;
            }
            .content {
                padding: 30px;
                color: #333;
            }
            .btn {
                display: inline-block;
                background: linear-gradient(135deg, #d4af37, #c9a961);
                color: #1a252f;
                text-decoration: none;
                padding: 12px 30px;
                border-radius: 25px;
                font-weight: bold;
                text-align: center;
            }
            .footer {
                background: #1a252f;
                color: rgba(255,255,255,0.7);
                text-align: center;
                padding: 15px;
                font-size: 13px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>Password Reset Request</div>
            <div class='content'>
                <p>Hi <strong>{$user['firstname']}</strong>,</p>
                <p>We received a request to reset your ApartmentHub account password.</p>
                <p>Click the button below to reset your password:</p>
                <p style='text-align:center;'>
                    <a href='{$resetLink}' class='btn'>Reset My Password</a>
                </p>
                <p>If the button doesn't work, copy and paste this link into your browser:</p>
                <p style='font-size:12px; color:#555;'>{$resetLink}</p>
                <p>This link will expire in <strong>24 hours</strong>.</p>
                <hr>
                <p>If you didn’t request this password reset, please ignore this email.</p>
            </div>
            <div class='footer'>
                © 2025 ApartmentHub | All Rights Reserved
            </div>
        </div>
    </body>
    </html>";

    // Send email
    $mail->send();

    echo json_encode(['success' => true, 'message' => 'Password reset email sent successfully.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
