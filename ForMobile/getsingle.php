<?php
// Database connection parameters

include "config.php";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set header to return JSON response
header('Content-Type: application/json');

// Check if email was sent (using email to identify user)
if(isset($_GET['email'])) {
    $email = $_GET['email'];
    
    // Prepare statement to prevent SQL injection
    // Only select the fields we need (excluding password)
    $stmt = $conn->prepare("SELECT customer_name, email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        // Fetch user data
        $user = $result->fetch_assoc();
        
        // Return success response with user data
        echo json_encode(array(
            "success" => true,
            "message" => "User found",
            "user" => $user
        ));
    } else {
        // User not found
        echo json_encode(array(
            "success" => false,
            "message" => "User not found"
        ));
    }
    
    $stmt->close();
} else {
    // No email provided
    echo json_encode(array(
        "success" => false,
        "message" => "No email provided"
    ));
}

// Close connection
$conn->close();
?>