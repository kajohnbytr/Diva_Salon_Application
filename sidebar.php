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
            <img src="profile.jpg" alt="Profile">
            <h3>Alisha B. Garcia</h3>
            <p>Manager</p>
        </div>
        <nav>
            <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="appointments.php">Appointments</a></li>
            <li><a href="stylist.php">Stylists</a></li>
            <li><a href="users.php">Users</a></li>
            </ul>
        </nav>
        <button class="logout" onclick="logout()">Log out</button>
        <script src="sidebar.js"></script>
    </div>
</body>
</html>
