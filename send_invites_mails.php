<?php
include("./_include/core/main_start.php");
global $g;
set_time_limit(0);
$group_users = json_decode($_POST['accounting']);
$ticket_id = $_POST['ticket_id'];
$ticket_name = $_POST['ticket_name'];
$user_email=$_POST['user_email'];
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
	$sql = "select * from user where mail='".$user_email."'";
	$result = $conn->query($sql);
	$user_id;
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$user_id = $row["user_id"];
		}
	}
	$send_users_id = array();	
	foreach($group_users as $user) 
	{
		$user_email = $user->email;
		$sql = "select * from user where mail='".$user_email."'";
		$result = $conn->query($sql);		
		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				$send_user_id = $row["user_id"];
				$u = array("user_id"=>$row["user_id"],"name"=>$row["name"],"email"=>$row["mail"]);
				array_push($send_users_id,$u);
				$date = date('Y-m-d H:i:s');
				$strtime = strtotime($date);
				$sql = "INSERT INTO mail_msg SET ".
                    " user_id=".$send_user_id.
                    ", user_from=".$user_id.
                    ", user_to=".$send_user_id.
                    ", folder=1".
                    ", new='N'".
					", subject='".$ticket_name."'".
                    ", text='".$ticket_name."'".
					", date_sent='".$strtime."'".
					", receiver_read='N'";
				$conn->query($sql);
			}
		}
	}
	$no = 0;
		
} catch (Exception $e) {
   $no = -1;
}
$conn-> close();
echo json_encode(
	[
		"status"=>"success", 
		"total"=>$no
	]
);
