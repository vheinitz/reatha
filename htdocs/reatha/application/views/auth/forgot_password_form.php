<?php $this->load->view('header_view'); ?>

<?php
if ($this->config->item('use_username', 'tank_auth')) {
	$login_label = 'Email or login';
} else {
	$login_label = 'Email';
}
?>

<form action="<?php echo base_url(); ?>auth/forgot_password" method="post" accept-charset="utf-8">
<table>
	<tr>
		<td><?php echo $login_label ?></td>
		<td><input type="text" name="login" id="login" value="<?php echo $this->form_validation->set_value('login'); ?>" maxlength=80 size=30 /></td>
		<td style="color: red;"><?php echo form_error('login'); ?><?php echo isset($errors['login'])?$errors['login']:''; ?></td>
	</tr>
</table>
<input type="submit" class="btn" name="reset" value="Get a new password" />

</form>

<?php $this->load->view('footer_view'); ?>