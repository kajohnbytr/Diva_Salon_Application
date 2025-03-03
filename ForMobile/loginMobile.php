<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include 'config.php';

$response = ["success" => false, "message" => "Invalid request"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get raw POST data
    $input = json_decode(file_get_contents("php://input"), true);

    if (isset($input['email']) && isset($input['password'])) {
        $email = trim($input['email']);
        $password = trim($input['password']);

        // Prepare and execute SQL statement
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $db_password);
            $stmt->fetch();

            // Direct password comparison (No hashing)
            if ($password === $db_password) {
                $response = ["success" => true, "message" => "Login successful!", "user_id" => $id];
            } else {
                $response["message"] = "Invalid credentials";
            }
        } else {
            $response["message"] = "User not found";
        }

        $stmt->close();
    } else {
        $response["message"] = "Email and password are required";
    }
}

// Return JSON response
echo json_encode($response);
?>
