<?php
session_start();
include 'config.php';

// Check if the admin is logged in 
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Fetch stylists from the database
$query = "SELECT id, stylist_name, expertise, phone, email FROM stylists";
$stylists = $conn->query($query);

// Add Stylist
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_stylist'])) {
    $name = htmlspecialchars($_POST['stylist_name'], ENT_QUOTES, 'UTF-8');
    $expertise = htmlspecialchars($_POST['expertise'], ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("INSERT INTO stylists (stylist_name, expertise, phone, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $expertise, $phone, $email);
        $stmt->execute();
    }
    header("Location: stylist.php");
    exit();
}

// Delete Stylist
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM stylists WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: stylist.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard - Stylists</title>
    <link rel="stylesheet" href="stylist.css">
    <link rel="stylesheet" href="modal.css">
    <?php include 'sidebar.php'; ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("addModal").style.display = "none";
    document.getElementById("editModal").style.display = "none";

    const searchInput = document.getElementById("searchStylist");
    const tableRows = document.querySelectorAll("tbody tr");

    searchInput.addEventListener("keyup", function () {
        let filter = searchInput.value.toLowerCase();
        tableRows.forEach(row => {
            let name = row.cells[1].innerText.toLowerCase();
            row.style.display = name.includes(filter) ? "" : "none";
        });
    });
});

function openAddModal() {
    document.getElementById("addModal").style.display = "flex";
}

function closeAddModal() {
    document.getElementById("addModal").style.display = "none";
}

function openEditModal(id, name, expertise, phone, email) {
    document.getElementById("edit_name").value = name;
    document.getElementById("edit_expertise").value = expertise;
    document.getElementById("edit_phone").value = phone;
    document.getElementById("edit_email").value = email;
    document.getElementById("editModal").style.display = "flex";
}

function closeEditModal() {
    document.getElementById("editModal").style.display = "none";
}

function deleteStylist(id) {
    if (confirm("Are you sure you want to delete this stylist?")) {
        document.getElementById("deleteForm_" + id).submit();
    }
}

window.onclick = function(event) {
    let addModal = document.getElementById('addModal');
    let editModal = document.getElementById('editModal');

    if (event.target === addModal) {
        closeAddModal();
    }
    if (event.target === editModal) {
        closeEditModal();
    }
};

</script>
</head>
<body>
    <div class="dashboard-container">
        <h1>Stylist Management</h1>
        
        <button class="add-btn" onclick="openAddModal()">Add Stylist</button>
        <input type="text" id="searchStylist" class="search-box" placeholder="Search Stylists..." onkeyup="searchStylists()">
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Expertise</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stylists->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']); ?></td>
                        <td><?= htmlspecialchars($row['stylist_name']); ?></td>
                        <td><?= htmlspecialchars($row['expertise']); ?></td>
                        <td><?= htmlspecialchars($row['phone']); ?></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td>
                            <button class="edit-btn" onclick="openEditModal(<?= $row['id']; ?>, '<?= htmlspecialchars($row['stylist_name']); ?>', '<?= htmlspecialchars($row['expertise']); ?>', '<?= htmlspecialchars($row['phone']); ?>', '<?= htmlspecialchars($row['email']); ?>')">Edit</button>
                            <form id="deleteForm_<?= $row['id']; ?>" method="POST" action="stylist.php" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?= $row['id']; ?>">
                                <button type="button" class="delete-btn" onclick="deleteStylist(<?= $row['id']; ?>)">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <center><h2>Add Stylist</h2></center>          
            <form method="POST" action="stylist.php">
            <label for="stylist_name">Full Name:</label>
            <input type="text" name="stylist_name" id="stylist_name" placeholder="Enter full name" required>

            <label for="expertise">Expertise:</label>
            <input type="text" name="expertise" id="expertise" placeholder="Enter expertise (e.g., Hair Stylist, Makeup Artist)" required>

            <label for="phone">Phone:</label>
            <input type="text" name="phone" id="phone" placeholder="Enter phone number" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" placeholder="Enter email address" required>

            <button type="submit" name="add_stylist">Add Stylist</button>
            <!-- #endregion --></form>
        </div>
    </div>
    
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <center><h2>Edit Stylist</h2></center>
            <form method="POST" action="edit_stylist.php"> 
                <input type="hidden" name="id" id="edit_id">
                <label for="stylist_name">Full Name:</label>
                <input type="text" name="stylist_name" id="edit_name" required>
                <label for="expertise">Expertise:</label>
                <input type="text" name="expertise" id="edit_expertise" required>
                <label for="phone">Phone:</label>
                <input type="text" name="phone" id="edit_phone" required>
                <label for="email">Email:</label>
                <input type="email" name="email" id="edit_email" required>
                <button type="submit" name="edit_stylist">Update</button>
            </form>
        </div>
    </div>
</body>
</html>