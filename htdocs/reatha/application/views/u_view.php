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
			<td id="<?php echo $device->id; ?>">
				<!-- this section will be filled by  get_devices_list_view() -->
				<?php echo $device->get_list_view(); ?>
			</td>
		</tr>			
		<?php } ?>
		</tbody>
		</table>
	</div>
</div>


<script type="text/javascript">
	get_devices_list_view('<?php echo base_url(); ?>');

	setInterval(function(){		
		get_devices_list_view('<?php echo base_url(); ?>');
	}, 5000);
</script>


<?php $this->load->view('footer_view'); ?>