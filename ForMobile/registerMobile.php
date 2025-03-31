<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'config.php';

$response = ["success" => false, "message" => "Invalid request"];

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $rawData = file_get_contents("php://input");
    
    // Log the raw input for debugging
    error_log("Raw input: " . $rawData);

    // Try to decode the JSON data
    $input = json_decode($rawData, true);
    
    // Check if JSON decoding was successful
    if ($input === null) {
        $response["message"] = "Invalid JSON data";
        echo json_encode($response);
        exit;
    }

    // Print the input for debugging
    error_log("Decoded input: " . print_r($input, true));

    // Check for required fields with more flexible key names
    $requiredFields = ['customer_name', 'email', 'phone', 'password'];
    $missingFields = [];

    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        $response["message"] = "Missing fields: " . implode(", ", $missingFields);
        echo json_encode($response);
        exit;
    }

    // Sanitize and validate inputs
    $fullName = trim($input['customer_name']);
    $email = trim($input['email']);
    $phone = trim($input['phone']);
    $password = trim($input['password']);

    // Additional validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["message"] = "Invalid email format";
        echo json_encode($response);
        exit;
    }

    // Check if email already exists
    $emailCheckStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $emailCheckStmt->bind_param("s", $email);
    $emailCheckStmt->execute();
    $emailCheckStmt->store_result();

    if ($emailCheckStmt->num_rows > 0) {
        $response["message"] = "Email already in use";
        $emailCheckStmt->close();
        echo json_encode($response);
        exit;
    }
    $emailCheckStmt->close();

    // Prepare and execute the insert statement
    $stmt = $conn->prepare("INSERT INTO users (customer_name, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $fullName, $email, $phone, $password);
    
    if ($stmt->execute()) {
        $response = [
            "success" => true, 
            "message" => "Registration successful!",
            "userId" => $stmt->insert_id
        ];
    } else {
        $response["message"] = "Registration failed: " . $stmt->error;
    }

    $stmt->close();
}

// Return JSON response
echo json_encode($response);
exit;
?>