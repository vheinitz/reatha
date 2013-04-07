<?php $this->load->view('header_view'); ?>

<div class="row">
	<div class="span12" id="user-devices-list">
		<table class="table table-striped table-bordered">
			<tbody> 
		<?php 
		
			$firstRow=1;
			foreach($devices as $device){
/*			$onclick=""; $class=""; 
			if($device->has_main_view()){
				$onclick = "onclick=\"window.location.href = '".base_url()."u/device/$device->id'\"";
				$class = "class = 'user-device-has-view'";
			}*/
			if(!$firstRow) echo "<tr><td>&nbsp;</td></tr>";
			$firstRow=0;

			?>
		<tr>
			<td>
				<?php
					//if device has device_list_view - process it, if not - show default view
					$list_view = $device->device_list_view->get();
					if($list_view->exists()){
						echo $list_view->process_placeholders();
					} else {
				?>
				<div id="user-device-wrapper" onclick="window.location.href='<?php echo base_url()."u/device/$device->id"; ?>'">
					<?php echo "<b>".$device->name."</b>"; ?><hr/>
					<?php echo "<b>Location: </b>".$device->location; ?><br/>
					<b>Power: </b><span id="power_<?php echo $device->id; ?>"></span><br/>
				</div>
				<a class="btn" id="user-device-notification-setup" href="<?php echo base_url(); ?>u/notifications/<?php echo $device->id; ?>" ><i class="icon-envelope"></i> Notifications</a>		
				<?php } ?>
			</td>
		</tr>			
		<?php } ?>
		</tbody>
		</table>
	</div>
</div>


<script type="text/javascript">
	get_devices_power_status('<?php echo base_url(); ?>');

	setInterval(function(){
		window.location.reload();
		//get_devices_power_status('<?php echo base_url(); ?>');
	}, 5000)
</script>


<?php $this->load->view('footer_view'); ?>