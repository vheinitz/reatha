<?php 
class Image extends Datamapper{
	var $table = "images";
	var $has_one = array('domain');
	var $auto_populate_has_one = true;

	function __construnct($id = null){
		parent:__construnct($id);
	}

	function process_image($file,$domain_name){
		$ci = &get_instance();
        $ci->load->library('imaging');        
        $ci->imaging->upload($file);
        if ($ci->imaging->uploaded){
            log_message('info','image/process_image | upload validated, image width: '.$ci->imaging->image_src_x);
            $ci->imaging->allowed = 'image/*'; 	 	
            $ci->imaging->file_max_size = '4194304';           
            
            $ci->imaging->process('assets/'.$domain_name);
            if($ci->imaging->processed){
                return array('type'=>'success', 'body'=>$ci->imaging->file_dst_name);
            } else {
                log_message('error','image/process_image | could not process image: '.$ci->imaging->error);
                return array('type'=>'error', 'body'=>$ci->imaging->error);  
            }
        } else {
            log_message('error','image/process_image | could not upload image: '.$ci->imaging->error);
            return array ('type'=>'error', 'body'=>$ci->imaging->error);            
        }
	}

}
?>