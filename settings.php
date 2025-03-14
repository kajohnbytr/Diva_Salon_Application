<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Define adminId - this was missing
$adminId = $_SESSION['admin_id'] ?? 1; // Use the ID from session or default to 1

// Fetch admin details
$query = "SELECT username, name, position, profile_image, password FROM admin WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $adminId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result) ?? [];

$name = htmlspecialchars($admin['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');
$position = htmlspecialchars($admin['position'] ?? 'Not Assigned', ENT_QUOTES, 'UTF-8');
$profileImage = !empty($admin['profile_image']) 
    ? 'data:image/jpeg;base64,' . base64_encode($admin['profile_image']) 
    : 'profile.jpg';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newName = mysqli_real_escape_string($conn, $_POST['name']);
    $newPosition = mysqli_real_escape_string($conn, $_POST['position']);
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $imageData = !empty($_FILES["profile_image"]["tmp_name"]) ? file_get_contents($_FILES["profile_image"]["tmp_name"]) : null;

    // Validate and update password if provided
    if (!empty($oldPassword) && !empty($newPassword) && !empty($confirmPassword)) {
        if ($oldPassword !== $admin['password']) {
            $_SESSION['error'] = "Old password is incorrect!";
        } elseif ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = "New password and confirmation do not match!";
        } else {
            $query = "UPDATE admin SET name=?, position=?, password=?, profile_image=? WHERE id=?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssssi", $newName, $newPosition, $newPassword, $imageData, $adminId);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = "Profile updated successfully!";
                header("Location: settings.php");
                exit();
            } else {
                $_SESSION['error'] = "Error updating profile!";
            }
        }
    } else {
        // Update without changing the password
        $query = "UPDATE admin SET name=?, position=?, profile_image=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $newName, $newPosition, $imageData, $adminId);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Profile updated successfully!";
            header("Location: settings.php");
            exit();
        } else {
            $_SESSION['error'] = "Error updating profile!";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="sidebar.css"> <!-- Sidebar CSS -->
    <style>
        body {
            display: flex;
            height: 100vh;
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .sidebar {
            width: 270px;
            height: 100vh;
            background: #d4af37;
            padding: 25px;
            color: white;
            position: fixed;
        }

        .main-content {
            margin-left: 290px;
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
            width: 450px;
            text-align: center;
        }

        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }

        form label {
            font-weight: bold;
            display: block;
            margin-top: 12px;
            text-align: left;
        }

        input, button {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        button {
            background: #d4af37;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 20px;
            font-size: 16px;
        }

        button:hover {
            background: #b8902b;
        }

        .message {
            margin-top: 12px;
            font-weight: bold;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <h2>Edit Profile</h2>
            
            <?php if (isset($_SESSION['success'])): ?>
                <p class="message success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <p class="message error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            <?php endif; ?>

            <img src="<?php echo $profileImage; ?>" class="profile-img" alt="Profile">

            <form method="POST" enctype="multipart/form-data">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo $name; ?>" required>

                <label for="position">Position</label>
                <input type="text" id="position" name="position" value="<?php echo $position; ?>" required>

                <label for="old_password">Old Password</label>
                <input type="password" id="old_password" name="old_password" placeholder="Enter old password">

                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter new password">

                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">

                <label for="profile_image">Profile Picture</label>
                <input type="file" id="profile_image" name="profile_image" accept="image/*">

                <button type="submit">Update Profile</button>
            </form>
        </div>
    </div>

</body>
</html>