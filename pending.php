<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}	

// Fetch pending appointments with customer name and stylist name
$appointments = $conn->query("
    SELECT 
        appointments.id, 
        users.customer_name, 
        stylists.stylist_name, 
        appointments.appointment_date, 
        appointments.appointment_time, 
        appointments.service, 
        appointments.status 
    FROM appointments
    JOIN users ON appointments.customer_id = users.id
    JOIN stylists ON appointments.stylist_id = stylists.id
    WHERE appointments.status = 'Pending'
");

// Check for query errors
if (!$appointments) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pending Appointments</title>
    <link rel="stylesheet" href="appointments.css">
    <?php include 'sidebar.php'; ?>
    <?php include 'notification.php'; ?>
</head>
<body>
    <div class="dashboard-container">
        <h1>Pending Appointments</h1>
        <input type="text" id="searchAppointment" class="search-box" placeholder="Search Appointments...">

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Stylist Name</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Service</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($appointments->num_rows === 0) : ?>
                    <tr><td colspan="7">No pending appointments found.</td></tr>
                <?php else : ?>
                    <?php while ($row = $appointments->fetch_assoc()) : ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']); ?></td>
                            <td><?= htmlspecialchars($row['customer_name']); ?></td>
                            <td><?= htmlspecialchars($row['stylist_name']); ?></td>
                            <td><?= htmlspecialchars($row['appointment_date']); ?></td>
                            <td>
                                <?php
                                // Convert appointment_time to 12-hour format
                                $time = DateTime::createFromFormat('H:i:s', $row['appointment_time']);
                                echo htmlspecialchars($time->format('h:i A')); // Format to 12-hour format with AM/PM
                                ?>
                            </td>
                            <td><?= htmlspecialchars($row['service']); ?></td>
                            <td>
                                <form method="POST" action="update_status.php">
                                    <input type="hidden" name="appointment_id" value="<?= $row['id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Approved" <?= $row['status'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="Rejected" <?= $row['status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Search functionality
        document.getElementById("searchAppointment").addEventListener("keyup", function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll("tbody tr");

            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? "" : "none";
            });
        });
    </script>
</body>
</html>