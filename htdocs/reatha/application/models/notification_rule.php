<?php
class Notification_rule extends Datamapper{
	var $table = "notification_rules";
	var $has_one = array('device','variable','notification');
	var $has_many = array('user');
	var $auto_populate_has_one = TRUE;
	var $auto_populate_has_many = TRUE;

	function __construct($id = NULL){
		parent::__construct($id);
	}

	function must_send_notification($var,$user){
		log_message('info','notification_rule/must_send_notification | entering function');
		//get last notification sent to user under this rule
		if($this->activated && $this->is_activated_for_user_id($user->id)){
			$last_sent = $user->get_last_sent_notification_under_rule($this->id);
			if((time() - $last_sent) >= $this->interval){
				//check if var value matches condition
				if(preg_match($this->condition,$var->value)){
					return TRUE;
				}
			}		
			return FALSE;
		} else {
			log_message('info','notification_rule/must_send_notification | rule not activated');
			return FALSE;
		}
	}

	function is_activated_for_user_id($user_id){
		return $this->db->where('user_id',$user_id)->where('notification_rule_id',$this->id)->where('activated','1')->count_all_results('notification_rules_users');		
	}

    function get_last_sent_notification($rule_id){
        $query = $this->db->where('user_id',$this->id)->where('notification_rule_id',$rule_id)->order_by('id','desc')->get('notifications',1);
        $result = $query->result();
        if(isset($result[0])){
            return $result[0]->created;
        } else {
            return '0';
        }
    }

    function get_severity_level(){
    	switch($this->severity_level){
    		case "1": return "Info"; break;
    		case "2": return "Warning"; break;
    		case "3": return "Error"; break;
    	}
    }	

}
?>