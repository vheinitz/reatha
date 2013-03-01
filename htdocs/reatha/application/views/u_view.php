<?php $this->load->view('header_view'); ?>

<div class="row">
	<div class="span12" id="user-devices-list">
		<table class="table table-striped table-bordered">
		  	<thead>
			    <tr>
				    <th style="text-align: center; width:40%">Devices:</th>
			    </tr>
			</thead>
			<tbody> 
		<?php foreach($devices as $device){
/*			$onclick=""; $class=""; 
			if($device->has_main_view()){
				$onclick = "onclick=\"window.location.href = '".base_url()."u/device/$device->id'\"";
				$class = "class = 'user-device-has-view'";
			}*/
			?>
		<tr>
			<td>
				<div id="user-device-wrapper" onclick="window.location.href='<?php echo base_url()."u/device/$device->id"; ?>'">
					<?php echo "<b>Name: </b>".$device->name; ?><br/>
					<?php echo "<b>Location: </b>".$device->location; ?><br/>
					<b>Power: </b><span id="power_<?php echo $device->id; ?>"></span><br/>
				</div>
				<a class="btn" id="user-device-notification-setup" href="<?php echo base_url(); ?>u/notifications/<?php echo $device->id; ?>" ><i class="icon-envelope"></i> Notifications</a>		
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
		get_devices_power_status('<?php echo base_url(); ?>');	
	}, 5000)
</script>


<?php $this->load->view('footer_view'); ?>