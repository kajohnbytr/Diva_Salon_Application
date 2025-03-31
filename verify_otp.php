<?php 
session_start(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = $_POST['otp'];
    
    if (isset($_SESSION['otp']) && $entered_otp == $_SESSION['otp']) {
        // OTP is correct
        unset($_SESSION['otp']); // Remove OTP from session after successful verification
        $_SESSION['otp_verified'] = true; // Set session flag for verified user
        header("Location: reset_password.php"); // Redirect to password reset page
        exit();
    } else {
        // OTP is incorrect
        echo "<script>
                alert('⚠ Invalid OTP. Please try again.');
                window.location.href = 'otp.php'; // Redirect back to OTP input page
              </script>";
        exit();
    }
}
?>