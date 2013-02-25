<?php 
class View extends Datamapper{
	var $table = "views";
	var $has_one = array('device');
	var $auto_populate_has_one = TRUE;

	function __construct($id = NULL){
		parent::__construct($id);
	}

    function process_vars(){
        $text = $this->body;

        //process references to other views
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

        //process vars
        preg_match_all("/\{(.+)\}/U", $text, $var_names);
        $var_names = $var_names[1];
        foreach($var_names as $var_name){
            $var = $this->device->variables->where('name',$var_name)->get();
            $text = str_replace('{'.$var_name.'}', $var->value, $text);
        }
        return $text;
    }	
}