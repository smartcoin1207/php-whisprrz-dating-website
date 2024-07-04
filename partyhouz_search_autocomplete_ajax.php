<?php
include("./_include/core/main_start.php");
require_once("./_include/current/partyhouz/tools.php");
require_once("./_include/current/partyhouz/partyhou_image_list.php");

set_time_limit(0);
header('Access-Control-Allow-Origin: *');
$search = $_POST['q'];
$search_type = $_POST['type'];
global $g;
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
			$sql = "SELECT p.partyhou_title AS `name` FROM partyhouz_partyhou p WHERE p.`partyhou_title` LIKE '" . $search . "%'";;
			break;
		default:
			break;
	}
	
	$result = $conn->query($sql);
	
	$userOpt = [];
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$userOpt[] = array("value" => $row['name'], "data" => $row['name']);
		}
	}
	
} catch (Exception $e) {
   var_dump($e);
   die();
}
$conn-> close();
echo json_encode(
	[
		"suggestions"=>$userOpt
	]
);
