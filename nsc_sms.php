<?php

$curl = curl_init();
$msg_content = "Hi Whisprrz!";
curl_setopt_array($curl, array(
	CURLOPT_URL => "https://quick-easy-sms.p.rapidapi.com/send",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "POST",
	//CURLOPT_POSTFIELDS => "message=message%20content&toNumber=15612920930",
	CURLOPT_POSTFIELDS => "message=".$msg_content."&toNumber=13399333986",
	CURLOPT_HTTPHEADER => array(
		"content-type: application/x-www-form-urlencoded",
		"x-rapidapi-host: quick-easy-sms.p.rapidapi.com",
		"x-rapidapi-key: 30f05e870dmsh2d6e078763de816p1d17c8jsn8a0c258c7a0f"
	),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
	echo "cURL Error #:" . $err;
} else {
	echo $response;
}
?>