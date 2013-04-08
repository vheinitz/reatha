<?php $this->load->view('header_view'); ?>

<h3>Domain Design</h3>


<form class="form-horizontal" action="" method="POST">
	<div class="control-group">
		<label class="control-label" for="name">Header Title:</label>
		<div class="controls">
			<input type="text" id="header_title" name="header_title"/>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="description">Header Color (hex):</label>
		<div class="controls">
			<textarea id="description" name="header_color"></textarea>
		</div>
	</div>			
	<div class="control-group">
		<div class="controls">
			<input type="hidden" name="domain_id" value="<?php echo $domain->id; ?>" />
			<button type="submit" class="btn">Save</button>
		</div>
	</div>				
</form>

<?php $this->load->view('footer_view'); ?>