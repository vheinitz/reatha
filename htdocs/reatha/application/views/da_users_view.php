<?php $this->load->view('header_view'); ?>

<table class="table table-striped table-bordered">
  	<thead>
	    <tr>
		    <th style="text-align: center">Username</th>
	        <th style="text-align: center">Device Assigned</th>
	        <th style="text-align: center">Action</th>
	    </tr>
	</thead>
	<tbody> 
<?php foreach($users as $domain_user){ ?>
<tr>
	<td><?php echo $domain_user->username; ?></td><td><?php echo $domain_user->device->description; ?></td><td><a href="#" onclick="return confirm('Are you sure?')">Delete</a></td>
</tr>
<?php } ?>
</tbody>
</table>
<hr/>
<h4>New User</h4>
		<form action="/da/add_user" method="post">
			<fieldset>
				<label>Username</label>
			 	<input type="text" name="username" maxlength=150 />

				<label>Password</label>
				<input type="password" name="password" maxlength=150 />

				<label>Email</label>
				<input type="text" name="email" maxlength=150 />
				
				<label>Assign to device</label>
			 	<select name="device">
				<?php foreach($devices as $device){ 
					echo "<option value='".$device->id."'>".$device->description."</option>";
			 	} ?> 
				</select>

				<label></label>
				<input type="submit" class="btn" value="Add" />
			</fieldset>
		</form>

<?php $this->load->view('footer_view'); ?>