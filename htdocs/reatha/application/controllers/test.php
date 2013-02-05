<?php
class Test extends CI_Controller{
	function __construct(){
		parent::__construct();
	}


	function domain_admins(){
		$d = new Domain(3);
		foreach($d->domain_admins as $domain_admin){
			echo $domain_admin->id."<br/>";
		}
	}

	function add_domain_admin(){
		$d = new Domain(3);
		$user = new User(1);
		$d->save_domain_admin($user);
	}

	function current_url(){
		echo $_SERVER["SERVER_NAME"];
	}

	function da_email(){
        $data['user']   = new User(16);
        $data['domain'] = new Domain(7);
        $data['site_name'] = $this->config->item('website_name', 'tank_auth');
        $data['site_url'] = base_url();
        $this->load->view('email/domain_admin_notification-html', $data);
	}

	function welcome_subj_line(){
		$type = 'welcome';
		$this->load->config('tank_auth', TRUE);
		echo $this->config->item('website_name', 'tank_auth');
		sprintf($this->lang->line('auth_subject_'.$type), $this->config->item('website_name', 'tank_auth'));
	}
}


?>