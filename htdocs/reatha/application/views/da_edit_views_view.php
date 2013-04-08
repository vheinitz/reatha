<?php $this->load->view('header_view'); ?>

<i class="icon icon-arrow-left"></i> <a href="<?php echo base_url(); ?>da">Back to device list</a><br/>

<h3>Views for device: <?php echo $device->name; ?></h3>

<table class="table table-striped table-bordered">
  	<thead>
	    <tr>
		    <th class="da-td-view-name">Name</th>
		    <th style="text-align: center">View</th>
	        <th class="da-td-view-actions">Action</th>
	    </tr>
	</thead>
	<tbody> 

<?php foreach($views as $view){ ?>
	<tr>
		<td class="da-td-view-name"><?php echo $view->name; ?></td>
		<td><?php echo htmlentities($view->body); ?></td>
		<td class="da-td-view-actions">
			<a class="btn btn-small" href="<?php echo base_url(); ?>da/edit_single_view/<?php echo $view->id; ?>"><i class="icon-pencil"></i> Edit</a><br/>
			<a class="btn btn-small" href="<?php echo base_url(); ?>da/delete_view/<?php echo $view->id; ?>"><i class="icon-remove"></i> Delete</a>
		</td>
	</tr>			
<?php } ?>
</tbody>
</table>
<hr/>

<div class="row">
	<div class="span6" style="text-align:left">
		<h4>New View</h4>
		<form class="form" action="<?php echo base_url(); ?>da/add_view/" method="POST">
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
								if($var->name != 'view')
									echo '{'.$var->name.'} ';
							} ?>			
						</small>
					</div>
					<div>
						<small>
							Available reserved variables: {_deviceName} {_deviceInfo} {_deviceLocation} {_deviceOn} {_alarmLevel}			
						</small>
					</div>					
					<?php
					$images = $device->domain->images->get();
					if(!empty($images)){ ?>
						<div>						
							<small>
								Available images: 
								<?php foreach ($images as $image){
									echo $image->file." ";
								}
								?>
							</small>
						</div>
					<?php } ?>
				</div>
			</div>	
			<div class="control-group">
				<div class="controls">
					<input type="hidden" name="device_id" value="<?php echo $device->id; ?>" />
					<a class="btn" href="javascript:void(0)" onclick="view_preview('<?php echo base_url(); ?>','<?php echo $device->id; ?>');">Preview</a>
					<button type="submit" class="btn">Save</button>
				</div>
			</div>				
		</form>
		<small><b>Upload New Image:</b></small>
		<form action="<?php echo base_url(); ?>da/upload_image" method="post" enctype="multipart/form-data">
			<fieldset>
				<input type="hidden" name="redirect" value="edit_views/<?php echo $device->id; ?>" />
				<input type="file" name="image" />&nbsp;										
				<input type="submit" class="btn" value="Add" />
			</fieldset>
		</form>			
	</div>
	<div class="span5">
		<h4>View Preview</h4>
		<div id="view-preview"></div>
	</div>
</div>

<?php $this->load->view('footer_view'); ?>