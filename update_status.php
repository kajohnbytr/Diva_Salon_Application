<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['appointment_id']) && isset($_POST['status'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $status = htmlspecialchars($_POST['status'], ENT_QUOTES, 'UTF-8');

    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $appointment_id);
    $stmt->execute();
}

// Redirect back to the appointments page
header("Location: appointments.php");
exit();
?>
