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
		$data['hide_success_message'] = 1;
		$this->load->view('u_view',$data);
	}

	function device($device_id, $view_name = "main"){
		$device = new Device($device_id);
		if($device->exists()){
			$user = new User($this->tank_auth->get_user_id());
			if($user->has_device($device->id)){				
				//saving view name in session - for checking whether we should switch views
				$this->session->set_userdata('current_view_name',$view_name);
				$data['user'] 	= $user;
				$data['device'] = $device;
				$data['view'] = $device->view->where('name',$view_name)->get(1);
				$data['hide_navbar'] = 1;
				$data['hide_success_message'] = 1;
				$this->load->view('u_device_view',$data);
			}
		}		
	}

	function get_devices_power_status(){
		$user = new User($this->tank_auth->get_user_id());
		$return = array(); $power = '';
		foreach($user->devices as $device){
			$time = time();
			($time - $device->updated) > 10 ? $power = 'OFF' : $power = 'ON';
			$return[] = array('device_id'=>$device->id, 'power'=>$power);
		}
		echo json_encode($return);
	}

	function get_device_vars($device_id){
		$user = new User($this->tank_auth->get_user_id());
		$device = new Device($device_id);
		$vars = "";
		if($device->exists()){
			if($user->has_device($device_id)){
				//get variables for each device
				foreach ($device->variable as $var) {
					if($var->name != "view")
						$vars.= "<b>$var->name: </b>$var->value<br/>";
				}
				echo ($vars);				
			} else {
				log_message('error',"u/get_device_vars | user is not assigned to device, device_id: $device_id, user id: $user->id");					
			}
		} else {
			log_message('error',"u/get_device_vars | device does not exist: $device_id");			
		}
	}

	function get_device_view($view_id){
		$view = new View($view_id);
		if($view->exists()){
			$user = new User($this->tank_auth->get_user_id());
			if($user->has_device($view->device->id)){
				//check if we must change the view
				$current_view = $this->session->userdata('current_view_name');			
				$new_view = $view->device->get_view_name();
				if($new_view != $current_view){
					//check if new view exists and is valid
					if($view->device->has_view($new_view)){
						echo json_encode(array('new_view_url'=>base_url()."u/device/".$view->device->id."/".$new_view));
					} else {
						log_message('error',"u/get_device_view | submitted view name is not valid, view name: $new_view, device id: $device->id");
						echo $view->process_placeholders();
					}
				} else {				
					echo $view->process_placeholders();
				}
			}
		}
	}

	function get_notifications_data($device_id){
		$user = new User($this->tank_auth->get_user_id());
		echo "hi";
	}

	function notifications($device_id){
		$device = new Device($device_id);
		if($device->exists()){
			$user = new User($this->tank_auth->get_user_id());
			if($user->has_device($device->id)){
				$data['user'] 	= $user;
				$data['device'] = $device;
				$data['hide_navbar'] = 1;
				$data['hide_success_message'] = 1;
				$this->load->view('u_notifications_view',$data);
			}
		}
	}	

	function toggle_notification_status($rule_id){
		$rule = new Notification_rule($rule_id);
		if($rule->exists()){
			$device_id = $rule->device->id;
			$user = new User($this->tank_auth->get_user_id());
			if($user->has_device($rule->device->id)){
				
				//if 1 then 0, if 0 then 1
				$current_flag = $rule->is_activated_for_user_id($user->id);
				$flag = abs($current_flag-1);

				if($this->db->where('notification_rule_id',$rule->id)->where('user_id',$user->id)->update('notification_rules_users', array('activated'=>$flag))){
					$return = array('type'=>'success','message'=>'Notification successfully updated.');							
				} else {
					log_message('error','u/toggle_notification_status | could not update rule flag, rule id:'.$rule->id);
					$return = array('type'=>'error','message'=>'Something went wrong. Please try again');							
				}	
			} else {
				log_message('error','u/toggle_notification_status | user has no rights to device, device id: '.$rule->device->id);
				$return = array('type'=>'error','message'=>'You do not have enough rights to perform this action.');					
			}
		} else {
			log_message('error','u/toggle_notification_status | no such rule, id: '.$rule_id);
			$return = array('type'=>'error','message'=>'This notification rule does not exist.');				
		}

		echo json_encode($return);		
	}

	function reset_notification($rule_id){
		$rule = new Notification_rule($rule_id);
		if($rule->exists()){
			$device_id = $rule->device->id;
			$user = new User($this->tank_auth->get_user_id());
			if($user->has_device($rule->device->id)){
				
				if($rule->notification->where('notification_rule_id',$rule->id)->update('created','')){
					$this->session->set_flashdata('message', array('type'=>'success','message'=>'Notification successfully updated.'));							
				} else {
					log_message('error','u/reset_notification | could not update rule flag, rule id:'.$rule->id);
					$this->session->set_flashdata('message', array('type'=>'error','message'=>'Something went wrong. Please try again'));							
				}	
			} else {
				log_message('error','u/reset_notification | user has no rights to device, device id: '.$rule->device->id);
				$this->session->set_flashdata('message', array('type'=>'error','message'=>'You do not have enough rights to perform this action.'));					
			}
		} else {
			log_message('error','u/reset_notification | no such rule, id: '.$rule_id);
			$this->session->set_flashdata('message', array('type'=>'error','message'=>'This notification rule does not exist.'));				
		}

		redirect('u/notifications/'.$device_id);	
	}

	function _check_message_variables($msg, $device_id){
		$device = new Device($device_id);
		if($device->exists()){
			preg_match_all("/\{(.+)\}/U", $msg, $var_names);			
			$var_names = $var_names[1];		
			foreach($var_names as $var_name){
				$var = $device->variables->where('name',$var_name)->get();
				if(!$var->exists()){
					log_message('error',"u/_check_message_variables | Variable $var_name doesn't exist for device id: $device_id. Notification_rule not saved.");
					$this->form_validation->set_message('_check_message_variables',"Variable $var_name doesn't exist for this device. Notification not saved.");
					return FALSE;
				}
			}
			return TRUE;						
		} else {
			log_message('error',"u/_check_message_variables | device id $device_id doesn't exist. Notification_rule not saved.");
			$this->form_validation->set_message('_check_message_variables',"This device doesn't exist");
			return FALSE;
		}
	}
}


?>