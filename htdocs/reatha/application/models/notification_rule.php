<?php
class Notification_rule extends Datamapper{
	var $table = "notification_rules";
	var $has_one = array('user','device','variable');
	var $auto_populate_has_one = TRUE;

	function __construct($id = NULL){
		parent::__construct($id);
	}

}
?>