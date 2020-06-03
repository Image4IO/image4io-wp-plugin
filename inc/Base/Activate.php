<?php
/**
 * @package image4ioPlugin
 */

namespace Image4io\Base;

use Image4io\Base\Functions;

class Activate{
    public static function activate(){
        flush_rewrite_rules();

        if ( Functions::get_image4io_option( 'image4io_settings' ) ) {
			return;
		}

		$default = array();

		Functions::update_image4io_option( 'image4io_settings', $default );
    }
}