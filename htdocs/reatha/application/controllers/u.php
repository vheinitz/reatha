<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class U extends CI_Controller{
	function __construct(){
		parent::__construct();
		if(!$this->tank_auth->is_logged_in()){
			redirect('/auth/login');
		}
		$user = new User($this->tank_auth->get_user_id());
		//check if user has privilleges, if not - show error
		if($user->role != '3'){
			show_error("Sorry, you don't have acces to this page");
		}		
	}

	function index(){
		$user = new User($this->tank_auth->get_user_id());
		$data['user'] 		= $user;
		$data['devices']	= $user->devices->get();
		$this->load->view('u_view',$data);
	}

	function get_device_vars($device_id){
		$user = new User($this->tank_auth->get_user_id());
		$device = new Device($device_id);
		$vars = array();
		if($device->exists()){
			if($user->has_device($device_id)){
				//get variables for each device
				foreach ($device->variable as $var) {
					$vars[] = array('name'=>$var->name, 'value'=>$var->value);
				}
				echo json_encode($vars);				
			} else {
				log_message('error',"u/get_device_vars | user is not assigned to device, device_id: $device_id, user id: $user->id");					
			}
		} else {
			log_message('error',"u/get_device_vars | device does not exist: $device_id");			
		}
	}
}


?>