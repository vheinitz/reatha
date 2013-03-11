<?php
class Device extends Datamapper{
	var $table = "devices";
	var $has_many = array('user','variable','notification_rule','view','transformation');
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
        log_message('info','device/update_variables | entered function'); 
        $var = $this->variable->where('name',$key)->get();
        if($var->where('id',$var->id)->update('value',$value)){
            //check if var has transformations
            if($var->has_transformations()){
                log_message('info','device/update_variables | var has transformations');
                //loop through each transformation
                foreach ($var->transformations as $t) {
                    //replace {var} placeholders with actual values
                    $body = $this->_process_transformation_vars($t->body);
                    log_message('info',"device/update_variables | body: $body");

                    //load evalmath library and evaluate the body
                    require_once('application/libraries/evalmath.class.php');
                    $e = new EvalMath();
                    $evaluated_body = $e->evaluate($body);                    
                    // $evaluated_body = eval('return '.$body.';');
                    
                    log_message('info',"device/update_variables |eval  body: $evaluated_body");

                    //save transformation value in db
                    $export_var = new Variable($t->export_var_id);
                    $export_var->where('id',$export_var->id)->update('value',$evaluated_body);
                }
            } else {
                log_message('info','device/update_variables | var has no transformations');
            }
        }

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
        $this->variables->delete_all();
        $this->views->delete_all();
    	return $this->delete();
    }

    function valid_variable_name($var, $haystack=''){
        //check if var is not a reserved var
        $reserved_vars = array('_deviceName','_deviceInfo','_deviceLocation','_deviceOn','_alarmLevel');
        if(in_array($var, $reserved_vars)){
            return false;
        }

        if(!$this->db->where('device_id',$this->id)->where('name',$var)->count_all_results('variables')){
            return true;
        }
        return false;
    }

    function valid_view_name($name){
        if(!$this->db->where('device_id',$this->id)->where('name',$name)->count_all_results('views')){
            return true;
        }
        return false;        
    }

    function has_main_view(){
        return $this->db->where('device_id',$this->id)->where('name','main')->count_all_results('views');            
    }

    function has_view($view_name){
        return $this->db->where('device_id',$this->id)->where('name',$view_name)->count_all_results('views');        
    }

    function has_view_variable(){
        return $this->db->where('device_id',$this->id)->where('name','view')->count_all_results('variables');        
    }

    function get_view_name(){
        $view = $this->variables->where('name','view')->get(1);
        return $view->value;
    }

    function _process_transformation_vars($text){
        preg_match_all("/\{(.+)\}/U", $text, $var_names);
        $var_names = $var_names[1];
        foreach($var_names as $var_name){
            $var = $this->variables->where('name',$var_name)->get();
            if(empty($var->value)) $var->value = 0;
            $text = str_replace('{'.$var_name.'}', $var->value, $text);
        }
        return $text;
    }    

}

?>