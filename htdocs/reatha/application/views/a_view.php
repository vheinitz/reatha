<<<<<<< HEAD
<?php $this->load->view('header_view'); ?>
    <table class="table table-striped table-bordered">
    	<thead>
	    <tr>
	        <th style="width:150px; text-align: center">Domain Name</th>
	        <th style="width:150px; text-align: center">Domain Admins</th>                
	        <th style="width:140px; text-align: center">Action</th>
	    </tr>
	    </thead>
	    <tbody>    	
<?php foreach($domains as $domain){ ?>
	<tr>
		<td><?php echo $domain->name; ?></td>
		<td><?php foreach($domain->domain_admin as $domain_admin){
			echo $domain_admin->username." <br/>";
		} ?></td>
		<td><a href="<?php echo base_url(); ?>a/delete_domain/<?php echo $domain->id; ?>" onclick="return confirm('This action will delete all domain-related data such as devices and domain users. Continue?')">Delete Domain</a></td>
	</tr>
<?php } ?>
</tbody>
</table>
<hr/>
<h4>New Domain</h4>
<form action="<?php echo base_url(); ?>a/add_domain" method="post">
	<fieldset>
		<label>Domain Name</label>
		<input type="text" name="domain_name" id="domain_name" maxlength=150 />
<!-- 
		<label>Domain Admin Username</label>
		<input type="text" name="domain_admin_username" maxlength=150 />				

		<label>Domain Admin Password</label>
		<input type="password" name="domain_admin_password" maxlength=150 />		

		<label>Domain Admin Email</label>
		<input type="text" name="domain_admin_email" maxlength=150 />	 -->	
		
		<label></label>
		<input type="submit" class="btn" value="Add" />
	</fieldset>
</form>


=======
<?php $this->load->view('header_view'); ?>
    <table class="table table-striped table-bordered">
    	<thead>
	    <tr>
	        <th style="width:150px; text-align: center">Domain Name</th>
	        <th style="width:150px; text-align: center">Domain Admins</th>                
	        <th style="width:140px; text-align: center">Action</th>
	    </tr>
	    </thead>
	    <tbody>    	
<?php foreach($domains as $domain){ ?>
	<tr>
		<td><?php echo $domain->name; ?></td>
		<td><?php foreach($domain->domain_admin as $domain_admin){
			echo $domain_admin->username." <br/>";
		} ?></td>
		<td><a href="<?php echo base_url(); ?>a/delete_domain/<?php echo $domain->id; ?>" onclick="return confirm('This action will delete all domain-related data such as devices and domain users. Continue?')">Delete Domain</a></td>
	</tr>
<?php } ?>
</tbody>
</table>
<hr/>
<h4>New Domain</h4>
<form action="<?php echo base_url(); ?>a/add_domain" method="post">
	<fieldset>
		<label>Domain Name</label>
		<input type="text" name="domain_name" id="domain_name" maxlength=150 />
<!-- 
		<label>Domain Admin Username</label>
		<input type="text" name="domain_admin_username" maxlength=150 />				

		<label>Domain Admin Password</label>
		<input type="password" name="domain_admin_password" maxlength=150 />		

		<label>Domain Admin Email</label>
		<input type="text" name="domain_admin_email" maxlength=150 />	 -->	
		
		<label></label>
		<input type="submit" class="btn" value="Add" />
	</fieldset>
</form>


>>>>>>> 532811372554ee26da7ed1f128604c714d0512c1
<?php $this->load->view('footer_view'); ?>