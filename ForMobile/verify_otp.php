<?php
header('Content-Type: application/json');
require 'config.php';

// Set timezone to prevent expiry issues
date_default_timezone_set('Asia/Manila');


// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Check if email and OTP are provided
if (empty($data['email']) || empty($data['otp'])) {
    echo json_encode(['success' => false, 'message' => 'Email and OTP are required']);
    exit;
}

$email = trim($data['email']);
$otp = trim($data['otp']);

// Fetch OTP details
$query = "SELECT otp, CONVERT_TZ(expires_at, '+00:00', '+08:00') AS expires_at, is_used 
          FROM otp_requests WHERE email = ? AND purpose = 'password_reset'";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'OTP record not found for this email']);
    exit;
}

$storedOTP = trim($row['otp']);
$expiresAt = $row['expires_at'];
$isUsed = $row['is_used'];

// Get the current time in the same format as `expires_at`
$currentTime = date('Y-m-d H:i:s');

// Debugging Output
if ($storedOTP !== $otp) {
    echo json_encode(['success' => false, 'message' => 'OTP mismatch', 'provided_otp' => $otp, 'stored_otp' => $storedOTP]);
    exit;
}

if ($isUsed == 1) {
    echo json_encode(['success' => false, 'message' => 'OTP has already been used']);
    exit;
}

// Debugging: Check expiry times
if (strtotime($expiresAt) < strtotime($currentTime)) {
    echo json_encode([
        'success' => false,
        'message' => 'OTP has expired',
        'expires_at' => $expiresAt,
        'current_time' => $currentTime
    ]);
    exit;
}

// If OTP is valid, generate a reset token
$resetToken = bin2hex(random_bytes(32)); // More secure token
$tokenExpiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));

// Mark OTP as used
$updateQuery = "UPDATE otp_requests SET is_used = 1 WHERE email = ? AND otp = ?";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param("ss", $email, $otp);
$updateStmt->execute();
$updateStmt->close();

// Store the reset token
$tokenQuery = "INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?)";
$tokenStmt = $conn->prepare($tokenQuery);
$tokenStmt->bind_param("sss", $email, $resetToken, $tokenExpiry);
$tokenStmt->execute();
$tokenStmt->close();

echo json_encode(['success' => true, 'message' => 'OTP verified successfully', 'token' => $resetToken]);

$stmt->close();
$conn->close();
?>
