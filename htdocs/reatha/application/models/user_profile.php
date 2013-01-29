<?php
class User_profile extends Datamapper{
    var $table = "User_profiles";
    var $has_one = array('user');
    var $auto_populate_has_one = TRUE;
    var $auto_populate_has_many = TRUE;
    
    function __construct($id = NULL){
        parent::__construct($id);
    }
         
}

?>