<?php $this->load->view('header_view'); ?>

<i class="icon icon-arrow-left"></i> <a href="<?php echo base_url(); ?>u">Back to device list</a><br/>

<h3>Notifications for Device: <?php echo $device->name; ?></h3>


<?php foreach ($device->notification_rules as $notification_rule) { ?>
	<table class="table table-bordered table-user-notification-list">
		<tbody>
			<tr>
				<td class="td-notification-list-toggle">
					<?php 
						$checked = $notification_rule->is_activated_for_user_id($user->id)?"checked='checked'":"";
					?>
					<input type="checkbox" id="td-notification-list-checkbox" <?php echo $checked; ?> onclick='toggle_notification_status("<?php echo base_url(); ?>",<?php echo $notification_rule->id; ?>)' />
				</td>
				<td class="td-notification-list-name"><?php echo $notification_rule->name;?></td>
				<td class="td-notification-list-toggle">
					<?php
					$class="";
					 if(!empty($notification_rule->notification->created)){ 
					 	$class=" triggered"; 
					 } 
					 ?>
					 <div class="user-notification-indicator<?php echo $class; ?>"></div>
				</td>
				<td class="td-notification-list-reset">
					<?php
						if(empty($class)){
							$class=" disabled";
							$href="#";
						} else {
							$class="";
							$href=base_url()."u/reset_notification/".$notification_rule->id;
						}

					?>
					<a href="<?php echo $href; ?>" class="btn<?php echo $class; ?>">Reset</a>
				</td>
				<td>
					<a href="#" id="notification-description" data-content="<?php echo $notification_rule->description; ?>" data-toggle="popover" data-original-title="Description">
						<img src="<?php echo base_url(); ?>layout/question-mark.png"/>
					</a>
				</td>
			</tr>		
		</tbody>
	</table>	
<?php } ?>

<script type="text/javascript">
	$(document).ready(function() {
		$('#notification-description').popover();
		update_notifications_view('<?php echo base_url(); ?>','<?php echo $device->id; ?>');
	});
</script>

<?php $this->load->view('footer_view'); ?>