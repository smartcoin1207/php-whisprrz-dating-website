<?php
include("./_include/core/main_start.php");

global $g;
set_time_limit(0);
$user_name=$_GET['user_name'];
$type=$_GET['user_type'];
$option=$_GET['option'];
$days=$_GET['days'];
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
	$sql ="select * from user where name='".$user_name."'";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$guser_type = $row["type"];
			$guser_gold_days = $row["gold_days"];			
		}
	}
	if (!isset($type) || $type=="") {
        $type = $guser_type;
    }
	if (!isset($days) || $days=="") {
        $days = $guser_gold_days;
    }
	//if ($type == 'membership' && Common::getOption('set', 'template_options') != 'urban') {
    //    $type = 'platinum';
    //}

    $keyCheck = $option . '_' . $type . '_check';
    
	$sql = 'SELECT code FROM payment_type
		WHERE type = "' . $type . '"
			AND code = ' . $option;
	$check = "";	
	$pay_result = $conn->query($sql);
	if ($pay_result->num_rows > 0) {
		while($row = $pay_result->fetch_assoc()) {
			$check = $row["code"];
		}
	}
	$checkForAll = "";
	$sql = 'SELECT code FROM payment_type
		WHERE code = ' . $option;		
	$checkForAll = "";
	$pay_result = $conn->query($sql);
	if ($pay_result->num_rows > 0) {
		while($row = $pay_result->fetch_assoc()) {
			$checkForAll = $row["code"];
		}
	}
	$pay_state = 0;
	
	if ($check!="" && $days > 0) {
        $pay_state = 1;
    } else {
        $pay_state = 0;//0
    }
	if ($checkForAll=="") {
        $pay_state = 1;
    }	
} catch (Exception $e) {
   
}
$conn-> close();
echo json_encode(
	[
		"status"=>"success", 
		"paystate"=>$pay_state
	]
);
