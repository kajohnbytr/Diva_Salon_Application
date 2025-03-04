<?php 
session_start();
include 'config.php';

// Check if the admin is logged in 
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $name = trim($_POST['customer_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    $insert_query = "INSERT INTO users (customer_name, phone, email) VALUES (?, ?, ?)";
    if ($stmt = $conn->prepare($insert_query)) {
        $stmt->bind_param("sss", $name, $phone, $email);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: users.php");
    exit();
}

// Fetch users from the database
$query = "SELECT id, customer_name, phone, email FROM users";
$users = $conn->query($query);

if (!$users) {
    die("Error fetching users: " . $conn->error);
}

// Handle user deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: users.php");
        exit();
    } else {
        echo "Error deleting record.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Users</title>
    <link rel="stylesheet" href="users.css">    
</head>
<body>
    <div class="dashboard-container">
        <h1>Users Management</h1>
        <button onclick="openAddUserModal()">Add User</button>
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
                        <button onclick="openEditModal(<?= $row['id']; ?>, '<?= htmlspecialchars($row['customer_name']); ?>', '<?= htmlspecialchars($row['phone']); ?>', '<?= htmlspecialchars($row['email']); ?>')"> Edit </button>
                            <form id="deleteForm_<?= $row['id']; ?>" method="post">
                            <input type="hidden" name="delete_id" value="<?= $row['id']; ?>">
                            <button type="button" onclick="confirmDelete(<?= $row['id']; ?>)">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddUserModal()">&times;</span>
            <h2>Add User</h2>
            <form method="POST" action="add_user.php">
                <label for="add_name">Full Name:</label>
                <input type="text" id="add_name" name="customer_name" required>
                <label for="add_phone">Phone:</label>
                <input type="text" id="add_phone" name="phone" required>
                <label for="add_email">Email:</label>
                <input type="email" id="add_email" name="email" required>
                <button type="submit" name="add_user">Add User</button>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit User</h2>
            <form method="POST" action="edit_user.php">
                <input type="hidden" id="edit_id" name="edit_id">
                <label for="edit_name">Full Name:</label>
                <input type="text" id="edit_name" name="customer_name" required>
                <label for="edit_phone">Phone:</label>
                <input type="text" id="edit_phone" name="phone" required>
                <label for="edit_email">Email:</label>
                <input type="email" id="edit_email" name="email" required>
                <button type="submit">Update</button>
            </form>
        </div>
    </div>

    <script src="users.js"></script>
    <?php include 'sidebar.php'; ?>
</body>
</html>
