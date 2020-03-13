<?php
/**
 * @package image4ioPlugin
 */

namespace Image4io\Base;

use Image4io\Base\BaseController;


class Enqueue extends BaseController
{
	public function register() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}
	
	function enqueue($hook) {
		// enqueue all our scripts
		//wp_enqueue_style( 'image4io_plugin_style', $this->plugin_url . 'assets/css/image4io.css' );
		//wp_enqueue_style('image4io_plugin_card',$this->plugin_url . 'assets/css/card.css');
		//$assetPath=array('image4io_static_images'=>"$this->plugin_url" .'assets/img/' );
		//wp_enqueue_script( 'image4io_script', $this->plugin_url . 'assets/js/image4io.js' );
		//wp_localize_script( 'image4io_script', 'assetPath', $assetPath );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style('thickbox');
	}
}