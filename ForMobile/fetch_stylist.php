<?php

include "config.php";

$conn = new mysqli($servername, $username, $password, $database);



// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Connection failed: ' . $conn->connect_error
    ]);
    exit();
}

// Set the response header to JSON
header('Content-Type: application/json');

// Query to get stylists
$sql = "SELECT id, stylist_name, picture FROM stylists";
$result = $conn->query($sql);

$stylists = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $imageUrl = null;
        
        // Check if picture data exists
        if ($row['picture'] !== null && !empty($row['picture'])) {
            // Convert BLOB to base64
            $imageUrl = 'data:image/jpeg;base64,' . base64_encode($row['picture']);
        }
        
        // Create stylist with field names matching the Kotlin model's @SerializedName annotations
        $stylist = [
            'id' => $row['id'],
            'name' => $row['stylist_name'],
            'specialty' => '',
            'imageUrl' => $imageUrl,
            'isSelected' => false
        ];
        
        $stylists[] = $stylist;
    }
    
    echo json_encode([
        'status' => 'success',
        'Stylist' => $stylists  // Change 'data' to 'Stylist' to match the Kotlin model
    ]);
} else {
    echo json_encode([
        'status' => 'success',
        'Stylist' => []
    ]);
}

$conn->close();
?>