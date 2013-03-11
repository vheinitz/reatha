<?php $this->load->view('header_view'); ?>

<i class="icon icon-arrow-left"></i> <a href="<?php echo base_url(); ?>da/users">Back to User List</a><br/><br/>

<div class="row">
	<div class="span6" style="text-align:left">
		<form class="form" action="<?php echo base_url(); ?>da/edit_device_list_view/<?php echo $view->id; ?>" method="POST">
			<div class="control-group">
				<label class="control-label" for="view-body">View:</label>
				<div class="controls">
					<textarea name="view" id="device-list-view-body"><?php echo $view->body; ?></textarea>							
				</div>
			</div>	
			<div class="control-group">
				<div class="controls">
					<a class="btn" href="javascript:void(0)" onclick="alert('TODO')">Preview</a>					
					<input type="hidden" name="user_id" value="<?php echo $device_list_user_id; ?>" />
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