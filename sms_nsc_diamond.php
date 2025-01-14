<?php

// Update the path below to your autoload.php,
// see https://getcomposer.org/doc/01-basic-usage.md
require_once './_include/current/Twilio/autoload.php';

use Twilio\Rest\Client;
//include("./_include/Twilio/Rest/Client.php");

// Find your Account Sid and Auth Token at twilio.com/console
// DANGER! This is insecure. See http://twil.io/secure
$sid    = "AC09bcd59d91aa1f305cfdb2da0fc97427";
$token  = "135612f420bae21bc5a776630d2ac696";
$twilio = new Client($sid, $token);

$message = $twilio->messages
                  ->create("+13399333986", // to
                           ["body" => "Hi Whisprrz!", "from" => "+79016451209"]
                  );

?>