<?php
session_start();
require_once "functions.php";
    $deviceId = $_GET["deviceId"];
	$user = $_SESSION["user"];
	if ( isNotifiedOn("error", $user, $deviceId ) )
	{
		echo ("<img src='res/notif_on.png'/>");
	}
	else
	{
		echo ("<img src='res/notif_off.png'/>");
	}	
?>

