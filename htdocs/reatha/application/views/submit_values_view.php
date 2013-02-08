<?php $this->load->view('header_view'); ?>

    <form action="<?php echo base_url(); ?>main/post_variables" method="POST">
	    <fieldset>
	    <label>Device Key</label>
	    <input type="text" name="key">
	    <label>Temperature</label>
	    <input type="text" name="temperature">
	    <label>Status</label>
	    <input type="text" name="status">
	    <button type="submit" class="btn">Submit</button>
	    </fieldset>
    </form>

    <hr/>
    <h5>Life Check</h5>
        <form action="<?php echo base_url(); ?>main/post_variables" method="POST">
	    <fieldset>
	    <label>Device Key</label>
	    <input type="text" name="key">	    	
	    <input type="hidden" name="lc" value="">
	    <button type="submit" class="btn">Submit</button>
	    </fieldset>
    </form>

<?php $this->load->view('footer_view'); ?>