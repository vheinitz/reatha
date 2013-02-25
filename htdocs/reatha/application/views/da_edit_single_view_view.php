<?php $this->load->view('header_view'); ?>

<i class="icon icon-arrow-left"></i> <a href="<?php echo base_url(); ?>da/edit_views/<?php echo $view->device->id; ?>">Back to view list</a><br/>

<h3>Edit view: <?php echo $view->name; ?></h3>

<form class="form-horizontal" action="<?php echo base_url(); ?>da/edit_single_view/<?php echo $view->id; ?>" method="POST">
	<div class="control-group">
		<label class="control-label" for="name">Name:</label>
		<div class="controls">
			<input type="text" name="name" id="name" value="<?php echo $view->name; ?>"/>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="view-body">View:</label>
		<div class="controls">
			<textarea name="view" id="view-body"><?php echo $view->body; ?></textarea>
			<div>
				<small>
					Available variables:
					<?php foreach ($view->device->variable as $var) {
						echo '{'.$var->name.'} ';
					} ?>			
				</small>
			</div>			
		</div>
	</div>	
	<div class="control-group">
		<div class="controls">
			<button type="submit" class="btn">Save</button>
		</div>
	</div>				
</form>


<?php $this->load->view('footer_view'); ?>