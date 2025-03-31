<?php
header('Content-Type: application/json');
require 'config.php';
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Check if email is provided
if (empty($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

$email = $data['email'];

// Check if email exists in the users table
$checkUserQuery = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($checkUserQuery);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Email not found']);
    exit;
}

// Generate a 6-digit OTP
$otp = sprintf("%06d", mt_rand(1, 999999));

// Set expiry time to 10 minutes from now
$expiryTime = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Delete any existing OTPs for this email
$deleteQuery = "DELETE FROM otp_requests WHERE email = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("s", $email);
$stmt->execute();

// Insert new OTP
$insertQuery = "INSERT INTO otp_requests (email, otp, purpose, expires_at) VALUES (?, ?, 'password_reset', ?)";
$stmt = $conn->prepare($insertQuery);
$stmt->bind_param("sss", $email, $otp, $expiryTime);

if ($stmt->execute()) {
    // Send email with PHPMailer
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  
        $mail->SMTPAuth = true;
        $mail->Username = 'batangamer@gmail.com'; // Replace with your Gmail
        $mail->Password = 'epbs taar ydpv wzht';  // Replace with your Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Email Content
        $mail->setFrom('batangamer@gmail.com', 'Diva Salon');
        $mail->addAddress($email);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body = "Your OTP for password reset is: $otp\nThis OTP will expire in 10 minutes.";

        $mail->send();
        echo json_encode(['success' => true, 'message' => 'OTP sent to your email']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Mail Error: ' . $mail->ErrorInfo]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to generate OTP']);
}

$stmt->close();
$conn->close();
?>
