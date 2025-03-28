<?php
session_start();
require 'vendor/autoload.php';
require 'config.php';

if (!isset($_SESSION['email'])) {
    echo "<script>alert('No email found. Try resetting your password again.'); window.location.href='forgotpassword.php';</script>";
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendOTP($email) {
    // Generate OTP
    $otp = sprintf("%04d", rand(1000, 9999));
    
    // Create PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_email@gmail.com'; // SMTP username
        $mail->Password   = 'your_app_password'; // App password, not your regular password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('no-reply@yourwebsite.com', 'Your Website');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your New OTP Code';
        $mail->Body    = "Your new OTP is: <b>$otp</b>";
        $mail->AltBody = "Your new OTP is: $otp";

        // Send email
        $mail->send();
        
        return $otp;
    } catch (Exception $e) {
        // Log the error
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Send OTP
$otp = sendOTP($_SESSION['email']);

if ($otp) {
    // Update OTP in session
    $_SESSION['otp'] = $otp;

    header("Location: otp.php");
    exit();
} else {
    echo "<script>alert('Failed to resend OTP. Please try again.'); window.location.href='forgotpassword.php';</script>";
}
?>