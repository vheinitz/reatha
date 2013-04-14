<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//loading rest controller lib
require'./application/libraries/REST_controller.php';

class Api extends REST_controller{

	//get a device key, by id
	function device_key_get(){
        if($this->get('id')){  
        	$device = new Device($this->get('id'));
        	if($device->exists()){
        		$user = new User($this->tank_auth->get_user_id());
        		log_message('info','api/device_key_get | user_id: '.$user->id);
        			$this->response(array("key"=>$device->key), 200);
        	} 
        } else {
        	log_message('info','api/device_key_get | no id provided');
            $this->response(NULL, 400); 
        } 
	}

	//TODO requires reatha admin as min auth
    function list_domains_post(){
        $domains = new Domain();
        $domains->get();
        $return = array();
        foreach($domains as $domain){
            //get domain admins
            $domain_admins = array();
            foreach ($domain->domain_admins as $domain_admin) {
                $domain_admins[] = $domain_admin->id;
            }
            $return[] = array(
				'id'                => $domain->id,
                'name'              => $domain->name,
                'description'       => $domain->description,
            );
        }
        $this->response($return, 200);            
    }
	
	function list_domain_admins_post(){
        $domain_admins = new User();
        $domain_admins->where('role','2')->get();
        $return = array();
        foreach($domain_admins as $domain_admin){            
            $return[] = array(
                'id'           => $domain_admin->id,
				'username'     => $domain_admin->username,
                'password'     => $domain_admin->password,
				'email'        => $domain_admin->email,
//				'role'         => $domain_admin->role
            );
        }
        $this->response($return, 200);            
    }
	
    function list_users_post(){
		$domain_id = $this->post('domain_id');	
		$domain = new Domain( );
		$domain->where('id',$domain_id)->get();
		if($domain->exists()){
			$return = array();                
			$users = new User();
			$users->where('belongs_to_domain_id',$domain->id)->get();
			//traversing the users array
			foreach ($users as $user) {
				$return[] = array(
				    'id'  => $user->id,
					'username'  => $user->username,
					'password'  => $user->password,
					'email'     => $user->email,
//                        'role'      => $user->role
				);
			}
			$this->response($return, 200);
		} 
		else {
			log_message('info','api/list_users_post | invalide domain provided');
			$this->response(NULL, 400); 
		}         
    }
	
	function list_devices_post(){
		$domain_id = $this->post('domain_id');	
		$domain = new Domain( );
		$domain->where('id',$domain_id)->get();
		if($domain->exists()){
			$return = array();                
			//traversing the device array
			foreach ($domain->devices as $device) {
				
				//getting users assigned to this device
				$assigned_users = array();
				foreach($device->users as $user){
					$assigned_users[] = $user->id;
				}

				//device list view
				$list_view = $device->device_list_view->get();
				$return[] = array(
					'id'                    => $device->id,
//					'domain_id'             => $device->domain_id,
//					'cloned_from_device_id' => $device->cloned_from_device_id,
					'name'                  => $device->name,
					'description'           => $device->description,
					'location'              => $device->location,
					'key'                   => $device->key,
//					'list_view'             => $list_view->body,
//					'assigned_users'        => $assigned_users
				);
			}
			$this->response($return, 200);
		} 
		else {
			log_message('info','api/list_users_post | invalide domain provided');
			$this->response(NULL, 400); 
		}         
    }

    function list_devices_get(){
        if($this->get('domain_id')){  
            $domain = new Domain($this->get('domain_id'));
            if($domain->exists()){
                $return = array();                
                //traversing the device array
                foreach ($domain->devices as $device) {
                    
                    //getting users assigned to this device
                    $assigned_users = array();
                    foreach($device->users as $user){
                        $assigned_users[] = $user->id;
                    }

                    //device list view
                    $list_view = $device->device_list_view->get();
                    $return[] = array(
                        'id'                    => $device->id,
                        'domain_id'             => $device->domain_id,
                        'cloned_from_device_id' => $device->cloned_from_device_id,
                        'name'                  => $device->name,
                        'description'           => $device->description,
                        'location'              => $device->location,
                        'key'                   => $device->key,
                        'list_view'             => $list_view->body,
                        'assigned_users'        => $assigned_users
                    );
                }
                $this->response($return, 200);
            } 
        } else {
            log_message('info','api/list_devices_get | no id provided');
            $this->response(NULL, 400); 
        }         
    }

    function list_views_post(){
        if($this->post('device_id')){  
            $device = new Device($this->post('device_id'));
            if($device->exists()){
                $return = array();                
                //traversing the views array
                foreach ($device->views as $view) {
                    $return[] = array(
                        'id'                    => $view->id,
                        'device_id'             => $view->device_id,
                        'name'                  => $view->name,
                        'body'                  => urlencode($view->body)
                    );
                }
                $this->response($return, 200);
            }
			else {
            log_message('info','api/list_views_post | invalide device');
            $this->response(NULL, 400); 
        }  
        } else {
            log_message('info','api/list_views_post | invalide device');
            $this->response(NULL, 400); 
        }        
    }
	
