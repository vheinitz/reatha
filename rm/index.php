<?php
session_start();

if(!isset($_SESSION["user"]))
	header("location:login.php");

if( isset($_GET["notify"]) && isset($_GET['user']) && isset($_GET['deviceId']) )
{
	require_once "functions.php";
	$user=$_GET['user'];
	$deviceId=$_GET['deviceId'];
	$notify=$_GET['notify'];
	$state=$_GET['state'];
	notifyOn( $state, $user, $deviceId, $notify  );	
}
?>



<html>
<head>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
	<meta http-equiv="cache-control" content="no-cache">
	<title>Main</title>
	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<script src="js/bootstrap.min.js"></script>
	</head>
<body>
<div align="center"> 
	<div style="width:50%; ">
    <div class="page-header">
	<div style="width:100%; text-align:right" id="logout"> <a href="logout.php">logout</a> </div>
    <h2>Devices</h2>	
    </div>

<!-- table border="1" -->   
<?php
require_once "functions.php";
listDevices();
?>
<!-- /table -->
 
</div>
</div>
</body>
</html>
