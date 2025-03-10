<?php
session_start();
include 'config.php'; // Database connection

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect to login page
    header("Location: login.php");
    exit();
}

// Initialize counts
$userCount = $stylistCount = $appointmentCount = 0;
$topStylists = [];
$appointmentsPerDay = $appointmentsPerWeek = $appointmentsPerMonth = [];

// Fetch top-rated stylists
$topStylistQuery = $conn->query("SELECT stylist_name, rating FROM stylists ORDER BY rating DESC LIMIT 3");
if ($topStylistQuery && $topStylistQuery->num_rows > 0) {
    while ($row = $topStylistQuery->fetch_assoc()) {
         $topStylists[] = $row;
    }
}

// Fetch upcoming appointments
$query = "SELECT id, customer_name, stylist_name, appointment_date, service, status FROM appointments";
$appointments = $conn->query($query);
    
try {
    // Fetch appointments per day
    $dayQuery = $conn->query("SELECT DATE(appointment_date) AS day, COUNT(*) AS total FROM appointments GROUP BY day");
    while ($row = $dayQuery->fetch_assoc()) {
        $appointmentsPerDay[$row['day']] = $row['total'];
    }

    // Fetch appointments per week
    $weekQuery = $conn->query("SELECT YEARWEEK(appointment_date) AS week, COUNT(*) AS total FROM appointments GROUP BY week");
    while ($row = $weekQuery->fetch_assoc()) {
        $appointmentsPerWeek[$row['week']] = $row['total'];
    }

    // Fetch appointments per month
    $monthQuery = $conn->query("SELECT DATE_FORMAT(appointment_date, '%Y-%m') AS month, COUNT(*) AS total FROM appointments GROUP BY month");
    while ($row = $monthQuery->fetch_assoc()) {
        $appointmentsPerMonth[$row['month']] = $row['total'];
    }
} catch (Exception $e) {
    error_log("Error fetching data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <?php include 'sidebar.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <h1><i class="fas fa-chart-line"></i> Admin Dashboard</h1>
        <div class="stats">
            <div class="stat-box users"><i class="fas fa-users"></i> Users: <span><?php echo $userCount; ?></span></div>
            <div class="stat-box stylists"><i class="fas fa-user-tie"></i> Stylists: <span><?php echo $stylistCount; ?></span></div>
            <div class="stat-box appointments"><i class="fas fa-calendar-check"></i> Appointments: <span><?php echo $appointmentCount; ?></span></div>
        </div>
        
        <section class="analytics">
    <div class="chart-wrapper"> <!-- New wrapper for the chart -->
        <div class="chart-container">
            <canvas id="chart"></canvas>
        </div>
    </div>
    <div class="stylist-wrapper"> <!-- New wrapper for the top stylist -->
        <div class="top-stylist">
            <h3>Top Rated Stylists</h3>
            <ul>
                <?php
                $topStylistQuery = $conn->query("SELECT stylist_name, rating FROM stylists ORDER BY rating DESC LIMIT 3");
                while ($row = $topStylistQuery->fetch_assoc()) {
                    echo "<li>{$row['stylist_name']} - {$row['rating']}★</li>";
                }
                ?>
            </ul>
        </div>
    </div>
</section>
        
        <section class="appointments">
            <h2>Upcoming Appointments</h2>
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Stylist</th>
                        <th>Date</th>
                        <th>Service</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $appointments->fetch_assoc()): ?>
                        <tr>    
                            <td><?= htmlspecialchars($row['customer_name']); ?></td>
                            <td><?= htmlspecialchars($row['stylist_name']); ?></td>
                            <td><?= htmlspecialchars($row['appointment_date']); ?></td>
                            <td><?= htmlspecialchars($row['service']); ?></td>
                            <td><?= htmlspecialchars($row['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </div>
    
    <script>
        const ctx = document.getElementById('chart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Mar', 'April', 'May', 'June'],
                datasets: [{
                    label: 'Appointments',
                    data: [6670, 9515, 6489, 8430],
                    backgroundColor: 'gold'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
