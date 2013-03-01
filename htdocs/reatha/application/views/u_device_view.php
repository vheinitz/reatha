<?php $this->load->view('header_view'); ?>
	<div class="row">
		<div class="span12" id="user-device-data">
			<i class="icon icon-arrow-left"></i> <a href="<?php echo base_url(); ?>u">Back to device list</a><br/><br/>
			<table class="table table-bordered">
				<tbody>
					<td id="user-device-variables">
					</td>
				</tbody>
			</table>		
		</div>
	</div>

	<script type="text/javascript">
		<?php if($view->exists()) { ?>
 			show_device_view('<?php echo $view->id; ?>','<?php echo base_url(); ?>')			
		 <?php } else {?> 
 			show_device_vars('<?php echo $device->id; ?>','<?php echo base_url(); ?>')		 	
		 	<?php }?>
	</script>

<?php $this->load->view('footer_view'); ?>