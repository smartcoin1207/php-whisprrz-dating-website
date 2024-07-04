<?php
include("./_include/core/main_start.php");

global $g;

set_time_limit(0);
header('Access-Control-Allow-Origin: *');  
$user_id=$_POST['user_id'];
$user_mail=$_POST['user_mail'];
$user_name=$_POST['user_name'];
$room_name=$_POST['room_name'];

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
	$sql = "select * from video_rooms where room_name='".$room_name."' and create_state=1 and online_state=1";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$create_user_id = $row["user_id"];
			$user_name = $row["user_name"];
			$user_mail = $row["user_mail"];
			$room_name = $row["room_name"];
			$room_type = $row["room_type"];
			$room_pass = $row["room_pass"];
			$group_id = $row["group_id"];
		}
	}else{
		$user_id = 0;			
		$room_type = "";
		$room_pass = "";
	}		
	$enter_allow = false;
	if($create_user_id == $user_id){		
		$enter_allow = true;		
	}else{
		if($room_type==2){
			$a_sql = "select * from friends_requests where (user_id=".$user_id." and friend_id=".$create_user_id.") or (friend_id=".$user_id." and user_id=".$create_user_id.")";
			$a_result = $conn->query($a_sql);
			if ($a_result->num_rows > 0) {
				$enter_allow = true;
			}		
		}else if($room_type==4){
			$b_sql = "select * from groups_group_member where user_id=".$user_id." and group_id=".$group_id;
			$b_result = $conn->query($b_sql);
			if ($b_result->num_rows > 0) {
				$enter_allow = true;
			}
			/*
			$a_sql = "select * from groups_group_member where user_id=".$user_id;
			$a_result = $conn->query($a_sql);
			if ($a_result->num_rows > 0) {
				while($a_row = $a_result->fetch_assoc()) {
					$group_id = $row['group_id'];
					$b_sql = "select * from groups_group_member where user_id=".$create_user_id." and group_id=".$group_id;
					$b_result = $conn->query($b_sql);
					if ($b_result->num_rows > 0) {
						$enter_allow = true;
						break;
					}
				}
			}
			*/
		}
	}

} catch (Exception $e) {
   
}
$conn-> close();
echo json_encode(
	[
		"status"=>"success", 
		"total"=>$no, 
		"user_name"=>$user_name,
		"user_mail"=>$user_mail,
		"enter_allow"=>$enter_allow,
		"room_name"=>$room_name,
		"room_pass"=>$room_pass,
		"room_type"=>$room_type
	]
);