	function list_notifications_post(){
        if($this->post('device_id')){  
            $device = new Device($this->post('device_id'));
            if($device->exists()){
                $return = array();                
                //looping through the notification rules list
                foreach ($device->notification_rules as $rule) {
                    //get users assigned to this notification rule
                    $assigned_users = array();
                    //foreach ($rule->users as $user) {
                    //    $assigned_users[] = $user->id;
                    //}
					
					//TODO: optimize pls.
					$var_name;
					foreach ($device->variables as $var) {
						if ( $var->id == $rule->variable_id )
						{
							$var_name = $var->name;
							break;
						}
					}
                    $return[] = array(
                        'id'                    => $rule->id,
//                        'device_id'             => $rule->device_id,
                        'variable'              => $var_name,
                        'name'                  => $rule->name,
                        'description'           => $rule->description,
                        'condition'             => $rule->condition,
                        'severity_level'        => $rule->severity_level,
                        'message'               => $rule->message,
                        'subject'               => $rule->subject,
                        'interval'              => $rule->interval,
                        'activated'             => $rule->activated,
//                        'assigned_users'        => $assigned_users
                    );
                }
                $this->response($return, 200);
            } 
        } else {
            log_message('info','api/list_notification_rules_get | no id provided');
            $this->response(NULL, 400); 
        }        
    }
	
	function list_views_get(){
        if($this->get('device_id')){  
            $device = new Device($this->get('device_id'));
            if($device->exists()){
                $return = array();                
                //traversing the views array
                foreach ($device->views as $view) {
                    $return[] = array(
                        'id'                    => $view->id,
                        'device_id'             => $view->device_id,
                        'name'                  => $view->name,
                        'body'                  => urlencode($view->body)
                    );
                }
                $this->response($return, 200);
            } 
        } else {
            log_message('info','api/list_views_get | no id provided');
            $this->response(NULL, 400); 
        }         
    } 

	function list_variables_post(){
        if($this->post('device_id')){  
            $device = new Device($this->post('device_id'));
            if($device->exists()){
                $return = array();                
                //traversing the views array
                foreach ($device->variables as $var) {
                    $return[] = array(
                        'id'                    => $var->id,
//                       'device_id'             => $var->device_id,
                        'name'                  => $var->name,
//                        'value'                 => $var->value
                    );
                }
                $this->response($return, 200);
            } 
        } else {
            log_message('info','api/list_variables_get | no id provided');
            $this->response(NULL, 400); 
        }         
    }
	
    function list_variables_get(){
        if($this->get('device_id')){  
            $device = new Device($this->get('device_id'));
            if($device->exists()){
                $return = array();                
                //traversing the views array
                foreach ($device->variables as $var) {
                    $return[] = array(
                        'id'                    => $var->id,
                        'device_id'             => $var->device_id,
                        'name'                  => $var->name,
                        'value'                 => $var->value
                    );
                }
                $this->response($return, 200);
            } 
        } else {
            log_message('info','api/list_variables_get | no id provided');
            $this->response(NULL, 400); 
        }         
    }

    function list_transformations_post(){
        if($this->post('device_id')){  
            $device = new Device($this->post('device_id'));
            if($device->exists()){
                $return = array();                
                //going through the transformations list
                foreach ($device->transformations as $t) {
					//TODO: optimize pls.
					$var_name;
					foreach ($device->variables as $var) {
						if ( $var->id == $t->export_var_id )
						{
							$var_name = $var->name;
							break;
						}
					}
                    $return[] = array(
                        'id'                    => $t->id,
//                        'device_id'             => $t->device_id,
                        'export_var'            => $var_name,
                        'body'                  => $t->body
                    );
                }
                $this->response($return, 200);
            } 
        } else {
            log_message('info','api/list_transformations_get | no id provided');
            $this->response(NULL, 400); 
        }         
    }  
	
	function list_transformations_get(){
        if($this->get('device_id')){  
            $device = new Device($this->get('device_id'));
            if($device->exists()){
                $return = array();                
                //going through the transformations list
                foreach ($device->transformations as $t) {
                    $return[] = array(
                        'id'                    => $t->id,
                        'device_id'             => $t->device_id,
                        'export_var_id'         => $t->export_var_id,
                        'body'                  => $t->body
                    );
                }
                $this->response($return, 200);
            } 
        } else {
            log_message('info','api/list_transformations_get | no id provided');
            $this->response(NULL, 400); 
        }         
    }  

