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
}


?>