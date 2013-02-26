<?php $this->load->view('header_view'); ?>
<h4>Upload Image</h4>
<form action="<?php echo base_url(); ?>test/handle_image_upload" method="post" enctype="multipart/form-data">
	<fieldset>
		<label>Image:</label>
		<input type="file" name="image" />
			
		<label></label>
		<input type="submit" class="btn" value="Add" />
	</fieldset>
</form>


<?php $this->load->view('footer_view'); ?>