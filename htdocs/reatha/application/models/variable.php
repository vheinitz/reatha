<?php
class Variable extends Datamapper{
	var $table = "variables";
	var $has_one = array('device','notification_rule');
    var $auto_populate_has_one = TRUE;
    var $default_order_by = array('id'=>'desc');

    function __construct($id = NULL){
    	parent::__construct($id);
    }

}

?>