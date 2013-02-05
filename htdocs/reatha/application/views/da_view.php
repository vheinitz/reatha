<<<<<<< HEAD
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
	<td>
		<?php echo "<b>Name: </b>".$device->name; ?><br/>
		<?php echo "<b>Location: </b>".$device->location; ?><br/>
		<?php echo "<b>Description: </b>".$device->description; ?><br/>
	</td>
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
		<label>Device Name</label>
		<input type="text" name="device_name" maxlength=100 />		
		<label>Device Description</label>
		<input type="text" name="device_description" maxlength=250 />
		<label>Device Location</label>
		<input type="text" name="device_location" maxlength=150 />
		<label></label>
		<input type="submit" class="btn" value="Add" />
	</fieldset>
</form>

=======
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
	<td>
		<?php echo "<b>Name: </b>".$device->name; ?><br/>
		<?php echo "<b>Location: </b>".$device->location; ?><br/>
		<?php echo "<b>Description: </b>".$device->description; ?><br/>
	</td>
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
		<label>Device Name</label>
		<input type="text" name="device_name" maxlength=100 />		
		<label>Device Description</label>
		<input type="text" name="device_description" maxlength=250 />
		<label>Device Location</label>
		<input type="text" name="device_location" maxlength=150 />
		<label></label>
		<input type="submit" class="btn" value="Add" />
	</fieldset>
</form>

>>>>>>> 532811372554ee26da7ed1f128604c714d0512c1
<?php $this->load->view('footer_view'); ?>