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
				// log_message('info','main/post_variables | life check');
				$device->update_life_check();				
			}
			
			//traverse the post array and attemt to update variables in database
			foreach ($variables as $key => $value) {
				if(!$device->update_variables($key,$value)){
					$device->update_invalid_data($key,$value);
					log_message('error',"main/post_variables | $key is not a valid variable, key value: $value");					
					echo "$key is not a valid variable<br/>";
				} else {
					//check if we must send notification
					$var = new variable();
					$var->where('name',$key)->where('device_id',$device->id)->get();
					if($var->notification_rule->exists()){
						log_message('info','main/post_variables | var_id '.$var->id.' has a notification rule, id: '.$var->notification_rule->id);
						foreach($var->notification_rule as $rule){
							//get last notification sent to user under this rule
							$last_sent = $rule->user->get_last_sent_notification_under_rule($rule->id);
							if(round((time() - $last_sent)/60) >= $rule->interval){
								//must send new notification
								$n = new Notification();
								//todo: send notification
						}
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