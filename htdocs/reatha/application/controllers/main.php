<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller{
	function __construct(){
		parent::__construct();
		if(!$this->tank_auth->is_logged_in()){
			redirect('auth/login');
		}
	}

	function index(){
		$user = new User($this->tank_auth->get_user_id());
		$data['user'] = $user;
		switch ($user->role) {
			case 1:
				redirect('a');
				break;
			case 2:
				redirect('da');
				break;				
			case 3:
				redirect('u');
				break;			
		}
	}
}


?>