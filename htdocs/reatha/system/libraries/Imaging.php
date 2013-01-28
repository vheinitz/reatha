<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Imaging Class
 *
 * A port of the class.upload.php by Colin Verot to allow you to use
 * the functionality of the class through CI as a library. You can
 * watermark, resize, crop all in a very memory efficient manner.
 * 
 * Using this class instead of the CI image_lib class because it has
 * better file support for PNG or GIF especially when transparent.
 *
 */

// Include the main class
require_once(APPPATH.'libraries/class.upload.php');

// Extend it
class Imaging extends upload {

    function Imaging() { 
        log_message('debug', get_class($this).' Class Initialized');
    }
    
}
// END Imaging Class

/* End of file Imaging.php */
/* Location: ./system/application/libraries/Imaging.php */  