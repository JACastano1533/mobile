<?php

$to = "techleadz.wpteam@gmail.com";
$from = "salman.ahmad@techleadz.com";
$subject = "Testing";
$message = "Testing";

$headers = "From:" . $from;

$result = mail($to, $subject, $message, $headers);
echo "Mail Sent.";
var_dump($result);
