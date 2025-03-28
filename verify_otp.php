<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = $_POST['otp'];

    if (isset($_SESSION['otp']) && $entered_otp == $_SESSION['otp']) {
        unset($_SESSION['otp']); // Remove OTP from session after successful verification
        header("Location: otp.php"); // Redirect to password reset page
        exit();
    } else {
        echo "<script>
                alert('⚠ Invalid OTP. Please try again.');
                window.location.href = 'otp.php'; // Redirect back to OTP input page
              </script>";
        exit();
    }
}
?>
