<?php
error_reporting(E_ERROR | E_PARSE | E_NOTICE);
?>

<?php
if(isset($_POST['delete_install'])){
	unlink('install.php');
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
    <head>
        <meta charset="utf-8"/>            
        <link href="../css/bootstrap.min.css" rel="stylesheet" media="screen" />          
        <link href="../css/style.css" rel="stylesheet" media="screen" />          
    </head>
    <body>
        <div class="navbar">
            <div class="navbar-inner">
                <a class="brand" href="#">Reatha</a>
            </div>
        </div>         
    <div class="container" id="main-container">
    	<div style="text-align: center"> 
    		<h2>Reatha installation finished</h2>
			<a href="..">Go to login...</a>
		</div>
	</div>
	</body>
</html>