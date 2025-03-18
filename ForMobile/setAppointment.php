<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection
include "config.php";

$conn = new mysqli($servername, $username, $password, $database);

// Check for connection error
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(array("success" => false, "message" => "Database connection failed: " . $conn->connect_error));
    exit();
}

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Check if JSON is valid
if (!$data) {
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "Invalid JSON received."));
    exit();
}

// Ensure required fields are present
if (
    !empty($data->customer_id) &&
    !empty($data->stylist_id) &&
    !empty($data->appointment_date) &&
    !empty($data->appointment_time) &&
    !empty($data->service)
) {
    // Sanitize input
    $customer_id = htmlspecialchars(strip_tags($data->customer_id));
    $stylist_id = htmlspecialchars(strip_tags($data->stylist_id));
    $appointment_date = htmlspecialchars(strip_tags($data->appointment_date));
    $appointment_time = htmlspecialchars(strip_tags($data->appointment_time));
    $service = htmlspecialchars(strip_tags($data->service));
    
    // First, verify that the customer_id exists in the users table
    $verify_query = "SELECT id FROM users WHERE id = ?";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bind_param("i", $customer_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows == 0) {
        // The customer_id doesn't exist in the users table
        http_response_code(400);
        echo json_encode(array(
            "success" => false, 
            "message" => "Invalid customer ID. Please log in again.", 
            "debug_info" => "Customer ID $customer_id not found in users table"
        ));
        $verify_stmt->close();
        $conn->close();
        exit();
    }
    $verify_stmt->close();
    
    // Next, verify that the stylist_id exists (assuming you have a stylists table)
    $verify_stylist_query = "SELECT id FROM stylists WHERE id = ?";
    $verify_stylist_stmt = $conn->prepare($verify_stylist_query);
    $verify_stylist_stmt->bind_param("i", $stylist_id);
    $verify_stylist_stmt->execute();
    $verify_stylist_result = $verify_stylist_stmt->get_result();
    
    if ($verify_stylist_result->num_rows == 0) {
        // The stylist_id doesn't exist
        http_response_code(400);
        echo json_encode(array(
            "success" => false, 
            "message" => "Invalid stylist selected.", 
            "debug_info" => "Stylist ID $stylist_id not found"
        ));
        $verify_stylist_stmt->close();
        $conn->close();
        exit();
    }
    $verify_stylist_stmt->close();
    
    // Prepare the SQL statement for insertion
    $query = "INSERT INTO appointments (customer_id, stylist_id, appointment_date, appointment_time, service, status) 
              VALUES (?, ?, ?, ?, ?, 'Pending')";

    $stmt = $conn->prepare($query);

    // Check if statement prepared successfully
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(array("success" => false, "message" => "Database error: " . $conn->error));
        exit();
    }

    // Bind parameters
    $stmt->bind_param("iisss", $customer_id, $stylist_id, $appointment_date, $appointment_time, $service);

    // Execute query
    if ($stmt->execute()) {
        $appointment_id = $conn->insert_id; // Get last inserted ID

        // Fetch the newly created appointment
        $query = "SELECT * FROM appointments WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $appointment = $result->fetch_assoc();

        // Set response code - 201 Created
        http_response_code(201);

        // Send JSON response
        echo json_encode(array(
            "success" => true,
            "message" => "Appointment was created successfully.",
            "appointment" => $appointment
        ));
    } else {
        // Set response code - 503 Service Unavailable
        http_response_code(503);
        echo json_encode(array(
            "success" => false, 
            "message" => "Unable to create appointment.",
            "debug_info" => $conn->error
        ));
    }
} else {
    // Set response code - 400 Bad Request
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "Data is incomplete."));
}

// Close connection
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>