<?php

$curl = curl_init();

curl_setopt_array($curl, array(
	CURLOPT_URL => "https://twilio-sms.p.rapidapi.com/2010-04-01/Accounts/AC09bcd59d91aa1f305cfdb2da0fc97427/Messages.json?from=89016451209&body=Hi%20Whisprrz&to=13399333986",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "POST",
	CURLOPT_POSTFIELDS => "",
	CURLOPT_HTTPHEADER => array(
		"content-type: application/x-www-form-urlencoded",
		"x-rapidapi-host: twilio-sms.p.rapidapi.com",
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