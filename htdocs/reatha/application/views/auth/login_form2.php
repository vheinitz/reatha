<?php

if ($login_by_username AND $login_by_email) {
	$login_label = 'Email or login';
} else if ($login_by_username) {
	$login_label = 'Login';
} else {
	$login_label = 'Email';
}
$password = array(
	'name'	=> 'password',
	'id'	=> 'password',
	'size'	=> 30,
);
$remember = array(
	'name'	=> 'remember',
	'id'	=> 'remember',
	'value'	=> 1,
	'checked'	=> set_value('remember'),
	'style' => 'margin:0;padding:0',
);
?>
<form action="<?php echo $this->uri->uri_string(); ?>" method="POST">
<table>
	<tr>
		<td>Username</td>
		<td><input type="text" name="login" maxlength="80" size="30" value="<?php set_value('login'); ?>" /></td>
		<td style="color: red;"><?php echo form_error('login'); ?><?php echo isset($errors['login'])?$errors['login']:''; ?></td>
	</tr>
	<tr>
		<td>Password</td>
		<td><input type="password" name="password" size="30" /></td>
		<td style="color: red;"><?php echo form_error('password'); ?><?php echo isset($errors['password'])?$errors['password']:''; ?></td>
	</tr>
	<tr>
		<td colspan="3">
			<input type="checkbox" name="remember" value="1" checked="<?php set_value('remember'); ?>" style="margin:0;padding:0" />
			<?php echo form_label('Remember me', $remember['id']); ?>
			<?php echo anchor('/auth/forgot_password/', 'Forgot password'); ?>
			<?php if ($this->config->item('allow_registration', 'tank_auth')) echo anchor('/auth/register/', 'Register'); ?>
		</td>
	</tr>
</table>
<input type="submit" value="Let me in" />
</form>