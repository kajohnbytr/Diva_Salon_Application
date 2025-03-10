<?php
session_start();
include 'config.php';

// Check if the admin is logged in 
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Fetch appointments from the database
$query = "SELECT id, customer_name, stylist_name, appointment_date, service, status FROM appointments";
$appointments = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard - Appointments</title>
    <link rel="stylesheet" href="appointments.css">
    <?php include 'sidebar.php'; ?>
</head>
<body>
    <div class="dashboard-container">
        <h1>Appointment Management</h1>
        <input type="text" id="searchAppointment" class="search-box" placeholder="Search Appointments...">
        
        <table>
    <thead>
        <tr>
            <th>ID</th>
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
                <td><?= htmlspecialchars($row['id']); ?></td>
                <td><?= htmlspecialchars($row['customer_name']); ?></td>
                <td><?= htmlspecialchars($row['stylist_name']); ?></td>
                <td><?= htmlspecialchars($row['appointment_date']); ?></td>
                <td><?= htmlspecialchars($row['service']); ?></td>
                <td>
                <form method="POST" action="update_status.php" style="display: inline;">
                <input type="hidden" name="appointment_id" value="<?= $row['id']; ?>">
                <select name="status" onchange="this.form.submit()" 
                style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; background-color: #f9f9f9;">
                <option value="Pending" <?= ($row['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Completed" <?= ($row['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                <option value="Cancelled" <?= ($row['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>     
    </div>
    <script src="appointments.js"></script>
</body>
</html>