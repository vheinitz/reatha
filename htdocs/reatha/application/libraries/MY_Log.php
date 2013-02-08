<?php
class MY_Log extends CI_Log{
    var $_levels	= array('ERROR' => '1', 'INFO' => '2',  'DEBUG' => '3', 'ALL' => '4');
    public function __construct()
    {
        parent::__construct();
    }
}
?>