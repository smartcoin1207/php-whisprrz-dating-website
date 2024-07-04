<?php
set_time_limit(0);
$servername = "localhost";
$username = "eric_cui";
$password = "nnsscc123456!#";

// Create connection
$conn = new mysqli($servername, $username, $password,"eric_classify_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}	
try
{
	$sql ="select * from ci_ads";
	$result = $conn->query($sql);
	$users = array();
	$no = 0;
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$city = $row["city_txt"];
			$sql_city ="select * from ci_cities where name='".$city."' and country_id=230";
			$result_city = $conn->query($sql_city);			
			if ($result_city->num_rows > 0) {
				while($row_city = $result_city->fetch_assoc()) {
					$no++;
					array_push($users,$row["city_txt"]);
					$update_sql = "update ci_ads set lang='".$row_city["long"]."' where id=".$row["id"]."; ";
					$conn->query($update_sql);	
					$update_sql = "update ci_ads set lat='".$row_city["lat"]."' where id=".$row["id"]."; ";
					$conn->query($update_sql);	
					$update_sql = "update ci_ads set country='".$row_city["country_id"]."' where id=".$row["id"]."; ";
					$conn->query($update_sql);	
					$update_sql = "update ci_ads set state='".$row_city["state_id"]."' where id=".$row["id"]."; ";
					$conn->query($update_sql);	
					$update_sql = "update ci_ads set city='".$row_city["id"]."' where id=".$row["id"]."; ";
					$conn->query($update_sql);	
					break;
				}
			}
		}
		
	}	
} catch (Exception $e) {
   
}
$conn-> close();
echo json_encode(
	[
		"status"=>"success", 
		"total"=>$no, 
		"users"=>$users
	]
);
