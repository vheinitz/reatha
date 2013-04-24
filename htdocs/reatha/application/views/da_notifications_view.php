<?php $this->load->view('header_view'); ?>

<i class="icon icon-arrow-left"></i> <a href="<?php echo base_url(); ?>da">Back to device list</a><br/>

<h3>Notifications for Device: <?php echo $device->name; ?></h3>
	<?php if($device->notification_rules->exists()){ ?>
	<table class="table table-hover table-da-notification-list">
		<thead>
			<tr>
				<th>#</th><th style="text-align:center">Name</th><th style="text-align:center">Actions</th>
			</tr>
		</thead>			
		<tbody>
		<?php foreach ($device->notification_rules as $key=>$notification_rule) { ?>
			<tr>
				<td><?php echo $key+1; ?></td>
				<td style="text-align:center"><?php echo $notification_rule->name;?></td>
				<td class="td-notification-list-action">
					<?php if($notification_rule->activated) { ?>
						<a href="<?php echo base_url(); ?>da/toggle_notification_status/<?php echo $notification_rule->id."/0"; ?>" class="btn btn-small" id="notification-list-action"><i class="icon-pause"></i> Deactivate</a>&nbsp;
					<?php } else { ?>
						<a href="<?php echo base_url(); ?>da/toggle_notification_status/<?php echo $notification_rule->id."/1"; ?>" class="btn btn-small" id="notification-list-action"><i class="icon-play"></i> Activate</a>&nbsp;
					<?php } ?>
					<a class="btn btn-small" id="notification-list-action" href="<?php echo base_url(); ?>da/edit_notification_rule/<?php echo $notification_rule->id; ?>"><i class="icon-pencil"></i> Edit</a>&nbsp;
					<a class="btn btn-small" id="notification-list-action" href="<?php echo base_url(); ?>da/delete_notification_rule/<?php echo $notification_rule->id; ?>"><i class="icon-remove"></i> Delete</a>&nbsp;
				</td>
			</tr>		
		<?php } ?>
		</tbody>
	</table>
	<?php } else {
		echo "No notifications";		
	}; ?>
	<br/>
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