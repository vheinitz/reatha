<?php $this->load->view('header_view'); ?>

<i class="icon icon-arrow-left"></i> <a href="<?php echo base_url(); ?>da">Back to device list</a><br/>

<h3>Views for device: <?php echo $device->name; ?></h3>

<?php foreach($views as $view){ ?>
<table class="table table-striped table-bordered">
	<tr>
		<td>Variable:</td>
		<td><?php echo $view->variable->name; ?></td>
		<td rowspan="2"><a href="<?php echo base_url(); ?>da/delete_view/<?php echo $view->id; ?>">Delete</a></td>
	</tr>
	<tr>
		<td>View:</td>
		<td><?php echo htmlentities($view->body); ?></td>
	</tr>			
</table>
<?php } ?>

<h4>New View</h4>
<form class="form-horizontal" action="<?php echo base_url(); ?>da/add_view/" method="POST">
	<div class="control-group">
		<label class="control-label" for="variable">Variable</label>
		<div class="controls">
			<select id="variable" name="variable_id">
				<?php foreach ($device->variable as $var) {
					echo "<option value='$var->id'>$var->name</option>";
				}
				?>
			</select>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="view">View</label>
		<div class="controls">
			<textarea name="view" id="view"></textarea>
		</div>
	</div>	
	<div class="control-group">
		<div class="controls">
			<input type="hidden" name="device_id" value="<?php echo $device->id; ?>" />
			<button type="submit" class="btn">Save</button>
		</div>
	</div>				
</form>


<?php $this->load->view('footer_view'); ?>