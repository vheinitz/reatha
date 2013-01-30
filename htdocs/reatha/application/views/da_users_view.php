<?php $this->load->view('header_view'); ?>

<table class="table table-striped table-bordered">
  	<thead>
	    <tr>
		    <th style="text-align: center">Username</th>
	        <th style="text-align: center">Devices Assigned</th>
	        <th style="text-align: center">Action</th>
	    </tr>
	</thead>
	<tbody> 
<?php foreach($users as $domain_user){ 
	$assigned_devices = $domain_user->device->get(); ?>
<tr>
	<td><?php echo $domain_user->username; ?></td>
	<td><?php foreach($assigned_devices as $assigned_device){ 
		echo $assigned_device->description." (<a href='".base_url()."da/unnasign_device/$assigned_device->id/$domain_user->id'>Unnasign</a>)<br/>";
		} ?>
	<form class="form-inline" action="<?php echo base_url(); ?>da/assign_device" method="post">
		<label>Assign New Device: </label>
		<select name='device'>
			<?php foreach($devices as $device){
				if(!$domain_user->has_device($device->id)) 
					echo "<option value='".$device->id."'>".$device->description."</option>";
			} ?> 
		</select>
		<input type="hidden" name="user_id" value="<?php echo $domain_user->id; ?>" />
		<button type="submit" class="btn">Assign</button>
	</form>	
	</td>
	<td><a href="<?php echo base_url(); ?>da/delete_user/<?php echo $domain_user->id; ?>" onclick="return confirm('Are you sure?')">Delete User</a></td>
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