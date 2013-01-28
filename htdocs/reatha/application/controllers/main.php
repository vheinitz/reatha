<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller{
	function __construnct(){
		parent::__construnct();
		if(!$this->tank_auth->is_logged_in()){
			redirect('/auth/login');
		}
	}

	function index(){
		$user = new User($this->tank_auth->get_user_id());
		$data['user'] = $user;
		switch ($user->role) {
			case 1:
				$this->load->view('admin_view',$data);
				break;
			case 2:
				$this->load->view('domain_admin_view',$data);
				break;				
			case 3:
				$this->load->view('user_view',$data);
				break;			
		}
	}
}


?>