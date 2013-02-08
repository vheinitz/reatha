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
			echo $var->name.': '.$var->value.'<br/>';
		} ?>
	</td>
<tr>	
<?php } ?>
</tbody>
</table>


<?php $this->load->view('footer_view'); ?>