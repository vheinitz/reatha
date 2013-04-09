</div>
<?php if(isset($user) && ($user->role == 3)){
	$domain = new Domain($user->belongs_to_domain_id); 	
	if(!empty($domain->footer_text)){ ?>
        <style type="text/CSS">
            <!-- 
            .navbar-inner#footer{background: <?php echo $domain->footer_color; ?>; text-align: center;} 
            .navbar-inner#footer p {color: <?php echo $domain->footer_text_color; ?>; text-shadow:none;} 
            -->
        </style>	
	    <div class="navbar navbar-fixed-bottom">
	    	<div class="navbar-inner" id="footer">
	    		<p class="navbar-text"><?php echo $domain->footer_text; ?></p>
			</div>
	    </div>
    <?php } } ?>
</body>
</html>