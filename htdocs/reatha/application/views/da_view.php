<?php $this->load->view('header_view'); ?>

<table class="table table-striped table-bordered">
  	<thead>
	    <tr>
		    <th style="text-align: center">Device</th>
	        <th style="text-align: center">Action</th>
	    </tr>
	</thead>
	<tbody> 
<?php foreach($devices as $device){ ?>
<tr>
	<td><?php echo $device->description; ?></td><td><a href="/da/delete_device/<?php echo $device->id; ?>" onclick="return confirm('Are you sure?')">Delete</a></td>
<tr>	
<?php } ?>
</tbody>
</table>
<hr/>
<h4>New Device</h4>
<form action="/da/add_device" method="post">
	<fieldset>
		<label>Device Description</label>
		<input type="text" name="device_description" maxlength=150 />
		<label></label>
		<input type="submit" class="btn" value="Add" />
	</fieldset>
</form>

<?php $this->load->view('footer_view'); ?>