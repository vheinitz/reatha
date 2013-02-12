<?php $this->load->view('header_view'); ?>

<i class="icon icon-arrow-left"></i> <a href="<?php echo base_url(); ?>u">Back to device list</a><br/>

<h3>Notifications for Device: <?php echo $device->name; ?></h3>

<form class="form-horizontal" action="<?php echo base_url(); ?>u/setup_notification/" method="POST">
	<div class="control-group">
		<label class="control-label" for="status">Status</label>
		<div class="controls">
			<label class="checkbox">
			<input type="checkbox" id="status" name="status" value="1"> Enabled
			</label>
		</div>		
	</div>
	<div class="control-group">
		<label class="control-label" for="variable">Variable</label>
		<div class="controls">
			<select id="variable" name="variable">
				<?php foreach ($device->variable as $var) {
					echo "<option value='$var->id'>$var->name</option>";
				}
				?>
			</select>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="interval">Interval (minutes)</label>
		<div class="controls">
			<input type="text" id="interval" name="interval"/>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="text">Text</label>
		<div class="controls">
			<textarea id="text" name="text"></textarea>
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