<?php

require_once('initdb.php');

function createSchema()
{	
	
}

function checkLogin( $user, $passwd )
{
	//echo $user.":".$passwd."<br>";
	$password = md5($passwd);
	$items = R::find('users'," name='$user' AND passwd='$password' AND activation='' ");
	if ( count( $items ) == 1 )
	{		
		$_SESSION['user'] = $user;
		return true;
	}
	return false;
}

function updateDeviceData( $deviceId, $tagId, $tagValue )
{
	$dd = R::findOne('dd'," deviceid='$deviceId' AND tagid='$tagId'");
	//echo ( " deviceid='$deviceId' AND tagid='$tagId'" );
	if ( $dd === NULL )
	{
		//echo(  "creating" );
		$dd = R::dispense('dd');
		$dd->deviceid = $deviceId;
		$dd->tagid = $tagId;
	}
	
	$dd->tagvalue = $tagValue;
	R::store( $dd );
}

function ajContentUpdater( $deviceId, $url )
{
	static $instanceId=1;
	echo("
	<script type='text/javascript'>
	(function updater_$instanceId() {
				  \$.ajax({
					url: '$url',
					data: 'deviceId=$deviceId',
					success: function(data) {
					  \$('#result_$instanceId').html(data);
					},
					complete: function() {
					  setTimeout(updater_$instanceId, 5000);
					}
				  });
				})();
	</script>
	<div align='left' width='100px' id='result_$instanceId'>???</div>
	");
	$instanceId +=1;
	
}
        
function listDevices()
{
	$user = $_SESSION["user"];
	
	$devs = R::getCol( " SELECT DISTINCT deviceid FROM dd " );
	foreach( $devs as $key=>$value )
	{		
		echo ("<br>");
		$deviceName=$value;	
		
		echo ("<div id='$deviceName' class='well'>" );
		echo ("<table><tr>" );    
		echo ("<td>" );    
		ajContentUpdater( $deviceName, "ajGetDeviceState.php" );
		echo ("</td><td align='center'>" );    
		ajContentUpdater( $deviceName, "ajGetDeviceStateInfo.php" );		
		echo ("</td><td align='center'>" );    
		echo ("<button class='btn' id='btn_notify_$deviceName'>");
		ajContentUpdater( $deviceName, "ajGetNotifInfo.php" );
	    $notify = isNotifiedOn("error", $user, $deviceName )?0:1;
		//echo ("$notify");
		
		echo ("</button>");
		echo ("
		<script type='text/javascript'>
			$('#btn_notify_$deviceName').click(
					function(){
						//$('#btn_notify_$deviceName').html('');
						$.get('notify.php',
							  'deviceId=$deviceName&notify=$notify&user=$user', 
								function(dummy){
										$('#btn_notify_$deviceName').html('');
										$.get( 'ajGetNotifInfo.php',
											   'deviceId=$deviceName', 
												function(data){
													$('#btn_notify_$deviceName').html(data);													
												}
											);									
								}
						);
					}
			 	);
		</script>
		");
		echo ("</td>" );    
		echo ("</tr></table></div>");		
		//echo ("</td></tr></table>\n");
		continue;
		$col="#0000ff00";
		if ( stripos($deviceStatus,"error:") == 0 )
		{
			$col="#00ff0000";
		}
		

		
		$user = $_SESSION["user"];
		if ( isNotifiedOn("finished", $user, $deviceName ) )
		{
			echo ("<td >
				<a href='index.php?user=$user&deviceId=$deviceName&notify=0&state=finished'>OFF</a>
			</td>");
		}
		else		
		{
			echo ("<td >
				<a href='index.php?user=$user&deviceId=$deviceName&notify=1&state=finished'>ON</a>
			</td>");
		}
		
		if ( isNotifiedOn("error", $user, $deviceName ) )
		{
			echo ("<td >
				<a href='index.php?user=$user&deviceId=$deviceName&notify=0&state=error'>OFF</a>
			</td>");
		}
		else		
		{
			echo ("<td >
				<a href='index.php?user=$user&deviceId=$deviceName&notify=1&state=error'>ON</a>
			</td>");
		}
		echo ("</tr>");
	}
}

function sendMail( $to, $subject, $message )
{
	mail($to,$subject,$message,"From: noreply@reatha.de");
}

function userExists( $user )
{	
	$items = R::find("users","name='$user'");
	
	if ( count( $items ) == 1 )
		return true;
	return false;
}

function registerUser($user, $password, $email)
{
	$timestamp = time();		
	$activationId = $user . $email ."$timestamp";
	$activationId = md5( $activationId );
	$item = R::dispense('users');
	$item->name = $user;
	$item->passwd = md5( $password );
	$item->email = $email;
	$item->activation = $activationId;
	R::store($item);
	$htmlCnt = "<html><a href='http://reatha.de/rm/register2.php?actId=$activationId'>Click to activate</a></html>";
	sendMail($email, "Your activation ID", $htmlCnt);
	return true;
}

function notifyOn($state, $user, $deviceId, $notify )
{
	if( $notify )
	{
		//echo (" SELECT user,deviceId,state FROM notification WHERE deviceid='$deviceId' AND user='$user' AND state = '$state' ");
		//$notification = R::getRow( " SELECT user,deviceId,state FROM notification WHERE deviceid='$deviceId' AND user='$user' AND state = '$state' " );
		$notification = R::findOne('notification'," deviceid='$deviceId' AND user='$user' AND state = '$state'");
		if ( $notification === null )
		{		
			//echo ("<br>notif item creating");
			$item = R::dispense('notification');
			$item->user = $user;
			$item->deviceid = $deviceId;
		}
		$item->state = $state;
		R::store($item);
	}
	else
	{
		//echo ("<br>DELETE FROM notification WHERE deviceid='$deviceId' AND user='$user' AND state = 'state' ");
		R::exec( "DELETE FROM notification WHERE deviceid='$deviceId' AND user='$user' AND state = '$state' " );
	}
}

function isNotifiedOn($state, $user, $deviceId )
{
	$notification = R::getRow( " SELECT user,deviceId,state FROM notification WHERE deviceid='$deviceId' AND user='$user' AND state = '$state' " );
	if ( $notification !== null )
		return true; //already registered
	
	//Table created?
	$notification = R::getRow( " SELECT user,deviceId,state FROM notification WHERE deviceid='$deviceId' AND user='$user'" );
	if ( $notification === null )
	{
		notifyOn( $state, $user, $deviceId, false );
	}	
	return false;
}

function acceptUser( $actId )
{	
	$items = R::find('users',"activation='$actId'");
	if ( count( $items ) == 1 )
	{
		foreach ($items as $item )
		{
			$item->activation='';
			R::store($item);
			return true;
		}
	}
	return false;
}

?>
