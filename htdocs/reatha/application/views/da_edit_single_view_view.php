<?php $this->load->view('header_view'); ?>

<i class="icon icon-arrow-left"></i> <a href="<?php echo base_url(); ?>da/edit_views/<?php echo $view->device->id; ?>">Back to view list</a><br/>

<div class="row">
	<div class="span6" style="text-align:left">
		<h3>Edit view: <?php echo $view->name; ?></h3>
		<form class="form" action="<?php echo base_url(); ?>da/edit_single_view/<?php echo $view->id; ?>" method="POST">
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
					<a class="btn" href="javascript:void(0)" onclick="view_preview('<?php echo base_url(); ?>','<?php echo $view->device->id; ?>');">Preview</a>
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