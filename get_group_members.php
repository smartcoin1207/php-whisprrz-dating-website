<?php
include("./_include/core/main_start.php");

global $g;

set_time_limit(0);
$user_email=$_GET['user_email'];

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
	$sql ="select uu.* from (SELECT ggm.* FROM `groups_group_member` AS ggm INNER JOIN groups_group AS gg ON gg.group_id=ggm.group_id INNER JOIN user AS u ON u.user_id=gg.user_id
			where u.mail='".$user_email."') group_u	LEFT JOIN user as uu ON uu.user_id = group_u.user_id";
	$result = $conn->query($sql);
	$users = array();
	$no = 0;
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			if($row["mail"]!=$user_email){
				$u = array("name"=>$row["name"],"email"=>$row["mail"]);
				$no++;
				array_push($users,$u);	
			}			
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
