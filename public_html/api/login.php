<?php
require_once("ClassFiles/DataBase.php");
require_once "constants.php";
require_once "tools.php";

if (!(isset($_POST['username']) && isset($_POST['password']))) {
	http_response_code(400);
	respond("All fields are required.");
	return;
}

try{
	$db = new DataBase();
} catch(PGException $exception){
	http_response_code(500);
	respond("Internal Database Response, please try again later: " . $exception->getMessage());
	if(!is_header_set("Location")){
		header("Location: " . HTTPS_HOST);
	}
	return;
}

$auth_code = $_POST["auth_code"] ?? "";

try{
	$result = $db->logIn($_POST['username'], $_POST['password'], $auth_code);
} catch(PGException $pgException){
	http_response_code(500);
	respond($pgException->getMessage());
	header("Location: " . HTTPS_HOST . "/login.php");
	return;
} catch(InvalidArgumentException $argumentException){
	http_response_code(401);
	respond($argumentException->getMessage());
	header("Location: " . HTTPS_HOST . "/login.php");
	return;
}

if (gettype($result) == 'boolean') {
	http_response_code(401);
	header("Location: " . HTTPS_HOST . "/login.php");
	return;
}

else{
	http_response_code(302);
	respond($result);
	if(!is_header_set("Location")){
		header("Location: " . HTTPS_HOST . "/profile.php");
	}
}

