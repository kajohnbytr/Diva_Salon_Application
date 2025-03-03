<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'config.php';

$response = ["success" => false, "message" => "Invalid request"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    if (
        isset($input['customer_name']) && 
        isset($input['email']) && 
        isset($input['phone']) && 
        isset($input['password'])
    ) {
        $fullName = trim($input['customer_name']);
        $email = trim($input['email']);
        $phone = trim($input['phone']);
        $password = trim($input['password']);

        // Insert into database (storing password as plain text as per your request)
        $stmt = $conn->prepare("INSERT INTO users (customer_name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fullName, $email, $phone, $password);
        
        if ($stmt->execute()) {
            $response = ["success" => true, "message" => "Registration successful!"];
        } else {
            $response["message"] = "Registration failed: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $response["message"] = "All fields are required";
    }
}

// Return JSON response
echo json_encode($response);
?>
