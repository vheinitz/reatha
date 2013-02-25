<?php
class Test extends CI_Controller{
	function __construct(){
		parent::__construct();
	}


	function domain_admins(){
		$d = new Domain(3);
		foreach($d->domain_admins as $domain_admin){
			echo $domain_admin->id."<br/>";
		}
	}

	function add_domain_admin(){
		$d = new Domain(3);
		$user = new User(1);
		$d->save_domain_admin($user);
	}

	function current_url(){
		echo $_SERVER["SERVER_NAME"];
	}

	function da_email(){
        $data['user']   = new User(16);
        $data['domain'] = new Domain(7);
        $data['site_name'] = $this->config->item('website_name', 'tank_auth');
        $data['site_url'] = base_url();
        $this->load->view('email/domain_admin_notification-html', $data);
	}

	function welcome_subj_line(){
		$type = 'welcome';
		$this->load->config('tank_auth', TRUE);
		echo $this->config->item('website_name', 'tank_auth');
		sprintf($this->lang->line('auth_subject_'.$type), $this->config->item('website_name', 'tank_auth'));
	}

	function save_new_var(){
		$var = new Variable();
		$device = new Device(6);
		$var->name = 'temperature';
		$var->save($device);
	}

	function submit_values(){
		$data['user'] = new User($this->tank_auth->get_user_id());
		$this->load->view('submit_values_view',$data);
	}

	function update_life_check(){
		$device = new Device(1);
		$device->where('id',$device->id)->update('life_check',1);
	}

	function post_data(){
		$data = $this->input->post();
		log_message('error','test/post_data | '.var_export($data,true));
	}

	function date_time(){
		$device = new Device(1);
		$date = new DateTime($device->updated);
		echo $date->format('Y-m-d H:i:sP');
	}

	function unixtime(){
		echo time();
	}

	function last_notification(){
		$rule = new Notification_rule(1);
		$last_sent = $rule->user->get_last_sent_notification_under_rule($rule->id);
		// echo round((time() - $last_sent)/60);
		if (round((time() - $last_sent)/60) >= $rule->interval) {
			echo "bigger";
		} else {
			"lower";
		}
	}

	function condition(){
		echo preg_match('/^[0-9]$/','10');
	}

	function mail_send(){
		$to = "contact@youtopica.com";
		$subject = "Test mail";
		$message = "Hello! This is a simple email message.";
		$from = "noreply@reatha.de";
		$headers = "From:" . $from;
		mail($to,$subject,$message,$headers);
		echo "Mail Sent.";		
	}

	function mail_send_2(){
		$this->load->library('email');
		$this->email->to('contact@youtopica.com');
		$this->email->from('noreply@reatha.de','Reatha');
		$this->email->subject('test mail');
		$this->email->message('<html><head></head><body>test <b>message</b></body></html>');
		$this->email->send();
		echo $this->email->print_debugger();
	}

	function find_vars(){
		$text = "status is {status} and temperature is {temperature}";
		preg_match_all("/\{(.+)\}/U", $text, $result);
		$vars = $result[1];
		$device = new Device(1);		
		foreach($vars as $var_name){
			$var = $device->variables->where('name',$var_name)->get();
			$text = str_replace('{'.$var_name.'}', $var->value, $text);
		}

		echo $text;
	}

	function find_views(){
		$text = "status is {view:test} and temperature is {view:temperature}";
		preg_match_all("/\{(view:[A-Za-z0-9]+)\}/U", $text, $result);
		$views = $result[1];	
		$device = new Device(1);	
		foreach($views as $view_name){
        	$view_name = explode(":", $view_name);
        	$view_name = $view_name[1];
        	log_message('info','test/find_views | view: '.$view_name);
        	if($device->has_view($view_name)){
        		log_message('info','test/find_views | device has view: '.$view_name);
        		$view = $device->views->where('name',$view_name)->get(1);
        		$text = str_replace('{view:'.$view_name.'}', "<a href='$view->id'>$view_name</a>", $text);
        	}
		}

		echo $text;
	}	

	function run_query(){
		$this->db->query("CREATE TABLE IF NOT EXISTS `views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL,
  `variable_id` int(11) NOT NULL,
  `body` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6");
	}


}


?>