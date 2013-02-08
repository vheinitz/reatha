<?php
class Device extends Datamapper{
	var $table = "devices";
	var $has_many = array('user','variable');
	var $has_one = array('domain');
    var $auto_populate_has_one = TRUE;
    var $auto_populate_has_many = TRUE;	
    var $default_order_by = array('id'=>'desc');

    function __construct($id = NULL){
    	parent::__construct($id);
    }

    //save variables to device. variables = string with comma separated values
    function add_variables($device_variables){
        $device_variables = explode(",", $device_variables);
        foreach ($device_variables as $device_variable) {
            $var = new Variable();
            $var->name = trim($device_variable);
            $var->save($this);
        }
    }

    function update_variables($key,$value){
        $this->variable->where('device_id',$this->id)->where('name',$key)->update('value',$value);
        return $this->db->affected_rows();
    }

    function update_invalid_data($key,$value){
        $invalid_data = "$key = $value";
        $this->where('id',$this->id)->update('invalid_data',$invalid_data);                
    }

    function update_life_check(){
        $this->where('id',$this->id)->update('life_check',1);        
    }

    function delete_device(){
    	return $this->delete();
    }	
}

?>