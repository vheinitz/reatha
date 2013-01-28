<?php
session_start();

if( isset($_SESSION["user"]))
{
require_once "functions.php";
	$user=$_SESSION["user"];
	$deviceId=$_GET['deviceId'];
	$notify=$_GET['notify'];
	notifyOn( "error", $user, $deviceId, $notify  );
}
	
?>
