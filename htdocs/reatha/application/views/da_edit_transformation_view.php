<?php $this->load->view('header_view'); ?>

<i class="icon icon-arrow-left"></i> <a href="<?php echo base_url(); ?>da/edit_device/<?php echo $t->device->id; ?>">Back to Edit Device</a><br/>

<h3>Edit transformation</h3>
<form action="<?php echo base_url(); ?>da/edit_transformation/<?php echo $t->id; ?>" method="post" class="form">
	<label>Variable: </label>		
	<select name="variable_id">
		<?php foreach ($t->device->variables as $var){

			//don't put the export var in the var list
			if($var->id != $t->export_var_id){
				$selected = ($var->id==$t->variable_id)?'selected="selected"':''; ?> 
				<option value="<?php echo $var->id; ?>" <?php echo $selected; ?>><?php echo $var->name; ?></option>
		<?php } } ?>
	</select>
	<label>Transformation: </label>
	<input type="text" name="transformation" value="<?php echo $t->body; ?>" /> &nbsp;&nbsp;<small>Example: ({var1}+{var2}) * 10}</small>
	<label>Export Variable Name: </label>
	<input type="text" name="export_variable_name" value="<?php $export_var = new Variable($t->export_var_id); echo $export_var->name; ?>" /> &nbsp;&nbsp;<small>Leave it as it is if you don't want to create another var.</small>			
	<br/><button type="submit" class="btn">Edit</button>
</form>



<?php $this->load->view('footer_view'); ?>