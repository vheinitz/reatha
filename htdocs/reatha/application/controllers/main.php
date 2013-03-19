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

				//send back device vars, as described in issue #46
				$device_vars = array();
				foreach($device->variables as $var){
					$device_vars[] = array($var->name => $var->value);
				}
				// log_message('info','main/post_variables | returning json to life_check: '.json_encode($device_vars));
				echo json_encode($device_vars);				
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
						log_message('info','main/post_variables | var has notification_rule');
						foreach($var->notification_rule as $rule){
							foreach($rule->user as $user){
								if($rule->must_send_notification($var, $user)){
									//must send new notification
									log_message('info','main/post_variables | must send notification');
									$n = new Notification();
									$n->save_and_email($rule, $var, $user);
								} else {
									log_message('info','main/post_variables | not sending notification');							
								}
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

	function get_device_key(){
		$this->load->library('form_validation');
		$this->form_validation->set_rules('username','Username','required|trim|xss_clean|max_length[25]');
		$this->form_validation->set_rules('password','Username','required|trim');
		$this->form_validation->set_rules('device_name','Device Name','required|trim|xss_clean|');
		if($this->form_validation->run()){		
			$username = $this->form_validation->set_value('username');
			$password = $this->form_validation->set_value('password');

			//temporarily allowing plain-text passwords; if pwd length == 60 then it is a hashed pwd 
			if(strlen($password) == 60){
				$user = new User();
				$user->where('username',$username)->where('password',$password)->get(1);
			} else {
				$this->tank_auth->login($username,$password,false,true,false);
				$user = new User($this->tank_auth->get_user_id());				
			}

			if($user->exists()){
				$device_name = $this->form_validation->set_value('device_name');
				$device = $user->devices->where('name',$device_name)->get();
				if($device->exists()){
					$return = array('key'=>$device->key);
				} else {
					log_message('error',"main/get_device_key | user is not assigned to such device, user id $user->id, device name: $device_name");
					$return = array('error'=>'User does not have such device assigned.');
				}
			} else {
				log_message('error',"main/get_device_key | Incorrect username/password combination");
				$return = array('error'=>'Incorrect username/password combination');
			}			
		} else {
			$return = array('error'=>validation_errors());
		}
		echo json_encode($return);
	}
}


?>