<?php
include("./_include/core/main_start.php");
global $g;
set_time_limit(0);
header('Access-Control-Allow-Origin: *');  
$user_id = $_POST['user_id'];
$user_name = $_POST['user_name'];
$user_mail=$_POST['user_mail'];
$servername = $g['db']['host'];
$dbnanme = $g["db"]["db"];
$username = $g["db"]["user"];
$password = $g["db"]["password"];
// Create connection
$conn = new mysqli($servername, $username, $password, $dbnanme);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}	
try
{
	$sql = "update video_rooms set create_state=0 where user_id=".$user_id;
	$result = $conn->query($sql);
} catch (Exception $e) {
	$no = -1;
}
$conn-> close();
echo json_encode(
	[
		"status"=>"success"
	]
);