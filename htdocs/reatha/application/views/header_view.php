<<<<<<< HEAD
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
    <head>
        <meta charset="utf-8"/>            
        <link href="<?php echo base_url(); ?>css/bootstrap.min.css" rel="stylesheet" media="screen" />       
        <script type="text/javascript" src="<?php echo base_url(); ?>js/jquery.1.9.0.min.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/bootstrap.min.js"></script>      
    </head>
    <body>
        <div class="navbar">
            <div class="navbar-inner">
                <a class="brand" href="#">Reatha</a>
                <?php if(isset($user)){ 
                    if($user->role == '1'){ ?>
                        <ul class="nav">
                            <li><a href="<?php echo base_url(); ?>a">Domains</a></li>
                            <li><a href="<?php echo base_url(); ?>a/domain_admins">Domain Admins</a></li>
                        </ul>                    
                    <?php } elseif($user->role == '2'){ ?>
                        <p class="navbar-text pull-left">Domain:</p>
                            <ul class="nav">
                            <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <b><?php echo $this->session->userdata('managing_domain_name'); ?></b>
                            <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu">
                                <?php foreach($domains as $domain){ 
                                    echo "<li><a tabindex='-1' href='".base_url()."da/change_managing_domain/$domain->id'>$domain->name</a></li>";
                                } ?>
                            </ul>
                            </li>
                            </ul>                        
                        <ul class="nav">
                            <li class="divider-vertical"></li>
                            <li><a href="<?php echo base_url(); ?>da">Devices</a></li>
                            <li><a href="<?php echo base_url(); ?>da/users">Users</a></li>
                        </ul>                         
                    <?php } ?>
                        <ul class="nav pull-right">
                            <li><a href="<?php echo base_url(); ?>auth/logout">Logout</a></li>
                        </ul>                    
                <? } ?>
            </div>
        </div>         
    <div class="container">       
    <?php 
  	$message = $this->session->flashdata('message');   
	if(!empty($message)){?>
	<div class="alert alert-<?php echo $message['type']; ?>"><?php echo $message['message']; ?></div>
=======
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
    <head>
        <meta charset="utf-8"/>            
        <link href="<?php echo base_url(); ?>css/bootstrap.min.css" rel="stylesheet" media="screen" />       
        <script type="text/javascript" src="<?php echo base_url(); ?>js/jquery.1.9.0.min.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/bootstrap.min.js"></script>      
    </head>
    <body>
        <div class="navbar">
            <div class="navbar-inner">
                <a class="brand" href="#">Reatha</a>
                <?php if(isset($user)){ 
                    if($user->role == '1'){ ?>
                        <ul class="nav">
                            <li><a href="<?php echo base_url(); ?>a">Domains</a></li>
                            <li><a href="<?php echo base_url(); ?>a/domain_admins">Domain Admins</a></li>
                        </ul>                    
                    <?php } elseif($user->role == '2'){ ?>
                        <p class="navbar-text pull-left">Domain:</p>
                            <ul class="nav">
                            <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <b><?php echo $this->session->userdata('managing_domain_name'); ?></b>
                            <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu">
                                <?php foreach($domains as $domain){ 
                                    echo "<li><a tabindex='-1' href='".base_url()."da/change_managing_domain/$domain->id'>$domain->name</a></li>";
                                } ?>
                            </ul>
                            </li>
                            </ul>                        
                        <ul class="nav">
                            <li class="divider-vertical"></li>
                            <li><a href="<?php echo base_url(); ?>da">Devices</a></li>
                            <li><a href="<?php echo base_url(); ?>da/users">Users</a></li>
                        </ul>                         
                    <?php } ?>
                        <ul class="nav pull-right">
                            <li><a href="<?php echo base_url(); ?>auth/logout">Logout</a></li>
                        </ul>                    
                <? } ?>
            </div>
        </div>         
    <div class="container">       
    <?php 
  	$message = $this->session->flashdata('message');   
	if(!empty($message)){?>
	<div class="alert alert-<?php echo $message['type']; ?>"><?php echo $message['message']; ?></div>
>>>>>>> 532811372554ee26da7ed1f128604c714d0512c1
	<?php }  	