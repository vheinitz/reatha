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

	function setup_notification(){
		$this->load->library('form_validation');
		$this->form_validation->set_rules('status','Status','trim|xss_clean');
		$this->form_validation->set_rules('variable','Variable','required|trim|xss_clean');
		$this->form_validation->set_rules('interval','Interval','required|trim|xss_clean');
		$this->form_validation->set_rules('text','Text','required|trim|xss_clean');	
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
					$rule->text = $this->form_validation->set_value('text');
					$rule->interval = $this->form_validation->set_value('interval');
					$rule->enabled = $this->form_validation->set_value('enabled');
					if($rule->save()){
						$this->session->set_flashdata('message',array('type'=>'success','message'=>'Notification setup saved'));						
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
}


?>