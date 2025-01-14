<?php
include("./_include/core/main_start.php");
global $g;
set_time_limit(0);
header('Access-Control-Allow-Origin: *');
$user_id = $_POST['user_id'];
$servername = $g['db']['host'];
$username = $g['db']["user"];
$password = $g['db']['password'];
$dbnanme = $g['db']['db'];
// Create connection
$conn = new mysqli($servername, $username, $password, $dbnanme);

// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}
try {
	$partyhouz = array();
	$sql = "SELECT partyhou_id, partyhou_title
			FROM partyhouz_partyhou
			WHERE user_id = '" . $user_id . "' AND is_saved = '1'";
	$result = $conn->query($sql);
	$no = 0;
	if ($result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {
			$partyhou = array("partyhou_id" => $row["partyhou_id"], "partyhou_title" => $row["partyhou_title"]);
			$no++;
			array_push($partyhouz, $partyhou);
		}
	}
} catch (Exception $e) {

}
$conn->close();
echo json_encode(
	[
		"status" => "success",
		"total" => $no,
		"partyhouz" => $partyhouz
	]
);