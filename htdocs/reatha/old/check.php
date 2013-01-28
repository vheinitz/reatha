<?php
session_start();

	
	$password=$_POST['pass'];		//Gets the password from previous page entry
	$fp=fopen("db/passwd","r");		//Opens the Pass.txt file
	$data=fgets($fp);				//Reads the File entirely
	fclose($fp);					//Closes the file since we already got our info
	if($password == $data)			//compare passwords 
	{
		$_SESSION['user'] = $_POST["user"];
		header("location:index.php");
	}
	else
	{
		header("location:login.php");
	}
?>
