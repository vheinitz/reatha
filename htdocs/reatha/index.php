<?php
$redirect = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/install/install.php";
header("Location: $redirect"); /* Redirect browser */

/* Make sure that code below does not get executed when we redirect. */
exit;
?>
