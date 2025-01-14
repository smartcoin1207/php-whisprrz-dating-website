<?php
include("./_include/core/main_start.php");
global $g;
set_time_limit(0);

/** popcorn modified 2024-05-26 */
header('Access-Control-Allow-Origin: *');
$user_id = $_POST['user_id'];
$user_name = $_POST['user_name'];
$user_mail=$_POST['user_mail'];
$room_name = $_POST['room_name'];
$room_type = $_POST['room_type'];
$party_date = $_POST['party_date'];
$is_lock = $_POST['is_lock'];
$lock_code = $_POST['lock_code'];
$is_open = $_POST['is_open'];
$is_friends = $_POST['is_friends'];
$is_group = $_POST['is_group'];
$invited_user_ids = $_POST['invited_user_ids'];
$cum_couples = $_POST['cum_couples'];
$cum_females = $_POST['cum_females'];
$cum_males = $_POST['cum_males'];
$cum_transgender = $_POST['cum_transgender'];
$cum_nonbinary = $_POST['cum_nonbinary'];
$cum_everyone = $_POST['cum_everyone'];
$lookin_couples = $_POST['lookin_couples'];
$lookin_females = $_POST['lookin_females'];
$lookin_males = $_POST['lookin_males'];
$lookin_transgender = $_POST['lookin_transgender'];
$lookin_nonbinary = $_POST['lookin_nonbinary'];
$lookin_everyone = $_POST['lookin_everyone'];
$is_saved = $_POST['is_saved'];
$saved_name = $_POST['saved_name'];
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
	$check_sql = "SELECT * FROM video_rooms WHERE room_name='".$room_name."'";
	$result = $conn->query($check_sql);
	if ($result->num_rows > 0) {
		$status = "falied";
		$message = "Your room name exists! Please enter the other name.";
	} else {
		$insert_sql = "INSERT INTO video_rooms (
			user_id, 
			user_mail, 
			user_name, 
			room_name, 
			room_type, 
			party_date, 
			is_lock, 
			lock_code, 
			is_open, 
			is_friends, 
			is_group,
			invited_user_ids, 
			cum_couples, 
			cum_females, 
			cum_males, 
			cum_transgender, 
			cum_nonbinary, 
			cum_everyone, 
			lookin_couples, 
			lookin_females, 
			lookin_males,
			lookin_transgender,
			lookin_nonbinary, 
			lookin_everyone,
			is_saved, 
			saved_name)
		VALUES (
			'".$user_id."',
			'".$user_mail."',
			'".$user_name."',
			'".$room_name."',
			'".$room_type."',
			'".$party_date."',
			'".$is_lock."',
			'".$lock_code."',
			'".$is_open."',
			'".$is_friends."',
			'".$is_group."',
			'".$invited_user_ids."',
			'".$cum_couples."',
			'".$cum_females."',
			'".$cum_males."',
			'".$cum_transgender."',
			'".$cum_nonbinary."',
			'".$cum_everyone."',
			'".$lookin_couples."',
			'".$lookin_females."',
			'".$lookin_males."',
			'".$lookin_transgender."',
			'".$lookin_nonbinary."',
			'".$lookin_everyone."',
			'".$is_saved."',
			'".$saved_name."'
			)";
		$conn->query($insert_sql);
		$status = "success";
		$message = "Successfully created!";
	}
	
} catch (Exception $e) {
	$no = -1;
	$enter_allow = false;
}
$conn-> close();
echo json_encode(
	[
		"status"=>$status, 
		"message"=>$message
	]
);
