<?php
class Device extends Datamapper{
	var $table = "devices";
	var $has_many = array('user','variable','notification_rule','view');
	var $has_one = array('domain');
    var $auto_populate_has_one = TRUE;
    var $auto_populate_has_many = TRUE;	
    var $default_order_by = array('id'=>'desc');
    var $local_time = FALSE;
    var $unix_timestamp = TRUE;    

    function __construct($id = NULL){
    	parent::__construct($id);
    }

    //save variables to device. variables = string with comma separated values
    function add_variables($device_variables){
        $return = array('type'=>'','message'=>'');
        $device_variables = explode(",", $device_variables);
        $device_variables = array_map("trim",$device_variables);

        foreach ($device_variables as $device_variable) {
            if($this->valid_variable_name($device_variable, $device_variables)){
                $var = new Variable();
                $var->name = trim($device_variable);
                $var->save($this);
            } else {
                log_message('info',"device/add_variables | var $device_variable is not valid");                
                $return['type'] = 'error';
                $return['message'] .= "Duplicate variable name found: $device_variable. Variable will not be saved.<br/>";
            }
        }
        if($return['type'] != 'error'){
            $return = array('type'=>'success','message'=>'Variables saved.');
        }

        return $return;
    }

    function update_variables($key,$value){
        //check if variable has a view
        $var = $this->variables->where('name',$key)->get();
        if($var->view->exists()){
            $value = $this->_process_view_vars($var,$value);
        }

        $this->variable->where('name',$key)->update('value',$value);
        return $this->db->affected_rows();
    }

    function update_invalid_data($key,$value){
        $invalid_data = "$key = $value";
        $this->where('id',$this->id)->update('invalid_data',$invalid_data);                
    }

    function update_life_check(){
        $this->where('id',$this->id)->update('life_check',1);        
    }

    function update_name($name){
        return $this->where('id',$this->id)->update('name',$name);
    }

    function update_description($description){
        return $this->where('id',$this->id)->update('description',$description);
    }

    function generate_key(){
        // Hash device key using phpass
        require_once('application/libraries/phpass-0.1/PasswordHash.php');
        $hasher     = new PasswordHash(6,FALSE);
        $device_key = $hasher->HashPassword($this->name);
        return ($this->where('id',$this->id)->update('key',$device_key));
    }    

    function delete_device(){
    	return $this->delete();
    }

    function valid_variable_name($var, $haystack){
        if(!$this->db->where('device_id',$this->id)->where('name',$var)->count_all_results('variables')){
            return true;
       /*     $values_count = array_count_values($haystack);            
            if($values_count[$var] > 1){
                log_message('info',"device/valid_variable_name | var $var is dupl in arra");                
            }*/
        }
        return false;
    }

    function _process_view_vars($var,$value){
        $view = $var->view->body;
        return str_replace('{'.$var->name.'}', $value, $view);
    }

}

?>