<?php $this->load->view('header_view'); ?>

<div class="row">
	<div class="span4" style="height: 350px; overflow: auto">
		<table class="table table-striped table-bordered">
		  	<thead>
			    <tr>
				    <th style="text-align: center; width:40%">Devices:</th>
			    </tr>
			</thead>
			<tbody> 
		<?php foreach($devices as $device){ ?>
		<tr>
			<td>
				<div id="user-device-wrapper" onclick="show_device_vars(<?php echo $device->id; ?>,'<?php echo base_url(); ?>')">
					<?php echo "<b>Name: </b>".$device->name; ?><br/>
					<?php echo "<b>Location: </b>".$device->location; ?><br/>
					<b>Power: </b><span id="power_<?php echo $device->id; ?>"></span><br/>
				</div>
				<a class="btn btn-mini" href="<?php echo base_url(); ?>u/notifications/<?php echo $device->id; ?>" ><i class="icon-envelope"></i> Notifications</a>		
			</td>
		</tr>	
		<?php } ?>
		</tbody>
		</table>
	</div>
	<div class="span6">
		<table class="table table-bordered">
		  	<thead>
			    <tr>
				    <th style="text-align: center; width:40%">Data:</th>
			    </tr>
			</thead>
			<tbody>
				<td id="user-device-variables">
				</td>
			</tbody>
		</table>		
	</div>
</div>

<!-- Modal -->
<div id="modal-notification-setup" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="myModalLabel">Setup Notifications</h3>
	</div>
	<div class="modal-body">
		<p>One fine body</p>
	</div>
	<div class="modal-footer">
		<button class="btn btn-primary">Save</button>
	</div>
</div>

<script type="text/javascript">
	get_devices_power_status('<?php echo base_url(); ?>');

	setInterval(function(){
		get_devices_power_status('<?php echo base_url(); ?>');	
	}, 5000)
</script>


<?php $this->load->view('footer_view'); ?>