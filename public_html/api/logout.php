<?php
require "DataBase.php";

try{
	$db = new DataBase();
	$db->logout();
} catch(PGException $exception){
	http_response_code(500);
	echo "Internal Database Error, please try again later: " . $exception->getMessage();
	return;
}