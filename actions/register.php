<?php
session_start();
require_once "../classes/database.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

// Always return JSON
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

// Handle invalid or missing action
if (!isset($_POST['action'])) {
    echo json_encode(["status" => "error", "message" => "No action specified"]);
    exit;
}

$action = $_POST['action'];
$db = new Database();
$conn = $db->connect();

try {
    if ($action === 'register') {
        $firstname = trim($_POST['firstname']);
        $lastname = trim($_POST['lastname']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $plainPassword = $_POST['password'];

        // Check if username or email already exists
        $checkStmt = $conn->prepare("SELECT * FROM tenants WHERE username = ? OR email = ?");
        $checkStmt->execute([$username, $email]);

        if ($checkStmt->rowCount() > 0) {
            echo json_encode(["status" => "error", "message" => "Username or email already exists"]);
            exit;
        }

        // Generate OTP (valid for 10 minutes)
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expiry'] = time() + 600; // 10 mins validity
        $_SESSION['registration_data'] = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'password' => $plainPassword
        ];

        // Send OTP
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'apthub@apartmenthub.online';
            $mail->Password = 'YOUR_SMTP_PASSWORD'; // <-- MOVE TO ENV FILE!
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->setFrom('apthub@apartmenthub.online', 'ApartmentHub');
            $mail->addAddress($email, $firstname);
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email - ApartmentHub';
            $mail->Body = "Your ApartmentHub verification code is: <b>$otp</b><br><br>This code will expire in 10 minutes.";

            $mail->send();
            echo json_encode(["status" => "success", "message" => "OTP sent"]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Failed to send OTP: " . $mail->ErrorInfo]);
        }

    } elseif ($action === 'verify_otp') {
        if (!isset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['registration_data'])) {
            echo json_encode(["status" => "error", "message" => "Session expired or invalid"]);
            exit;
        }

        $enteredOtp = $_POST['otp'];
        if (time() > $_SESSION['otp_expiry']) {
            unset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['registration_data']);
            echo json_encode(["status" => "error", "message" => "OTP expired"]);
            exit;
        }

        if ($enteredOtp != $_SESSION['otp']) {
            echo json_encode(["status" => "error", "message" => "Invalid OTP"]);
            exit;
        }

        $data = $_SESSION['registration_data'];
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO tenants (firstname, lastname, username, email, phone, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['firstname'], $data['lastname'], $data['username'], $data['email'], $data['phone'], $hashedPassword]);

        // Clear session
        unset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['registration_data']);

        // Optionally send welcome email
        try {
            $welcome = new PHPMailer(true);
            $welcome->isSMTP();
            $welcome->Host = 'smtp.hostinger.com';
            $welcome->SMTPAuth = true;
            $welcome->Username = 'apthub@apartmenthub.online';
            $welcome->Password = 'YOUR_SMTP_PASSWORD'; // same sender
            $welcome->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $welcome->Port = 587;
            $welcome->setFrom('apthub@apartmenthub.online', 'ApartmentHub');
            $welcome->addAddress($data['email'], $data['firstname']);
            $welcome->isHTML(true);
            $welcome->Subject = 'Welcome to ApartmentHub!';
            $welcome->Body = "Hi <b>{$data['firstname']}</b>, your registration was successful! You can now log in and explore ApartmentHub.";

            $welcome->send();
        } catch (Exception $e) {
            error_log("Welcome email failed: " . $e->getMessage());
        }

        echo json_encode(["status" => "success", "message" => "OTP verified, registration completed"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid action"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>
