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
        		log_message('info','api/device_key_het | user_id: '.$user->id);
        		if($user->has_device($device->id)){
        			$this->response(array("key"=>$device->key), 200);
        		}
        	} 
        } else {
        	log_message('info','api/device_key_het | no id provided');
            $this->response(NULL, 400); 
        } 
	}
}