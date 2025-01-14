<?php
include("./_include/core/main_start.php");
require_once("./_include/current/partyhouz/tools.php");
require_once("./_include/current/partyhouz/partyhou_image_list.php");

set_time_limit(0);
header('Access-Control-Allow-Origin: *');
$user_id=$_POST['user_id'];
$partyhou_id=$_POST['partyhou_id'];
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
	$sql = "
		SELECT * from partyhouz_partyhou WHERE partyhou_id ='".$partyhou_id."';
	";
	$result = $conn->query($sql);
	$row = $result->fetch_assoc();
	$cum_couples = $row["cum_couples"];
	$cum_males = $row["cum_males"];
	$cum_females = $row["cum_females"];
	$is_open = $row["is_open"];

	$sql = "
		SELECT * from user WHERE user_id ='".$user_id."';
	";
	$result = $conn->query($sql);
	$row = $result->fetch_assoc();
	$gender = $row["gender"];
	$couple = $row["couple"];
	$is_allowed = false;
	if($is_open == 1) {
		$is_allowed = true;
	} else {
		if ($couple == "Y") {
			$is_allowed = $cum_couples == 1 ? true : false;
		} else {
			if ($gender == "F") {
				$is_allowed = $cum_females == 1 ? true : false;
			} else {
				$is_allowed = $cum_males == 1 ? true : false;
			}
		}
	}
	
} catch (Exception $e) {
   var_dump($e);
   die();
}
$conn-> close();
echo json_encode(
	[
		"status"=>"success",
		"is_allowed"=>$is_allowed
	]
);
