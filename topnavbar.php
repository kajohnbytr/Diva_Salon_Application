<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Navbar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f8f9fa;
        }

        /* Navbar Styles */
        .top-navbar {
            width: 100%;
            background-color: #FFC107; /* Yellow Background */
            padding: 15px 20px;
            display: flex;
            justify-content: flex-end; /* Push icons to the right */
            align-items: center;
        }

        .navbar-icons {
            display: flex;
            align-items: center;
            gap: 30px; /* Space between icons */
            padding-right: 20px; /* Extra spacing on the right */
        }

        .icon {
            font-size: 32px; /* Bigger Icons */
            cursor: pointer;
            position: relative;
            transition: color 0.3s ease;
        }

        .icon:hover {
            color: #d39e00; /* Darker Yellow Hover */
        }

        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: red;
            color: white;
            font-size: 12px;
            padding: 3px 6px;
            border-radius: 50%;
        }

        /* Profile Icon Styling */
        .profile-container {
            position: relative;
            cursor: pointer;
        }

        .profile-container i {
            font-size: 32px; /* Bigger Profile Icon */
        }

        .profile-dropdown {
            display: none;
            position: absolute;
            top: 45px;
            right: 0;
            background-color: white;
            color: black;
            width: 160px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
        }

        .profile-dropdown a {
            display: block;
            padding: 12px;
            text-decoration: none;
            color: black;
            font-weight: bold;
            transition: background 0.3s;
        }

        .profile-dropdown a:hover {
            background-color: #f1f1f1;
        }

        /* Show Dropdown */
        .show {
            display: block;
        }
    </style>
</head>
<body>

    <!-- Top Navbar -->
    <div class="top-navbar">
        <div class="navbar-icons">
            <!-- Notification Bell -->
            <div class="icon" id="notification-icon">
                <i class="fa-solid fa-bell"></i>
                <span class="notification-badge" id="notification-badge">3</span>
            </div>

            <!-- Profile Icon -->
            <div class="profile-container" id="profile-icon">
                <i class="fa-solid fa-user-circle"></i>
                <div class="profile-dropdown" id="profile-dropdown">
                    <a href="profile.php">Profile</a>
                    <a href="#" id="logout-btn">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Notification Click Event
            document.getElementById("notification-icon").addEventListener("click", function () {
                alert("You have new notifications!");
                document.getElementById("notification-badge").style.display = "none"; // Hide badge after click
            });

            // Toggle Profile Dropdown
            const profileIcon = document.getElementById("profile-icon");
            const profileDropdown = document.getElementById("profile-dropdown");

            profileIcon.addEventListener("click", function (event) {
                event.stopPropagation();
                profileDropdown.classList.toggle("show");
            });

            // Close dropdown when clicking outside
            window.addEventListener("click", function (event) {
                if (!profileIcon.contains(event.target)) {
                    profileDropdown.classList.remove("show");
                }
            });

            // Logout Confirmation
            document.getElementById("logout-btn").addEventListener("click", function (e) {
                e.preventDefault();
                if (confirm("Are you sure you want to log out?")) {
                    window.location.href = "logout.php"; // Redirect to logout page
                }
            });
        });
    </script>

</body>
</html>
