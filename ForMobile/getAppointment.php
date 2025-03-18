<?php

include "config.php";

// Check if user_id is provided
if (!isset($_GET['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ]);
    exit;
}

$userId = $_GET['user_id'];

// Sanitize input
$userId = mysqli_real_escape_string($conn, $userId);

// Prepare the SQL query
// Join with stylists table to get stylist name
$sql = "SELECT a.*, s.stylist_name as stylist_name, s.expertise as stylist_specialty 
        FROM appointments a 
        JOIN stylists s ON a.stylist_id = s.id 
        WHERE a.customer_id = ? 
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";

// Prepare statement
if ($stmt = mysqli_prepare($conn, $sql)) {
    // Bind parameters
    mysqli_stmt_bind_param($stmt, "i", $userId);
    
    // Execute the statement
    mysqli_stmt_execute($stmt);
    
    // Get result
    $result = mysqli_stmt_get_result($stmt);
    
    // Check if any appointments were found
    if (mysqli_num_rows($result) > 0) {
        $appointments = [];
        
        // Fetch the data
        while ($row = mysqli_fetch_assoc($result)) {
            // Convert the appointment date to proper format
            $appointmentDate = date('Y-m-d', strtotime($row['appointment_date']));
            
            // Format appointment time
            $appointmentTime = date('H:i:s', strtotime($row['appointment_time']));
            
            $appointments[] = [
                'id' => (int)$row['id'],
                'customer_id' => (int)$row['customer_id'],
                'stylist_id' => $row['stylist_id'], // Keep as string to match your model
                'stylist_name' => $row['stylist_name'],
                'stylist_specialty' => $row['stylist_specialty'],
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime,
                'service' => $row['service'],
                'status' => $row['status'],
                'created_at' => $row['created_at']
            ];
        }
        
        // Return success response with appointments
        echo json_encode([
            'success' => true,
            'message' => 'Appointments retrieved successfully',
            'appointments' => $appointments
        ]);
    } else {
        // No appointments found
        echo json_encode([
            'success' => true,
            'message' => 'No appointments found for this user',
            'appointments' => []
        ]);
    }
    
    // Close statement
    mysqli_stmt_close($stmt);
} else {
    // Error with prepared statement
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . mysqli_error($conn)
    ]);
}

// Close connection
mysqli_close($conn);
?>