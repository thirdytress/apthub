<?php
session_start();
require_once "../classes/database.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Use absolute path for Hostinger compatibility
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "Composer autoload not found. Path: " . $autoloadPath
    ]);
    exit;
}
require $autoloadPath;

// ====================================
// CONFIGURATION
// ====================================
error_reporting(E_ALL);
ini_set('display_errors', 0); // hide errors from screen in production
ini_set('log_errors', 1);

// For Hostinger, use relative path or /home/username/error_log.txt
$logPath = __DIR__ . '/error_log.txt';
ini_set('error_log', $logPath);

date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json');

// ====================================
// MAIN LOGIC
// ====================================
try {
  // Debug: Log incoming POST data
  error_log("POST data received: " . print_r($_POST, true));
  
  if (!isset($_POST['action'])) {
    echo json_encode(["status" => "error", "message" => "No action specified."]);
    exit;
  }

  $action = $_POST['action'];
  $db = new Database();
  $conn = $db->connect();

  // ====================================
  // 1️⃣ REGISTER - SEND OTP
  // ====================================
  if ($action === 'register') {
    // Validate required fields
    $required = ['firstname', 'lastname', 'username', 'email', 'phone', 'password'];
    foreach ($required as $field) {
      if (empty($_POST[$field])) {
        echo json_encode(["status" => "error", "message" => "Missing required field: $field"]);
        exit;
      }
    }
    
    $firstname = trim($_POST['firstname']);
    $lastname  = trim($_POST['lastname']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username or email already exists
    $checkStmt = $conn->prepare("SELECT * FROM tenants WHERE username = ? OR email = ?");
    $checkStmt->execute([$username, $email]);

    if ($checkStmt->rowCount() > 0) {
      echo json_encode(["status" => "error", "message" => "Username or email already exists."]);
      exit;
    }

    // Generate OTP
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['registration_data'] = [
      'firstname' => $firstname,
      'lastname'  => $lastname,
      'username'  => $username,
      'email'     => $email,
      'phone'     => $phone,
      'password'  => $password
    ];

    // Send OTP Email
    $mail = new PHPMailer(true);
    try {
      $mail->isSMTP();
      $mail->Host = 'smtp.hostinger.com';
      $mail->SMTPAuth = true;
      $mail->Username = 'apthub@apartmenthub.com';
      $mail->Password = 'Thirdy_090803';
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = 587;
      $mail->CharSet = 'UTF-8';
      
      // Disable peer verification for Hostinger
      $mail->SMTPOptions = [
        'ssl' => [
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
        ]
      ];
      
      $mail->Timeout = 30;
      $mail->SMTPDebug = 0; // Set to 2 for debugging

      $mail->setFrom('apthub@apartmenthub.com', 'ApartmentHub');
      $mail->addAddress($email, $firstname);
      $mail->isHTML(true);
      $mail->Subject = 'Verify Your Email - ApartmentHub';
      $mail->Body = "
        <div style='font-family:Arial,sans-serif;'>
          <h2>Welcome to ApartmentHub, $firstname!</h2>
          <p>To complete your registration, please verify your email using the code below:</p>
          <h3 style='color:#2e6c80;'>$otp</h3>
          <p>This OTP will expire soon. Please do not share it with anyone.</p>
          <br>
          <p>Best regards,<br><strong>ApartmentHub Team</strong></p>
        </div>
      ";

      $mail->send();
      echo json_encode(["status" => "success", "message" => "OTP_SENT"]);
    } catch (Exception $e) {
      error_log("Mail error: " . $mail->ErrorInfo);
      echo json_encode(["status" => "error", "message" => "Failed to send OTP. Please try again later."]);
    }
  }

  // ====================================
  // 2️⃣ VERIFY OTP - COMPLETE REGISTRATION
  // ====================================
  elseif ($action === 'verify_otp') {
    if (!isset($_SESSION['otp']) || !isset($_SESSION['registration_data'])) {
      echo json_encode(["status" => "error", "message" => "Session expired. Please register again."]);
      exit;
    }

    if ($_POST['otp'] == $_SESSION['otp']) {
      $data = $_SESSION['registration_data'];

      $stmt = $conn->prepare("INSERT INTO tenants (firstname, lastname, username, email, phone, password) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->execute([
        $data['firstname'],
        $data['lastname'],
        $data['username'],
        $data['email'],
        $data['phone'],
        $data['password']
      ]);

      // Send Welcome Email
      $welcomeMail = new PHPMailer(true);
      try {
        $welcomeMail->isSMTP();
        $welcomeMail->Host = 'smtp.hostinger.com';
        $welcomeMail->SMTPAuth = true;
        $welcomeMail->Username = 'apthub@apartmenthub.com';
        $welcomeMail->Password = 'Thirdy_090803';
        $welcomeMail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $welcomeMail->Port = 587;
        $welcomeMail->CharSet = 'UTF-8';
        $welcomeMail->Timeout = 30;
        $welcomeMail->SMTPOptions = [
          'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
          ]
        ];

        $welcomeMail->setFrom('apthub@apartmenthub.com', 'ApartmentHub');
        $welcomeMail->addAddress($data['email'], $data['firstname']);
        $welcomeMail->isHTML(true);
        $welcomeMail->Subject = 'Welcome to ApartmentHub!';
        $welcomeMail->Body = "
          <div style='font-family:Arial,sans-serif;'>
            <h2>Hi {$data['firstname']}!</h2>
            <p>Your registration with <strong>ApartmentHub</strong> is successful!</p>
            <p>You can now log in and manage your apartment with ease.</p>
            <br>
            <p>Thank you for joining us,<br><strong>ApartmentHub Team</strong></p>
          </div>
        ";
        $welcomeMail->send();
      } catch (Exception $e) {
        error_log("Welcome email failed: " . $e->getMessage());
      }

      unset($_SESSION['otp']);
      unset($_SESSION['registration_data']);
      echo json_encode(["status" => "success", "message" => "OTP_VALID"]);
    } else {
      echo json_encode(["status" => "error", "message" => "INVALID_OTP"]);
    }
  }

  else {
    echo json_encode(["status" => "error", "message" => "Invalid action."]);
  }

} catch (Exception $e) {
  error_log("General error: " . $e->getMessage());
  echo json_encode(["status" => "error", "message" => "An unexpected error occurred."]);
}
?>
