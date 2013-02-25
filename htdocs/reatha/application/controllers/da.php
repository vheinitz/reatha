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
		$devices = new Device(); $domain = new Domain($this->session->userdata('managing_domain_id'));
		$domains = $user->domains->get();
		
		//if user has a domain under management
		if($domain->id){
			$devices = $devices->get_by_domain_id($domain->id);
		}
		$data['user'] 		= $user;
		$data['devices']	= $devices;
		$data['domains']	= $domains;
		$this->load->view('da_view',$data);
	}

	function users(){
		$user = new User($this->tank_auth->get_user_id());
		$domain = new Domain($this->session->userdata('managing_domain_id'));
		$domains = $user->domains->get();
		
		//if user has a domain under manangement
		$users = new User(); $devices = new Device();
		if($domain->id){					
			$users = $users->where('belongs_to_domain_id',$domain->id)->group_by('users.id')->get();
			$devices = $devices->get_by_domain_id($domain->id);
		}
		$data['user'] 		= $user;
		$data['users'] 		= $users;
		$data['domains']	= $domains;
		$data['devices'] 	= $devices;
		$this->load->view('da_users_view',$data);
	}

	function add_device(){
		$user = new User($this->tank_auth->get_user_id());
		$this->load->library('form_validation');
		$this->form_validation->set_rules('device_name','Device Name','required|trim|xss_clean|max_length[100]|callback_valid_device_name');
		$this->form_validation->set_rules('device_description','Device Description','required|trim|xss_clean|max_length[250]');
		$this->form_validation->set_rules('device_location','Device Location','required|trim|xss_clean|max_length[150]');
		$this->form_validation->set_rules('device_variables','Device Variables','required|trim|xss_clean|max_length[300]');
		$this->form_validation->set_message('valid_device_name','This device name is already in use in this domain.');
		if($this->form_validation->run()){
			$device_name 		= $this->form_validation->set_value('device_name');
			$device_description = $this->form_validation->set_value('device_description');
			$device_location 	= $this->form_validation->set_value('device_location');
			$device_variables	= $this->form_validation->set_value('device_variables');
			
			// Hash device key using phpass
			require_once('application/libraries/phpass-0.1/PasswordHash.php');
			$hasher 	= new PasswordHash(6,FALSE);
			$device_key = $hasher->HashPassword($device_name);
						
			$domain = new Domain($this->session->userdata('managing_domain_id'));
			$device = new Device();
			$device->domain_id 		= $domain->id;
			$device->name 			= $device_name;
			$device->description 	= $device_description;
			$device->location 		= $device_location;
			$device->key 			= $device_key;
			if(!$device->save()){
				log_message('error','da/add_device | could not save device, user id: '.$user->id.', device name: '.$device_name);
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, we couldn't save this device. Please try again."));
			} else {
				//device saved, now process and save device variables
				$result = $device->add_variables($device_variables);
				if($result['type']=='success'){
					$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"Device successfully created."));				
				} else {
					$this->session->set_flashdata('message',array('type'=>'error', 'message'=>$result['message']));					
				}
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>validation_errors()));			
		}
		redirect('da');
	}	

	function add_user(){
		$this->load->library('form_validation');		
		$this->form_validation->set_rules('username','Username','trim|required|xss_clean');
		$this->form_validation->set_rules('password','Password','trim|required|xss_clean');
		$this->form_validation->set_rules('email','Email','trim|required|xss_clean');
		$this->form_validation->set_rules('device_id','Device Id','trim|xss_clean');
		$this->form_validation->set_rules('domain_id','Domain Id','trim|required|xss_clean');
		if($this->form_validation->run()){
			$this->load->library('tank_auth');
			$email_activation = $this->config->item('email_activation', 'tank_auth');
			if (!is_null($data = $this->tank_auth->create_user(
					$this->form_validation->set_value('username'),
					$this->form_validation->set_value('email'),
					$this->form_validation->set_value('password'),
					$email_activation))){
				//send welcome email to newly created user admin, with login details
				$data['site_name'] = $this->config->item('website_name', 'tank_auth');
				$this->_send_email('welcome', $data['email'], $data);

				//mark user as belonging to submitted domain id
				$new_user = new User($data['user_id']);
				$domain_id = $this->form_validation->set_value('domain_id');
				$domain = new Domain($domain_id);
				if($domain->exists()){
					$new_user->belongs_to_domain_id = $domain_id;
					$new_user->role = '3';
					$new_user->save();

					//if we have a device_id submitted - assign user to device
					$device_id = $this->form_validation->set_value('device_id');
					if(!empty($device_id)){
						$device = new Device($device_id);
						$new_user = new User($data['user_id']);
						$new_user->save($device);
					}
					$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"User added successfully"));					
				} else {
					$this->session->set_flashdata('message',array('type'=>'error', 'message'=>'This domain does not exist'));					
				}	
			}			
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>validation_errors()));	
		}
		redirect('da/users');
	}

	function unnasign_device($device_id, $user_id){
		$domain = new Domain();
		$domain->where_related_device('id',$device_id)->get();
		$domain_admin = new User($this->tank_auth->get_user_id());

		//check if current user is an admin of the device's domain
		if($domain_admin->is_admin_of($domain->id)){
			//check if user_id is associated with device_id
			$user = new User($user_id);
			if($user->has_device($device_id)){
				if($user->unnassign_device($device_id)){
					$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"Device unnassigned"));					
				} else {
					$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Device could not be unnassigned. Please try again"));	
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
			$user = new User($this->tank_auth->get_user_id());

			//check if current user is an admin of the device's domain
			if($user->is_admin_of($domain->id)){
				//check if user_id is not already associated with device_id
				$user = new User($user_id);
				if(!$user->has_device($device_id)){
					if($user->assign_device($device_id)){
						$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"Device assigned"));					
					} else {
						$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Device could not be assigned. Please try again"));	
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
			$domain_admin = new User($this->tank_auth->get_user_id());

			//check if domain exists and if user is the domain admin of it
			if($domain->exists() && ($domain_admin->is_admin_of($domain->id))){
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
			$user = new User($this->tank_auth->get_user_id());
			//check if user is the admin of the device's domain
			if($user->is_admin_of($device->domain->id)){
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

	function edit_device($device_id){
		$device = new Device($device_id);

		//check if device exists
		if($device->exists()){
			$user = new User($this->tank_auth->get_user_id());
			//check if user is the admin of the device's domain
			if($user->is_admin_of($device->domain->id)){
				$data['user'] = $user;
				$data['device'] = $device;
				$data['domains'] = $user->domains->get();
				$this->load->view('da_edit_device_view',$data);
			} else {
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, you don't have enough rights to perform this action"));
				log_message('error',"da/delete_device | user is not the devices's domain admin; device_id: $device_id");				
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, this device doesn't exist"));
			log_message('error',"da/delete_device | device does not exist, device_id:$device_id");			
		}		
	}

	function edit_views($device_id){
		$device = new Device($device_id);

		//check if device exists
		if($device->exists()){
			$user = new User($this->tank_auth->get_user_id());
			//check if user is the admin of the device's domain
			if($user->is_admin_of($device->domain->id)){
				$data['user'] = $user;
				$data['device'] = $device;
				$data['views'] = $device->views->get();
				$data['domains'] = $user->domains->get();
				$this->load->view('da_edit_views_view',$data);
			} else {
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, you don't have enough rights to perform this action"));
				log_message('error',"da/delete_device | user is not the devices's domain admin; device_id: $device_id");				
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, this device doesn't exist"));
			log_message('error',"da/delete_device | device does not exist, device_id:$device_id");			
		}	
	}

	function add_view(){
		$this->load->library('form_validation');
		$this->form_validation->set_rules('device_id','Device id','required|trim|xss_clean');
		$this->form_validation->set_rules('name','Name','required|trim|xss_clean|alpha_numeric|callback__valid_view_name['.$this->input->post('device_id',true).']');
		$this->form_validation->set_rules('view','View','required|trim|callback__check_view_variables['.$this->input->post('device_id',true).']');
		$this->form_validation->set_message('_valid_view_name','This view name is already in use with this device.');
		if($this->form_validation->run()){
			$device = new Device($this->form_validation->set_value('device_id'));
			//check if device exists
			if($device->exists()){
				$user = new User($this->tank_auth->get_user_id());
				//check if user is the admin of the device's domain
				if($user->is_admin_of($device->domain->id)){
					//save view
					$view = new View();
					$view->device_id 	= $device->id;
					$view->name 		= $this->form_validation->set_value('name');
					$view->body 		= $this->form_validation->set_value('view');
					if($view->save()){
						$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"View successfully saved."));
					} else {
						log_message('error',"da/add_view | could not save view for device_id: $device->id");
						$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Could not create view, please try again"));
					}
				} else {
					$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, you don't have enough rights to perform this action"));
					log_message('error',"da/add_view | user is not the devices's domain admin; device_id: $device->id");				
				}
			} else {
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, this device doesn't exist"));
				log_message('error',"da/add_view | device does not exist, device_id:$device->id");			
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>validation_errors()));			
		}
		redirect('da/edit_views/'.$this->form_validation->set_value('device_id'));		
	}

	function edit_single_view($view_id){
		$view = new View($view_id);
		if($view->exists()){
			$device_id = $view->device->id;			
			$user = new User($this->tank_auth->get_user_id());
			//check if user is the admin of the device's domain
			if($user->is_admin_of($view->device->domain->id)){
				$this->load->library('form_validation');
				$this->form_validation->set_rules('name','Name','required|trim|xss_clean|alpha_numeric');
				$this->form_validation->set_rules('view','View','required|trim|callback__check_view_variables['.$device_id.']');
				$this->form_validation->set_message('_valid_view_name','This view name is already in use with this device.');
				if($this->form_validation->run()){
					$view->name 		= $this->form_validation->set_value('name');
					$view->body 		= $this->form_validation->set_value('view');
					if($view->save()){
						$this->session->set_flashdata('message',array('type'=>'success','message'=>'View successfully edited'));
						redirect($this->uri->uri_string());						
					}
				} else {
					if(validation_errors()){
						//redirect so that we can show errors						
						$this->session->set_flashdata('message',array('type'=>'error','message'=>validation_errors()));
						redirect($this->uri->uri_string());
					}
					$data['user'] = $user;
					$data['view'] = $view;
					$this->load->view('da_edit_single_view_view',$data);
				}
			} else {
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, you don't have enough rights to perform this action"));
				log_message('error',"da/edit_single_view | user is not the devices's domain admin; device_id: ".$view->device->id);	
				redirect('da/edit_views/'.$device_id);			
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, this view doesn't exist"));
			log_message('error',"da/edit_single_view | view doesn't exist, view_id: $view_id");
			redirect('da/edit_views/'.$device_id);			
		}	
	}

	function delete_view($view_id){
		$view = new View($view_id);
		if($view->exists()){
			$device_id = $view->device->id;			
			$user = new User($this->tank_auth->get_user_id());
			//check if user is the admin of the device's domain
			if($user->is_admin_of($view->device->domain->id)){
				if($view->delete()){
					$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"View successfully deleted"));
				} else {
					log_message('error',"da/delete_view | could not delete view, view_id: $view_id");
					$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, view could not be deleted, please try again."));					
				}
			} else {
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, you don't have enough rights to perform this action"));
				log_message('error',"da/delete_view | user is not the devices's domain admin; device_id: ".$view->device->id);				
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, this view doesn't exist"));
			log_message('error',"da/delete_view | view doesn't exist, view_id: $view_id");			
		}
		redirect('da/edit_views/'.$device_id);
	}

	function delete_var($var_id){
		$var = new Variable($var_id);
		if($var->exists()){
			//check if the user is the admin of the device's domain
			$user = new User($this->tank_auth->get_user_id());
			if($user->is_admin_of($var->device->domain->id)){
				$device_id = $var->device->id;
				if($var->delete()){
					$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"Variable successfully deleted"));						
				} else {
					$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, we were not able to delete this variabe. Please try again."));						
					log_message('error',"da/delete_var | could not delete var, var_id: $var_id");					
				}
			} else {
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, you don't have enough rights to perform this action."));						
				log_message('error',"da/delete_var |  user is not the devices's domain admin, var_id: $var_id ");				
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, this variable doesn't seem to exist."));						
			log_message('error',"da/delete_var | no such variable, var_id: $var_id");			
		}
		log_message('info','da/delete_var | device_id: '.$var->device->id);
		redirect('da/edit_device/'.$device_id);			
	}

	function add_var(){
		$user = new User($this->tank_auth->get_user_id());
		$this->load->library('form_validation');
		$this->form_validation->set_rules('device_id','Device Id','required|trim|xss_clean|max_length[100]');
		$this->form_validation->set_rules('variables','Variables','required|trim|xss_clean|max_length[300]');	
		if($this->form_validation->run()){
			$device = new Device($this->form_validation->set_value('device_id'));
			$variables = $this->form_validation->set_value('variables');

			//check if such device exists
			if($device->exists()){
				//check if user is the admin of device's domain
				if($user->is_admin_of($device->domain->id)){
					$result = $device->add_variables($variables);
					if($result['type']=='success'){
						$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"Device successfully created."));				
					} else {
						$this->session->set_flashdata('message',array('type'=>'error', 'message'=>$result['message']));					
					}					
				} else {
					$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, you don't have enough rights to perform this action."));						
					log_message('error',"da/add_var |  user is not the devices's domain admin, device_id: $device->id ");					
				}
			} else {
				$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, this device doesn't seem to exist."));						
				log_message('error',"da/add_var | no such device, device id: $device->id");					
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>validation_errors()));			
		}
		redirect('da/edit_device/'.$device->id);				
	}

	function get_device_key($device_id){
		$user = new User($this->tank_auth->get_user_id());
		$device = new Device($device_id);
		$return = array();
		if($device->exists()){
			if($user->is_admin_of($device->domain->id)){
				$return = array('type'=>'success','key'=>$device->key);
			} else {
				$return = array('type'=>'error','message'=>"Sorry, you do not have enough rights to view this key.");	
				log_message('error',"da/get_device_key | user is not admin of device's domain, device_id: $device_id");			
			}
		} else {
			$return = array('type'=>'error','message'=>"Sorry, this device does not seem to exist");	
			log_message('error',"da/get_device_key | device doesn't exist, device_id: $device_id");					
		}
		echo json_encode($return);
	}

	function generate_device_key($device_id){
		$user = new User($this->tank_auth->get_user_id());
		$device = new Device($device_id);
		if($device->exists()){
			if($user->is_admin_of($device->domain->id)){
				if($device->generate_key()){
					$this->session->set_flashdata('message', array('type'=>'success','message'=>"Device key successfully generated."));
				} else {
					$this->session->set_flashdata('message', array('type'=>'error','message'=>"Sorry, something went wrong. Please try again."));	
					log_message('error',"da/generate_device_key | could not generate device key, device_id: $device_id");						
				}
			} else {
				$this->session->set_flashdata('message', array('type'=>'error','message'=>"Sorry, you do not have enough rights to view this key."));	
				log_message('error',"da/generate_device_key | user is not admin of device's domain, device_id: $device_id");			
			}
		} else {
			$this->session->set_flashdata('message', array('type'=>'error','message'=>"Sorry, this device does not seem to exist"));	
			log_message('error',"da/generate_device_key | device doesn't exist, device_id: $device_id");					
		}
		redirect('da/edit_device/'.$device->id);	
	}

	function change_device_name(){
		$this->load->library('form_validation');
		$this->form_validation->set_rules('device_name','Device Name','required|trim|xss_clean');
		$this->form_validation->set_rules('device_id','Device Id','required|trim|xss_clean');
		if($this->form_validation->run()){
			$user = new User($this->tank_auth->get_user_id());
			$name = $this->form_validation->set_value('device_name');
			$device = new Device($this->form_validation->set_value('device_id'));
			//check if such device exists
			if($device->exists()){
				//check if user is the admin of device's domain
				if($user->is_admin_of($device->domain->id)){
					if($device->update_name($name)){
						$this->session->set_flashdata('message', array('type'=>'success', 'message'=>"Device name successfully updated."));				
					} else {
						$this->session->set_flashdata('message', array('type'=>'error', 'message'=>'Sorry, something went wrong. Please try again.'));					
					}					
				}else {
					$this->session->set_flashdata('message', array('type'=>'error','message'=>"Sorry, you do not have enough rights to perform this action."));	
					log_message('error',"da/get_device_key | user is not admin of device's domain, device_id: $device_id");						
				}
			} else {
				$this->session->set_flashdata('message', array('type'=>'error','message'=>"Sorry, this device does not seem to exist"));	
				log_message('error',"da/change_device_name | device doesn't exist, device_id: $device->id");					
			}
		}

		redirect('da/edit_device/'.$device->id);
	}

	function change_device_description(){
		$this->load->library('form_validation');
		$this->form_validation->set_rules('device_description','Device Description','required|trim|xss_clean');
		$this->form_validation->set_rules('device_id','Device Id','required|trim|xss_clean');
		if($this->form_validation->run()){
			$user = new User($this->tank_auth->get_user_id());
			$description = $this->form_validation->set_value('device_description');
			$device = new Device($this->form_validation->set_value('device_id'));
			//check if such device exists
			if($device->exists()){
				//check if user is the admin of device's domain
				if($user->is_admin_of($device->domain->id)){
					if($device->update_description($description)){
						$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"Device description successfully updated."));				
					} else {
						$this->session->set_flashdata('message',array('type'=>'error', 'message'=>'Sorry, something went wrong. Please try again.'));					
					}					
				}else {
					$this->session->set_flashdata('message', array('type'=>'error','message'=>"Sorry, you do not have enough rights to perform this action."));	
					log_message('error',"da/change_device_description | user is not admin of device's domain, device_id: $device_id");						
				}
			} else {
				$this->session->set_flashdata('message', array('type'=>'error','message'=>"Sorry, this device does not seem to exist"));	
				log_message('error',"da/change_device_description | device doesn't exist, device_id: $device->id");					
			}
		}

		redirect('da/edit_device/'.$device->id);
	}	

	function change_managing_domain($domain_id){
		$user = new User($this->tank_auth->get_user_id());
		if($user->is_admin_of($domain_id)){
			$domain = new Domain($domain_id);
			$this->session->set_userdata('managing_domain_id',$domain_id);
			$this->session->set_userdata('managing_domain_name',$domain->name);
		} else {
			$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, you are not an admin of this domain"));
			log_message('error',"da/change_managing_domain | user attempted to change managing domain to one he is not an admin of; domain_id: $device_id, user_id: $user->id");			
		}

		redirect('da');
	}

	function valid_device_name($device_name){
		$domain_id = $this->session->userdata('managing_domain_id');
		$count = $this->db->where('domain_id',$domain_id)->where('name',$device_name)->count_all_results('devices'); 
		if($count){
			//such device name has been found in this domain - device name not valid
			return FALSE;
		} else {
			//device name is valid
			return TRUE;
		}
	}

	function _valid_view_name($name, $device_id){
		$device = new Device($device_id);
		if($device->exists()){
			return $device->valid_view_name($name);
		} else {
			log_message('error',"da/_check_view_variables | device id $device_id doesn't exist. View not saved.");
			$this->form_validation->set_message('_check_view_variables',"This device doesn't exist");
			return FALSE;
		}		
	}

	function _check_view_variables($string, $device_id){
		$device = new Device($device_id);
		if($device->exists()){
			preg_match_all("/\{(.+)\}/U", $string, $var_names);			
			$var_names = $var_names[1];		
			foreach($var_names as $var_name){
				if((strpos($var_name,"view:") === false)){ 
					$var = $device->variables->where('name',$var_name)->get();
					if(!$var->exists()){
						log_message('error',"da/_check_view_variables | Variable $var_name doesn't exist for device id: $device_id. Notification_rule not saved.");
						$this->form_validation->set_message('_check_view_variables',"Variable $var_name doesn't exist for this device. View not saved.");
						return FALSE;
					}
				}
			}
			return TRUE;						
		} else {
			log_message('error',"da/_check_view_variables | device id $device_id doesn't exist. View not saved.");
			$this->form_validation->set_message('_check_view_variables',"This device doesn't exist");
			return FALSE;
		}
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