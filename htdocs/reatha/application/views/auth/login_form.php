
<form action="/auth/login" method="post" accept-charset="utf-8">
<table>
	<tr>
		<td>Username</td>
		<td><input name="login" id="login" maxlength=80 size=30 value="<?php set_value('login'); ?>" /></td>
		<td style="color: red;"><?php echo form_error('login'); ?><?php echo isset($errors['login'])?$errors['login']:''; ?></td>
	</tr>
	<tr>
		<td>Password</td>
		<td><input type="password" name="password" id="password" size=30 /></td>
		<td style="color: red;"><?php echo form_error('password'); ?><?php echo isset($errors['password'])?$errors['password']:''; ?></td>
	</tr>

	<tr>
		<td colspan="3">
			<input type="checkbox" name="remember" id="remember" value=1 checked="<?php set_value('remember'); ?>" style="margin:0;padding:0" />
			<label for="remember">Remember me</label>
			<a href="/auth/forgot_password">Forgot password?</a>
			<?php if ($this->config->item('allow_registration', 'tank_auth')) echo anchor('/auth/register/', 'Register'); ?>
		</td>
	</tr>
</table>
<input type="submit" value="Let me in" /> 
</form>