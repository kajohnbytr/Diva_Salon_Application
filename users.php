<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}	
// Fetch users from the database
$query = "SELECT id, customer_name, phone, email FROM users";
$users = $conn->query($query);

// Add User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $name = htmlspecialchars($_POST['customer_name'], ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("INSERT INTO users (customer_name, phone, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $phone, $email);
        $stmt->execute();
    }
    header("Location: users.php");
    exit();
}

// Delete User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: users.php");
    exit();
}

// Update User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $id = intval($_POST['id']);
    $name = htmlspecialchars($_POST['customer_name'], ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("UPDATE users SET customer_name = ?, phone = ?, email = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $phone, $email, $id);
        $stmt->execute();
    }
    header("Location: users.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard - Users</title>
    <link rel="stylesheet" href="users.css">
    <link rel="stylesheet" href="modal.css">
    <?php include 'sidebar.php'; ?>
    <?php include 'notification.php'; ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("addModal").style.display = "none";
    document.getElementById("editModal").style.display = "none";

    const searchInput = document.getElementById("searchUser");
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

function openEditModal(id, name, phone, email) {
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_name").value = name;
    document.getElementById("edit_phone").value = phone;
    document.getElementById("edit_email").value = email;
    document.getElementById("editModal").style.display = "flex";
}

function closeEditModal() {
    document.getElementById("editModal").style.display = "none";
}

function deleteUser(id) {
    if (confirm("Are you sure you want to delete this user?")) {
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
        <h1>User Management</h1>
        
        <button class="add-btn" onclick="openAddModal()">Add User</button>
        <input type="text" id="searchUser" class="search-box" placeholder="Search Users...">
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']); ?></td>
                        <td><?= htmlspecialchars($row['customer_name']); ?></td>
                        <td><?= htmlspecialchars($row['phone']); ?></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td>
                            <button class="edit-btn" onclick="openEditModal(<?= $row['id']; ?>, '<?= htmlspecialchars($row['customer_name']); ?>', '<?= htmlspecialchars($row['phone']); ?>', '<?= htmlspecialchars($row['email']); ?>')">Edit</button>
                            <form id="deleteForm_<?= $row['id']; ?>" method="POST" action="users.php" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?= $row['id']; ?>">
                                <button type="button" class="delete-btn" onclick="deleteUser(<?= $row['id']; ?>)">Delete</button>
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
            <center><h2>Add User</h2></center>        
            <form method="POST" action="users.php">
                <label for="customer_name">Full Name:</label>
                <input type="text" name="customer_name" placeholder="Enter Full Name" id="customer_name" required>
                
                <label for="phone">Phone:</label>
                <input type="text" name="phone" placeholder="Enter phone" id="phone" required>
                
                <label for="email">Email:</label>
                <input type="email" name="email" placeholder="Enter email" id="email" required>
                
                <button type="submit" name="add_user">Add User</button>
            </form>
        </div>
    </div>
    
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <center><h2>Edit User</h2></center>
            <form method="POST" action="users.php">
    <input type="hidden" name="id" id="edit_id">
    
    <label for="edit_name">Full Name:</label>
    <input type="text" name="customer_name" id="edit_name" required>
    
    <label for="edit_phone">Phone:</label>
    <input type="text" name="phone" id="edit_phone" required>
    
    <label for="edit_email">Email:</label>
    <input type="email" name="email" id="edit_email" required>
    
    <button type="submit" name="edit_user">Update</button>
</form>
        </div>
    </div>
</body>
</html>