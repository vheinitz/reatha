<?php 
class View extends Datamapper{
	var $table = "views";
	var $has_one = array('device');
	var $auto_populate_has_one = TRUE;

	function __construct($id = NULL){
		parent::__construct($id);
	}

    function process_placeholders(){
        if(empty($text)) $text = $this->body;

        //process references to reserved views
        $text = $this->_process_reserved_view_references($text);

        //process references to other views
        $text = $this->_process_view_references($text);

        //process {files}
        $text = $this->_process_files($text);

        //process reserved vars
        $text = $this->_process_reserved_vars($text);

        //process vars
        $text = $this->_process_vars($text);

        return $text;
    }

    /**  process references to reserved views */
    function _process_reserved_view_references($text){

        //{view:_deviceList} - returns to device list
        $text = str_replace('{view:_deviceList}', base_url().'u/', $text);

        //{view:_notifications} - goes to device notification list.
        $text = str_replace('{view:_notifications}', base_url().'u/notifications/'.$this->device->id, $text);

        //{view:_deviceView} - goes to device view
        $text = str_replace('{view:_deviceView}', base_url().'u/device/'.$this->device->id, $text);           

        return $text;
    }    

    /**  process references to other views */
    function _process_view_references($text){
        preg_match_all("/\{(view:[A-Za-z0-9]+)\}/U", $text, $views);
        $views = $views[1];
        if(!empty($views)){
            foreach($views as $view_name){
                $view_name = explode(":", $view_name);
                $view_name = $view_name[1];
                if($this->device->has_view($view_name)){
                    $view = $this->device->views->where('name',$view_name)->get(1);
                    $text = str_replace('{view:'.$view_name.'}',base_url()."u/device/".$view->device->id."/".$view->name, $text);
                }
            }
        }
        return $text;
    }

    /**  process references to {:files} */
    function _process_files($text){
        $text = str_replace('{:files}', base_url().'assets/'.$this->device->domain->name, $text);
        return $text;
    }

    /**  process references to reserved vars */
    function _process_reserved_vars($text){

        //{_deviceId} - id of current device
        $text = str_replace('{_deviceId}', $this->device->id, $text);

        //{_deviceName} - name of current device.
        $text = str_replace('{_deviceName}', $this->device->name, $text); 

        //{_deviceInfo} - description of current device.
        $text = str_replace('{_deviceInfo}', $this->device->description, $text);

        //{_deviceLocation} - location of current device.
        $text = str_replace('{_deviceLocation}', $this->device->location, $text);

        //{_deviceOn} - 1 if device online, 0 otherwise
        $time = time();
        $power_status = ($time - $this->device->updated) > 10 ? $power = '0' : $power = '1';
        $text = str_replace('{_deviceOn}', $power_status, $text);

        //{_alarmLevel} - if one of the device's notification rules has triggered a notification which has not been reset yet then display "1", otherwise - "0"
        if(strpos($text, '{_alarmLevel}')!== FALSE){
            $level = '0';
            foreach($this->device->notification_rules as $rule){
                if(!empty($rule->notification->created)){
                    //device has at least one triggered notification
                    $level = '1';
                    break;
                }
            }
            $text = str_replace('{_alarmLevel}', $level, $text);
        }

        //{_setVariable} - used as form action. This form will update a variable's value in db
        $text = str_replace('{_setVariables}', base_url().'u/set_variables', $text);

        return $text;
    }

    /**  process references to vars */
    function _process_vars($text){
        preg_match_all("/\{(.+)\}/U", $text, $var_names);
        $var_names = $var_names[1];
        foreach($var_names as $var_name){
            $var = $this->device->variables->where('name',$var_name)->get();
            $text = str_replace('{'.$var_name.'}', $var->value, $text);
        }
        return $text;        
    }	
}