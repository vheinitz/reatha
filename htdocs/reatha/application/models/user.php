<?php
class User extends Datamapper{
    var $table = "users";
    var $has_many = array('device', 'notification_rule','notification',
        'domain' => array(
            'class'         => 'domain',
            'other_field'   => 'domain_admin',
            'join_self_as'  => 'da',
            'join_other_as' => 'domain',
            'join_table'    => 'domain_admin_domains'
            ));
    var $has_one = array('user_profile','device_list_view');
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

    function unassign_domain($domain_id){
        return $this->db->where('domain_id',$domain_id)->where('da_id',$this->id)->delete('domain_admin_domains');        
    }

    function assign_domain($domain_id){
        return $this->db->insert('domain_admin_domains', array('da_id' => $this->id, 'domain_id' => $domain_id));
    }    

    function is_admin_of($domain_id){
        return $this->db->where('da_id',$this->id)->where('domain_id',$domain_id)->count_all_results('domain_admin_domains');        
    }

    function send_domain_admin_email_notification($domain){
        $ci = & get_instance();
        $ci->load->config('tank_auth', TRUE);
        $data['user']       = $this;
        $data['domain']     = $domain;
        $data['site_name']  = $ci->config->item('website_name', 'tank_auth');
        $data['site_url']   = base_url();
        $ci->load->library('email');
        $ci->email->from($ci->config->item('webmaster_email', 'tank_auth'), $ci->config->item('website_name', 'tank_auth'));
        $ci->email->reply_to($ci->config->item('webmaster_email', 'tank_auth'), $ci->config->item('website_name', 'tank_auth'));
        $ci->email->to($this->email);
        $ci->email->subject('You are now a domain admin on Reatha.de');
        $ci->email->message($this->load->view('email/domain_admin_notification-html', $data, TRUE));
        $ci->email->send();        
    }

    function get_last_sent_notification_under_rule($rule_id){
        $query = $this->db->where('user_id',$this->id)->where('notification_rule_id',$rule_id)->order_by('id','desc')->get('notifications',1);
        $result = $query->result();
        if(isset($result[0])){
            return $result[0]->created;
        } else {
            return '0';
        }
    }

    function delete_user(){
		$this->user_profile->delete();
		return($this->delete());    	
    }    

}

?>