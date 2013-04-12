<?php $this->load->view('header_view'); ?>

		<form action="<?php echo base_url(); ?>auth/login" method="post" accept-charset="utf-8">
		<table class="login-table">
			<tr>
				<td>Username:&nbsp;</td>
				<td><input type="text" name="login" id="login" maxlength=80 size=30 value="<?php $this->form_validation->set_value('login'); ?>" /></td>
				<td style="color: red;"><?php echo form_error('login'); ?><?php echo isset($errors['login'])?$errors['login']:''; ?></td>
			</tr>
			<tr>
				<td>Password:&nbsp;</td>
				<td><input type="password" name="password" id="password" size=30 /></td>
				<td style="color: red;"><?php echo form_error('password'); ?><?php echo isset($errors['password'])?$errors['password']:''; ?></td>
			</tr>
			<tr>
				<td></td>
				<td colspan="2">
					<br/>
					<input type="checkbox" name="remember" id="remember" value=1 checked="<?php set_value('remember'); ?>" style="margin:0;padding:0" />
					Remember me &nbsp;
					<div class="login-forgot"><a href="<?php echo base_url(); ?>auth/forgot_password">Forgot Password?</a></div>
				</td>
			</tr>
			<tr>
				<td></td>
				<td colspan="2">
					<br/>
					<input type="submit" class="btn" value="Let me in" />
				</td>
			</tr>
			<tr>
				<td colspan="3" class="td-login-copyright">
					<hr/>
					&copy;2013 Retha.de V0.0.1
				</td>
			</tr>	
		</table>
		</form>


<?php $this->load->view('footer_view'); ?>