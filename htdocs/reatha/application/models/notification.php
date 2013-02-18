<?php
class Notification extends Datamapper{
	var $table = "notifications";
	var $has_one = array('user','notification_rule');
	var $auto_populate_has_one = TRUE;
    var $local_time = FALSE;
    var $unix_timestamp = TRUE;   

	function __construct($id = NULL){
		parent::__construct($id);
	}

	// save notification and email it. Params: rule - notification rule, var - variable that triggered the notification
	function save_and_email($rule, $var){
		log_message('info',"notification/save_and_email | entering fucntion");
		$this->body = $this->_process_message_vars($rule,'body');
		$this->subject = $this->_process_message_vars($rule,'subject');
		if($this->save(array($rule, $rule->user))){
			$this->_send_email();
		}
	}

	function _process_message_vars($rule, $type){
		//check whether to process notification body or notification subject
		if($type == 'body'){
			$text = $rule->message;
		} else {
			$text = $rule->subject;
		}
		preg_match_all("/\{(.+)\}/U", $text, $var_names);
		$var_names = $var_names[1];
		$device = $rule->device->get();
		foreach($var_names as $var_name){
			$var = $device->variables->where('name',$var_name)->get();
			$text = str_replace('{'.$var_name.'}', $var->value, $text);
		}
		return $text;
	}

	function _send_email(){
		$ci = &get_instance();
		$ci->lang->load('notifications');
		$ci->load->library('email');

		$message = sprintf($ci->lang->line('email_notification'), $this->user->username, $this->body);

		$ci->email->from('noreply@reatha.de','Reatha');
		$ci->email->to($this->user->email);

		$ci->email->subject($this->subject);
		$ci->email->message($message);

		$ci->email->send();		
	}

}
?>