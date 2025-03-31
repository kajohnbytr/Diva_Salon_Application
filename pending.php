<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}    

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
    <style>
.custom-dropdown {
    position: relative;
    display: inline-block;
    width: 120px;
    height:auto;
    
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    height: max-content;
}
.tableHeader {
    max-height: fit-content;
}
td{
    height: auto;
}
.tableRows{
    height: max-content;
}

.dropdown-button {
    padding: 5px;
    border-radius: 5px;
    font-weight: bold;
    border: none;
    width: 100%;
    text-align: center;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dropdown-button::after {
    content: " ▼";
    font-size: 12px;
}

.dropdown-menu {
    display: none;
    position: absolute;
    background-color: white;
    box-shadow: 0px 4px 6px rgba(0,0,0,0.1);
    z-index: 10; /* Increased z-index to ensure it appears above other elements */
    width: 100%;
    border-radius: 5px;
    overflow: hidden;
    top: 100%; /* Position dropdown below the button */
    left: 0;
    max-height: 200px; /* Optional: limits height if many options */
    overflow-y:visible; /* Allows scrolling if content is long */
}

/* Status Colors */
.dropdown-button.pending {
    background-color: yellow !important;
    color: black !important;
}

.dropdown-button.approved {
    background-color: green !important;
    color: white !important;
}

.dropdown-button.rejected {
    background-color: red !important;
    color: white !important;
}

/* Hover Effects */
.dropdown-button.pending:hover {
    background-color: darkgoldenrod !important;
    color: white !important;
}

.dropdown-button.approved:hover {
    background-color: darkgreen !important;
}

.dropdown-button.rejected:hover {
    background-color: darkred !important;
}

.dropdown-menu div {
    padding: 5px;
    cursor: pointer;
    text-align: center;
}

.dropdown-menu div.approved {
    background-color: green;
    color: white;
}

.dropdown-menu div.rejected {
    background-color: red;
    color: white;
}

.dropdown-menu div.approved:hover {
    background-color: darkgreen;
}

.dropdown-menu div.rejected:hover {
    background-color: darkred;
}

    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Pending Appointments</h1>
        <input type="text" id="searchAppointment" class="search-box" placeholder="Search Appointments...">

        <table>
            <thead class="tableHeader">
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
                        <tr class="tableRows">
                            <td><?= htmlspecialchars($row['id']); ?></td>
                            <td><?= htmlspecialchars($row['customer_name']); ?></td>
                            <td><?= htmlspecialchars($row['stylist_name']); ?></td>
                            <td><?= htmlspecialchars($row['appointment_date']); ?></td>
                            <td>
                                <?php
                                $time = DateTime::createFromFormat('H:i:s', $row['appointment_time']);
                                echo htmlspecialchars($time->format('h:i A'));
                                ?>
                            </td>
                            <td><?= htmlspecialchars($row['service']); ?></td>
                            <td>
                                <div class="custom-dropdown">
                                    <button class="dropdown-button <?= strtolower($row['status']); ?>" onclick="toggleDropdown(this)">
                                        <?= htmlspecialchars($row['status']); ?>
                                    </button>
                                    <div class="dropdown-menu">
                                        <form method="POST" action="update_status.php">
                                            <input type="hidden" name="appointment_id" value="<?= $row['id']; ?>">
                                            <div class="approved" onclick="updateStatus(this, 'Approved')">Approved</div>
                                            <div class="rejected" onclick="updateStatus(this, 'Rejected')">Rejected</div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    function toggleDropdown(button) {
        const menu = button.nextElementSibling;
        document.querySelectorAll(".dropdown-menu").forEach(dropdown => {
            if (dropdown !== menu) {
                dropdown.style.display = "none";
            }
        });
        menu.style.display = menu.style.display === "block" ? "none" : "block";

        document.addEventListener("click", function(event) {
            if (!button.contains(event.target) && !menu.contains(event.target)) {
                menu.style.display = "none";
            }
        }, { once: true });
    }

    function updateStatus(option, status) {
        const dropdown = option.closest(".custom-dropdown");
        const button = dropdown.querySelector(".dropdown-button");

        button.textContent = status;
        button.className = `dropdown-button ${status.toLowerCase()}`;

        if (status === "Approved") {
            button.style.backgroundColor = "green";
            button.style.color = "white";
        } else {
            button.style.backgroundColor = "red";
            button.style.color = "white";
        }

        const form = option.closest("form");
        const statusInput = document.createElement("input");
        statusInput.type = "hidden";
        statusInput.name = "status";
        statusInput.value = status;
        form.appendChild(statusInput);
        form.submit();
    }
    </script>

</body>
</html>
