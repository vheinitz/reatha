<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class A extends CI_Controller{
	function __construnct(){
		parent::__construnct();
		if(!$this->tank_auth->is_logged_in()){
			redirect('/auth/login');
		}
		$user = new User($this->tank_auth->get_user_id());
		//check if user has privilleges, if not - show error
		if($user->role != '1'){
			show_error("Sorry, you don't have acces to this page");
		}		
	}

	function index(){
		$user = new User($this->tank_auth->get_user_id());
		$domains = new Domain();
		$data['user'] 		= $user;
		$data['domains']	= $domains->get();
		$this->load->view('a_view',$data);
	}

	function add_domain(){
		$user = new User($this->tank_auth->get_user_id());
		$this->load->library('form_validation');
		$this->form_validation->set_rules('domain_name','Domain Name','required|trim|xss_clean');
		$this->form_validation->set_rules('domain_admin_username','Domain Admin Username','required|trim|xss_clean');
		$this->form_validation->set_rules('domain_admin_password','Domain Admin Password','required|trim|xss_clean');
		$this->form_validation->set_rules('domain_admin_email','Domain Admin Email','required|trim|xss_clean');
		if($this->form_validation->run()){
			$domain_name = $this->input->post('domain_name',true);
			$domain = new Domain();
			$domain->a_id = $user->id;
			$domain->name = $domain_name;
			if(!$domain->save()){
				log_message('error','a/add_domain | could not save domain, user id: '.$user->id.', domain name: '.$domain_name);
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, we couldn't save this domain. Please try again."));
			} else {
				$this->load->library('tank_auth');
				$email_activation = $this->config->item('email_activation', 'tank_auth');
				if (!is_null($data = $this->tank_auth->create_user(
						$this->form_validation->set_value('domain_admin_username'),
						$this->form_validation->set_value('domain_admin_email'),
						$this->form_validation->set_value('domain_admin_password'),
						$email_activation))){
							$user = new User($data['user_id']);
							$domain->da_id = $user->id; $domain->save();
							$user->role = '2'; $user->save();	
							$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"Domain successfully created."));				
						} else {
							$this->session->set_flashdata('message',array('type'=>'error', 'message'=>'Could not create user.'));
						}												
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>validation_errors()));
		}
		redirect('/a');		
	}

	function delete_domain($domain_id){
		$domain = new Domain($domain_id);

		//check if domain exists
		if($domain->exists()){

			//check if user has the right to delete this domain
			$user = new User($this->tank_auth->get_user_id());
			if($user->role == '1'){
				if($domain->delete_domain()){
					$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"Domain successfully deleted"));						
				} else {
					$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, we were not able to delete this domain. Please try again."));						
					log_message('error',"a/delete_domain | could not delete domain, domain_id: $domain_id");
				}
			} else {
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, you don't have enough rights to perform this action"));
				log_message('error',"a/delete_domain | user is not an admin; domain_id: $domain_id");				
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, this domain doesn't exist"));
			log_message('error',"a/delete_domain | domain does not exist, domain_id:$domain_id");			
		}
		redirect('a');		
	}		

}


?>