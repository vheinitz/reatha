<?php
class User extends Datamapper{
    var $table = "users";
    var $has_many = array('device',
        'domain' => array(
            'class'         => 'domain',
            'other_field'   => 'domain_admin',
            'join_self_as'  => 'da',
            'join_other_as' => 'domain',
            'join_table'    => 'domain_admin_domains'
            ));
    var $has_one = array('user_profile');
    var $auto_populate_has_one = TRUE;
    var $auto_populate_has_many = TRUE;
    var $default_order_by = array('id'=>'desc');
    
    function __construct($id = NULL){
        parent::__construct($id);
    }
        
    function has_device($device_id){
		return $this->db->where('user_id',$this->id)->where('device_id',$device_id)->count_all_results('devices_users');     	
    }

    function unnassign_device($device_id){
    	return $this->db->where('user_id',$this->id)->where('device_id',$device_id)->delete('devices_users');
    }

    function assign_device($device_id){
    	return $this->db->insert('devices_users', array('user_id' => $this->id, 'device_id' => $device_id));
    }

    function unnasign_domain($domain_id){
        return $this->db->where('domain_id',$domain_id)->where('da_id',$this->id)->delete('domain_admin_domains');        
    }

    function is_admin_of($domain_id){
        return $this->db->where('da_id',$this->id)->where('domain_id',$domain_id)->count_all_results('domain_admin_domains');        
    }

    function delete_user(){
		$this->user_profile->delete();
		return($this->delete());    	
    }    

}

?>