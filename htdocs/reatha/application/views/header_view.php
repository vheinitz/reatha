<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
    <head>
        <meta charset="utf-8"/> 
        <meta name="viewport" content="width=device-width" />
        <meta name="viewport" content="width=240, height=320, user-scalable=yes, initial-scale=0.7, maximum-scale=5.0, minimum-scale=1.0" />
        <meta name="apple-mobile-web-app-capable" content="yes" />                    
        <link href="<?php echo base_url(); ?>css/bootstrap.min.css" rel="stylesheet" media="screen" />       
        <link href="<?php echo base_url(); ?>css/style.css" rel="stylesheet" />
        <script type="text/javascript" src="<?php echo base_url(); ?>js/jquery.1.9.0.min.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/functions.js"></script>
    </head>
    <body>
        <?php if(!isset($hide_navbar)) { ?> 
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
                            <li><a href="<?php echo base_url(); ?>da/images">Images</a></li>
                        </ul>                         
                    <?php } ?>
                        <ul class="nav pull-right">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <b><?php echo $user->username; ?></b>
                                <b class="caret"></b>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a tabindex="-1" href="<?php echo base_url(); ?>auth/change_password">Change Password</a></li>
                                    <li><a tabindex="-1" href="<?php echo base_url(); ?>auth/logout">Logout</a></li>
                                </ul>
                            </li>                            
                        </ul>                    
                <? } ?>
            </div>
        </div> 
        <?php } ?>        
    <div class="container">       
    <?php 
  	$message = $this->session->flashdata('message');   
	if(!empty($message)){
        if(!(isset($hide_success_message) && $message['type'] == 'success')){ ?>
	       <div class="alert alert-<?php echo $message['type']; ?>"><?php echo $message['message']; ?></div>
	<?php }  }	