    function list_notification_rules_get(){
        if($this->get('device_id')){  
            $device = new Device($this->get('device_id'));
            if($device->exists()){
                $return = array();                
                //looping through the notification rules list
                foreach ($device->notification_rules as $rule) {
                    //get users assigned to this notification rule
                    $assigned_users = array();
                    foreach ($rule->users as $user) {
                        $assigned_users[] = $user->id;
                    }
                    $return[] = array(
                        'id'                    => $rule->id,
                        'device_id'             => $rule->device_id,
                        'variable_id'           => $rule->variable_id,
                        'name'                  => $rule->name,
                        'description'           => $rule->description,
                        'condition'             => $rule->condition,
                        'severity_level'        => $rule->severity_level,
                        'message'               => $rule->message,
                        'subject'               => $rule->subject,
                        'interval'              => $rule->interval,
                        'activated'             => $rule->activated,
                        'assigned_users'        => $assigned_users
                    );
                }
                $this->response($return, 200);
            } 
        } else {
            log_message('info','api/list_notification_rules_get | no id provided');
            $this->response(NULL, 400); 
        }       
    }

    function list_images_post(){
        if($this->post('domain_id')){  
            $domain = new Domain($this->post('domain_id'));
            if($domain->exists()){
                $return = array();                
                //traversing the device array
                foreach ($domain->images as $image) {
                    //$image_as_base64 = base64_encode(file_get_contents('./assets/'.$domain->name.'/'.$image->file));
                    $return[] = array(
                        'id'                    => $image->id,
//                        'domain_id'             => $image->domain_id,
                        'url'                   => './assets/'.$domain->name.'/'.$image->file,
//                        'file_as_base64'        => $image_as_base64,
//                        'created'               => $image->created
                    );
                }
                $this->response($return, 200);
            } 
        } else {
            log_message('info','api/list_devices_get | no id provided');
            $this->response(NULL, 400); 
        }  
    }
	
	function list_images_get(){
        if($this->get('domain_id')){  
            $domain = new Domain($this->get('domain_id'));
            if($domain->exists()){
                $return = array();                
                //traversing the device array
                foreach ($domain->images as $image) {
                    $image_as_base64 = base64_encode(file_get_contents('./assets/'.$domain->name.'/'.$image->file));
                    $return[] = array(
                        'id'                    => $image->id,
                        'domain_id'             => $image->domain_id,
                        'file'                  => $image->file,
                        'file_as_base64'        => $image_as_base64,
                        'created'               => $image->created
                    );
                }
                $this->response($return, 200);
            } 
        } else {
            log_message('info','api/list_devices_get | no id provided');
            $this->response(NULL, 400); 
        }  
    } 

    // PUT FUNCTIONS                       

    function create_domains_put(){
        $json = $this->put('domains');
        if(!empty($json)){
            //truncating the domains and domain_admins table, so that we start with a clean one
            $this->db->truncate('domains');
            $this->db->truncate('domain_admin_domains');
            $domains = json_decode($json);
            $success = false;
            foreach($domains as $domain){
                //inserting domain data
                $result = $this->db->insert('domains',array(
                    'id'                => $domain->id,
                    'name'              => $domain->name,
                    'description'       => $domain->description,
                    'header_title'      => $domain->header_title,
                    'header_color'      => $domain->header_color,
                    'header_text_color' => $domain->header_text_color,
                    'footer_text'       => $domain->footer_text,
                    'footer_color'      => $domain->footer_color,
                    'footer_text_color' => $domain->footer_text_color
                ));
                //if we had an error - exit the loop
                if(!$result){                   
                    break;
                } else {
                    $success = true;                    
                }
                //inserting domain admins
                if(!empty($domain->domain_admins)){
                    foreach ($domain->domain_admins as $domain_admin) {
                        $result_da = $this->db->insert('domain_admin_domains',array(
                            'da_id'     => $domain_admin,
                            'domain_id' => $domain->id
                        ));
                        if($result_da){
                            break;
                        } else {
                            $success = true;                            
                        }
                    }
                }
            }
            if($success){
                $this->response(array('type'=>'success'),200);
            } else {
                log_message('error','api/create_domains_put | success is false');
                $this->response(array('type'=>'error', 'message'=>'Something went wrong, please try again'),200);                
            }
        } else {
            log_message('error','api/create_domains_put | no json provided');
            $this->response(NULL, 400);             
        }
    }

    function create_views_put(){
        $json = $this->put('views');
        if(!empty($json)){
            //truncating the views table
            $this->db->truncate('views');
            $views = json_decode($json);
            $success = false;
            foreach($views as $view){
                //inserting view data
                $result = $this->db->insert('views',array(
                    'id'                => $view->id,
                    'device_id'         => $view->device_id,
                    'name'              => $view->name,
                    'body'              => urldecode($view->body)
                ));
                //if we had an error - exit the loop
                if(!$result){                   
                    break;
                } else {
                    $success = true;                    
                }
            }
            if($success){
                $this->response(array('type'=>'success'),200);
            } else {
                $this->response(array('type'=>'error', 'message'=>'Something went wrong, please try again'),200);                
            }
        } else {
            log_message('info','api/create_views_put | no json provided');
            $this->response(NULL, 400);             
        }
    }
}