<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['appointment_id']) && isset($_POST['status'])) {
    $appointment_id = intval($_POST['appointment_id']); // Ensure it's an integer
    $status = trim($_POST['status']); // Trim whitespace

    // Validate status to prevent SQL injection (only allow predefined values)
    $allowed_statuses = ["Pending", "Approved", "Rejected"];
    if (!in_array($status, $allowed_statuses)) {
        die("Invalid status value.");
    }

    // Prepare and execute the update query
    if ($stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?")) {
        $stmt->bind_param("si", $status, $appointment_id);
        
        if ($stmt->execute()) {
            // Close the statement and connection
            $stmt->close();
            $conn->close();

            // Set session message for success
            $_SESSION['message'] = "Appointment status updated successfully.";

            // Redirect to the correct page based on the updated status
            switch ($status) {
                case "Pending":
                    header("Location: pending.php");
                    break;
                case "Approved":
                    header("Location: approved.php");
                    break;
                case "Rejected":
                    header("Location: rejected.php");
                    break;
            }
            exit(); // Always call exit after a redirect
        } else {
            // Error handling in case the query fails
            $_SESSION['message'] = "Error updating appointment status. Please try again.";
            header("Location: pending.php"); // Redirect back to pending page with error message
            exit();
        }
    } else {
        // Error handling if prepare fails
        $_SESSION['message'] = "Error preparing the update query. Please try again.";
        header("Location: pending.php");
        exit();
    }
} else {
    die("Invalid request."); // In case no POST data or invalid data is received
}
?>
