<?php

require_once("ClassFiles/Verifications.php");
require_once "constants.php";

if(!(isset($_GET["email"]) && isset($_GET["code"]))){
	http_response_code(400);
	header("Location: " . HTTPS_HOST);
	respond("The given link was malformed.");
	return;
}

$verify = new Verifications();

if(!$verify->check_verification($_GET["email"], $_GET["code"])){
	respond("The code does not match what we provided.");
	header("Location: " . HTTPS_HOST);
	http_response_code(400);
} else{
	echo "Verification Email Accepted" . PHP_EOL;
	header("Location: /profile.php");
	http_response_code(303);
}