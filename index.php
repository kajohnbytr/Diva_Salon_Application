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
        <title>Diva Dashboard</title>
        <link rel="stylesheet" href="styles.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
                </div>
                <section class="analytics">
                    <h2>Analytics</h2>
                    <div class="chart-container">
                        <canvas id="chart"></canvas>
                    </div>
                </div>
        <script>
            const ctx = document.getElementById('chart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr'],
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
    