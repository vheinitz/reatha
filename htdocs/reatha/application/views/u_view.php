<?php $this->load->view('header_view'); ?>

<table class="table table-striped table-bordered">
  	<thead>
	    <tr>
		    <th style="text-align: center">Device</th>
	    </tr>
	</thead>
	<tbody> 
<?php foreach($devices as $device){ ?>
<tr>
	<td><?php echo $device->description; ?></td>
<tr>	
<?php } ?>
</tbody>
</table>


<?php $this->load->view('footer_view'); ?>