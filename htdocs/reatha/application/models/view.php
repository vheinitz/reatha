<?php 
class View extends Datamapper{
	var $table = "views";
	var $has_one = array('variable','device');
	var $auto_populate_has_one = TRUE;

	function __construct($id = NULL){
		parent::__construct($id);
	}
}