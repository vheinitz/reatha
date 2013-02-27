<?php
class Variable extends Datamapper{
	var $table = "variables";
	var $has_one = array('device','notification_rule');
	var $has_many = array('transformation');
    var $auto_populate_has_one = TRUE;
    var $auto_populate_has_many = TRUE;
    var $default_order_by = array('id'=>'desc');

    function __construct($id = NULL){
    	parent::__construct($id);
    }

    function has_transformations(){
    	return $this->db->where('variable_id',$this->id)->count_all_results('transformations');
    }

}

?>