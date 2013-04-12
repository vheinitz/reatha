<?php $this->load->view('header_view'); ?>
<link href="<?php echo base_url(); ?>css/jquery.minicolors.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo base_url(); ?>js/jquery.minicolors.js"></script>
<h3>Domain Design</h3>


<form class="form-horizontal" action="<?php echo base_url(); ?>da/edit_design" method="POST">
	<div class="control-group">
		<label class="control-label" for="name">Header Title:</label>
		<div class="controls">
			<input type="text" id="header_title" name="header_title" value="<?php echo $domain->header_title; ?>"/>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="description">Header Color:</label>
		<div class="controls">
			<input type="text" name="header_color" class="header_color" value="<?php echo $domain->header_color; ?>" />
		</div>
	</div>	
	<div class="control-group">
		<label class="control-label" for="description">Header Text Color:</label>
		<div class="controls">
			<input type="text" name="header_text_color" class="header_text_color" value="<?php echo $domain->header_text_color; ?>" />
		</div>
	</div>		
	<div class="control-group">
		<label class="control-label" for="description">Footer Text:</label>
		<div class="controls">
			<input type="text" name="footer_text" value="<?php echo $domain->footer_text; ?>" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="description">Footer Color:</label>
		<div class="controls">
			<input type="text" name="footer_color" class="footer_color" value="<?php echo $domain->footer_color; ?>" />
		</div>
	</div>	
	<div class="control-group">
		<label class="control-label" for="description">Footer Text Color:</label>
		<div class="controls">
			<input type="text" name="footer_text_color" class="footer_text_color" value="<?php echo $domain->footer_text_color; ?>" />
		</div>
	</div>					
	<div class="control-group">
		<div class="controls">
			<input type="hidden" name="domain_id" value="<?php echo $domain->id; ?>" />
			<button type="submit" class="btn">Save</button>
		</div>
	</div>				
</form>

<script type="text/javascript">
$(document).ready(function (){
	$('input.header_color').minicolors();	
	$('input.header_text_color').minicolors();
	$('input.footer_color').minicolors();	
	$('input.footer_text_color').minicolors();	
});
</script>

<?php $this->load->view('footer_view'); ?>