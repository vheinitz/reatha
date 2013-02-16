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
		$message = str_replace('{'.$var->name.'}', $var->value, $rule->message);
		$this->body = $message;
		if($this->save(array($rule, $rule->user))){
			$this->_send_email();
		}
	}

	function _process_message_vars(){
		$body = $this->body;
		preg_match_all("/\{(.+)\}/U", $body, $result);
		$vars = $result[1];
		foreach($vars as $var){
			//TODO: check if such var exists and the current device has this var
			//$body = str_replace('{'.$var.'}', $var->value, $rule->message);
		}
	}

	function _send_email(){
		$ci = &get_instance();
		$ci->lang->load('notifications');
		$ci->load->library('email');

		$message = sprintf($ci->lang->line('email_notification'), $ci->user->username, $this->body);

		$ci->email->from('noreply@reatha.de','Reatha');
		$ci->email->to($this->user->email);

		$ci->email->subject('New Notification');
		$ci->email->message($message);

		$ci->email->send();		
		
/*		$to = $this->user->email;
		$subject = "New Notification";
		$message = $this->body;
		$from = "noreply@reatha.de";
		$headers = "From:" . $from;
		mail($to,$subject,$message,$headers);*/		
	}

}
?>