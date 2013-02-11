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
			<td class="td-device-list" onclick="show_device_vars(<?php echo $device->id; ?>,'<?php echo base_url(); ?>')">
				<?php echo "<b>Name: </b>".$device->name; ?><br/>
				<?php echo "<b>Location: </b>".$device->location; ?><br/>
				<?php echo "<b>Description: </b>".$device->description; ?><br/>		
			</td>
		<tr>	
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

<script type="text/javascript">
/*	setInterval(function(){
		$.getJSON("<?php echo base_url(); ?>u/get_device_vars", function(variables) {
			$.each(variables, function(i, variable){
				$('#var_'+i).html(variable);
				// $('body').append(i+': '+variable+',  ');
			});
		});		
	}, 2000)*/
</script>


<?php $this->load->view('footer_view'); ?>