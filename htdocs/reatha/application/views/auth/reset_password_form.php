<?php $this->load->view('header_view'); ?>

<?php
$new_password = array(
	'name'	=> 'new_password',
	'id'	=> 'new_password',
	'maxlength'	=> $this->config->item('password_max_length', 'tank_auth'),
	'size'	=> 30,
);
$confirm_new_password = array(
	'name'	=> 'confirm_new_password',
	'id'	=> 'confirm_new_password',
	'maxlength'	=> $this->config->item('password_max_length', 'tank_auth'),
	'size' 	=> 30,
);
?>

<form action="<?php echo base_url().$this->uri->uri_string(); ?>" method="post" accept-charset="utf-8">
<table>
	<tr>
		<td>New Password</td>
		<td><input type="password" name="new_password" id="new_password" size=30 maxlength="<?php echo $this->config->item('password_max_length', 'tank_auth'); ?>" /></td>
		<td style="color: red;"><?php echo form_error('new_password'); ?><?php echo isset($errors['new_password'])?$errors['new_password']:''; ?></td>
	</tr>
	<tr>
		<td>Confirm New Password</td>
		<td><input type="password" name="confirm_new_password" id="confirm_new_password" size=30 maxlength="<?php echo $this->config->item('password_max_length', 'tank_auth'); ?>" /></td>
		<td style="color: red;"><?php echo form_error('confirm_new_password'); ?><?php echo isset($errors['confirm_new_password'])?$errors['confirm_new_password']:''; ?></td>
	</tr>
	<tr>
		<td></td>
		<td colspan="2">
			<input type="submit" class="btn" name="change" value="Change Password" />
		</td>
	</tr>	
</table>
</form>

<?php $this->load->view('footer_view'); ?>