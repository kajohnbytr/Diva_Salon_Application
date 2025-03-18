<?php
header('Content-Type: application/json');
require 'config.php';

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Check if all required fields are provided
if (empty($data['email']) || empty($data['token']) || empty($data['new_password'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$email = $data['email'];
$token = $data['token'];
$newPassword = $data['new_password']; // Store the plain password as requested

// Verify the token
$tokenQuery = "SELECT * FROM password_reset_tokens 
               WHERE email = ? 
               AND token = ? 
               AND used = 0
               AND expires_at > NOW()";

$stmt = $conn->prepare($tokenQuery);
$stmt->bind_param("ss", $email, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update user's password (without hashing)
    $updateQuery = "UPDATE users SET password = ? WHERE email = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ss", $newPassword, $email);
    
    if ($updateStmt->execute()) {
        // Mark token as used
        $markTokenQuery = "UPDATE password_reset_tokens SET used = 1 WHERE email = ? AND token = ?";
        $markTokenStmt = $conn->prepare($markTokenQuery);
        $markTokenStmt->bind_param("ss", $email, $token);
        $markTokenStmt->execute();
        $markTokenStmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update password']);
    }
    
    $updateStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
}

$stmt->close();
$conn->close();
?>
