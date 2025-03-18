<?php
session_start();
include 'config.php';

// Debugging (REMOVE IN PRODUCTION)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Fetch stylists from the database
$query = "SELECT id, stylist_name, expertise, phone, email, picture FROM stylists";
$stylists = $conn->query($query);

// Add Stylist
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_stylist'])) {
    $name = htmlspecialchars($_POST['stylist_name'], ENT_QUOTES, 'UTF-8');
    $expertise = htmlspecialchars($_POST['expertise'], ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Handle picture upload
    $picture = null;
    $picture_uploaded = false;
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
        if ($_FILES['picture']['size'] > 0) {
            $picture = file_get_contents($_FILES['picture']['tmp_name']);
            if ($picture === false) {
                error_log("Failed to read picture file (add): " . $_FILES['picture']['tmp_name']);
            } else {
                $picture_uploaded = true;
            }
        } else {
            error_log("The uploaded picture is empty (add).");
        }
    } else {
        error_log("Error uploading picture (add): " . $_FILES['picture']['error']);
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            // Use NULL directly in the query
            $sql = "INSERT INTO stylists (stylist_name, expertise, phone, email, picture) VALUES (?, ?, ?, ?, " . ($picture_uploaded ? "?" : "NULL") . ")";
            $stmt = $conn->prepare($sql);

            if ($picture_uploaded) {
                $stmt->bind_param("ssssss", $name, $expertise, $phone, $email, $picture);
            } else {
                $stmt->bind_param("sssss", $name, $expertise, $phone, $email);
            }

            if ($stmt->execute() === false) {
                error_log("Statement failed to execute (add): " . $stmt->error);
            }
            $stmt->close();
            header("Location: stylist.php");
            exit();
        } catch (Exception $e) {
            error_log("Exception occurred (add): " . $e->getMessage());
        }
    }
}

// Update Stylist
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_stylist'])) {
    $id = intval($_POST['id']);
    $name = htmlspecialchars($_POST['stylist_name'], ENT_QUOTES, 'UTF-8');
    $expertise = htmlspecialchars($_POST['expertise'], ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Handle picture upload for edit
    $picture = null;
    $picture_uploaded = false;
    if (isset($_FILES['edit_picture']) && $_FILES['edit_picture']['error'] == 0) {
        if ($_FILES['edit_picture']['size'] > 0) {
            $picture = file_get_contents($_FILES['edit_picture']['tmp_name']);
            if ($picture === false) {
                error_log("Failed to read picture file (edit): " . $_FILES['edit_picture']['tmp_name']);
            } else {
                $picture_uploaded = true;
            }
        } else {
            error_log("The uploaded picture is empty (edit).");
        }
    } else {
        error_log("Error uploading picture (edit): " . $_FILES['edit_picture']['error']);
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            // Only update the picture if a new one is uploaded
            if ($picture_uploaded) {
                // Picture is being updated, so include the new image in the query
                $sql = "UPDATE stylists SET stylist_name = ?, expertise = ?, phone = ?, email = ?, picture = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssi", $name, $expertise, $phone, $email, $picture, $id);
            } else {
                // No picture is being uploaded, so do not change the picture field
                $sql = "UPDATE stylists SET stylist_name = ?, expertise = ?, phone = ?, email = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $name, $expertise, $phone, $email, $id);
            }

            if ($stmt->execute() === false) {
                error_log("Statement failed to execute (edit): " . $stmt->error);
            }

            $stmt->close();
            header("Location: stylist.php");
            exit();
        } catch (Exception $e) {
            error_log("Exception occurred (edit): " . $e->getMessage());
        }
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
            <th>Picture</th>
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
                <td>
                    <?php if (!empty($row['picture'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($row['picture']); ?>"
                             alt="<?= htmlspecialchars($row['stylist_name']); ?>" width="50" height="50"
                             style="border-radius: 50%;">
                    <?php else: ?>
                        <img src="default_avatar.png" alt="Default" width="50" height="50"
                             style="border-radius: 50%;">
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['stylist_name']); ?></td>
                <td><?= htmlspecialchars($row['expertise']); ?></td>
                <td><?= htmlspecialchars($row['phone']); ?></td>
                <td><?= htmlspecialchars($row['email']); ?></td>
                <td>
                    <button class="edit-btn"
                            onclick="openEditModal(<?= $row['id']; ?>, '<?= htmlspecialchars($row['stylist_name'], ENT_QUOTES); ?>', '<?= htmlspecialchars($row['expertise'], ENT_QUOTES); ?>', '<?= htmlspecialchars($row['phone'], ENT_QUOTES); ?>', '<?= htmlspecialchars($row['email'], ENT_QUOTES); ?>')">
                        Edit
                    </button>
                    <form method="POST" action="stylist.php" style="display:inline;" id="deleteForm_<?= $row['id']; ?>">
                        <input type="hidden" name="delete_id" value="<?= $row['id']; ?>">
                        <button type="button" class="delete-btn" onclick="deleteStylist(<?= $row['id']; ?>)">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Stylist Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddModal()">&times;</span>
        <center><h2>Add Stylist</h2></center>
        <form method="POST" action="stylist.php" enctype="multipart/form-data">
            <label for="stylist_name">Full Name:</label>
            <input type="text" name="stylist_name" id="stylist_name" required>
            <label for="expertise">Expertise:</label>
            <input type="text" name="expertise" id="expertise" required>
            <label for="phone">Phone:</label>
            <input type="text" name="phone" id="phone" required>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
            <label for="picture">Profile Picture:</label>
            <input type="file" name="picture" id="picture" accept="image/*">
            <button type="submit" name="add_stylist">Add Stylist</button>
        </form>
    </div>
</div>

<!-- Edit Stylist Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <center><h2>Edit Stylist</h2></center>
        <form method="POST" action="stylist.php" enctype="multipart/form-data">
            <input type="hidden" name="id" id="edit_id">
            <label for="edit_name">Full Name:</label>
            <input type="text" name="stylist_name" id="edit_name" required>
            <label for="edit_expertise">Expertise:</label>
            <input type="text" name="expertise" id="edit_expertise" required>
            <label for="edit_phone">Phone:</label>
            <input type="text" name="phone" id="edit_phone" required>
            <label for="edit_email">Email:</label>
            <input type="email" name="email" id="edit_email" required>
            <label for="edit_picture">Update Profile Picture:</label>
            <input type="file" name="edit_picture" id="edit_picture" accept="image/*">
            <button type="submit" name="edit_stylist">Update</button>
        </form>
    </div>
</div>

<script src="stylist.js"></script>
</body>
</html>
