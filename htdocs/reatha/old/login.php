<?php
session_start();
require "functions.php";
if (isset($_POST['user']) && isset($_POST['passwd']))
	if ( checkLogin( $_POST['user'], $_POST['passwd'] ) )
		header("location:index.php");
?>

<HTML>
<HEAD>
<TITLE>Login</TITLE>

</HEAD>
<BODY bgcolor="green" text="#FFFFFF">

<center>
<br><br><br>


<form name="form1" method="post" action="login.php">
User:<br/>
<input type="text" name="user" size="15"><br><br>
Password:<br/>
<input type="password" name="passwd" size="15" >
<br/>
<input type="submit" name="submit" value="OK">
</form>
<br>
<a href="register.php">Register</a>
<br><br>

</center>
</BODY>
</HTML>
