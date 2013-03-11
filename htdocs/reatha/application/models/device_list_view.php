<?php 
class Device_list_view extends Datamapper{
	var $table = "device_list_views";
	var $has_one = array('user');
	var $auto_populate_has_one = TRUE;

	function __construct($id = NULL){
		parent::__construct($id);
	}

    function process_placeholders($device){
        $text = $this->body;
        $this->device = $device;

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


        return $text;
    }

    /**  process references to reserved views */
    function _process_reserved_view_references($text){

        //{view:_devicelist} - returns to device list
        $text = str_replace('{view:_devicelist}', base_url().'u/', $text);

        //{view:_notifications} - goes to device notification list.
        $text = str_replace('{view:_notifications}', base_url().'u/notifications/'.$this->device->id, $text);

        return $text;
    }    


    /**  process references to {:files} */
    function _process_files($text){
        $text = str_replace('{:files}', base_url().'assets/'.$this->device->domain->name, $text);
        return $text;
    }

}