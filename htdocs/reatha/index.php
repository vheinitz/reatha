<?php
$redirect = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/install/install.php";
header("Location: $redirect"); /* Redirect browser */
?>