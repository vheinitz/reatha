<?php 
class Device_list_view extends Datamapper{
	var $table = "device_list_views";
	var $has_one = array('device');
	var $auto_populate_has_one = TRUE;

	function __construct($id = NULL){
		parent::__construct($id);
	}

    function process_placeholders(){
        $text = $this->body;

        //process references to reserved views
        $text = $this->_process_reserved_view_references($text);

        //process {files}
        $text = $this->_process_files($text);

        //process reserved vars
        $text = $this->_process_reserved_vars($text);

        return $text;
    }	

    /**  process references to reserved vars */
    function _process_reserved_vars($text){

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


    /**  process references to {:files} */
    function _process_files($text){
        // log_message('info','Device_list_view/_process_files | device id: '.$this->device->id);
        $text = str_replace('{:files}', base_url().'assets/'.$this->device->domain->name, $text);
        return $text;
    }

}