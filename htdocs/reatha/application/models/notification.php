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
	function save_and_email($rule, $var, $user){
		log_message('info',"notification/save_and_email | entering fucntion");
		$this->body = $this->_process_message_vars($rule,'body');
		$this->subject = $this->_process_message_vars($rule,'subject');
		if($this->save(array($rule, $user))){
			$this->_send_email($user);
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

        //{_deviceId} - id of current device
        $text = str_replace('{_deviceId}', $device->id, $text);

        //{_deviceName} - name of current device.
        $text = str_replace('{_deviceName}', $device->name, $text); 

        //{_deviceInfo} - description of current device.
        $text = str_replace('{_deviceInfo}', $device->description, $text);

        //{_deviceLocation} - location of current device.
        $text = str_replace('{_deviceLocation}', $device->location, $text);

        //{_deviceOn} - 1 if device online, 0 otherwise
        $time = time();
        $power_status = ($time - $device->updated) > 10 ? $power = '0' : $power = '1';
        $text = str_replace('{_deviceOn}', $power_status, $text);

        //{_alarmLevel} - if one of the device's notification rules has triggered a notification which has not been reset yet then display "1", otherwise - "0"
        if(strpos($text, '{_alarmLevel}')!== FALSE){
            $level = '0';
            foreach($device->notification_rules as $rule){
                if(!empty($rule->notification->created)){
                    //device has at least one triggered notification
                    $level = '1';
                    break;
                }
            }
            $text = str_replace('{_alarmLevel}', $level, $text);
        }	
	        		
		foreach($var_names as $var_name){
			$var = $device->variables->where('name',$var_name)->get();		
			$text = str_replace('{'.$var_name.'}', $var->value, $text);
		}
		return $text;
	}

	function _send_email($user){
		$ci = &get_instance();
		$ci->lang->load('notifications');
		$ci->load->library('email');

		$message = sprintf($ci->lang->line('email_notification'), $user->username, $this->body);

		$ci->email->from('noreply@reatha.de','Reatha');
		$ci->email->to($user->email);

		$ci->email->subject($this->subject);
		$ci->email->message($message);

		$ci->email->send();		
	}

}
?>