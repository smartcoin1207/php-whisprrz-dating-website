<?php
include("./_include/core/main_start.php");
require_once("./_include/current/partyhouz/tools.php");
require_once("./_include/current/partyhouz/partyhou_image_list.php");

global $g;
set_time_limit(0);
header('Access-Control-Allow-Origin: *');
$user_id=$_POST['user_id'];
$servername = $g['db']['host'];
$dbnanme = $g["db"]["db"];
$username = $g["db"]["user"];
$password = $g["db"]["password"];
// Create connection
$conn = new mysqli($servername, $username, $password, $dbnanme);

if (Common::getOption('timezone', 'main')) {
    $zone = new DateTimeZone(Common::getOption('timezone', 'main'));
} else {
    $zone = new DateTimeZone(date_default_timezone_get());
}

// Create datetime objects for the given timezone and UTC
$datetimeZone = new DateTime('now', $zone);
$datetimeUTC = new DateTime('now', new DateTimeZone('UTC'));

// Calculate the time difference
$timeDifference = $datetimeZone->getOffset() - $datetimeUTC->getOffset();
$timeDifferenceMins = $timeDifference / 60;
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}	
try
{
	$sql = "SELECT pc.category_id, 
			pc.category_title, 
			pp.partyhou_id, 
			pp.user_id,
			u.name, 
			pp.partyhou_datetime, 
			pp.partyhou_title, 
			pp.partyhou_n_comments, 
			LENGTH(pp.invited_user_ids) - LENGTH(REPLACE(pp.invited_user_ids, ',', '')) + 1 AS guest_invited_count,
			CONCAT(
				LPAD(FLOOR(TIME_TO_SEC(TIMEDIFF(pp.partyhou_datetime - INTERVAL ".$timeDifferenceMins." MINUTE, NOW())) / 60) - 5, 2, '0'),
				':',
				LPAD(TIME_TO_SEC(TIMEDIFF(pp.partyhou_datetime, NOW())) % 60, 2, '0')
			) AS remain_time,
			pp.partyhou_n_guests
			FROM partyhouz_partyhou_guest ppg
			LEFT JOIN partyhouz_partyhou pp ON pp.partyhou_id = ppg.partyhou_id
			LEFT JOIN partyhouz_category pc ON pc.category_id = pp.category_id
			LEFT JOIN user u ON u.user_id = pp.user_id
			WHERE ppg.user_id='".$user_id."' AND ppg.declined = '0' AND NOW() >= pp.partyhou_datetime - INTERVAL ".$timeDifferenceMins." MINUTE - INTERVAL 15 MINUTE AND NOW() <= pp.partyhou_datetime - INTERVAL ".$timeDifferenceMins." MINUTE - INTERVAL 5 MINUTE";
	$result = $conn->query($sql);
	$partyhous = array();
	$no = 0;
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$partyhou_title = strcut(to_html($row['partyhou_title']), 20);
			$partyhou_title_full = to_html($row['partyhou_title']);
			$partyhou_n_comments = $row['partyhou_n_comments'];
			$partyhou_n_guests = $row['partyhou_n_guests'];
			$partyhou_date = to_html(Common::dateFormat($row['partyhou_datetime'],'partyhouz_partyhou_date'));
			$partyhou_time = to_html(Common::dateFormat($row['partyhou_datetime'],'partyhouz_partyhou_time'));
			$partyhou_datetime_raw = to_html($row['partyhou_datetime']);
			$partyhouz_partyhou_image_list = new CpartyhouzpartyhouImageList("partyhouz_partyhou_image_list", $g['tmpl']['dir_tmpl_main'] . "_partyhouz_partyhou_image_list.html");

			$images = CpartyhouzTools::partyhou_images($row['partyhou_id']);
			$image_thumbnail = $images["image_thumbnail_b"];
			$partyhou = array(
				"category_id" => $row["category_id"],
				"category_title" => $row["category_title"],
				"user_id" => $row["user_id"],
				"user_name" => $row["name"],
				"partyhou_id" => $row["partyhou_id"],
				"guest_invited_count" => $row["guest_invited_count"],
				"partyhou_title" => $partyhou_title,
				"partyhou_title_full" => $partyhou_title_full,
				"partyhou_n_comments" => $partyhou_n_comments,
				"remain_time" => $row["remain_time"],
				"partyhou_n_guests" => $partyhou_n_guests,
				"partyhou_date" => $partyhou_date,
				"partyhou_time" => $partyhou_time,
				"partyhou_datetime_raw" => $partyhou_datetime_raw,
				"image_thumbnail" => $image_thumbnail
			);
			$no++;
			array_push($partyhous,$partyhou);
			
			/* START - Divyesh - 28082023 */
			$userTo = User::getInfoBasic($row["user_id"]);

			Common::usersms('party_start_sms', $userTo, 'set_sms_alert_pa');

			/* END - Divyesh - 28082023 */
		}
	}	
} catch (Exception $e) {
   
}
$conn-> close();
echo json_encode(
	[
		"status"=>"success", 
		"total"=>$no, 
		"partyhous"=>$partyhous
	]
);
