<?php
include("./_include/core/main_start.php");

global $g;
set_time_limit(0);
header('Access-Control-Allow-Origin: *');
$user_id=$_POST['user_id'];
$group_id=$_POST['group_id'];
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
	$sql = "SELECT u.name, u.user_id FROM groups_social_subscribers gss 
			LEFT JOIN groups_social gs ON gs.group_id = gss.group_id
			LEFT JOIN user u ON u.user_id = gss.user_id
			WHERE gs.group_id='".$group_id."' AND u.user_id<>'".$user_id."'" ;
	$result = $conn->query($sql);
	$users = array();
	$no = 0;
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$user_photo = User::getPhotoDefault($row["user_id"], "m");
			$u = array("user_name"=>$row["name"],"user_id"=>$row["user_id"], "user_photo"=>$user_photo);
			$no++;
			array_push($users,$u);						
		}
	}	
} catch (Exception $e) {
   
}
$conn-> close();
echo json_encode(
	[
		"status"=>"success", 
		"total"=>$no, 
		"users"=>$users
	]
);
