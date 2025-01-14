<?php
include("./_include/core/main_start.php");
require_once("./_include/current/partyhouz/tools.php");
require_once("./_include/current/partyhouz/partyhou_image_list.php");
global $g;

set_time_limit(0);
header('Access-Control-Allow-Origin: *');
$user_id=$_POST['user_id'];
$partyhou_id=$_POST['partyhou_id'];
$accepted=$_POST['accepted'];
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
		UPDATE partyhouz_partyhou_invites SET status = ".$accepted."
		WHERE partyhou_id = '".$partyhou_id."' AND invited_user_id = '".$user_id."';
	";
	$result = $conn->query($sql);
	if ($accepted == "1") {
		$sql = "
			INSERT INTO partyhouz_partyhou_guest (partyhou_id, user_id, guest_n_friends, created_at)
			VALUES ('".$partyhou_id."', '".$user_id."', '0', NOW());
		";
		$result = $conn->query($sql);
		$sql = "
			UPDATE partyhouz_partyhou SET partyhou_n_guests = partyhou_n_guests + 1
			WHERE partyhou_id = '".$partyhou_id."';
		";
		$result = $conn->query($sql);
	}
} catch (Exception $e) {
   var_dump($e);
   die();
}
$conn-> close();
echo json_encode(
	[
		"status"=>"success"
	]
);
