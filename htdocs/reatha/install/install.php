<?php
if(isset($_POST['dbuser']) && isset($_POST['dbname'])){
	$web_dir = dirname($_SERVER['PHP_SELF']);
	$web_dir = str_replace('/install', '', $web_dir);
	echo $web_dir;

	//write database data to config	
	$file = file_get_contents('../application/config/database.php', FILE_USE_INCLUDE_PATH);
	$file = str_replace("db['default']['username'] = ''", "db['default']['username'] = '".$_POST['dbuser']."'", $file);
	$file = str_replace("db['default']['password'] = ''", "db['default']['password'] = '".$_POST['dbpass']."'", $file);
	$file = str_replace("db['default']['database'] = ''", "db['default']['database'] = '".$_POST['dbname']."'", $file);
	file_put_contents('../application/config/database.php', $file);

	//rewrite .htaccess data
	$file = file_get_contents('../.htaccess', FILE_USE_INCLUDE_PATH);
	echo $file;	
	$file = str_replace("/index.php", $web_dir."/index.php", $file);
	file_put_contents('../.htaccess', $file);

	header( 'Location: '.$web_dir);	
}
?>

<html>
<head></head>
<body>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
		<table>
			<tr><td>Database Username: </td><td><input type="text" name="dbuser"/></td></tr>
			<tr><td>Database Password: </td><td><input type="password" name="dbpass"/></td></tr>
			<tr><td>Database Name: </td><td><input type="text" name="dbname"/></td></tr>
			<tr><td></td><td><input type="submit" value="Save"/></td></tr>
		</table>
	</form>
</body>
</html>