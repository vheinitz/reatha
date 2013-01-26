<?php
require_once('initdb.php');
    $deviceId = $_GET["deviceId"];
	$deviceStatus="";
	$devStat = R::getRow( " SELECT tagValue FROM dd WHERE deviceid='$deviceId' AND tagId='status' " );
	if ( $devStat != null )
	foreach( $devStat as $tagId=>$tagValue )
	{
		echo ("<td width='300' >$tagValue</td>");
		break;
	}	
?>

