<?php $this->load->view('header_view'); ?>

<table class="table table-striped table-bordered">
  	<thead>
	    <tr>
		    <th style="text-align: center">Device</th>
		    <th style="text-align: center">Variables</th>
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
		foreach($device->variable as $var){
			echo $var->name." (<a href='".base_url()."da/delete_var/$var->id/'>Delete</a>)<br/>";
		}
	 ?>	 
	</td>	
	<td><?php
		foreach($device_users as $device_user){
			echo $device_user->username."<br/>";
		}
	 ?></td>
	<td>
		<a href="<?php echo base_url(); ?>da/delete_device/<?php echo $device->id; ?>" onclick="return confirm('Are you sure?')">Delete Device</a><br/>
		<button class="btn btn-mini" onclick="show_device_key(<?php echo $device->id; ?>);">Show Key</button>
	</td>
<tr>	
<?php } ?>
</tbody>
</table>
<hr/>
<div class="row">
	<div class="span5">
		<h4>New Device</h4>
		<form action="<?php echo base_url(); ?>da/add_device" method="post">
			<fieldset>
				<label>Device Name</label>
				<input type="text" name="device_name" maxlength=100 />		
				<label>Device Description</label>
				<input type="text" name="device_description" maxlength=250 />
				<label>Device Location</label>
				<input type="text" name="device_location" maxlength=150 />
				<label>Device Variables (comma separated)</label>
				<input type="text" name="device_variables" maxlength=150 placeholder="e.g: status, temperature, color" />		
				<label></label>
				<input type="submit" class="btn" value="Add" />
			</fieldset>
		</form>
	</div>
	<div class="span5">
		<h4>New Variables</h4>
		<form action="<?php echo base_url(); ?>da/add_var" method="post">
			<fieldset>
				<label>Device</label>
				<select name="device_id">
					<?php foreach ($devices as $device) {
						echo "<option value='$device->id'>$device->name</option>";
					} ?>
				</select>		
				<label>Variables (comma separated)</label>
				<input type="text" name="variables" maxlength=50 placeholder="e.g: status, temperature, color" />	
				<label></label>
				<input type="submit" class="btn" value="Add" />
			</fieldset>
		</form>		
	</div>
</div>

<script type="text/javascript">
	function show_device_key($device_id){
		$.get('<?php echo base_url(); ?>da/get_device_key/'+$device_id, function($data){
			$data = $.parseJSON($data);
			if($data.type == 'success'){
				alert($data.key);
			} else {
				alert($data.message);
			}
		});
	}
</script>

<?php $this->load->view('footer_view'); ?>