<?php $this->load->view('header_view'); ?>

<table class="table table-striped table-bordered">
  	<thead>
	    <tr>
		    <th style="text-align: center; width:40%">Device</th>
		    <th style="text-align: center">Data</th>
	    </tr>
	</thead>
	<tbody> 
<?php foreach($devices as $device){ ?>
<tr>
	<td>
		<?php echo "<b>Name: </b>".$device->name; ?><br/>
		<?php echo "<b>Location: </b>".$device->location; ?><br/>
		<?php echo "<b>Description: </b>".$device->description; ?><br/>		
	</td>
	<td>
		<?php foreach($device->variable as $var){
			echo $var->name.": <span id='var_$var->id'>".$var->value."</span><br/>";
		} ?>
	</td>
<tr>	
<?php } ?>
</tbody>
</table>

<script type="text/javascript">
	setInterval(function(){
		$.getJSON("<?php echo base_url(); ?>u/get_device_vars", function(variables) {
			$.each(variables, function(i, variable){
				$('#var_'+i).html(variable);
				// $('body').append(i+': '+variable+',  ');
			});
		});		
	}, 2000)
</script>


<?php $this->load->view('footer_view'); ?>