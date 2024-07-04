<?php
include("./_include/core/main_start.php");

global $g;
set_time_limit(0);
header('Access-Control-Allow-Origin: *');
$user_id=$_POST['user_id'];
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
	$sql = "SELECT gg.group_id, gg.title FROM groups_social_subscribers ggm 
			LEFT JOIN groups_social gg ON gg.group_id = ggm.group_id
			WHERE ggm.user_id='".$user_id."'";

	$result = $conn->query($sql);
	$groups = array();
	$no = 0;
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$u = array("group_name"=>$row["title"],"group_id"=>$row["group_id"]);
			$no++;
			array_push($groups,$u);						
		}
	}	
} catch (Exception $e) {
   
}
$conn-> close();
echo json_encode(
	[
		"status"=>"success", 
		"total"=>$no, 
		"groups"=>$groups
	]
);
