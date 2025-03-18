<?php
session_start();
include 'config.php'; // Include database connection

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (!empty($username) && !empty($password)) {
        // Prepare statement to fetch stylist credentials
        $stmt = $conn->prepare("SELECT id, stylist_name, email, password FROM stylists WHERE email = ?");
        
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $id = $row['id'];
                $stylist_name = $row['stylist_name'];
                $email = $row['email'];
                $db_password = $row['password'];
                
                // Direct password comparison as per your current system
                // Note: This is kept as-is per your requirements, but hashing is recommended
                if ($password === $db_password) {
                    $_SESSION['user_id'] = $id;
                    $_SESSION['username'] = $stylist_name;
                    $_SESSION['email'] = $email;
                    $_SESSION['admin_logged_in'] = true;
                    
                    // Set a last activity timestamp for session timeout management
                    $_SESSION['last_activity'] = time();
                    
                    header("Location: stylist_appointments.php"); // Redirect to the stylist panel
                    exit();
                } else {
                    $error = "❌ Invalid email or password!";
                }
            } else {
                $error = "❌ Stylist user not found!";
            }
            
            $stmt->close();
        } else {
            $error = "❌ Database error. Please try again later.";
        }
    } else {
        $error = "❌ Please enter both email and password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIVA Login</title>
    <link rel="stylesheet" type="text/css" href="login.css">
</head>
<body>
    <div class="logo">
        <h1>D I V A</h1>
        <p>Achieve your style</p>
    </div>
    
    <div class="container">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <strong><label for="username">Username:</label></strong>
            <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            
            <strong><label for="password">Password:</label></strong>
            <input type="password" id="password" name="password" required>
            
            <?php if (!empty($error)): ?>
            <center><p style="color: red; font-weight: bold;"><?php echo $error; ?></p></center>
            <?php endif; ?>
            
            <input type="submit" value="Log in">
        </form>
        <center><p>Forgot password? <a href="forgotpassword.php" style="color: blue; text-decoration: underline; display: inline;">Click here</a></p></center>
    </div>
</body>
</html>