<?php
include("./_include/core/main_start.php");
require_once("./_include/current/partyhouz/tools.php");
require_once("./_include/current/partyhouz/partyhou_image_list.php");
global $g;
set_time_limit(0);
header('Access-Control-Allow-Origin: *');
$search = $_POST['q'];
$search_type = $_POST['type'];
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
	$sql = "";
	switch($search_type) {
		case 1:
			$sql = "SELECT * FROM user WHERE `name` LIKE '" . $search . "%'";
			break;
		case 2:
			$sql = "SELECT u.* FROM friends_requests fr
				LEFT JOIN user u ON u.user_id = fr.friend_id
				WHERE fr.user_id='".$user_id."' AND fr.accepted = 1 AND u.name LIKE '%" . $search . "%'
				UNION
				SELECT u.* FROM friends_requests fr
				LEFT JOIN user u ON u.user_id = fr.user_id
				WHERE fr.friend_id='".$user_id."' AND fr.accepted = 1 AND u.name LIKE '%" . $search . "%'";
			break;
		case 3:
			$sql = "SELECT u.* FROM groups_social_subscribers gss 
				LEFT JOIN groups_social gs ON gs.group_id = gss.group_id
				LEFT JOIN user u ON u.user_id = gss.user_id
				WHERE u.user_id <> '" . $user_id . "' AND u.name LIKE '%" . $search . "%'";
			break;
		default:
			break;
	}
	
	$result = $conn->query($sql);
	
	$userOpt = [];
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$userOpt[] = array("value" => $row['name'], "label" => $row['name']);
		}
	}
} catch (Exception $e) {
   die();
}
$conn-> close();
echo json_encode(
	$userOpt
);
