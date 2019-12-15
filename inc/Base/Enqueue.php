<?php
/**
 * @package image4ioPlugin
 */

namespace Inc\Base;

use Inc\Base\BaseController;


class Enqueue extends BaseController
{
	public function register() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}
	
	function enqueue($hook) {
		//TODO: bootstrap only to console page

		// enqueue all our scripts
		wp_enqueue_style( 'mypluginstyle', $this->plugin_url . 'assets/mystyle.css' );
		//wp_enqueue_style( 'bootstrapjs', $this->plugin_url . 'assets/bootstrap/css/bootstrap.min.css');
		wp_enqueue_script( 'mypluginscript', $this->plugin_url . 'assets/myscript.js' );
		//wp_enqueue_scripts('jquery',$this->plugin_url . 'assets/jquery/jquery-3.4.1.min.js');
	}
}