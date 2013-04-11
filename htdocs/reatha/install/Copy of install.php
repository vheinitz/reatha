<?php
error_reporting(E_ERROR | E_PARSE | E_NOTICE);
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
    <head>
        <meta charset="utf-8"/> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />           
        <link href="../css/bootstrap.min.css" rel="stylesheet" media="screen" />          
        <link href="../css/style.css" rel="stylesheet" media="screen" />
    </head>
    <body id="main-body">
        <div class="navbar" id="install-navbar">
            <div class="navbar-inner">
                <a class="brand" href="#">Reatha</a>
            </div>
        </div>         
    <div class="container" id="main-container">
    	<div class="install-wrapper"> 
    		<h2>Reatha Installer</h2>   		

<?php
if(isset($_POST['db_user']) && isset($_POST['db_name'])){
	$errors = array();

	$db_host = $_POST['db_host'];
	$db_user = $_POST['db_user'];
	$db_pass = $_POST['db_pass'];
	$db_name = $_POST['db_name'];

	//check if entered database login is valid
	$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
	if (!$mysqli->connect_error) {

		//loading database structure and data
		$db_data = file_get_contents('./reatha.sql', FILE_USE_INCLUDE_PATH);
		if(!$mysqli->multi_query($db_data)) {
			die('Error: '.$mysqli->error);				
		} else {
			//freeing the result otherwise we can't run queries
			while(mysqli_next_result($mysqli));			
		}


		//create admin user
		if(isset($_POST['admin_username']) && isset($_POST['admin_password']) && isset($_POST['admin_email']) && empty($errors)){
			if(!empty($_POST['admin_username']) && !empty($_POST['admin_password']) && !empty($_POST['admin_email'])){
				$username 	= $_POST['admin_username'];
				$password 	= $_POST['admin_password'];
				$email 		= $_POST['admin_email']; 

				//loading password hashing lib
				require_once('../application/libraries/phpass-0.1/PasswordHash.php');
				$hasher = new PasswordHash(8,FALSE);

				$password = $hasher->HashPassword($password);

				if($mysqli->query("INSERT INTO users(username, password, email, role) VALUES('$username','$password','$email','1')")){
					if(!$mysqli->query("INSERT INTO user_profiles(user_id) VALUES('$mysqli->insert_id')")){
						$errors[] = 'Database Error on user_profiles: ' . $mysqli->error;
					}
				} else {
					$errors[] = 'Database Error: ' . $mysqli->error;			
				}
			} else {
				$errors[] = "Please provide admin access data";
			}
		}		
	
		//write database data to config
		if(empty($errors)){
			$db_config = '../application/config/database.php';	
			if(is_writeable($db_config) ){
				$file = file_get_contents($db_config, FILE_USE_INCLUDE_PATH);
				$file = str_replace("db['default']['hostname'] = ''", "db['default']['hostname'] = '".$db_host."'", $file);
				$file = str_replace("db['default']['username'] = ''", "db['default']['username'] = '".$db_user."'", $file);
				$file = str_replace("db['default']['password'] = ''", "db['default']['password'] = '".$db_pass."'", $file);
				$file = str_replace("db['default']['database'] = ''", "db['default']['database'] = '".$db_name."'", $file);
				file_put_contents($db_config, $file);
			} else {
				$errors[] = "We were not able to write data to file 'application/config/database.php'. Please change file permissions or edit the following values manually:<br/>
				db['default']['hostname'] = '$db_host'; <br/>
				db['default']['username'] = '$db_user'; <br/>
				db['default']['password'] = '$db_pass'; <br/>
				db['default']['database'] = '$db_name'; <br/>
				";
			}
		}
		//get current web dir
		if(empty($errors)){		
			$web_dir = dirname($_SERVER['PHP_SELF']);
			$web_dir = str_replace('/install', '', $web_dir);
			if(empty($web_dir)) $web_dir = '/';		

			//moving .htaccess-template to the root
			if(!copy('./ci_.htaccess','../.htaccess')){
				$errors[] = 'Please manually copy the ci_.htaccess file from /install directory to .htaccess in the root of your Reatha folder .';
			}
		}

		if(empty($errors)){
			//rewrite .htaccess data
			$htaccess_file = '../.htaccess';
			if(is_writeable($htaccess_file)){
				$file = file_get_contents($htaccess_file, FILE_USE_INCLUDE_PATH);
				$file = str_replace("\$ /index.php", "\$ ".$web_dir."/index.php", $file);
				file_put_contents($htaccess_file, $file);
			} else {
				if(!empty($web_dir)){
					$errors[] = "We were not able to write data to file '../.htaccess'. Please change file permissions or manually replace the file content with these 3 lines and then move the file to the root of your Reatha directory:<br/>
					RewriteEngine on<br/>
					RewriteCond \$1 !^(index\.php|favicon\.ico|install|layout|js|css|robots\.txt)<br/>
					RewriteRule ^(.*)\$ $web_dir/index.php/\$1 [L]<br/>";				
				}
			}
		}	

		if(empty($errors)){
			//rewrite base_url() value in config.php
			$web_dir = "http://". $_SERVER["SERVER_NAME"].$web_dir;
			$config_file = '../application/config/config.php';
			if(is_writable($config_file)){
				$file = file_get_contents($config_file, FILE_USE_INCLUDE_PATH);	
				$file = str_replace("config['base_url']	= ''", "config['base_url']	= '".$web_dir."'", $file);
				file_put_contents($config_file, $file);
			} else {
				$errors[] = "We were not able to write data to file 'application/config/config.php'. Please change file permissions or edit the following values manually:<br/>
				config['base_url'] = '$web_dir';<br/>";
			}
		}

		if(empty($errors)){
			//moving original Codeigniter index.php instead of the temporary index.php
			if(!copy('./ci_index.php','../index.php')){
				$errors[] = 'Please manually copy the ci_index.php file from /install directory to the root of your Reatha folder.';
			}
		}

		if(empty($errors)){
			//removing default index.html
			if(file_exists('../index.html') && !unlink('../index.html')){
				$errors[] = 'Please remove index.html file from / directory manually';
			}
		}

	} else {
		$errors[] = 'Connection Error: ' . $mysqli->connect_error;
	}

	if(empty($errors)){ ?>
		<div class='alert alert-success'>
			Reatha was successfully installed.
			<form action="finish.php" method="POST">
				<input type="hidden" name="delete_install" value="1" />
				<input type="submit" class="btn" value="Complete Installation" />
			</form>
		</div>
		<?php 
		exit();
	} else {
		echo "<div class='alert alert-error'>";
		foreach ($errors as $error) {
			echo $error;
		}
		echo "</div>";
	}
}

