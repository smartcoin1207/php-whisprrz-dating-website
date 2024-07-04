<?php
include("./_include/core/main_start.php");
global $g;
set_time_limit(0);
header('Access-Control-Allow-Origin: *');  
$user_id = $_POST['user_id'];
$user_name = $_POST['user_name'];
$user_mail=$_POST['user_mail'];
$room_name = $_POST['room_name'];
$room_type = $_POST['room_type'];
$room_pass = $_POST['room_pass'];
$group_id = $_POST['group_id'];
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
			$room_type =  $row["room_type"];
			$room_pass =  $row["room_pass"];
			$group_id = $row["group_id"];
			$room_id = $row["id"];
		}
		$u_sql = "select * from video_rooms where room_name='".$room_name."' and user_mail='".$user_mail."' and online_state=1";
		$u_result = $conn->query($u_sql);
		if ($u_result->num_rows > 0) {
			$no = 3;
		}else{
			$n_sql = "INSERT INTO video_rooms SET ".
                    " user_id=".$user_id.
					", user_mail='".$user_mail."'".
                    ", user_name='".$user_name."'".
                    ", room_name='".$room_name."'".
					", room_pass='".$room_pass."'".
                    ", room_type=".$room_type.
					", create_state=0";
			$conn->query($n_sql);
			$no = 2;	
		}	
		$room_id = 0;
		$room_sql = "SELECT MAX( id ) AS max FROM video_rooms";
		$room_result = $conn->query($room_sql);
		if ($room_result->num_rows > 0) {
			while($room_row = $room_result->fetch_assoc()) {
				$room_id = $room_row["max"];
			}
		}
		$date = date('Y-m-d H:i:s');
		$r_sql = "INSERT INTO wall SET ".
                    " date='".$date."'".
					", user_id=".$user_id.
					", section='enter_room'".
					", parent_user_id=".$user_id.
                    ", item_id=".$room_id.",params_section='',params='',comments=0,comments_item=0,likes=0,hide_from_user=0,comment_user_id=0,site_section_item_id=0,last_action_like='2020-10-30 20:30:04',last_action_comment='2020-10-30 20:30:04',last_action_comment_like='2020-10-30 20:30:04',shares_count=0,last_action_shares='2020-10-30 20:30:00'";
		$conn->query($r_sql);
		if($room_type==4 && $group_id>0){
			$msg = ' joined into https://whisprrz.com:8443/'.$room_name.' party-house.';
			$r_sql = "INSERT INTO groups_group_comment SET ".
	                    " group_id=".$group_id.
						", user_id=".$user_id.
						", comment_text='".$msg."'".
						", created_at=".$date;								
			$conn->query($r_sql);	
		}
	}else{
		$sql = "INSERT INTO video_rooms SET ".
                    " user_id=".$user_id.
					", user_mail='".$user_mail."'".
                    ", user_name='".$user_name."'".
                    ", room_name='".$room_name."'".
					", room_pass='".$room_pass."'".
                    ", room_type=".$room_type.
					", group_id=".$group_id.
					", create_state=1";
		$conn->query($sql);
		$create_user_id = $user_id;
		$no = 1;
		$room_id = 0;
		$room_sql = "SELECT MAX( id ) AS max FROM video_rooms";
		$room_result = $conn->query($room_sql);
		if ($room_result->num_rows > 0) {
			while($room_row = $room_result->fetch_assoc()) {
				$room_id = $room_row["max"];
			}
		}
		$date = date('Y-m-d H:i:s');
		$sql = "INSERT INTO wall SET ".
                    " date='".$date."'".
					", user_id=".$user_id.
					", section='create_room'".
					", parent_user_id=".$user_id.
                    ", item_id=".$room_id.",params_section='',params='',comments=0,comments_item=0,likes=0,hide_from_user=0,comment_user_id=0,site_section_item_id=0,last_action_like='2020-10-30 20:30:04',last_action_comment='2020-10-30 20:30:04',last_action_comment_like='2020-10-30 20:30:04',shares_count=0,last_action_shares='2020-10-30 20:30:00'";
		$conn->query($sql);	
		if($room_type==4 && $group_id>0){
			$msg = ' created https://whisprrz.com:8443/'.$room_name.' party-house.';
			$r_sql = "INSERT INTO groups_group_comment SET ".
	                    " group_id=".$group_id.
						", user_id=".$user_id.
						", comment_text='".$msg."'".
						", created_at='".$date."'";						
			$conn->query($r_sql);	
		}			
	}	
	$enter_allow = false;
	if($create_user_id == $user_id){
		$enter_allow = true;
	}else{
		$a_sql = "select * from friends_requests where (user_id=".$user_id." and friend_id=".$create_user_id.") or (friend_id=".$user_id." and user_id=".$create_user_id.")";
		$a_result = $conn->query($a_sql);
		if ($a_result->num_rows > 0) {
			$enter_allow = true;
		}
	}
	
} catch (Exception $e) {
	$no = -1;
	$enter_allow = false;
}
$conn-> close();
echo json_encode(
	[
		"status"=>"success", 
		"total"=>$no,
		"room_pass"=>$room_pass,
		"user_id"=>$create_user_id,
		"enter_allow"=>$enter_allow,
		"room_type"=>$room_type
	]
);
