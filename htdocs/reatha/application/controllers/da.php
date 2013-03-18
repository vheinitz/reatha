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

	function images(){
		$user = new User($this->tank_auth->get_user_id());
		$domain = new Domain($this->session->userdata('managing_domain_id'));

		$data['user'] = $user;
		$data['domain'] = $domain;
		$data['domains'] = $user->domains->get();
		$data['images'] = $domain->images->get();
		$this->load->view('da_images_view',$data);		
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
					//unsubscribe user to device notification rules
					foreach ($device->notification_rule as $rule) {
						$user->delete($rule);
					}					
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
						//subscribe user to device notification rules
						foreach ($device->notification_rule as $rule) {
							$user->save($rule);
						}
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

	function clone_device($device_id){
		$device = new Device($device_id);

		//check if device exists
		if($device->exists()){
			$user = new User($this->tank_auth->get_user_id());
			//check if user is the admin of the device's domain
			if($user->is_admin_of($device->domain->id)){
				$cloned_device = $device->get_copy();

				//count the amount of devices with this name, so that we can do device_1, device_2 etc
				$device_count = $device->where('cloned_from_device_id',$device->id)->count();
				$cloned_device->name 	= $device->name.'_'.($device_count+1);

				// Hash device key using phpass
				require_once('application/libraries/phpass-0.1/PasswordHash.php');
				$hasher 	= new PasswordHash(6,FALSE);
				$cloned_device_key = $hasher->HashPassword($cloned_device->name);
				$cloned_device->key	= $cloned_device_key;	
				$cloned_device->cloned_from_device_id = $device->id;			

				if($cloned_device->save()){
					//now clone relationships
					//assign users to cloned device
					foreach($device->users as $user){
						$user->assign_device($cloned_device->id);
					}

					//clone variables
					$cloned_variables = $device->variables->get();
					foreach ($cloned_variables as $cloned_variable) {
						$var = $cloned_variable->get_copy();						
						$var->device_id = $cloned_device->id;
						if($var->save()){
							//clone var transformations
							$cloned_transformations = $cloned_variable->transformations->get();
							foreach ($cloned_transformations as $t) {
								$t->id = "";
								$t->device_id = $cloned_device->id;
								$t->variable_id = $var->id;
								$t->save_as_new();	
							}

							//clone notification rules
							$cloned_rules = $cloned_variable->notification_rule->get();
							foreach ($cloned_rules as $cloned_rule) {
								$cloned_rule->id = "";
								$cloned_rule->device_id = $cloned_device->id;
								$cloned_rule->variable_id = $var->id;
								if($cloned_rule->save_as_new()){
									//adding cloned notification rule to each user assigned to device
									foreach($cloned_device->user as $user){						
										$user->save($cloned_rule);

									}									
								}
							}

							//clone device list view
							$cloned_view = $device->device_list_view->get();
							$cloned_view->id = "";
							$cloned_view->device_id = $cloned_device->id;
							$cloned_view->save_as_new();
						}
					}

					//clone views
					$cloned_views = $device->views->get();
					foreach ($cloned_views as $view) {
						$view->id = "";
						$view->device_id = $cloned_device->id;
						$view->save_as_new();
					}					

					$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"Device successfully cloned;"));
				} else {
					$this->session->set_flashdata('message',array('type'=>'error', 'message'=>"Sorry, something went wrong, please try again"));
					log_message('error',"da/delete_device | could not save clonned device from device id:  $device->id");					
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
						//saving "view" variable that controls which view to display
						if(!$device->has_view_variable()){
							$var = new Variable();
							$var->name 	= "view";
							$var->value = "main";
							$var->save($device);
						}
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
					$data['domains'] = $user->domains->get();
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
				if(
					$var->transformations->delete_all() && 
					$this->db->where('export_var_id',$var_id)->delete('transformations') && 
					$var->delete()
					){

					$this->db->where('export_var_id',$var_id)->delete('transformations');
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
						$this->session->set_flashdata('message',array('type'=>'success', 'message'=>"Variable successfully created."));				
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

	function delete_image($image_id){
		$image = new Image($image_id);
		if($image->exists()){
			$user = new User($this->tank_auth->get_user_id());
			if($user->is_admin_of($image->domain->id)){
				if(file_exists('assets/'.$image->domain->name.'/'.$image->file)){
					if(unlink('assets/'.$image->domain->name.'/'.$image->file)){
						if($image->delete()){
							$this->session->set_flashdata('message', array('type'=>'success','message'=>"Image successfully deleted"));
						} else {
							log_message('error','da/delete_image | could not delete image from database, image id: '.$image_id);
							$this->session->set_flashdata('message', array('type'=>'error','message'=>"Could not fully delete this image."));
						}
					} else {
						log_message('error','da/delete_image | could not delete image from disk, image id: '.$image_id);
						$this->session->set_flashdata('message', array('type'=>'error','message'=>"Could not delete this image."));						
					}
				} else {
					log_message('error','da/delete_image | image does not exist on disk, image name: '.$image->name);
					$this->session->set_flashdata('message', array('type'=>'error','message'=>"This image does not exist on disk"));					
				}
			} else {
				log_message('error','da/delete_image | user is not an admin of image domain, image id: '.$image_id.', user id: '.$user->id);
				$this->session->set_flashdata('message', array('type'=>'error','message'=>"Sorry, you do not have enough rights to delete this image."));				
			}
		} else {
			log_message('error','da/delete_image | image does not exist in database, image id: '.$image_id);
			$this->session->set_flashdata('message', array('type'=>'error','message'=>"This image does not exist"));			
		}
		redirect('da/images');
	}

	function upload_image(){
		if(isset($_FILES['image'])){
			$image = new Image();
			$domain = new Domain($this->session->userdata('managing_domain_id'));
			$result = $image->process_image($_FILES['image'],$domain->name);
			if($result['type'] == 'success'){
				$image->file = $result['body'];
				$image->domain_id = $domain->id;
				if($image->save()){
					$this->session->set_flashdata('message', array('type'=>'success','message'=>"Image successfully uploaded"));					
				} else {
					log_message('error','da/upload_image | could not save image, image name: '.$result['body']);
					$this->session->set_flashdata('message', array('type'=>'error','message'=>"Sorry, something went wrong"));					
				}
			} else {
				log_message('error','da/upload_image | could not upload image, error: '.$result['body']);
				$this->session->set_flashdata('message', array('type'=>'error','message'=>"Error: ".$result['body']));				
			}
		} else {
			log_message('error','da/upload_image | function accesed with empty file');
			$this->session->set_flashdata('message', array('type'=>'error','message'=>"Sorry, we had nothing to upload"));
		}

		redirect('da/images');		
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

	function add_transformation(){
		$this->load->library('form_validation');
		$this->form_validation->set_rules('variable_id','Variable id','required|trim|xss_clean');
		$this->form_validation->set_rules('transformation','Transformation','required|trim|xss_clean|callback__check_view_variables['.$this->input->post('device_id').']');
		$this->form_validation->set_rules('export_variable_name','Export Variable Name','required|trim|xss_clean');
		$this->form_validation->set_rules('device_id','Device id','required|trim|xss_clean');
		if($this->form_validation->run()){
			$device = new Device($this->form_validation->set_value('device_id'));
			if($device->exists()){
				$user = new User($this->tank_auth->get_user_id());
				if($user->is_admin_of($device->domain->id)){
					$var = new Variable($this->form_validation->set_value('variable_id'));
					if($var->exists()){
						//check if export_variable_name is a valid var namer, i.e. such var doesn't exists with this device
						$export_variable_name = $this->form_validation->set_value('export_variable_name');
						if($device->valid_variable_name($export_variable_name)){
							//create the export var
							$export_var = new Variable();
							$export_var->device_id = $device->id;
							$export_var->name = $export_variable_name;
							if($export_var->save()){
								//export variable saved, now create the transformation
								$t = new Transformation();							
								$t->device_id = $device->id;
								$t->variable_id = $var->id;
								$t->export_var_id = $export_var->id;
								$t->body = $this->form_validation->set_value('transformation');
								if($t->save()){
									$this->session->set_flashdata('message', array('type'=>'success','message'=>"Transformation successfully saved"));										
								} else {
									$this->session->set_flashdata('message', array('type'=>'error','message'=>"Sorry, transformation could not be saved"));	
									log_message('error',"da/add_transformation | transformation could not be saved for device id: $device->id");
								}
							} else {
								$this->session->set_flashdata('message', array('type'=>'error','message'=>"Sorry, something went wrong, please try again."));	
								log_message('error',"da/add_transformation | could not save export_var, export_var name: $export_variable_name, device id: $device->id");								
							}
						} else {
							$this->session->set_flashdata('message', array('type'=>'error','message'=>"This export variable name is already in use."));	
							log_message('error',"da/add_transformation | export variable name already in use, var name: $export_variable_name, device id: $device->id");							
						}
					} else {
						$this->session->set_flashdata('message', array('type'=>'error','message'=>"This variable doesn't exist"));	
						log_message('error',"da/add_transformation | variable doesn't exist, var id: ".$this->form_validation->set_value('variable_id'));						
					}
				} else {
					$this->session->set_flashdata('message', array('type'=>'error','message'=>"Sorry, you don't have enough rights to perform this action."));	
					log_message('error',"da/add_transformation | user not an admin of domain id: ".$device->domain->id.", user id: $user->id");				
				}
			} else {
				$this->session->set_flashdata('message', array('type'=>'error','message'=>"This device doesn't exist."));	
				log_message('error',"da/add_transformation | device doesn't exist, device id: ".$this->form_validation->set_value('device_id'));				
			}
		} else {
			$this->session->set_flashdata('message', array('type'=>'error','message'=>validation_errors()));			
		}
		redirect('da/edit_device/'.$this->input->post('device_id'));
	}

	function delete_transformation($t_id){
		$t = new Transformation($t_id);
		if($t->exists()){
			$device_id = $t->device_id;
			$user = new User($this->tank_auth->get_user_id());
			if($user->is_admin_of($t->device->domain->id)){
				if($t->delete()){
					$this->session->set_flashdata('message', array('type'=>'success','message'=>"Transformation successfully deleted"));					
				} else {
					$this->session->set_flashdata('message', array('type'=>'error','message'=>"Could not delete transformation, please try again."));	
					log_message('error',"da/delete_transformation | could not delete transformation, transformation id: $t->id");					
				}
			} else {
				$this->session->set_flashdata('message', array('type'=>'error','message'=>"Sorry, you don't have enough rights to perform this action."));	
				log_message('error',"da/delete_transformation | user not an admin of domain id: ".$t->device->domain->id.", user id: $user->id");	
			}
		} else {
			$this->session->set_flashdata('message', array('type'=>'error','message'=>"This transformation doesn't exist."));	
			log_message('error',"da/delete_transformation | transformation does not exist, t id: $t_id");				
		}
		redirect('da/edit_device/'.$device_id);
	}

	function edit_transformation($t_id){
		$t = new Transformation($t_id);
		if($t->exists()){
			$device_id = $t->device_id;
			$user = new User($this->tank_auth->get_user_id());
			if($user->is_admin_of($t->device->domain->id)){
				$this->load->library('form_validation');
				$this->form_validation->set_rules('variable_id','Variable id','required|trim|xss_clean');
				$this->form_validation->set_rules('transformation','Transformation','required|trim|xss_clean|callback__check_view_variables['.$device_id.']');
				$this->form_validation->set_rules('export_variable_name','Export Variable Name','required|trim|xss_clean');
				if($this->form_validation->run()){
					$device = new Device($device_id);
					$export_variable_name = $this->form_validation->set_value('export_variable_name');
					$current_export_var = new Variable($t->export_var_id);

					//if new export variable entered
					if($current_export_var->name != $export_variable_name){
						if($device->valid_variable_name($export_variable_name)){
							//create the export var
							$export_var = new Variable();
							$export_var->device_id = $device->id;
							$export_var->name = $export_variable_name;
							if($export_var->save()){
								//set new export var in transformation
								$t->export_var_id = $export_var->id;								
							} 							
						}
					}

					//update transformation						
					$t->variable_id = $this->form_validation->set_value('variable_id');
					$t->body = $this->form_validation->set_value('transformation');
					if($t->save()){
						$this->session->set_flashdata('message', array('type'=>'success','message'=>"Transformation successfully saved"));										
					} else {
						$this->session->set_flashdata('message', array('type'=>'error','message'=>"Sorry, transformation could not be saved"));	
						log_message('error',"da/edit_transformation | transformation could not be saved for device id: $device->id");
					}

					redirect($this->uri->uri_string());
				} else {
					if(validation_errors()){
						//redirect to show errors
						$this->session->set_flashdata('message',array('type'=>'error','message'=>validation_errors()));
						redirect($this->uri->uri_string());						
					}
					$data['user'] 		= $user;
					$data['domains']	= $user->domains->get();						
					$data['t'] = $t;
					$this->load->view('da_edit_transformation_view',$data);	
				}			
			} else {
				$this->session->set_flashdata('message', array('type'=>'error','message'=>"Sorry, you don't have enough rights to perform this action."));	
				log_message('error',"da/delete_transformation | user not an admin of domain id: ".$t->device->domain->id.", user id: $user->id");	
			}
		} else {
			$this->session->set_flashdata('message', array('type'=>'error','message'=>"This transformation doesn't exist."));	
			log_message('error',"da/delete_transformation | transformation does not exist, t id: $t_id");				
		}	
	}

	function view_preview(){
		$this->load->library('form_validation');
		$this->form_validation->set_rules('view','View','required|trim');
		$this->form_validation->set_rules('device_id','Device id','required|trim');
		if($this->form_validation->run()){
			$device = new Device($this->form_validation->set_value('device_id'));
			if($device->exists()){
				$view = new View();
				$view->body = $this->form_validation->set_value('view');
				$view->device = $device;
				echo $view->process_placeholders();
			}
		}
	}

	function notifications($device_id){
		$device = new Device($device_id);
		if($device->exists()){
			$user = new User($this->tank_auth->get_user_id());
			if($user->is_admin_of($device->domain->id)){
				$data['user'] 	= $user;
				$data['device'] = $device;
				$this->load->view('da_notifications_view',$data);
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
		$this->form_validation->set_rules('severity_level','Severity Level','required|trim|xss_clean');
		$this->form_validation->set_rules('message','Message','required|trim|xss_clean|callback__check_message_variables['.$this->input->post('device_id').']');	
		$this->form_validation->set_rules('subject','Subject','required|trim|xss_clean|callback__check_message_variables['.$this->input->post('device_id').']');
		$this->form_validation->set_rules('device_id','device','required|trim|xss_clean');
		$device = new Device($this->input->post('device_id',true));		
		if($this->form_validation->run()){
			if($device->exists()){
				$user = new User($this->tank_auth->get_user_id());
				if($user->is_admin_of($device->domain->id)){
					$notification_rule_save_result = true;

					//saving notification rule 
					$rule = new Notification_rule();
					$rule->device_id = $device->id;
					$rule->variable_id = $this->form_validation->set_value('variable');
					$rule->name = $this->form_validation->set_value('name');
					$rule->description = $this->form_validation->set_value('description');
					$rule->condition = $this->form_validation->set_value('condition');
					$rule->message = strip_tags($this->form_validation->set_value('message'),"<br><br/><p><i>");
					$rule->subject = $this->form_validation->set_value('subject');
					$rule->interval = $this->form_validation->set_value('interval');
					$rule->severity_level = $this->form_validation->set_value('severity_level');
					if($rule->save()){
						//adding notification rule to each user assigned to device
						foreach($device->user as $user){						
							if(!$user->save($rule)){
								$notification_rule_save_result = false;
								break;						
							}
						}
						if($notification_rule_save_result){
							$this->session->set_flashdata('message',array('type'=>'success','message'=>'Notification successfully saved'));						
						} else {
							$this->session->set_flashdata('message',array('type'=>'error','message'=>'Something went wrong, please try again'));							
						}
					} else {
						$this->session->set_flashdata('message', array('type'=>'error','message'=>"Sorry, something went wrong, please try again"));	
						log_message('error',"da/add_notification_rule | could not save notification_rule in database, variable_id: ".$this->form_validation->set_value('variable'));
					}					
				} else {
					$this->session->set_flashdata('message', array('type'=>'error','message'=>"You don't have enough rights to perform this action"));	
					log_message('error',"da/add_notification_rule | device doesn't exist, device_id: ".$this->input->post('device_id',true));
				}
			} else {
				$this->session->set_flashdata('message', array('type'=>'error','message'=>"This device doesn't exist"));	
				log_message('error',"da/add_notification_rule | could not save notification_rule in database, variable_id: ".$this->form_validation->set_value('variable'));
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error','message'=>validation_errors()));
		}
		redirect('da/notifications/'.$device->id);	
	}	

	function edit_notification_rule($rule_id){
		$rule = new Notification_rule($rule_id);
		if($rule->exists()){
			$device_id = $rule->device->id;
			$user = new User($this->tank_auth->get_user_id());
			if($user->is_admin_of($rule->device->domain->id)){
				$this->load->library('form_validation');
				$this->form_validation->set_rules('name','Name','required|trim|xss_clean');		
				$this->form_validation->set_rules('description','Description','required|trim|xss_clean');				
				$this->form_validation->set_rules('variable','Variable','required|trim|xss_clean');
				$this->form_validation->set_rules('condition','Condition','required|trim|xss_clean');
				$this->form_validation->set_rules('interval','Interval','required|trim|numeric|xss_clean');
				$this->form_validation->set_rules('severity_level','Severity Level','required|trim|numeric|xss_clean');
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
					$rule->severity_level = $this->form_validation->set_value('severity_level');
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
					$this->load->view('da_edit_notifications_view',$data);
				}
			} else {
				log_message('error','u/edit_notification_rule | user has no rights to device, device id: '.$rule->device->id);
				$this->session->set_flashdata('message',array('type'=>'error','message'=>'You do not have enough rights to perform this action.'));		
				redirect('da/notifications/'.$device_id);			
			}
		} else {
			log_message('error','u/edit_notification_rule | no such rule, id: '.$rule_id);
			$this->session->set_flashdata('message',array('type'=>'error','message'=>'This notification rule does not exist.'));
			redirect('da/notifications/'.$device_id);				
		}

	}

	function delete_notification_rule($rule_id){
		$rule = new Notification_rule($rule_id);
		if($rule->exists()){
			$device_id = $rule->device->id;
			$user = new User($this->tank_auth->get_user_id());
			if($user->is_admin_of($rule->device->domain->id)){
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

		redirect('da/notifications/'.$device_id);
	}

	function toggle_notification_status($rule_id,$flag){
		$rule = new Notification_rule($rule_id);
		if($rule->exists()){
			$device_id = $rule->device->id;
			$user = new User($this->tank_auth->get_user_id());
			if($user->is_admin_of($rule->device->domain->id)){
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

		redirect('da/notifications/'.$device_id);		
	}

	function customize_device_list($device_id){		
		$user = new User($this->tank_auth->get_user_id());
		$view = new Device_list_view();
		$view->get_by_device_id($device_id);
		$data['user'] = $user;
		$data['domains'] = $user->domains->get();
		$data['device_id'] = $device_id;
		$data['view'] = $view;
		$this->load->view('da_customize_device_list_view',$data);
	}

	function device_list_view_preview(){
		$this->load->library('form_validation');
		$this->form_validation->set_rules('view','View','required|trim');
		$this->form_validation->set_rules('device_id','Device id','required|trim|xss_clean');
		if($this->form_validation->run()){
			$view = new Device_list_view();
			$view->device = new Device($this->form_validation->set_value('device_id'));
			$view->body = $this->form_validation->set_value('view');
			echo $view->process_placeholders();
		}
	}

	function edit_device_list_view(){
		$this->load->library('form_validation');		
		$this->form_validation->set_rules('view','View','required|trim');			
		$this->form_validation->set_rules('device_id','Device id','required|trim|xss_clean');
		if($this->form_validation->run()){
			$view = new Device_list_view();
			$device_id = $this->form_validation->set_value('device_id');
			$body = $this->form_validation->set_value('view');
			$view->get_by_device_id($device_id);

			$view->device_id = $device_id;
			$view->body = $body;

			if($view->save()){
				$this->session->set_flashdata('message',array('type'=>'success','message'=>'Device list view successfully saved'));										
			} else {
				log_message('error','da/edit_device_list_view | could not save view');
				$this->session->set_flashdata('message',array('type'=>'error','message'=>'Something went wrong, please try again'));						
			}
		} else {
			$this->session->set_flashdata('message',array('type'=>'error','message'=>validation_errors()));
		}
		redirect('da/customize_device_list/'.$device_id);
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

			//check references to other views - if such views exist
	        preg_match_all("/\{(view:[A-Za-z0-9]+)\}/U", $string, $views);
	        $views = $views[1];
	        if(!empty($views)){
	        	foreach($views as $view_name){
	        		$view_name = explode(":", $view_name);
	        		$view_name = $view_name[1];
	        		if(!$device->has_view($view_name)){
						log_message('error',"da/_check_view_variables | View $view_name doesn't exist for device id: $device_id. View not saved.");
						$this->form_validation->set_message('_check_view_variables',"View $view_name doesn't exist for this device. View not saved.");
						return FALSE;	        			
	        		}
	        	}
	        }			

	        //check vars
			preg_match_all("/\{(.+)\}/U", $string, $var_names);			
			$var_names = $var_names[1];		
			foreach($var_names as $var_name){
				//filter out views, :files placeholder, and reserved vars (they start with _ ) and then check if such var exists
				if((strpos($var_name,"view:") === false) && (strpos($var_name,":files") === false) && ($var_name[0]!="_")){ 
					$var = $device->variables->where('name',$var_name)->get();
					if(!$var->exists()){
						log_message('error',"da/_check_view_variables | Variable $var_name doesn't exist for device id: $device_id. View not saved.");
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