//default database login data
include_once "./dbconfig.php";
if(!isset($config['db_host'])){
	$config['db_host'] = '';
}
if(!isset($config['db_user'])){
	$config['db_user'] = '';
}
if(!isset($config['db_pass'])){
	$config['db_pass'] = '';
}
if(!isset($config['db_name'])){
	$config['db_name'] = '';
}


?>
	<div class="alert">
		<strong>Warning!</strong> All data previously created will be lost.
	</div> 
	<h4>Database</h4>
	<hr/>	
	<form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
		<div class="control-group">
			<label class="install-label control-label" for="input_dbhost">Database Host: </label>
			<div class="controls">
				<input type="text" name="db_host" id="input_dbhost" value="<?php echo $config['db_host']; ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="install-label control-label" for="input_dbuser">Database Username:</label>
			<div class="controls">
				<input type="text" name="db_user" id="input_dbuser" value="<?php echo $config['db_user']; ?>" />
			</div>
		</div>
		<div class="control-group">
			<label class="install-label control-label" for="input_dbpass">Database Password:</label>
			<div class="controls">
				<input type="password" name="db_pass" id="input_dbpass" value="<?php echo $config['db_pass']; ?>" />
			</div>
		</div>
		<div class="control-group">
			<label class="install-label control-label" for="input_dbname">Database Name:</label>
			<div class="controls">
				<input type="text" name="db_name" id="input_dbname" value="<?php echo $config['db_name']; ?>" />
			</div>
		</div>					
		

		<h4>Admin Data</h4>
		<hr/>
		<div class="control-group">
			<label class="install-label control-label" for="input_admin_username">Username:</label>
			<div class="controls">
				<input type="text" name="admin_username" id="input_admin_username" value="">
			</div>
		</div>
		<div class="control-group">
			<label class="install-label control-label" for="input_admin_password">Password:</label>
			<div class="controls">
				<input type="password" name="admin_password" id="input_admin_password" value="">
			</div>
		</div>	
		<div class="control-group">
			<label class="install-label control-label" for="input_admin_email">Email:</label>
			<div class="controls">
				<input type="text" name="admin_email" id="input_admin_email" value="">
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
				<button type="submit" class="btn">Install</button>
			</div>
		</div>		
	</form>
</div>
</div>
</body>
</html>