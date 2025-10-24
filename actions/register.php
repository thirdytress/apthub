<?php
session_start();
require_once "../classes/database.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

// Always return JSON
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

try {
  if (isset($_POST['action'])) {

    // ==============================
    // REGISTER ACTION
    // ==============================
    if ($_POST['action'] == 'register') {
      $firstname = trim($_POST['firstname']);
      $lastname  = trim($_POST['lastname']);
      $username  = trim($_POST['username']);
      $email     = trim($_POST['email']);
      $phone     = trim($_POST['phone']);
      $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

      $db = new Database();
      $conn = $db->connect();

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
        'lastname' => $lastname,
        'username' => $username,
        'email' => $email,
        'phone' => $phone,
        'password' => $password
      ];

      // Send OTP via email
      $mail = new PHPMailer(true);
      try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jgarvia9@gmail.com';
        $mail->Password = 'zswa rnsf tpqs yqzy';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->SMTPOptions = [
          'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
          ]
        ];

        $mail->setFrom('jgarvia9@gmail.com', 'ApartmentHub');
        $mail->addAddress($email, $firstname);
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - ApartmentHub';
        $mail->Body = "
          <h2>Welcome to ApartmentHub, $firstname!</h2>
          <p>Your One-Time Password (OTP) for verification is:</p>
          <h3 style='color:#2e6c80;'>$otp</h3>
          <p>This code will expire soon. Please do not share it with anyone.</p>
        ";

        $mail->send();
        echo json_encode(["status" => "success", "message" => "OTP_SENT"]);
      } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Failed to send OTP: " . $mail->ErrorInfo]);
      }
    }

    // ==============================
    // VERIFY OTP ACTION
    // ==============================
    elseif ($_POST['action'] == 'verify_otp') {
      if ($_POST['otp'] == $_SESSION['otp']) {
        $data = $_SESSION['registration_data'];

        $db = new Database();
        $conn = $db->connect();

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
          $welcomeMail->Host = 'smtp.gmail.com';
          $welcomeMail->SMTPAuth = true;
          $welcomeMail->Username = 'jgarvia9@gmail.com';
          $welcomeMail->Password = 'zswa rnsf tpqs yqzy';
          $welcomeMail->SMTPSecure = 'tls';
          $welcomeMail->Port = 587;
          $welcomeMail->CharSet = 'UTF-8';
          $welcomeMail->SMTPOptions = [
            'ssl' => [
              'verify_peer' => false,
              'verify_peer_name' => false,
              'allow_self_signed' => true
            ]
          ];

          $welcomeMail->setFrom('jgarvia9@gmail.com', 'ApartmentHub');
          $welcomeMail->addAddress($data['email'], $data['firstname']);
          $welcomeMail->isHTML(true);
          $welcomeMail->Subject = 'Registration Successful - Welcome to ApartmentHub!';
          $welcomeMail->Body = "
            <h2>Welcome aboard, {$data['firstname']}!</h2>
            <p>Your registration with <strong>ApartmentHub</strong> was successful.</p>
            <p>You can now log in and start exploring your dashboard.</p>
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
  } else {
    echo json_encode(["status" => "error", "message" => "No action specified."]);
  }
} catch (Exception $e) {
  echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
