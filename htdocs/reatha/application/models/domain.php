<?php
class Domain extends Datamapper{
	var $table = "domains";
	var $has_many = array('device');
    var $auto_populate_has_one = TRUE;
    var $auto_populate_has_many = TRUE;	
    var $default_order_by = array('id'=>'desc');

    function __construct($id = NULL){
    	parent::__construct($id);
    }

    function delete_domain(){
    	$users = new User();
    	
    	$this->devices->delete_all();
    	$users->where('belongs_to_domain_id',$this->id)->get();
    	foreach ($users as $user) {
    		$user->delete_user();
    	}
    	$users->where('id',$this->da_id)->get()->delete_user();
    	return $this->delete();

    }	
}

?>