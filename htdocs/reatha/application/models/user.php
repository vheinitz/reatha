<?php
class User extends Datamapper{
    var $table = "users";
    var $has_many = array('device');
    var $auto_populate_has_one = TRUE;
    var $auto_populate_has_many = TRUE;
    var $default_order_by = array('id'=>'desc');
    
    function __construct($id = NULL){
        parent::__construct($id);
    }
        


}

?>