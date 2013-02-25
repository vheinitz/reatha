<?php $this->load->view('header_view'); ?>

<i class="icon icon-arrow-left"></i> <a href="<?php echo base_url(); ?>da">Back to device list</a><br/>

<h3>Views for device: <?php echo $device->name; ?></h3>

<table class="table table-striped table-bordered">
  	<thead>
	    <tr>
		    <th style="text-align: center">Name</th>
		    <th style="text-align: center">View</th>
	        <th style="text-align: center">Action</th>
	    </tr>
	</thead>
	<tbody> 

<?php foreach($views as $view){ ?>
	<tr>
		<td><?php echo $view->name; ?></td>
		<td><?php echo htmlentities($view->body); ?></td>
		<td>
			<a class="btn btn-small" href="<?php echo base_url(); ?>da/edit_single_view/<?php echo $view->id; ?>"><i class="icon-pencil"></i> Edit</a><br/>
			<a class="btn btn-small" href="<?php echo base_url(); ?>da/delete_view/<?php echo $view->id; ?>"><i class="icon-remove"></i> Delete</a>
		</td>
	</tr>			
<?php } ?>
</tbody>
</table>
<hr/>

<h4>New View</h4>
<form class="form-horizontal span8" action="<?php echo base_url(); ?>da/add_view/" method="POST">
	<div class="control-group">
		<label class="control-label" for="name">Name:</label>
		<div class="controls">
			<input type="text" name="name" id="name" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="view-body">View:</label>
		<div class="controls">
			<textarea name="view" id="view-body"></textarea>
			<div>
				<small>
					Available variables:
					<?php foreach ($device->variable as $var) {
						echo '{'.$var->name.'} ';
					} ?>			
				</small>
			</div>
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