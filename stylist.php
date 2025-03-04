<?php
session_start();
include 'config.php';

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function openAddModal() {
            document.getElementById("addModal").style.display = "block";
        }
        
        function closeAddModal() {
            document.getElementById("addModal").style.display = "none";
        }
        
        function openEditModal(id, name, expertise, phone, email) {
            document.getElementById("edit_id").value = id;
            document.getElementById("edit_name").value = name;
            document.getElementById("edit_expertise").value = expertise;
            document.getElementById("edit_phone").value = phone;
            document.getElementById("edit_email").value = email;
            document.getElementById("editModal").style.display = "block";
        }
        
        function closeEditModal() {
            document.getElementById("editModal").style.display = "none";
        }

        function deleteStylist(id) {
            if (confirm("Are you sure you want to delete this stylist?")) {
                document.getElementById("deleteForm_" + id).submit();
            }
        }
    </script>
</head>
<body>
    <?php include 'sidebar.php'; ?>
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
                        <td><?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($row['stylist_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($row['expertise'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <button class="edit-btn" onclick="openEditModal(<?= $row['id']; ?>, '<?= htmlspecialchars($row['stylist_name'], ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($row['expertise'], ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?>')">Edit</button>
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
            <h2>Add Stylist</h2>
            <form method="POST" action="stylist.php">
                <input type="text" name="stylist_name" placeholder="Name" required>
                <input type="text" name="expertise" placeholder="Expertise" required>
                <input type="text" name="phone" placeholder="Phone" required>
                <input type="email" name="email" placeholder="Email" required>
                <button type="submit" name="add_stylist">Add</button>
            </form>
        </div>
    </div>
    
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Stylist</h2>
            <form method="POST" action="edit_stylist.php">
                <input type="hidden" name="id" id="edit_id">
                <input type="text" name="stylist_name" id="edit_name" required>
                <input type="text" name="expertise" id="edit_expertise" required>
                <input type="text" name="phone" id="edit_phone" required>
                <input type="email" name="email" id="edit_email" required>
                <button type="submit" name="edit_stylist">Update</button>
            </form>
        </div>
    </div>
</body>
</html>