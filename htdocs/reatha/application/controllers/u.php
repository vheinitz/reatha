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

	function device($device_id, $view_name = "main"){
		$device = new Device($device_id);
		if($device->exists()){
			$user = new User($this->tank_auth->get_user_id());
			if($user->has_device($device->id)){
				$data['user'] 	= $user;
				$data['device'] = $device;
				$data['view'] = $device->view->where('name',$view_name)->get(1);
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

	function get_device_view($view_id){
		$view = new View($view_id);
		if($view->exists()){
			$user = new User($this->tank_auth->get_user_id());
			if($user->has_device($view->device->id)){
				echo $view->process_vars();
			}
		}
	}

	function notifications($device_id){
		$device = new Device($device_id);
		if($device->exists()){
			$user = new User($this->tank_auth->get_user_id());
			if($user->has_device($device->id)){
				$data['user'] 	= $user;
				$data['device'] = $device;
				$this->load->view('u_notifications_view',$data);
			}
		}
	}

	function add_notification_rule(){
		$this->load->library('form_validation');
		$this->form_validation->set_rules('name','Name','required|trim|xss_clean');		
		$this->form_validation->set_rules('description','Description','required|trim|xss_clean');
		$this->form_validation->set_rules('variable','Variable','required|trim|xss_clean');
		$this->form_validation->set_rules('condition','Condition','required|trim|xss_clean');
		$this->form_validation->set_rules('interval','Interval','required|trim|numeric|xss_clean');
		$this->form_validation->set_rules('message','Message','required|trim|xss_clean|callback__check_message_variables['.$this->input->post('device_id').']');	
		$this->form_validation->set_rules('subject','Subject','required|trim|xss_clean|callback__check_message_variables['.$this->input->post('device_id').']');
		$this->form_validation->set_rules('device_id','device','required|trim|xss_clean');
		$device = new Device($this->input->post('device_id',true));		
		if($this->form_validation->run()){
			if($device->exists()){
				$user = new User($this->tank_auth->get_user_id());
				if($user->has_device($device->id)){
					$rule = new Notification_rule();
					$rule->user_id = $user->id;
					$rule->device_id = $device->id;
					$rule->variable_id = $this->form_validation->set_value('variable');
					$rule->name = $this->form_validation->set_value('name');
					$rule->description = $this->form_validation->set_value('description');
					$rule->condition = $this->form_validation->set_value('condition');
					$rule->message = strip_tags($this->form_validation->set_value('message'),"<br><br/><p><i>");
					$rule->subject = $this->form_validation->set_value('subject');
					$rule->interval = $this->form_validation->set_value('interval');
					if($rule->save()){
						$this->session->set_flashdata('message',array('type'=>'success','message'=>'Notification successfully saved'));						
					}
				} else {
					//todo log error
				}
			} else {
				//todo log error
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error','message'=>validation_errors()));
		}
		redirect('u/notifications/'.$device->id);	
	}

	function edit_notification_rule($rule_id){
		$rule = new Notification_rule($rule_id);
		if($rule->exists()){
			$device_id = $rule->device->id;
			$user = new User($this->tank_auth->get_user_id());
			if($user->has_device($rule->device->id)){
				$this->load->library('form_validation');
				$this->form_validation->set_rules('name','Name','required|trim|xss_clean');		
				$this->form_validation->set_rules('description','Description','required|trim|xss_clean');				
				$this->form_validation->set_rules('variable','Variable','required|trim|xss_clean');
				$this->form_validation->set_rules('condition','Condition','required|trim|xss_clean');
				$this->form_validation->set_rules('interval','Interval','required|trim|numeric|xss_clean');
				$this->form_validation->set_rules('message','Message','required|trim|xss_clean|callback__check_message_variables['.$rule->device_id.']');
				$this->form_validation->set_rules('subject','Subject','required|trim|xss_clean|callback__check_message_variables['.$rule->device_id.']');	
				if($this->form_validation->run()){
					$rule->variable_id = $this->form_validation->set_value('variable');
					$rule->name = $this->form_validation->set_value('name');
					$rule->description = $this->form_validation->set_value('description');					
					$rule->condition = $this->form_validation->set_value('condition');
					$rule->message = strip_tags($this->form_validation->set_value('message'),"<br><br/><p><i>");
					$rule->subject = $this->form_validation->set_value('subject');
					$rule->interval = $this->form_validation->set_value('interval');
					if($rule->save()){
						$this->session->set_flashdata('message',array('type'=>'success','message'=>'Notification successfully saved'));
						redirect($this->uri->uri_string());						
					}
				} else {
					if(validation_errors()){
						//redirect so that we can show errors						
						$this->session->set_flashdata('message',array('type'=>'error','message'=>validation_errors()));
						redirect($this->uri->uri_string());
					}
					$data['user'] = $user;
					$data['notification_rule'] = $rule;
					$this->load->view('u_edit_notifications_view',$data);
				}
			} else {
				log_message('error','u/edit_notification_rule | user has no rights to device, device id: '.$rule->device->id);
				$this->session->set_flashdata('message',array('type'=>'error','message'=>'You do not have enough rights to perform this action.'));		
				redirect('u/notifications/'.$device_id);			
			}
		} else {
			log_message('error','u/edit_notification_rule | no such rule, id: '.$rule_id);
			$this->session->set_flashdata('message',array('type'=>'error','message'=>'This notification rule does not exist.'));
			redirect('u/notifications/'.$device_id);				
		}

	}

	function delete_notification_rule($rule_id){
		$rule = new Notification_rule($rule_id);
		if($rule->exists()){
			$device_id = $rule->device->id;
			$user = new User($this->tank_auth->get_user_id());
			if($user->has_device($rule->device->id)){
				if($rule->delete()){
					$this->session->set_flashdata('message',array('type'=>'success','message'=>'Notification successfully deleted.'));						
				} else {
					log_message('error','u/delete_notification_rule | could not delete notification_rule, id: '.$rule_id);
					$this->session->set_flashdata('message',array('type'=>'error','message'=>'Something went wrong, please try again.'));					
				}
			} else {
				log_message('error','u/delete_notification_rule | user has no rights to device, device id: '.$rule->device->id);
				$this->session->set_flashdata('message',array('type'=>'error','message'=>'You do not have enough rights to perform this action.'));					
			}
		} else {
			log_message('error','u/delete_notification_rule | no such rule, id: '.$rule_id);
			$this->session->set_flashdata('message',array('type'=>'error','message'=>'This notification rule does not exist.'));				
		}

		redirect('u/notifications/'.$device_id);
	}

	function toggle_notification_status($rule_id,$flag){
		$rule = new Notification_rule($rule_id);
		if($rule->exists()){
			$device_id = $rule->device->id;
			$user = new User($this->tank_auth->get_user_id());
			if($user->has_device($rule->device->id)){
				if(in_array($flag, array('0','1'))){
					if($rule->where('id',$rule->id)->update('activated',$flag)){
						$this->session->set_flashdata('message',array('type'=>'success','message'=>'Notification successfully updated.'));							
					} else {
						log_message('error','u/toggle_notification_status | could not update rule flag, rule id:'.$rule->id);
						$this->session->set_flashdata('message',array('type'=>'error','message'=>'Something went wrong. Please try again'));							
					}
				} else {
					log_message('error','u/toggle_notification_status | Provided flag is not valid:'.$flag.', rule id: '.$rule->id);
					$this->session->set_flashdata('message',array('type'=>'error','message'=>'Something went wrong. Please try again'));					
				}
	
			} else {
				log_message('error','u/toggle_notification_status | user has no rights to device, device id: '.$rule->device->id);
				$this->session->set_flashdata('message',array('type'=>'error','message'=>'You do not have enough rights to perform this action.'));					
			}
		} else {
			log_message('error','u/toggle_notification_status | no such rule, id: '.$rule_id);
			$this->session->set_flashdata('message',array('type'=>'error','message'=>'This notification rule does not exist.'));				
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