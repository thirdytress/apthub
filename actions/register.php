<?php
session_start();
require_once "../classes/database.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';

// ======================================================
// CONFIGURATION
// ======================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');
date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json');

// ======================================================
// MAIN PROCESS
// ======================================================
try {
    // Make sure this is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["status" => "error", "message" => "Invalid request method"]);
        exit;
    }

    // Read and validate inputs
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $confirm   = trim($_POST['confirm'] ?? '');

    if (!$firstname || !$lastname || !$username || !$email || !$phone || !$password || !$confirm) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    // Connect to DB
    $db = new Database();
    $conn = $db->connect();

    // Check if username/email already exists
    $check = $conn->prepare("SELECT * FROM tenants WHERE username=:u OR email=:e LIMIT 1");
    $check->bindParam(':u', $username);
    $check->bindParam(':e', $email);
    $check->execute();

    if ($check->fetch()) {
        echo json_encode(["status" => "error", "message" => "Username or Email already exists."]);
        exit;
    }

    // Generate OTP
    $otp = rand(100000, 999999);
    $_SESSION['register_otp'] = $otp;
    $_SESSION['register_data'] = compact('firstname', 'lastname', 'username', 'email', 'phone', 'password', 'confirm');

    // ======================================================
    // SEND OTP EMAIL (Hostinger SMTP)
    // ======================================================
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'apthub@apartmenthub.online'; // your Hostinger email
        $mail->Password = 'Thirdy_090803';          // <-- put your actual Hostinger email password here
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('apthub@apartmenthub.online', 'ApartmentHub');
        $mail->addAddress($email, $firstname . ' ' . $lastname);

        $mail->isHTML(true);
        $mail->Subject = 'Your ApartmentHub OTP Code';
        $mail->Body = "
            <h2>ApartmentHub Registration</h2>
            <p>Hi <b>{$firstname}</b>,</p>
            <p>Your OTP code is:</p>
            <h1 style='color:#4CAF50'>{$otp}</h1>
            <p>This code will expire in 5 minutes.</p>
        ";

        // Optional: log SMTP activity
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = function($str, $level) {
            file_put_contents(__DIR__ . '/../mail_debug.log', $str . "\n", FILE_APPEND);
        };

        $mail->send();
        echo json_encode(["status" => "success", "message" => "OTP sent to $email"]);
        exit;

    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        echo json_encode(["status" => "error", "message" => "Failed to send OTP: " . $mail->ErrorInfo]);
        exit;
    }

} catch (Exception $e) {
    error_log("Register Error: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Server error occurred. Check error.log"]);
    exit;
}
