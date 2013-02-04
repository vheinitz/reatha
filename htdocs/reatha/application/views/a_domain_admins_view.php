<?php $this->load->view('header_view'); ?>

<table class="table table-striped table-bordered">
  	<thead>
	    <tr>
		    <th style="text-align: center">Username</th>
	        <th style="text-align: center">Domains Assigned</th>
	        <th style="text-align: center">Action</th>
	    </tr>
	</thead>
	<tbody> 
<?php foreach($domain_admins as $domain_admin){ 
	$assigned_domains = $domain_admin->domains->get(); ?>
<tr>
	<td><?php echo $domain_admin->username; ?></td>
	<td><?php foreach($assigned_domains as $assigned_domain){ 
		echo $assigned_domain->name." (<a href='".base_url()."a/unnasign_domain_admin/$assigned_domain->id/$domain_admin->id'>Unnasign</a>)<br/>";
		} ?>
	<form class="form-inline" action="<?php echo base_url(); ?>a/assign_domain_admin" method="post">
		<label>Assign New Domain: </label>
		<select name='device'>
			<?php foreach($domains as $domain){
				if(!$domain_admin->is_admin_of($domain->id)) 
					echo "<option value='".$domain->id."'>".$domain->name."</option>";
			} ?> 
		</select>
		<input type="hidden" name="user_id" value="<?php echo $domain_admin->id; ?>" />
		<button type="submit" class="btn">Assign</button>
	</form>	
	</td>
	<td><a href="<?php echo base_url(); ?>da/delete_user/<?php echo $domain_admin->id; ?>" onclick="return confirm('Are you sure?')">Delete User</a></td>
</tr>
<?php } ?>
</tbody>
</table>
<hr/>
<h4>New User</h4>
		<form action="<?php echo base_url(); ?>da/add_user" method="post">
			<fieldset>
				<label>Username</label>
			 	<input type="text" name="username" maxlength=150 />

				<label>Password</label>
				<input type="password" name="password" maxlength=150 />

				<label>Email</label>
				<input type="text" name="email" maxlength=150 />
				
				<label>Assign to device</label>
			 	<select name="device">
				echo "<option value=''></option>";
				<?php foreach($devices as $device){ 
					echo "<option value='".$device->id."'>".$device->description."</option>";
			 	} ?> 
				</select>

				<label></label>
				<input type="submit" class="btn" value="Add" />
			</fieldset>
		</form>

<?php $this->load->view('footer_view'); ?>