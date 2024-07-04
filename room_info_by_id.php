<?php

include("./_include/core/main_start.php");

global $g;

set_time_limit(0);
header('Access-Control-Allow-Origin: *');
$room_id = $_POST['room_id'];
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

/** popcorn modified 2024-05-26 */
try {
	$sql = "SELECT partyhou_id,
				user_id, 
				partyhou_title,
				user_mail,
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
				saved_name,
				is_saved,
				category_id,
				partyhou_datetime
			FROM partyhouz_partyhou
			WHERE partyhou_id = '" . $room_id . "';";
	$result = $conn->query($sql);
	$no = 0;
	if ($result->num_rows > 0) {
		$no = 1;
		$row = $result->fetch_assoc();
		$room = array("partyhou_id" => $row["partyhou_id"], 
			"user_id" => $row["user_id"],
			"partyhou_title" => $row["partyhou_title"],
			"user_mail" => $row["user_mail"],
			"is_lock" => $row["is_lock"],
			"lock_code" => $row["lock_code"],
			"is_open" => $row["is_open"],
			"is_friends" => $row["is_friends"],
			"is_group" => $row["is_group"],
			"invited_user_ids" => $row["invited_user_ids"],
			"cum_couples" => $row["cum_couples"],
			"cum_females" => $row["cum_females"],
			"cum_males" => $row["cum_males"],
			"cum_transgender" => $row["cum_transgender"],
			"cum_nonbinary" => $row["cum_nonbinary"],
			"cum_everyone" => $row["cum_everyone"],
			"lookin_couples" => $row["lookin_couples"],
			"lookin_females" => $row["lookin_females"],
			"lookin_males" => $row["lookin_males"],
			"lookin_transgender" => $row["lookin_transgender"],
			"lookin_nonbinary" => $row["lookin_nonbinary"],
			"lookin_everyone" => $row["lookin_everyone"],
			"saved_name" => $row["saved_name"],
			"is_saved" => $row["is_saved"],
			"category_id" => $row["category_id"],
			"partyhou_datetime" => $row["partyhou_datetime"]
		);
		$sql = "SELECT u.user_id, u.name
				FROM user u
				WHERE FIND_IN_SET(u.user_id, '".$room['invited_user_ids']."') > 0;";
		$result = $conn->query($sql);
		$invited_users = array();
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$invited_user = array("user_id" => $row["user_id"], "user_name" => $row["name"]);
				array_push($invited_users, $invited_user);
			}
		}
	} else {
		$room = array();
		$invited_users = array();
	}
} catch (Exception $e) {
	var_dump($e);
	die();
}
$conn->close();
echo json_encode(
	[
		"status" => "success",
		"total" => $no,
		"room" => $room,
		"invited_users" => $invited_users
	]
);