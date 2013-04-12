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

    function list_domains_get(){
        $domains = new Domain();
        $domains->get();
        $return = array();
        foreach($domains as $domain){
            $return[] = array(
                'id'            => $domain->id,
                'name'          => $domain->name,
                'description'   => $domain->description
            );
        }
        $this->response($return, 200);            
    }

    function list_users_get(){
        if($this->get('domain_id')){  
            $domain = new Domain($this->get('domain_id'));
            if($domain->exists()){
                $return = array();                
                $users = new User();
                $users->where('belongs_to_domain_id',$domain->id)->get();
                //traversing the users array
                foreach ($users as $user) {
                    $return[] = array(
                        'id'        => $user->id,
                        'domain_id' => $user->belongs_to_domain_id,
                        'password'  => $user->password,
                        'email'     => $user->email,
                        'role'      => $user->role
                    );
                }
                $this->response($return, 200);
            } 
        } else {
            log_message('info','api/list_users_get | no id provided');
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
                    $return[] = array(
                        'id'                    => $device->id,
                        'domain_id'             => $device->domain_id,
                        'cloned_from_device_id' => $device->cloned_from_device_id,
                        'name'                  => $device->name,
                        'description'           => $device->description,
                        'location'              => $device->location,
                        'key'                   => $device->key
                    );
                }
                $this->response($return, 200);
            } 
        } else {
            log_message('info','api/list_devices_get | no id provided');
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
                        'body'                  => $view->body
                    );
                }
                $this->response($return, 200);
            } 
        } else {
            log_message('info','api/list_views_get | no id provided');
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
                        'activated'             => $rule->activated
                    );
                }
                $this->response($return, 200);
            } 
        } else {
            log_message('info','api/list_notification_rules_get | no id provided');
            $this->response(NULL, 400); 
        }         
    }                    
}