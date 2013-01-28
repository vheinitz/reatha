<?php
require "functions.php";
	$deviceId=$_GET['deviceId'];
	$tagId=$_GET['tagId'];
	$tagValue=$_GET['tagValue'];
	
	updateDeviceData($deviceId,$tagId, $tagValue );
	
	if ( stripos($tagId, "status") !== false && stripos($tagValue, "error") !== false )
	{
		sendMail("vheinitz@googlemail.com", "Error ".$deviceId, "Device:".$deviceId."\nState:".$tagValue );
	}
?>
