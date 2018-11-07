<?php 
require "TwilioKJ/autoload.php";
    use Twilio\Rest\Client;

    
    $AccountSid = "ACe96a3094711322e2ab4e0169ef74bbbd";
    $AuthToken = "82daf98a1af66668123fa78b2e82a1cb";

    $client = new Client($AccountSid, $AuthToken);

    
	$client->messages->create(
	    
	    '+923348662017',
	    array(
	        
	        'from' => '+15017250604',
	        
	        'body' => 'Hey Jenny! Good luck on the bar exam!'
	    )
	);
?>