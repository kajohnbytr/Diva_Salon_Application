<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $_SESSION['email'] = $email;
    
    // Generate a 4-digit OTP
    $otp = rand(1000, 9999);
    $_SESSION['otp'] = $otp;
    
    // PHPMailer setup
    $mail = new PHPMailer(true);
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'delacruzjefrey15@gmail.com';
        $mail->Password = 'hzjf rwpp kwjp djum'; // Replace with new app password
        $mail->SMTPSecure = 'tls'; // Changed from PHPMailer::ENCRYPTION_STARTTLS
        $mail->Port = 587;
        
        // Email setup
        $mail->setFrom('Salon@gmail.com', 'FROM DIVAS SALON');
        $mail->addAddress($email);
        $mail->Subject = "OTP Code For Password Reset";
        $mail->Body = "Your OTP for password reset is: " . $otp;
        $mail->isHTML(false);
        
        if ($mail->send()) {
            echo "<script>
                    alert('OTP sent successfully! Redirecting to OTP verification...');
                    window.location.href = 'otp.php';
                  </script>";
            exit();
        } else {
            echo "<script>alert('Failed to send OTP. Please try again.');</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Error sending OTP: " . addslashes($mail->ErrorInfo) . "');</script>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password?</title>
    <link rel="stylesheet" type="text/css" href="forgotpass.css">
</head>
<body>
    <div class="container">
        <h2>Forgot Password?</h2>
        <p>Enter your email address to reset your password.</p>
        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            <br>
            <button type="submit">Next</button>
        </form>
    </div>
</body>
</html>