<?php
include("./_include/core/main_start.php");

global $g;

set_time_limit(0);
header('Access-Control-Allow-Origin: *');
$m_user_id=$_POST['user_id'];
$m_user_mail=$_POST['user_mail'];
$m_user_name=$_POST['user_name'];
$page = $_POST["page"];
$sort = $_POST["sort"];
$search = $_POST["search"];
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
	$search_sql = "";
	if($search!=""){
		$search_sql =" and room_name like '%".$search."%' ";
	}else{
		$search_sql ="";
	}
	$sql = "select * from video_rooms where create_state=1 and online_state=1 ".$search_sql." order by id ".$sort;
	$result = $conn->query($sql);
	$total = $result->num_rows;
	$start = ($page-1)*10;	
	$sql = "select * from video_rooms where create_state=1 and online_state=1 ".$search_sql." order by id ".$sort." limit ".$start.", 10";
	$result = $conn->query($sql);
	$no = 0;
	$group_id=0;
	$rooms = array();
	if ($result!=null && $result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$user_id = $row["user_id"];
			$user_name = $row["user_name"];
			$user_mail = $row["user_mail"];
			$room_name = $row["room_name"];
			$room_type = $row["room_type"];
			$room_pass = $row["room_pass"];
			$enter_allow = false;
			if($user_id == $m_user_id){		
				$enter_allow = true;		
			}else{
				if($room_type==2){
					$a_sql = "select * from friends_requests where (user_id=".$m_user_id." and friend_id=".$user_id.") or (friend_id=".$m_user_id." and user_id=".$user_id.")";
					$a_result = $conn->query($a_sql);
					if ($a_result->num_rows > 0) {
						$enter_allow = true;
					}		
				}else if($room_type==4){
					$a_sql = "select * from groups_group_member where user_id=".$m_user_id;
					$a_result = $conn->query($a_sql);
					if ($a_result->num_rows > 0) {
						while($a_row = $a_result->fetch_assoc()) {
							$group_id = $row['group_id'];
							$b_sql = "select * from groups_group_member where user_id=".$user_id." and group_id=".$group_id;
							$b_result = $conn->query($b_sql);
							if ($b_result->num_rows > 0) {
								$enter_allow = true;
								break;
							}
						}
					}
				}
			}
			$u = array("user_id"=>$user_id,"user_name"=>$row["user_name"],"user_mail"=>$row["user_mail"],"room_name"=>$room_name,"room_type"=>$room_type,"room_pass"=>$room_pass,"group_id"=>$group_id,"create_date"=>$row["create_date"],"enter_allow"=>$enter_allow);
			$no++;
			array_push($rooms,$u);
		}
	}else{
		$user_id = 0;			
		$room_type = "";
		$room_pass = "";
	}		
} catch (Exception $e) {
   
}
$conn-> close();
echo json_encode(
	[
		"status"=>"success", 
		"no"=>$no,
		"total"=>$total, 
		"search"=>$search,
		"rooms"=>$rooms
	]
);
