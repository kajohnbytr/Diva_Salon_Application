<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}	
// Fetch stylists from the database
$query = "SELECT id, stylist_name, expertise, phone, email, rating FROM stylists";
$stylists = $conn->query($query);

// Add Stylist
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_stylist'])) {
    $name = htmlspecialchars($_POST['stylist_name'], ENT_QUOTES, 'UTF-8');
    $expertise = htmlspecialchars($_POST['expertise'], ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $default_rating = 5;

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("INSERT INTO stylists (stylist_name, expertise, phone, email, password, rating) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $name, $expertise, $phone, $email, $password, $default_rating);
        $stmt->execute();
        $stmt->close();
        header("Location: stylist.php");
        exit();
    }
}

// Update Stylist
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_stylist'])) {
    $id = intval($_POST['id']);
    $name = htmlspecialchars($_POST['stylist_name'], ENT_QUOTES, 'UTF-8');
    $expertise = htmlspecialchars($_POST['expertise'], ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("UPDATE stylists SET stylist_name = ?, expertise = ?, phone = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $expertise, $phone, $email, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: stylist.php");
        exit();
    }
}

// Delete Stylist
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM stylists WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
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
    <?php include 'notification.php'; ?>
    <script src="stylist.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <h1>Stylist Management</h1>
        
        <button class="add-btn" onclick="openAddModal()">Add Stylist</button>
        <input type="text" id="searchStylist" class="search-box" placeholder="Search Stylists...">
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Expertise</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Rating</th>
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
                        <td><?= htmlspecialchars($row['rating'] ?? '0'); ?></td>
                        <td>
                            <button class="edit-btn" onclick="openEditModal(<?= $row['id']; ?>, '<?= htmlspecialchars($row['stylist_name'], ENT_QUOTES); ?>', '<?= htmlspecialchars($row['expertise'], ENT_QUOTES); ?>', '<?= htmlspecialchars($row['phone'], ENT_QUOTES); ?>', '<?= htmlspecialchars($row['email'], ENT_QUOTES); ?>')">Edit</button>
                            <form method="POST" action="stylist.php" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?= $row['id']; ?>">
                                <button type="submit" class="delete-btn">Delete</button>
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
                <input type="text" name="stylist_name" id="stylist_name" required>
                <label for="expertise">Expertise:</label>
                <input type="text" name="expertise" id="expertise" required>
                <label for="phone">Phone:</label>
                <input type="text" name="phone" id="phone" required>
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
                <button type="submit" name="add_stylist">Add Stylist</button>
            </form>
        </div>
    </div>
    
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <center><h2>Edit Stylist</h2></center>
            <form method="POST" action="stylist.php"> 
                <input type="hidden" name="id" id="edit_id">
                <label for="edit_name">Full Name:</label>
                <input type="text" name="stylist_name" id="edit_name" required>
                <label for="edit_expertise">Expertise:</label>
                <input type="text" name="expertise" id="edit_expertise" required>
                <label for="edit_phone">Phone:</label>
                <input type="text" name="phone" id="edit_phone" required>
                <label for="edit_email">Email:</label>
                <input type="email" name="email" id="edit_email" required>
                <button type="submit" name="edit_stylist">Update</button>
            </form>
        </div>
    </div>
</body>
</html>
