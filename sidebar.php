<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include 'config.php';

// Initialize default values
$name = "Unknown";
$position = "Not Assigned";
$profileImage = "profile.jpg"; // Default profile image
$password = ""; // Initialize password variable

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $id = $_SESSION['user_id']; // Get the logged-in user's ID from session
    
    // Fetch admin details from the database using prepared statement
    $query = "SELECT name, position, profile_image, password FROM admin WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $name = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
        $position = htmlspecialchars($row['position'], ENT_QUOTES, 'UTF-8');
        $password = $row['password']; // Store password as plain text (not hashed)
        
        // If an image exists, convert it to base64
        if (!empty($row['profile_image'])) {
            $profileImage = 'data:image/jpeg;base64,' . base64_encode($row['profile_image']);
        }
    }
}

// Close the database connection
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

        nav ul li, .dropdown button {
            padding: 10px;
            background: white;
            color: black;
            margin: 5px 0;
            text-align: center;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            display: block;
            width: 100%;
        }

        nav ul li a {
            text-decoration: none;
            color: black;
            display: block;
            width: 100%;
        }

        /* Active button styling (swaps colors) */
        .active, .dropdown button.active {
            background: black !important;
            color: white !important;
        }

        /* Dropdown menu */
        .dropdown {
            display: none;
            flex-direction: column;
            width: 100%;
            margin-top: 5px;
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
            <img src="<?php echo $profileImage; ?>" alt="Profile">
            <h3><?php echo $name; ?></h3>
            <p><?php echo $position; ?></p>
        </div>
        <nav>
            <ul>
                <li onclick="setActive(this)"><a href="dashboard.php">Dashboard</a></li>
                <li class="appointments-btn" onclick="toggleDropdown(this)">Appointments ▼</li>
                <div class="dropdown" id="appointmentDropdown">
                    <button data-href="pending.php">Pending</button>
                    <button data-href="approved.php">Approved</button>
                    <button data-href="rejected.php">Rejected</button>
                </div>
                <li onclick="setActive(this)"><a href="stylist.php">Stylists</a></li>
                <li onclick="setActive(this)"><a href="users.php">Users</a></li>
                <li onclick="setActive(this)"><a href="settings.php">Settings</a></li>
            </ul>
        </nav>
        <button id="logout-btn" class="logout">Log out</button>      
        <script src="sidebar.js"></script>
    </div>
</body>
</html>
