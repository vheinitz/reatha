<?php $this->load->view('header_view'); ?>

<i class="icon icon-arrow-left"></i> <a href="<?php echo base_url(); ?>u/notifications/<?php echo $notification_rule->device_id; ?>">Back to notifications list</a><br/>

	<h4>Edit Notification</h4>
	<form class="form-horizontal" action="<?php echo base_url(); ?>u/edit_notification_rule/<?php echo $notification_rule->id; ?>" method="POST">
		<div class="control-group">
			<label class="control-label" for="variable">Variable</label>
			<div class="controls">
				<select id="variable" name="variable">
					<?php foreach ($notification_rule->device->variable as $var) {
						$selected = $var->id==$notification_rule->variable_id?" selected='selected'":"";
						echo "<option value='$var->id' $selected>$var->name</option>";
					}
					?>
				</select>	
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="interval">Condition</label>
			<div class="controls">
				<input type="text" id="condition" name="condition" value="<?php echo $notification_rule->condition; ?>"/>
			</div>
		</div>	
		<div class="control-group">
			<label class="control-label" for="interval">Interval (seconds)</label>
			<div class="controls">
				<input type="text" id="interval" name="interval" value="<?php echo $notification_rule->interval; ?>"/>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="text">Message</label>
			<div class="controls">
				<textarea id="text" name="message"><?php echo $notification_rule->message; ?></textarea>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="subject">Email Subject</label>
			<div class="controls">
				<input type="text" id="subject" name="subject" value="<?php echo $notification_rule->subject; ?>" />
			</div>
		</div>		
		<div class="control-group">
			<div class="controls">
				<button type="submit" class="btn">Save</button>
			</div>
		</div>				
	</form>		

<?php $this->load->view('footer_view'); ?>