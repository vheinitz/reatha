<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller{
	function __construct(){
		parent::__construct();
	}

	function index(){
		if(!$this->tank_auth->is_logged_in()){
			redirect('auth/login');
		} else {		
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

	function post_variables(){
		$variables = $this->input->post(NULL, true);
		$key = $variables['key'];
		$device = new Device();
		$device->get_by_key($key);
		if($device->exists()){
			//remove access key from the post array
			unset($variables['key']);

			//check if this is a life check post
			if(isset($variables['lc'])){
				$device->update_life_check();
				echo "life checked";				
			} else {
				//traverse the post array and attemt to update variables in database
				foreach ($variables as $key => $value) {
					if(!$device->update_variables($key,$value)){
						$device->update_invalid_data($key,$value);
						log_message('error',"main/post_variables | $key is not a valid variable, key value: $value");					
						echo "$key is not a valid variable<br/>";
					}
				}
			}
		} else {
			echo "The provided key is not valid.";
			log_message('error',"main/post_variables | key not valid, no such device key, key: $key");
		}
	}

	function post_life_check(){
		$this->load->library('form_validation');
		$this->form_validation->set_values('key','Key','required');
		$this->form_validation->set_values('life_check','Life Check','required');
		if($this->form_validation->run()){
			$key = $this->form_validation->set_value('key');
			$device = new Device($key);
			if($device->exists()){
				$device->update_life_check();
			} else {						
				log_message('error',"main/post_life_check | no such device key, device key: $key");					
			}
		} else {
			echo validation_errors();
		}
	}
}


?>