<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller{
	function __construnct(){
		parent::__construnct();
	}

	function index(){
		$this->load->view('index_view');
	}
}


?>