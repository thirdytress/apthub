<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

function sendOTP($toEmail, $otpCode) {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'jgarvia9@gmail.com';       // Palitan ng Gmail mo
        $mail->Password   = 'thirdygarcia2108';    // Palitan ng Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom('yourgmail@gmail.com', 'ApartmentHub');
        $mail->addAddress($toEmail);

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = "Your OTP code is: <b>$otpCode</b>";
        $mail->AltBody = "Your OTP code is: $otpCode";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Failed to send OTP: " . $mail->ErrorInfo;
    }
}
