<?php
$hotdate_description = "Hi Whisprrz Hotdates!!";
$curl = curl_init();
$Live_Url="http://www.easysendsms.com/sms/bulksms-api/bulksms-api?username=minamina2020&password=esm41140&from=+12017628299&to=13399333986&text=".$hotdate_description."&type=0";
curl_setopt_array($curl, array(
	CURLOPT_URL => "https://www.easysendsms.com/sms/bulksms-api/bulksms-api?username=minamina2020&password=esm41140&from=+12017628299&to=13399333986&text=Whisprrz%20Hotdates&type=0", //"https://twilio-sms.p.rapidapi.com/2010-04-01/Accounts/AC09bcd59d91aa1f305cfdb2da0fc97427/Messages.json?from=89016451209&body=Hi%20Whisprrz&to=13399333986",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "POST",
	CURLOPT_POSTFIELDS => "",
	CURLOPT_HTTPHEADER => array(
		"content-type: application/x-www-form-urlencoded"
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