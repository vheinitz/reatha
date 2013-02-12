<?php
class Notification extends Datamapper{
	var $table = "notifications";
	var $has_one = array('user','notification_rule');
	var $auto_populate_has_one = TRUE;
    var $local_time = FALSE;
    var $unix_timestamp = TRUE;   

	function __construct($id = NULL){
		parent::__construct($id);
	}

}
?>