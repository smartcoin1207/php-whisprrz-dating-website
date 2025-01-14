<?php
include("./_include/core/main_start.php");

global $g;

set_time_limit(0);
header('Access-Control-Allow-Origin: *');
$user_id = $_POST['user_id'];
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
	$sql = "SELECT u.name, u.user_id FROM friends_requests fr
			LEFT JOIN user u ON u.user_id = fr.friend_id
			WHERE fr.user_id='".$user_id."' AND fr.accepted = 1
			UNION
			SELECT u.name, u.user_id FROM friends_requests fr
			LEFT JOIN user u ON u.user_id = fr.user_id
			WHERE fr.friend_id='".$user_id."' AND fr.accepted = 1";
	$result = $conn->query($sql);
	$friends = array();
	$no = 0;
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$user_photo = User::getPhotoDefault($row["user_id"], "m");
			$u = array("user_name"=>$row["name"],"user_id"=>$row["user_id"], "user_photo"=>$user_photo);
			$no++;
			array_push($friends,$u);						
		}
	}	
} catch (Exception $e) {
   
}
$conn-> close();
echo json_encode(
	[
		"status"=>"success", 
		"total"=>$no, 
		"friends"=>$friends
	]
);
