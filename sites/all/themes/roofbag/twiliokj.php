<?php

require "Twilio/autoload.php";

use Twilio\Rest\Client;

$AccountSid = "ACf084a4b4cd3d5fbc82f554a8170ea2c6";
$AuthToken = "4612a275f509fc7b61f825318d63ee21";

//TEST DETAILS
//$AccountSid = "ACe96a3094711322e2ab4e0169ef74bbbd";
//$AuthToken = "82daf98a1af66668123fa78b2e82a1cb";


//$message = "Your Order id is " . $order_id . " You can access any time your order by click on the below link: \n\n"; // old code kp
$message = "\n\n  From RoofBag: \n\n Your saved order is # " . $order_id . ". \n\n  Questions? Call 800-276-6322. \n\n  You can access your order here: ";

$client = new Client($AccountSid, $AuthToken);

$client->messages->create(
	"+1" . $_GET['mobile'], array(
    "from" => "+1 858-295-8418",
    "body" => $message . " " . $base_url . "/?order_number=" . $order_number
	)
);

//echo '<pre>'; print_r($client); echo '<pre>'; exit; //kp
?>