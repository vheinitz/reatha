<?php $this->load->view('header_view'); ?>

<i class="icon icon-arrow-left"></i> <a href="<?php echo base_url(); ?>da">Back to device list</a><br/>

<h3>Notifications for Device: <?php echo $device->name; ?></h3>


<?php foreach ($device->notification_rules as $notification_rule) { ?>
	<table class="table table-striped table-bordered">
		<tbody>
			<tr>
				<td class="td-notification-list-row-caption">Name:</td><td><?php echo $notification_rule->name;?></td>
				<td rowspan="9" class="td-notification-list-action">
					<?php if($notification_rule->activated) { ?>
						<a href="<?php echo base_url(); ?>da/toggle_notification_status/<?php echo $notification_rule->id."/0"; ?>" class="btn btn-small" id="notification-list-action"><i class="icon-pause"></i> Deactivate</a><br/>
					<?php } else { ?>
						<a href="<?php echo base_url(); ?>da/toggle_notification_status/<?php echo $notification_rule->id."/1"; ?>" class="btn btn-small" id="notification-list-action"><i class="icon-play"></i> Activate</a><br/>
					<?php } ?>
					<a class="btn btn-small" id="notification-list-action" href="<?php echo base_url(); ?>da/edit_notification_rule/<?php echo $notification_rule->id; ?>"><i class="icon-pencil"></i> Edit</a><br/>
					<a class="btn btn-small" id="notification-list-action" href="<?php echo base_url(); ?>da/delete_notification_rule/<?php echo $notification_rule->id; ?>"><i class="icon-remove"></i> Delete</a>
				</td>
			</tr>
			<tr><td>Description:</td><td><?php echo $notification_rule->description;?></td></tr>
			<tr><td>Variable:</td><td><?php echo $notification_rule->variable->name;?></td></tr>			
			<tr><td>Condition:</td><td><?php echo $notification_rule->condition;?></td></tr>
			<tr><td>Min. Interval:</td><td><?php echo $notification_rule->interval;?></td></tr>
			<tr><td>Severity Level:</td><td><?php echo $notification_rule->get_severity_level();?></td></tr>
			<tr><td>Message:</td><td><?php echo $notification_rule->message;?></td></tr>
			<tr><td>Email Subject:</td><td><?php echo $notification_rule->subject;?></td></tr>
			<tr><td>Activated:</td><td><?php echo $notification_rule->activated?"Yes":"No";?></td></tr>			
		</tbody>
	</table>	
<?php } ?>


<h4>New Notification</h4>
<form class="form-horizontal" action="<?php echo base_url(); ?>da/add_notification_rule/" method="POST">
	<div class="control-group">
		<label class="control-label" for="name">Name</label>
		<div class="controls">
			<input type="text" id="name" name="name"/>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="description">Description</label>
		<div class="controls">
			<textarea id="description" name="description"></textarea>
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
		<label class="control-label" for="condition">Condition</label>
		<div class="controls">
			<input type="text" id="condition" name="condition"/>
		</div>
	</div>	
	<div class="control-group">
		<label class="control-label" for="interval">Interval (seconds)</label>
		<div class="controls">
			<input type="text" id="interval" name="interval"/>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="interval">Severity Level</label>
		<div class="controls">
			<label class="radio inline" ><input type="radio" name="severity_level" value="1"/>Info</label>
			<label class="radio inline" ><input type="radio" name="severity_level" value="2"/>Warning</label>
			<label class="radio inline" ><input type="radio" name="severity_level" value="3"/>Error</label>
		</div>
	</div>	
	<div class="control-group">
		<label class="control-label" for="text">Message</label>
		<div class="controls">
			<textarea id="text" name="message"></textarea>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="subject">Email Subject</label>
		<div class="controls">
			<input type="text" id="subject" name="subject" value="You have a new notification" />
		</div>
	</div>
	<div class="control-group">
		<label></label>
		<div class="controls">
			<small>
				Available variables:
				<?php foreach ($device->variable as $var) {
					if($var->name != 'view')
						echo '{'.$var->name.'} ';
				} ?>			
			</small><br/>			
			<small>
				Available reserved variables: {_deviceName} {_deviceInfo} {_deviceLocation} {_deviceOn} {_alarmLevel}			
			</small>			
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