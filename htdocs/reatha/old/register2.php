<?php
session_start();
require "functions.php";

	
	$actId=$_GET['actId'];

	if ( acceptUser($actId) )
	{
		header("location:login.php");
	}	
	else
	{
		header("location:error_invalid_actid.php");
	}	
	
?>
