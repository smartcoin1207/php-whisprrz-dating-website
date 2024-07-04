<?php
// Include necessary files and establish a database connection here if not already done

include("./_include/core/main_start.php");
header('Access-Control-Allow-Origin: *');

// Assuming you have a database connection established
$db_host = 'localhost';
$db_user = 'eric_cui';
$db_pass = 'nnsscc123456!#';
$db_name = 'eric_whisprrz_new';

// Create a new connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assuming you have a way to identify the currently logged-in user (replace with your actual user identification method)
$userId = $g_user['user_id']; // Replace with your session variable or user ID retrieval method

// Retrieve the JSON data sent from the client
$data = json_decode(file_get_contents("php://input"));

$latitude = $data->latitude; // Replace with the actual variable name
$longitude = $data->longitude; // Replace with the actual variable name

// Replace 'user' with your database table name and use placeholders in the query
$sql = "UPDATE user SET geo_position_lat = ?, geo_position_long = ? WHERE user_id = ?";

// Prepare the statement
$stmt = $conn->prepare($sql);

if ($stmt) {
    // Bind parameters and execute the query
    $stmt->bind_param("sss", $latitude, $longitude, $userId);

    if ($stmt->execute()) {
        echo "Location data updated successfully for user " . $userId;
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
} else {
    echo "Error preparing the statement: " . $conn->error;
}

// Close the database connection
$conn->close();
?>
