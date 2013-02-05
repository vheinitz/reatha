<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class A extends CI_Controller{
	function __construct(){
		parent::__construct();
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

	function domain_admins(){
		$user = new User($this->tank_auth->get_user_id());
		$domain_admins = new User();
		$domains = new Domain();
		$data['user'] = $user;
		$data['domain_admins'] = $domain_admins->where('role','2')->get();
		$data['domains'] = $domains->get();
		$this->load->view('a_domain_admins_view',$data);

	}

	function add_domain(){
		$user = new User($this->tank_auth->get_user_id());
		$this->load->library('form_validation');
		$this->form_validation->set_rules('domain_name','Domain Name','required|trim|xss_clean|callback_valid_domain_name');
		$this->form_validation->set_message('valid_domain_name','Sorry, this domain name is already in use.');
		if($this->form_validation->run()){
			$domain_name = $this->input->post('domain_name',true);
			$domain = new Domain();
			$domain->name = $domain_name;
			if(!$domain->save()){
				//error - domain could not be saved
				log_message('error','a/add_domain | could not save domain, user id: '.$user->id.', domain name: '.$domain_name);
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, we couldn't save this domain. Please try again."));
			} else {
				//domain saved, create the domain_admin user
				// $this->load->library('tank_auth');
				// $email_activation = $this->config->item('email_activation', 'tank_auth');
				// if (!is_null($data = $this->tank_auth->create_user(
				// 		$this->form_validation->set_value('domain_admin_username'),
				// 		$this->form_validation->set_value('domain_admin_email'),
				// 		$this->form_validation->set_value('domain_admin_password'),
				// 		$email_activation))){
				// 			//domain_admin user created, save relationship between domain and user, set user role as domain admin
				// 			$user = new User($data['user_id']);
				// 			$domain->save_domain_admin($user);
				// 			$user->role = '2'; $user->save();	
							$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"Domain successfully created."));				
						// } else {
						// 	$this->session->set_flashdata('message',array('type'=>'error', 'message'=>'Could not create user.'));
						// }												
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>validation_errors()));
		}
		redirect('a');		
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

	function unassign_domain_admin($domain_id,$domain_admin_id){
		$domain = new Domain($domain_id);
		//check if domain exists
		if($domain->exists()){
			//such domain exists, attemt to unnasign domain admin from domain
			$user = new User($domain_admin_id);
			if($user->unassign_domain($domain_id)){
				$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"Domain admin successfully unassigned"));					
			} else {
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, something went wrong. Please try again"));
				log_message('error',"a/unnasign_domain_admin | could not unnasign domain_admin from domain; domain:$domain_id, domain_admin_id: $domain_admin_id");					
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, this domain doesn't exist"));
			log_message('error',"a/unnasign_domain_admin | domain does not exist, domain_id:$domain_id");				
		}

		redirect('a/domain_admins');
	}

	function assign_domain_admin(){
		$this->load->library('form_validation');
		$this->form_validation->set_rules('domain_id','Domain Id','required|trim|xss_clean');
		$this->form_validation->set_rules('domain_admin_id','Domain Admin Id','required|trim|xss_clean');
		if($this->form_validation->run()){
			$domain_id = $this->form_validation->set_value('domain_id');
			$domain_admin_id = $this->form_validation->set_value('domain_admin_id');
			$user = new User($domain_admin_id);
			if($user->assign_domain($domain_id)){
				$domain = new Domain($domain_id);
				//domain admin assigned to domain, send email notification to domain admin
				$user->send_domain_admin_email_notification($domain);
				$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"Domain admin successfully assigned"));					
			} else {
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, something went wrong. Please try again."));
				log_message('error',"a/assign_domain_admin | could not assign new domain admin to domain, domain_id: $domain_id, $domain_admin_id: $domain_admin_id");				
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>validation_errors()));
		}

		redirect('a/domain_admins');
	}

	function add_domain_admin(){
		$this->load->library('form_validation');		
		$this->form_validation->set_rules('username','Username','trim|required|xss_clean');
		$this->form_validation->set_rules('password','Password','trim|required|xss_clean');
		$this->form_validation->set_rules('email','Email','trim|required|xss_clean');
		$this->form_validation->set_rules('domain_id','Domain Id','trim|xss_clean');
		if($this->form_validation->run()){
			$this->load->library('tank_auth');
			$email_activation = $this->config->item('email_activation', 'tank_auth');
			if (!is_null($data = $this->tank_auth->create_user(
					$this->form_validation->set_value('username'),
					$this->form_validation->set_value('email'),
					$this->form_validation->set_value('password'),
					$email_activation))){
				//send welcome email to newly created domain admin, with login details
				$data['site_name'] = $this->config->item('website_name', 'tank_auth');
				$this->_send_email('welcome', $data['email'], $data);

				//assign domain admin to to domain
				$domain_id = $this->form_validation->set_value('domain_id');
				$new_user = new User($data['user_id']);
				$new_user->role = '2';
				$new_user->save();				
				if(!empty($domain_id)){
					//if we have a domain id, save the newly created user as a domain admin
					$domain = new Domain($this->form_validation->set_value('domain_id'));
					if($domain->save_domain_admin($new_user)){
						//domain admin assigned to domain, send email notification to domain admin
						$new_user->send_domain_admin_email_notification($domain);
					}					
				}
				$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"User created successfully"));	
			}			
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>validation_errors()));	
		}
		redirect('a/domain_admins');
	}

	function delete_domain_admin($user_id){
		$user = new User($user_id);

		//check if such user exists
		if($user->exists()){

			//check if user is a domain admin
			if($user->role == '2'){
				if($user->delete_user()){
					$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"User successfully deleted"));						
				} else {
					$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, we were not able to delete this user. Please try again."));						
					log_message('error',"a/delete_domain_admin | could not delete user, user_id: $user_id");
				}
			} else {
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, you don't have enough rights to perform this action"));
				log_message('error',"a/delete_domain_admin | the deletion user is not a domain admin; user_id: $user_id");				
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, this user doesn't exist"));
			log_message('error',"a/delete_domain_admin | user does not exist, user_id:$user_id");			
		}
		redirect('a/domain_admins');			
	}

	function valid_domain_name($domain_name){
		return !($this->db->where('name',$domain_name)->count_all_results('domains')); 
	}

	function _send_email($type, $email, &$data)
	{
		$this->load->config('tank_auth', TRUE);
		$this->lang->load('tank_auth');
		$this->load->library('email');
		$this->email->from($this->config->item('webmaster_email', 'tank_auth'), $this->config->item('website_name', 'tank_auth'));
		$this->email->reply_to($this->config->item('webmaster_email', 'tank_auth'), $this->config->item('website_name', 'tank_auth'));
		$this->email->to($email);
		$this->email->subject(sprintf($this->lang->line('auth_subject_'.$type), $this->config->item('website_name', 'tank_auth')));
		$this->email->message($this->load->view('email/'.$type.'-html', $data, TRUE));
		$this->email->send();
	}			

}


?>