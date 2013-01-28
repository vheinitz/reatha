<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Da extends CI_Controller{
	function __construnct(){
		parent::__construnct();
		if(!$this->tank_auth->is_logged_in()){
			redirect('/auth/login');
		}
		$user = new User($this->tank_auth->get_user_id());
		//check if user has privilleges, if not - show error
		if($user->role != '2'){
			show_error("Sorry, you don't have acces to this page");
		}		
	}

	function index(){
		$user = new User($this->tank_auth->get_user_id());
		$devices = new Device(); $domain = new Domain();
		$domain = $domain->get_by_da_id($user->id);
		
		//if user has a domain under management
		if($domain->id){
			$devices = $devices->get_by_domain_id($domain->id);
		}
		$data['user'] 		= $user;
		$data['devices']	= $devices;
		$data['domain']		= $domain;
		$this->load->view('da_view',$data);
	}

	function users(){
		$user = new User($this->tank_auth->get_user_id());
		$domain = new Domain(); $domain = $domain->get_by_da_id($user->id);
		
		//if user has a domain under manangement
		$users = new User(); $devices = new Device();
		if($domain->id){					
			$users = $users->where_related_device('domain_id',$domain->id)->get();
			$devices = $devices->get_by_domain_id($domain->id);
		}
		$data['user'] 		= $user;
		$data['users'] 		= $users;
		$data['domain']		= $domain;
		$data['devices'] 	= $devices;
		$this->load->view('da_users_view',$data);
	}

	function add_device(){
		$user = new User($this->tank_auth->get_user_id());
		$this->load->library('form_validation');
		$this->form_validation->set_rules('device_description','Device Description','required|trim|xss_clean');
		if($this->form_validation->run()){
			$device_description = $this->input->post('device_description',true);
			$domain = new Domain();
			$domain = $domain->get_by_da_id($user->id);
			$device = new Device();
			$device->domain_id = $domain->id;
			$device->description = $device_description;
			if(!$device->save()){
				log_message('error','da/add_device | could not save device, user id: '.$user->id.', device description: '.$device_description);
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, we couldn't save this device. Please try again."));
			} else {
				$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"Device successfully created."));				
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>validation_errors()));			
		}
		redirect('/da');
	}	

	function add_user(){
		$this->load->library('form_validation');		
		$this->form_validation->set_rules('username','Username','trim|required|xss_clean');
		$this->form_validation->set_rules('password','Password','trim|required|xss_clean');
		$this->form_validation->set_rules('email','Email','trim|required|xss_clean');
		$this->form_validation->set_rules('device','Device','trim|required|xss_clean');
		if($this->form_validation->run()){
			$this->load->library('tank_auth');
			$email_activation = $this->config->item('email_activation', 'tank_auth');
			if (!is_null($data = $this->tank_auth->create_user(
					$this->form_validation->set_value('username'),
					$this->form_validation->set_value('email'),
					$this->form_validation->set_value('password'),
					$email_activation))){
				//assign user to device
				$device = new Device($this->input->post('device'));
				$new_user = new User($data['user_id']);
				$new_user->role = '3';
				$new_user->save($device);
				$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"User added successfully"));	
			}			
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>validation_errors()));	
		}
		redirect('/da/users');
	}
}


?>