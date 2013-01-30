<?php $this->load->view('header_view'); ?>

<table class="table table-striped table-bordered">
  	<thead>
	    <tr>
		    <th style="text-align: center">Device</th>
		    <th style="text-align: center">Assigned Users</th>
	        <th style="text-align: center">Action</th>
	    </tr>
	</thead>
	<tbody> 
<?php foreach($devices as $device){ 
	$device_users = $device->users->get(); ?>
<tr>
	<td><?php echo $device->description; ?></td>
	<td><?php
		foreach($device_users as $device_user){
			echo $device_user->username."<br/>";
		}
	 ?></td>
	<td><a href="<?php echo base_url(); ?>da/delete_device/<?php echo $device->id; ?>" onclick="return confirm('Are you sure?')">Delete Device</a></td>
<tr>	
<?php } ?>
</tbody>
</table>
<hr/>
<h4>New Device</h4>
<form action="<?php echo base_url(); ?>da/add_device" method="post">
	<fieldset>
		<label>Device Description</label>
		<input type="text" name="device_description" maxlength=150 />
		<label></label>
		<input type="submit" class="btn" value="Add" />
	</fieldset>
</form>

<?php $this->load->view('footer_view'); ?>