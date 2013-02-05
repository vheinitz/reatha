<?php $this->load->view('header_view'); ?>

<form action="<?php echo base_url(); ?>auth/login" method="post" accept-charset="utf-8">
<table>
	<tr>
		<td>Username</td>
		<td><input type="text" name="login" id="login" maxlength=80 size=30 value="<?php set_value('login'); ?>" /></td>
		<td style="color: red;"><?php echo form_error('login'); ?><?php echo isset($errors['login'])?$errors['login']:''; ?></td>
	</tr>
	<tr>
		<td>Password</td>
		<td><input type="password" name="password" id="password" size=30 /></td>
		<td style="color: red;"><?php echo form_error('password'); ?><?php echo isset($errors['password'])?$errors['password']:''; ?></td>
	</tr>
	<tr>
		<td></td>
		<td colspan="2">
			<input type="checkbox" name="remember" id="remember" value=1 checked="<?php set_value('remember'); ?>" style="margin:0;padding:0" />
			Remember me
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan="2">
			<input type="submit" class="btn" value="Let me in" />
		</td>
	</tr>	
</table>
 
</form>

<?php $this->load->view('footer_view'); ?>