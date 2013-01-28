<?php
class Domain extends Datamapper{
	var $table = "domains";
    var $default_order_by = array('id'=>'desc');

    function __construct($id = NULL){
    	parent::__construct($id);
    }	
}

?>