<?php $this->load->view('header_view'); ?>

<table class="table table-striped table-bordered">
  	<thead>
	    <tr>
		    <th style="text-align: center">Image</th>
	        <th style="text-align: center">Image Name</th>
	        <th style="text-align: center">Action</th>
	    </tr>
	</thead>
	<tbody> 
<?php foreach($images as $image){ ?>
<tr>
	<td><img src="<?php echo base_url().'assets/'.$domain->name.'/'.$image->file; ?>" class="da-image" /></td>
	<td><?php echo $image->file; ?></td>
	<td><a href="<?php echo base_url(); ?>da/delete_image/<?php echo $image->id; ?>" onclick="return confirm('Are you sure?')">Delete Image</a></td>
</tr>
<?php } ?>
</tbody>
</table>
<hr/>
<h4>Upload New Image</h4>
		<form action="<?php echo base_url(); ?>da/upload_image" method="post" enctype="multipart/form-data">
			<fieldset>
				<label>Image:</label>
				<input type="file" name="image" />
					
				<label></label>
				<input type="submit" class="btn" value="Add" />
			</fieldset>
		</form>

<?php $this->load->view('footer_view'); ?>