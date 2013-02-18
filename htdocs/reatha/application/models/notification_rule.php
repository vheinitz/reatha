<?php
class Notification_rule extends Datamapper{
	var $table = "notification_rules";
	var $has_one = array('user','device','variable');
	var $auto_populate_has_one = TRUE;

	function __construct($id = NULL){
		parent::__construct($id);
	}

	function must_send_notification($var){
		log_message('info','notification_rule/must_send_notification | entering function');
		//get last notification sent to user under this rule
		if($this->activated){
			$last_sent = $this->user->get_last_sent_notification_under_rule($this->id);
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

}
?>