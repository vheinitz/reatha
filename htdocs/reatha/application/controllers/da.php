<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Da extends CI_Controller{
	function __construct(){
		parent::__construct();
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
			$users = $users->where('belongs_to_domain_id',$domain->id)->group_by('users.id')->get();
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
				$new_user->belongs_to_domain_id = $device->domain_id;
				$new_user->save($device);
				$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"User added successfully"));	
			}			
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>validation_errors()));	
		}
		redirect('/da/users');
	}

	function unnasign_device($device_id, $user_id){
		$domain = new Domain();
		$domain->where_related_device('id',$device_id)->get();

		//check if current user is an admin of the device's domain
		if($domain->da_id == $this->tank_auth->get_user_id()){
			//check if user_id is associated with device_id
			$user = new User($user_id);
			if($user->has_device($device_id)){
				if($user->unnassign_device($device_id)){
					$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"Device unnasigned"));					
				} else {
					$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Device could not be unnasigned. Please try again"));	
				}
			} else {
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"User id not associated with this Device. "));
				log_message('error',"da/unnasign_device | user_id $user_id is not associated with device_id $device_id");					
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, you don't have enough rights to perform this action"));			
			log_message('error',"da/unnasign_device | user_id $user_id is not a domain id of the device_id $device_id domain");
		}
		redirect('da/users');
	}

	function assign_device(){
		$this->load->library('form_validation');
		$this->form_validation->set_rules('device','Device','required|trim|xss_clean');
		$this->form_validation->set_rules('user_id','User','required|trim|xss_clean');
		if($this->form_validation->run()){
			$device_id = $this->form_validation->set_value('device');
			$user_id = $this->form_validation->set_value('user_id');
			$domain = new Domain(); $device = new Device($device_id);
			$domain = $device->domain->get();

			//check if current user is an admin of the device's domain
			if($domain->da_id == $this->tank_auth->get_user_id()){
				//check if user_id is not already associated with device_id
				$user = new User($user_id);
				if(!$user->has_device($device_id)){
					if($user->assign_device($device_id)){
						$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"Device unnasigned"));					
					} else {
						$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Device could not be asigned. Please try again"));	
					}
				} else {
					$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"User id is already associated with this Device. "));
					log_message('error',"da/unnasign_device | user_id $user_id is already associated with device_id $device_id");					
				}
			} else {
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, you don't have enough rights to perform this action"));			
				log_message('error',"da/unnasign_device | user_id $user_id is not a domain id of the device_id $device_id domain");
			}			
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>validation_errors()));
		}
			redirect('da/users');		
	}

	function delete_user($user_id){
		$user = new User($user_id);

		//check if such user exists
		if($user->exists()){
			$domain = new Domain($user->belongs_to_domain_id);

			//check if domain exists and if user is the domain admin of it
			if($domain->exists() && ($domain->da_id == $this->tank_auth->get_user_id())){
				if($user->delete_user()){
					$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"User successfully deleted"));						
				} else {
					$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, we were not able to delete this user. Please try again."));						
					log_message('error',"da/delete_user | could not delete user, user_id: $user_id");
				}
			} else {
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, you don't have enough rights to perform this action"));
				log_message('error',"da/delete_user | domain id doesn't exists or user is not the domain admin; user_id: $user_id, domain_id: $domain->id");				
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, this user doesn't exist"));
			log_message('error',"da/delete_user | user does not exist, user_id:$user_id");			
		}
		redirect('da/users');		
	}

	function delete_device($device_id){
		$device = new Device($device_id);

		//check if device exists
		if($device->exists()){

			//check if user is the admin of the device's domain
			if($device->domain->da_id == $this->tank_auth->get_user_id()){
				if($device->delete_device()){
					$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"Device successfully deleted"));						
				} else {
					$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, we were not able to delete this device. Please try again."));						
					log_message('error',"da/delete_device | could not delete device, device_id: $device_id");
				}
			} else {
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, you don't have enough rights to perform this action"));
				log_message('error',"da/delete_device | user is not the devices's domain admin; device_id: $device_id");				
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, this device doesn't exist"));
			log_message('error',"da/delete_device | device does not exist, device_id:$device_id");			
		}
		redirect('da');		
	}
}


?>