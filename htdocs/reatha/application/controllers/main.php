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
		$variables = array();
		$variables = $this->input->post(NULL, true);
		$key = $variables['key'];
		$device = new Device();
		$device->get_by_key($key);
		if($device->exists()){
			log_message('info','main/post_variables | found device with given key');
			//remove access key from the post array
			unset($variables['key']);

			//check if this is a life check post
			if(isset($variables['lc'])){
				// log_message('info','main/post_variables | life check');
				$device->update_life_check();				
			}
			
			//traverse the post array and attemt to update variables in database
			foreach ($variables as $name => $value) {
				if(!$device->update_variables($name,$value)){
					$device->update_invalid_data($name,$value);
					log_message('error',"main/post_variables | $name is not a valid variable, key value: $value");					
					echo "$name is not a valid variable<br/>";
				} else {
					//check if we must send notification
					$var = new variable();
					$var->where('name',$name)->where('device_id',$device->id)->get();
					if($var->notification_rule->exists()){
						foreach($var->notification_rule as $rule){
							if($rule->must_send_notification($var)){
								//must send new notification
								$n = new Notification();
								$n->save_and_email($rule, $var);
						} else {
							log_message('info','main/post_variables | not sending notification');							
						}
					}
				} else {
					log_message('info','main/post_variables | variable has no notification rules');
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