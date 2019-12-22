<?php
/**
 * @package image4ioPlugin
 */

namespace Inc\Base;

class Activate{
    public static function activate(){
        flush_rewrite_rules();

        if ( get_option( 'image4io_settings' ) ) {
			return;
		}

		$default = array();

		update_option( 'image4io_settings', $default );
    }
}