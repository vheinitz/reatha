<?php
class Device extends Datamapper{
	var $table = "devices";
	var $has_many = array('user');
	var $has_one = array('domain');
    var $auto_populate_has_one = TRUE;
    var $auto_populate_has_many = TRUE;	
    var $default_order_by = array('id'=>'desc');

    function __construct($id = NULL){
    	parent::__construct($id);
    }

    function delete_device(){
    	return $this->delete();
    }	
}

?>