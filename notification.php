<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php'; // Ensure this file exists and is correctly set up

// Fetch new users count
$userQuery = $conn->query("SELECT COUNT(*) AS count FROM users WHERE created_at >= NOW() - INTERVAL 1 DAY");
$newUsers = ($userQuery) ? $userQuery->fetch_assoc()['count'] : 0;

// Fetch new stylists count
$stylistQuery = $conn->query("SELECT COUNT(*) AS count FROM stylists WHERE created_at >= NOW() - INTERVAL 1 DAY");
$newStylists = ($stylistQuery) ? $stylistQuery->fetch_assoc()['count'] : 0;

// Fetch new appointments count
$appointmentQuery = $conn->query("SELECT COUNT(*) AS count FROM appointments WHERE created_at >= NOW() - INTERVAL 1 DAY");
$newAppointments = ($appointmentQuery) ? $appointmentQuery->fetch_assoc()['count'] : 0;

// Fetch upcoming appointments with JOIN to get actual names
$upcomingAppointments = $conn->query("
    SELECT 
        users.customer_name, 
        stylists.stylist_name, 
        appointments.appointment_date, 
        appointments.service 
    FROM appointments
    JOIN users ON appointments.customer_id = users.id
    JOIN stylists ON appointments.stylist_id = stylists.id
    WHERE appointment_date >= CURDATE()
");

$totalNotifications = $newUsers + $newStylists + $newAppointments;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Bell</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .notification-container {
            position: absolute;
            top: 10px;
            right: 20px;
            cursor: pointer;
        }
        #notification-bell {
            color: red;
            font-size: 24px;
        }
        .badge {
            background-color: red;
            color: white;
            padding: 5px 10px;
            border-radius: 50%;
            position: absolute;
            top: -5px;
            right: -10px;
            font-size: 12px;
            display: <?= ($totalNotifications > 0) ? 'inline-block' : 'none' ?>;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            padding: 10px;
            width: 250px;
            border-radius: 5px;
        }
        .notification-container:hover .dropdown-content {
            display: block;
        }
    </style>
</head>
<body>
    <div class="notification-container">
        <i class="fa-solid fa-bell" id="notification-bell"></i>
        <span id="notification-count" class="badge"><?= $totalNotifications ?></span>
        <div class="dropdown-content">
            <h4>Notifications</h4>
            <ul id="notification-list">
                <?php if ($totalNotifications == 0) echo "<li>No new notifications</li>"; ?>
                <?php if ($newUsers > 0) echo "<li>$newUsers new user(s) registered</li>"; ?>
                <?php if ($newStylists > 0) echo "<li>$newStylists new stylist(s) added</li>"; ?>
                <?php if ($newAppointments > 0) echo "<li>$newAppointments new appointment(s) scheduled</li>"; ?>
                <?php while ($row = $upcomingAppointments->fetch_assoc()) : ?>
                    <li>Upcoming: <?= $row['customer_name'] ?> with <?= $row['stylist_name'] ?> on <?= $row['appointment_date'] ?> for <?= $row['service'] ?></li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

    <script>
        function refreshNotifications() {
            location.reload(); // Reloads the page every 30 seconds to update notifications
        }

        setInterval(refreshNotifications, 300000);
    </script>
</body>
</html>

<?php $conn->close(); ?>
