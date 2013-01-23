<?php
require_once('initdb.php');
	$curTS =  time();
    $deviceId = $_GET["deviceId"];
	//echo (" SELECT tagValue FROM dd WHERE deviceid='$deviceId' AND tagId='lc' ");
	$devOnline = R::getRow( " SELECT tagValue FROM dd WHERE deviceid='$deviceId' AND tagId='lc' " );
	if ( $devOnline != null )
	{
		foreach( $devOnline as $tagId=>$tagValue )
		{
			if ( ($tagValue/1000)+10 > $curTS ) //has life check older then 10 sec
			{
				echo ("<img src='res/device_s.png'/>");
			}
			else
			{
				echo ("<img src='res/device_s_d.png'/>");
			}
			break; //"misusing foreach for getting 1st row"
		}
	}
	else
	{
		echo ("<img src='res/device_s_d.png'/>");
	}
?>

