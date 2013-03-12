<?php $this->load->view('header_view'); ?>

<i class="icon icon-arrow-left"></i> <a href="<?php echo base_url(); ?>da/edit_device/<?php echo $device_id; ?>">Back to Edit Device</a>

<h3>Custom List View</h3>

<div class="row">
	<div class="span6" style="text-align:left">
		<form class="form" action="<?php echo base_url(); ?>da/edit_device_list_view/<?php echo $view->id; ?>" method="POST">
			<div class="control-group">
				<label class="control-label" for="view-body">View:</label>
				<div class="controls">
					<textarea name="view" id="device-list-view-body"><?php echo $view->body; ?></textarea>
					<div>
						<small>
							Available variables:
							<?php foreach ($view->device->variable as $var) {
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
					<div>
						<small>
							Available reserved views: {view:_deviceList} {view:_notifications} {view:_deviceView}
						</small>
					</div>											
					<?php
					$images = $view->device->domain->images->get();
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
					<a class="btn" href="javascript:void(0)" onclick="device_list_preview('<?php echo base_url(); ?>',<?php echo $device_id; ?>)">Preview</a>					
					<input type="hidden" name="device_id" value="<?php echo $device_id; ?>" />
					<button type="submit" class="btn">Save</button>
				</div>
			</div>				
		</form>
	</div>
	<div class="span5">
		<h4>View Preview</h4>
		<div id="view-preview"></div>
	</div>	
</div>


<?php $this->load->view('footer_view'); ?>