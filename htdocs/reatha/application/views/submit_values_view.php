<?php $this->load->view('header_view'); ?>

    <form action="<?php echo base_url(); ?>main/post_variables" method="POST">
	    <fieldset>
	    <label>Device Key</label>
	    <input type="text" name="key">
	    <label>Temperature</label>
	    <input type="text" name="temperatureg">
	    <label>Status</label>
	    <input type="text" name="status">
	    <button type="submit" class="btn">Submit</button>
	    </fieldset>
    </form>

<?php $this->load->view('footer_view'); ?>