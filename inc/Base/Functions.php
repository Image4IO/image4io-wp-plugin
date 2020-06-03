<?php
/**
 * @package image4ioPlugin
 */

namespace Image4io\Base;

final class Functions {
    public static function get_image4io_option($option_name) {
        
        if(THIS_PLUGIN_NETWORK_ACTIVATED== true) {
            
            return get_site_option($option_name);
        }
        else {
            return get_option($option_name);
        }
    }
    public static function update_image4io_option($option_name, $option_value) {

        if(THIS_PLUGIN_NETWORK_ACTIVATED== true) {
            return update_site_option($option_name, $option_value);
        }
        else {
            return update_option($option_name, $option_value);
        }
    }
}