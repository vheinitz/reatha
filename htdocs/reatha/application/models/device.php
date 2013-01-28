<?php
class Device extends Datamapper{
	var $table = "devices";
	var $has_many = array('user');
    var $default_order_by = array('id'=>'desc');

    function __construct($id = NULL){
    	parent::__construct($id);
    }	
}

?>