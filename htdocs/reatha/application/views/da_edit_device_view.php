<?php $this->load->view('header_view'); ?>

<i class="icon icon-arrow-left"></i> <a href="<?php echo base_url(); ?>da">Back to device list</a><br/>

<h3>Device: <?php echo $device->name; ?></h3>
<table class="table table-striped table-bordered">
	<tr>
		<td>Name:</td>
		<td><?php echo $device->name; ?></td>
		<td><a href="#device-change-name" data-toggle="modal" role="button">Change</a></td>
	</tr>
	<tr>
		<td>Description:</td>
		<td><?php echo $device->description; ?></td>
		<td><a href="#device-change-description" data-toggle="modal" role="button">Change</a></td>
	</tr>	
	<tr>
		<td>Variables:</td>
		<td><?php 
			foreach($device->variable as $var){
				echo $var->name." (<a href='".base_url()."da/delete_var/$var->id/'>Delete</a>)<br/>";
			}
		 ?></td>
		<td><a href="#device-add-variables" data-toggle="modal" role="button">Add Variables</a></td>
	</tr>	
	<tr>
		<td>Key:</td>
		<td><a href="#" onclick="show_device_key(<?php echo $device->id; ?>,'<?php echo base_url(); ?>');" >Show</a></td>
		<td><a href="<?php echo base_url(); ?>da/generate_device_key/<?php echo $device->id; ?>">Re-generate</a></td>
	</tr>	
</table>

<!--Change Name Modal -->
<form action="<?php echo base_url(); ?>da/change_device_name" method="post" class="form-inline">
	<div id="device-change-name" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Change Device Name</h3>
		</div>
		<div class="modal-body">
			<label>Device Name: </label>		
			<input type="text" name="device_name" value="<?php echo $device->name; ?>" />
			<input type="hidden" name="device_id" value="<?php echo $device->id; ?>" />
		</div>
		<div class="modal-footer">
			<button type="submit" class="btn btn-primary">Save</button>
		</div>
	</div>
</form>

<!--Change Description Modal -->
<form action="<?php echo base_url(); ?>da/change_device_description" method="post" class="form-inline">
	<div id="device-change-description" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Change Device Description</h3>
		</div>
		<div class="modal-body">
			<label>Description: </label>		
			<input type="text" name="device_description" value="<?php echo $device->description; ?>" />
			<input type="hidden" name="device_id" value="<?php echo $device->id; ?>" />
		</div>
		<div class="modal-footer">
			<button type="submit" class="btn btn-primary">Save</button>
		</div>
	</div>
</form>

<!--Add new variable modal -->
<form action="<?php echo base_url(); ?>da/add_var" method="post" class="form">
	<div id="device-add-variables" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Add Variables</h3>
		</div>
		<div class="modal-body">
			<label>Variables (comma separated): </label>		
			<input type="text" name="variables" maxlength=50 placeholder="e.g: status, temperature, color" />
			<input type="hidden" name="device_id" value="<?php echo $device->id; ?>" />
		</div>
		<div class="modal-footer">
			<button type="submit" class="btn btn-primary">Add</button>
		</div>
	</div>
</form>

<?php $this->load->view('footer_view'); ?>