<?php

require_once('rb.php');
R::setup('mysql:host=localhost;dbname=remon','remon','remon'); 
//R::freeze(true);

$item = R::dispense('users');
//$item = R::load('users',2);
$item->name = 'hugo';
$item->passwd = 'abc';
$item->email = 'aaa@bbb.cc';
R::store($item);

$items = R::find("users","name='hugo'");

foreach ( $items as $item)
echo $item->name."<br>";


?>
