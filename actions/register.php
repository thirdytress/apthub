<?php
session_start();
require_once "../classes/database.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

if (isset($_POST['action'])) {
  if ($_POST['action'] == 'register') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $db = new Database();
    $conn = $db->connect();

    // Check if username or email already exists
    $checkStmt = $conn->prepare("SELECT * FROM tenants WHERE username = ? OR email = ?");
    $checkStmt->execute([$username, $email]);
    if ($checkStmt->rowCount() > 0) {
      echo "Username or email already exists.";
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

    // Send OTP via email using PHPMailer
    $mail = new PHPMailer(true);
    try {
       $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'apthub@apartmenthub.online';
    $mail->Password = 'Thirdy_090803';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
      $mail->CharSet = 'UTF-8';
      
      $mail->SMTPOptions = array(
        'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
        )
      );

      $mail->setFrom('jgarvia9@gmail.com', 'ApartmentHub');
      $mail->addAddress($email, $firstname);

      $mail->isHTML(true);
      $mail->Subject = 'Verify Your Email - ApartmentHub';
      $mail->Body = '
      <!DOCTYPE html>
      <html>
      <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
      </head>
      <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f1e8;">
          <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f5f1e8; padding: 40px 20px;">
              <tr>
                  <td align="center">
                      <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
                          
                          <!-- Header -->
                          <tr>
                              <td style="background: linear-gradient(135deg, #1a252f, #2c3e50); padding: 40px 30px; text-align: center; border-bottom: 4px solid #d4af37;">
                                  <h1 style="color: #ffffff; font-size: 32px; font-weight: 700; margin: 0 0 5px 0; letter-spacing: 1px;">ApartmentHub</h1>
                                  <p style="color: #d4af37; font-size: 14px; margin: 0;">Your Trusted Property Management Platform</p>
                              </td>
                          </tr>
                          
                          <!-- Content -->
                          <tr>
                              <td style="padding: 50px 40px;">
                                  <h2 style="font-size: 24px; font-weight: 600; color: #2c3e50; margin: 0 0 20px 0;">Welcome, ' . htmlspecialchars($firstname) . '!</h2>
                                  
                                  <p style="font-size: 16px; color: #555555; line-height: 1.8; margin: 0 0 30px 0;">
                                      Thank you for registering with ApartmentHub! We are excited to have you join our community.
                                      To complete your registration, please verify your email address using the code below.
                                  </p>
                                  
                                  <!-- OTP Box -->
                                  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 30px 0;">
                                      <tr>
                                          <td style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border: 3px dashed #d4af37; border-radius: 15px; padding: 30px; text-align: center;">
                                              <p style="font-size: 14px; color: #666666; margin: 0 0 10px 0; text-transform: uppercase; letter-spacing: 1px;">YOUR VERIFICATION CODE</p>
                                              <div style="font-size: 42px; font-weight: 700; color: #d4af37; letter-spacing: 8px; font-family: Courier New, monospace; margin: 15px 0;">' . $otp . '</div>
                                              <p style="font-size: 13px; color: #666666; margin: 10px 0 0 0;">Enter this code in the verification window</p>
                                          </td>
                                      </tr>
                                  </table>
                                  
                                  <!-- Expiry Notice -->
                                  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 25px 0;">
                                      <tr>
                                          <td style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 5px;">
                                              <p style="font-size: 14px; color: #856404; margin: 0;"><strong>Important:</strong> This code will expire in <strong>10 minutes</strong> for security reasons.</p>
                                          </td>
                                      </tr>
                                  </table>
                                  
                                  <hr style="border: none; border-top: 2px solid #e0e0e0; margin: 30px 0;">
                                  
                                  <!-- Steps -->
                                  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                      <tr>
                                          <td style="background-color: #f8f9fa; padding: 25px; border-radius: 10px; border-left: 4px solid #28a745;">
                                              <h3 style="font-size: 16px; color: #2c3e50; margin: 0 0 15px 0;">Next Steps:</h3>
                                              <ol style="margin: 0; padding-left: 20px; color: #555555;">
                                                  <li style="font-size: 14px; margin-bottom: 8px; line-height: 1.6;">Return to the registration page</li>
                                                  <li style="font-size: 14px; margin-bottom: 8px; line-height: 1.6;">Enter the 6-digit code shown above</li>
                                                  <li style="font-size: 14px; margin-bottom: 8px; line-height: 1.6;">Click Verify OTP to complete your registration</li>
                                                  <li style="font-size: 14px; margin-bottom: 8px; line-height: 1.6;">Start browsing available apartments!</li>
                                              </ol>
                                          </td>
                                      </tr>
                                  </table>
                                  
                                  <!-- Security Notice -->
                                  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 25px;">
                                      <tr>
                                          <td style="background-color: #e7f3ff; padding: 15px; border-radius: 8px;">
                                              <p style="font-size: 13px; color: #004085; margin: 0;">
                                                  <strong>Security Tip:</strong> Never share this code with anyone. 
                                                  ApartmentHub staff will never ask for your verification code.
                                                  If you did not request this code, please ignore this email.
                                              </p>
                                          </td>
                                      </tr>
                                  </table>
                              </td>
                          </tr>
                          
                          <!-- Footer -->
                          <tr>
                              <td style="background: linear-gradient(135deg, #1a252f, #2c3e50); padding: 30px; text-align: center; border-top: 4px solid #d4af37;">
                                  <p style="color: #d4af37; font-size: 13px; margin: 5px 0; font-weight: 600;">&copy; 2025 ApartmentHub</p>
                                  <p style="color: rgba(255,255,255,0.7); font-size: 13px; margin: 5px 0;">Making apartment management simple and efficient</p>
                                  <div style="margin-top: 20px;">
                                      <a href="#" style="color: #d4af37; text-decoration: none; font-size: 12px; margin: 0 10px;">Privacy Policy</a> | 
                                      <a href="#" style="color: #d4af37; text-decoration: none; font-size: 12px; margin: 0 10px;">Terms of Service</a> | 
                                      <a href="#" style="color: #d4af37; text-decoration: none; font-size: 12px; margin: 0 10px;">Contact Support</a>
                                  </div>
                              </td>
                          </tr>
                          
                      </table>
                  </td>
              </tr>
          </table>
      </body>
      </html>
      ';

      $mail->send();
      echo "OTP_SENT";
    } catch (Exception $e) {
      echo "Failed to send OTP: " . $mail->ErrorInfo;
    }
  }

  if ($_POST['action'] == 'verify_otp') {
    if ($_POST['otp'] == $_SESSION['otp']) {
      $data = $_SESSION['registration_data'];
      $db = new Database();
      $conn = $db->connect();

      $stmt = $conn->prepare("INSERT INTO tenants (firstname, lastname, username, email, phone, password) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->execute([$data['firstname'], $data['lastname'], $data['username'], $data['email'], $data['phone'], $data['password']]);

      // Send Welcome Email after successful registration
      $welcomeMail = new PHPMailer(true);
      try {
        $welcomeMail->SMTPDebug = 0;
        $welcomeMail->isSMTP();
        $welcomeMail->Host = 'smtp.gmail.com';
        $welcomeMail->SMTPAuth = true;
        $welcomeMail->Username = 'jgarvia9@gmail.com';
        $welcomeMail->Password = 'zswa rnsf tpqs yqzy';
        $welcomeMail->SMTPSecure = 'tls';
        $welcomeMail->Port = 587;
        $welcomeMail->CharSet = 'UTF-8';
        
        $welcomeMail->SMTPOptions = array(
          'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
          )
        );

        $welcomeMail->setFrom('jgarvia9@gmail.com', 'ApartmentHub');
        $welcomeMail->addAddress($data['email'], $data['firstname']);

        $welcomeMail->isHTML(true);
        $welcomeMail->Subject = 'Registration Successful - Welcome to ApartmentHub!';
        $welcomeMail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f1e8;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f5f1e8; padding: 40px 20px;">
                <tr>
                    <td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
                            
                            <!-- Header -->
                            <tr>
                                <td style="background: linear-gradient(135deg, #1a252f, #2c3e50); padding: 40px 30px; text-align: center; border-bottom: 4px solid #d4af37;">
                                    <h1 style="color: #ffffff; font-size: 32px; font-weight: 700; margin: 0 0 5px 0; letter-spacing: 1px;">ApartmentHub</h1>
                                    <p style="color: #d4af37; font-size: 14px; margin: 0;">Your Trusted Property Management Platform</p>
                                </td>
                            </tr>
                            
                            <!-- Content -->
                            <tr>
                                <td style="padding: 50px 40px;">
                                    <h2 style="font-size: 28px; font-weight: 600; color: #28a745; margin: 0 0 20px 0; text-align: center;">Registration Successful!</h2>
                                    
                                    <p style="font-size: 16px; color: #555555; line-height: 1.8; margin: 0 0 30px 0;">
                                        Dear <strong>' . htmlspecialchars($data['firstname']) . ' ' . htmlspecialchars($data['lastname']) . '</strong>,
                                    </p>
                                    
                                    <p style="font-size: 16px; color: #555555; line-height: 1.8; margin: 0 0 30px 0;">
                                        Congratulations! Your account has been successfully created and verified. 
                                        Welcome to the ApartmentHub family! We are thrilled to have you join our community.
                                    </p>
                                    
                                    <!-- Success Box -->
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 30px 0;">
                                        <tr>
                                            <td style="background: linear-gradient(135deg, #d4f4dd, #c3f0d3); border: 3px solid #28a745; border-radius: 15px; padding: 30px; text-align: center;">
                                                <div style="font-size: 48px; margin-bottom: 15px;">&#10004;</div>
                                                <h3 style="color: #28a745; font-size: 20px; margin: 0 0 10px 0;">Account Activated!</h3>
                                                <p style="font-size: 14px; color: #555555; margin: 0;">You can now login and start browsing available apartments</p>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <!-- Account Details -->
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 25px 0;">
                                        <tr>
                                            <td style="background-color: #f8f9fa; border-left: 4px solid #3498db; padding: 20px; border-radius: 5px;">
                                                <h3 style="font-size: 16px; color: #2c3e50; margin: 0 0 15px 0;">Your Account Details:</h3>
                                                <p style="font-size: 14px; color: #555555; margin: 5px 0;"><strong>Username:</strong> ' . htmlspecialchars($data['username']) . '</p>
                                                <p style="font-size: 14px; color: #555555; margin: 5px 0;"><strong>Email:</strong> ' . htmlspecialchars($data['email']) . '</p>
                                                <p style="font-size: 14px; color: #555555; margin: 5px 0;"><strong>Phone:</strong> ' . htmlspecialchars($data['phone']) . '</p>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <hr style="border: none; border-top: 2px solid #e0e0e0; margin: 30px 0;">
                                    
                                    <!-- What\'s Next -->
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td style="background-color: #f8f9fa; padding: 25px; border-radius: 10px; border-left: 4px solid #d4af37;">
                                                <h3 style="font-size: 16px; color: #2c3e50; margin: 0 0 15px 0;">What You Can Do Now:</h3>
                                                <ol style="margin: 0; padding-left: 20px; color: #555555;">
                                                    <li style="font-size: 14px; margin-bottom: 8px; line-height: 1.6;">Browse our collection of available apartments</li>
                                                    <li style="font-size: 14px; margin-bottom: 8px; line-height: 1.6;">Apply for apartments that match your needs</li>
                                                    <li style="font-size: 14px; margin-bottom: 8px; line-height: 1.6;">Track your applications in your dashboard</li>
                                                    <li style="font-size: 14px; margin-bottom: 8px; line-height: 1.6;">Manage your profile and preferences</li>
                                                </ol>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <!-- Login Button -->
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 30px 0;">
                                        <tr>
                                            <td align="center">
                                                <a href="http://localhost/aahub-main/index.php" style="display: inline-block; background: linear-gradient(135deg, #d4af37, #c9a961); color: #1a252f; text-decoration: none; padding: 16px 50px; border-radius: 30px; font-weight: 600; font-size: 16px;">Login to Your Account</a>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <!-- Support Notice -->
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 25px;">
                                        <tr>
                                            <td style="background-color: #e7f3ff; padding: 15px; border-radius: 8px;">
                                                <p style="font-size: 13px; color: #004085; margin: 0;">
                                                    <strong>Need Help?</strong> Our support team is here to assist you. 
                                                    If you have any questions or need assistance, feel free to contact us anytime.
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style="background: linear-gradient(135deg, #1a252f, #2c3e50); padding: 30px; text-align: center; border-top: 4px solid #d4af37;">
                                    <p style="color: #d4af37; font-size: 13px; margin: 5px 0; font-weight: 600;">&copy; 2025 ApartmentHub</p>
                                    <p style="color: rgba(255,255,255,0.7); font-size: 13px; margin: 5px 0;">Making apartment management simple and efficient</p>
                                    <div style="margin-top: 20px;">
                                        <a href="#" style="color: #d4af37; text-decoration: none; font-size: 12px; margin: 0 10px;">Privacy Policy</a> | 
                                        <a href="#" style="color: #d4af37; text-decoration: none; font-size: 12px; margin: 0 10px;">Terms of Service</a> | 
                                        <a href="#" style="color: #d4af37; text-decoration: none; font-size: 12px; margin: 0 10px;">Contact Support</a>
                                    </div>
                                </td>
                            </tr>
                            
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ';

        $welcomeMail->send();
      } catch (Exception $e) {
        // Don't fail the registration if welcome email fails
        error_log("Welcome email failed: " . $e->getMessage());
      }

      unset($_SESSION['otp']);
      unset($_SESSION['registration_data']);
      echo "OTP_VALID";
    } else {
      echo "INVALID_OTP";
    }
  }
}
?>