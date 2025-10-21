<?php

session_start();
require_once "../classes/database.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

// Add error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        throw new Exception('Email is required');
    }

    $db = new Database();
    $conn = $db->connect();

    // Check if user exists in tenants table
    $stmt = $conn->prepare("SELECT tenant_id, email, firstname FROM tenants WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('No account found with that email address');
    }

    // Generate reset token
    $token = bin2hex(random_bytes(32));
    // Changed to 24 hours instead of 1 hour for testing
    $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // Store token in database
    $stmt = $conn->prepare("UPDATE tenants SET reset_token = ?, reset_token_expiry = ? WHERE tenant_id = ?");
    $updateResult = $stmt->execute([$token, $expiry, $user['tenant_id']]);
    
    if (!$updateResult) {
        throw new Exception('Failed to store reset token in database');
    }

    // Send email
    $mail = new PHPMailer(true);
    
    // SMTP configuration
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'jgarvia9@gmail.com';
    $mail->Password = 'zswa rnsf tpqs yqzy';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    // Recipients
    $mail->setFrom('martynjosephseloterio@gmail.com', 'ApartmentHub');
    $mail->addAddress($user['email'], $user['firstname']);

    // Content
    $resetLink = "http://localhost/aahub-main/reset_password.php?token=" . $token;
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request - ApartmentHub';
    $mail->Body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
            
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Poppins', Arial, sans-serif;
                background: linear-gradient(135deg, #f5f1e8 0%, #e8dcc8 100%);
                padding: 40px 20px;
            }
            
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background: white;
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            }
            
            .header {
                background: linear-gradient(135deg, #1a252f, #2c3e50);
                padding: 40px 30px;
                text-align: center;
                border-bottom: 4px solid #d4af37;
            }
            
            .header h1 {
                color: white;
                font-size: 32px;
                font-weight: 700;
                margin-bottom: 5px;
                letter-spacing: 1px;
            }
            
            .header p {
                color: #d4af37;
                font-size: 14px;
                font-weight: 300;
            }
            
            .content {
                padding: 50px 40px;
            }
            
            .greeting {
                font-size: 24px;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 20px;
            }
            
            .message {
                font-size: 16px;
                color: #555;
                line-height: 1.8;
                margin-bottom: 30px;
            }
            
            .button-container {
                text-align: center;
                margin: 40px 0;
            }
            
            .reset-button {
                display: inline-block;
                background: linear-gradient(135deg, #d4af37, #c9a961);
                color: #1a252f !important;
                text-decoration: none;
                padding: 16px 50px;
                border-radius: 30px;
                font-weight: 600;
                font-size: 16px;
                box-shadow: 0 5px 20px rgba(212, 175, 55, 0.4);
                transition: transform 0.3s;
            }
            
            .reset-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(212, 175, 55, 0.5);
            }
            
            .divider {
                border-top: 2px solid #e0e0e0;
                margin: 30px 0;
            }
            
            .alternative {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 10px;
                border-left: 4px solid #d4af37;
            }
            
            .alternative p {
                font-size: 13px;
                color: #666;
                margin-bottom: 10px;
            }
            
            .link-text {
                font-size: 12px;
                color: #3498db;
                word-break: break-all;
                background: white;
                padding: 10px;
                border-radius: 5px;
                border: 1px solid #e0e0e0;
            }
            
            .expiry-notice {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 15px;
                margin: 25px 0;
                border-radius: 5px;
            }
            
            .expiry-notice p {
                font-size: 14px;
                color: #856404;
                margin: 0;
            }
            
            .security-notice {
                background: #e7f3ff;
                padding: 15px;
                border-radius: 8px;
                margin-top: 25px;
            }
            
            .security-notice p {
                font-size: 13px;
                color: #004085;
                margin: 0;
            }
            
            .footer {
                background: linear-gradient(135deg, #1a252f, #2c3e50);
                padding: 30px;
                text-align: center;
                border-top: 4px solid #d4af37;
            }
            
            .footer p {
                color: rgba(255,255,255,0.7);
                font-size: 13px;
                margin: 5px 0;
            }
            
            .footer .year {
                color: #d4af37;
                font-weight: 600;
            }
            
            .social-links {
                margin-top: 20px;
            }
            
            .social-links a {
                display: inline-block;
                margin: 0 10px;
                color: #d4af37;
                text-decoration: none;
                font-size: 12px;
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <!-- Header -->
            <div class='header'>
                <h1>🏢 ApartmentHub</h1>
                <p>Your Trusted Property Management Platform</p>
            </div>
            
            <!-- Content -->
            <div class='content'>
                <div class='greeting'>Hello, {$user['firstname']}! 👋</div>
                
                <div class='message'>
                    We received a request to reset your password for your ApartmentHub account. 
                    No worries, it happens to the best of us!
                </div>
                
                <div class='button-container'>
                    <a href='{$resetLink}' class='reset-button'>🔐 Reset My Password</a>
                </div>
                
                <div class='expiry-notice'>
                    <p>⏰ <strong>Important:</strong> This link will expire in <strong>24 hours</strong> for security reasons.</p>
                </div>
                
                <div class='divider'></div>
                
                <!-- Alternative Link -->
                <div class='alternative'>
                    <p><strong>Button not working?</strong> Copy and paste this link into your browser:</p>
                    <div class='link-text'>{$resetLink}</div>
                </div>
                
                <!-- Security Notice -->
                <div class='security-notice'>
                    <p>
                        🛡️ <strong>Security Tip:</strong> If you didn't request this password reset, 
                        please ignore this email or contact our support team immediately. 
                        Your account is safe and no changes have been made.
                    </p>
                </div>
            </div>
            
            <!-- Footer -->
            <div class='footer'>
                <p class='year'>© 2025 ApartmentHub</p>
                <p>Making apartment management simple and efficient</p>
                <div class='social-links'>
                    <a href='#'>Privacy Policy</a> | 
                    <a href='#'>Terms of Service</a> | 
                    <a href='#'>Contact Support</a>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Reset email sent successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>