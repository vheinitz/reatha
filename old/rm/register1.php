<?php
session_start();
require "functions.php";

	
	$password=$_POST['pass'];
	$user=$_POST['user'];
	$email=$_POST['email'];
	if( !userExists( $user ) )
	{
		registerUser($user,$password,$email);
		header("location:login.php");
	}
	else
	{
		header("location:error_user_exists.php");
	}
	//TODO check if user existd,save activation id, send e-mail
	
	
?>
