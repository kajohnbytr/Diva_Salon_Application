<?php
session_start();
include 'config.php'; // Database connection

// Check if the admin is logged in 
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}	
// Initialize counts
$userCount = $stylistCount = $appointmentCount = 0;
$appointmentsPerMonth = [];

// Fetch total users
$userQuery = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($userQuery && $userQuery->num_rows > 0) {
    $userCount = $userQuery->fetch_assoc()['total'];
}

// Fetch total stylists
$stylistQuery = $conn->query("SELECT COUNT(*) AS total FROM stylists");
if ($stylistQuery && $stylistQuery->num_rows > 0) {
    $stylistCount = $stylistQuery->fetch_assoc()['total'];
}

// Fetch total appointments
$appointmentQuery = $conn->query("SELECT COUNT(*) AS total FROM appointments");
if ($appointmentQuery && $appointmentQuery->num_rows > 0) {
    $appointmentCount = $appointmentQuery->fetch_assoc()['total'];
}
// Fetch appointments per day
$dayQuery = $conn->query("SELECT DATE_FORMAT(appointment_date, '%Y-%m-%d') AS day, COUNT(*) AS total FROM appointments GROUP BY day");
while ($row = $dayQuery->fetch_assoc()) {
    $appointmentsPerDay[$row['day']] = $row['total'];
}

// Fetch appointments per week
$weekQuery = $conn->query("SELECT YEARWEEK(appointment_date, 1) AS week, COUNT(*) AS total FROM appointments GROUP BY week");
while ($row = $weekQuery->fetch_assoc()) {
    $appointmentsPerWeek[$row['week']] = $row['total'];
}
// Fetch appointments per month
$monthQuery = $conn->query("SELECT DATE_FORMAT(appointment_date, '%Y-%m') AS month, COUNT(*) AS total FROM appointments GROUP BY month");
while ($row = $monthQuery->fetch_assoc()) {
    $appointmentsPerMonth[$row['month']] = $row['total'];
}

$appointments = $conn->query("
    SELECT 
        users.customer_name, 
        stylists.stylist_name, 
        appointments.appointment_date, 
        appointments.service, 
        appointments.status 
    FROM appointments
    JOIN users ON appointments.customer_id = users.id
    JOIN stylists ON appointments.stylist_id = stylists.id
");


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<?php include 'notification.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="dashboard-container">
    <h1><i class="fas fa-chart-line"></i> Admin Dashboard</h1>
    <br>
    <br>
    
    <div class="stats">
    <div class="stat-box appointments"><i class="fas fa-calendar-check"></i> Appointments: <span><?php echo $appointmentCount; ?></span></div>
    <div class="stat-box stylists"><i class="fas fa-user-tie"></i> Stylists: <span><?php echo $stylistCount; ?></span></div>
    <div class="stat-box users"><i class="fas fa-users"></i> Users: <span><?php echo $userCount; ?></span></div>
    </div>
    
    <section class="analytics">
        <div class="chart-wrapper">
            <div class="chart-container">
                <canvas id="chart"></canvas>
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
   const chartLabels = <?= json_encode(array_keys($appointmentsPerMonth)); ?>;
    const chartData = <?= json_encode(array_values($appointmentsPerMonth)); ?>;
    const dailyLabels = <?= json_encode(array_keys($appointmentsPerDay)); ?>;
    const dailyData = <?= json_encode(array_values($appointmentsPerDay)); ?>;
    const weeklyLabels = <?= json_encode(array_keys($appointmentsPerWeek)); ?>;
    const weeklyData = <?= json_encode(array_values($appointmentsPerWeek)); ?>;

    const ctx = document.getElementById('chart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [
              
                {
                    label: 'Appointments Per Day',
                    data: dailyData,
                    backgroundColor: 'blue'
                },
                {
                    label: 'Appointments Per Week',
                    data: weeklyData,
                    backgroundColor: 'green'
                },
                {
                    label: 'Appointments Per Month',
                    data: chartData,
                    backgroundColor: 'gold'
                }
            ]
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