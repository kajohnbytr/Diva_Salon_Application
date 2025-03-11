<?php
// Ensure session is started only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
$admin_username = $_SESSION['admin_username'] ?? '';

// Include database connection
include 'config.php';

// Fetch admin details from the database
$query = "SELECT username, position, profile_image FROM admin WHERE username = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $admin_username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Initialize variables to prevent undefined errors
$username = "";
$position = "";
$profileImage = "";

// Fetch data if found
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $username = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
    $position = htmlspecialchars($row['position'], ENT_QUOTES, 'UTF-8');

    // Handle profile image
    if (!empty($row['profile_image'])) {
        $profileImage = "data:image/jpeg;base64," . base64_encode($row['profile_image']);
    }
}

// Close the statement and database connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diva Sidebar</title>
    <link rel="stylesheet" href="sidebar.css">
    <style>
        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            display: flex;
            justify-content: flex-start;
            align-items: flex-start;
            height: 100vh;
            margin: 0;
            background-color: #f8f9fa;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #d4af37;
            padding: 20px;
            height: 100vh;
            color: white;
        }

        .sidebar h2, .sidebar p {
            text-align: center;
        }

        .profile {
            text-align: center;
            margin: 20px 0;
        }

        .profile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }

        nav ul {
            list-style: none;
            padding: 0;
        }

        nav ul li {
            padding: 10px;
            cursor: pointer;
            background: white;
            color: black;
            margin: 5px 0;
            text-align: center;
            border-radius: 5px;
        }

        nav ul li a {
            text-decoration: none;
            color: black;
            display: block;
            width: 100%;
        }

        .logout {
            width: 100%;
            padding: 10px;
            background: black;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 20px;
        }

        /* Responsive Sidebar */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>DIVA</h2>
        <p>Achieve Your Style</p>
        <div class="profile">
            <?php if (!empty($profileImage)): ?>
                <img src="<?php echo $profileImage; ?>" alt="Profile">
            <?php endif; ?>
            <?php if (!empty($username)): ?>
                <h3><?php echo $username; ?></h3>
            <?php endif; ?>
            <?php if (!empty($position)): ?>
                <p><?php echo $position; ?></p>
            <?php endif; ?>
        </div>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="appointments.php">Appointments</a></li>
                <li><a href="stylist.php">Stylists</a></li>
                <li><a href="users.php">Users</a></li>
            </ul>
        </nav>
        <button id="logout-btn" class="logout">Log out</button>
        <script src="sidebar.js"></script>
    </div>
</body>
</html>
