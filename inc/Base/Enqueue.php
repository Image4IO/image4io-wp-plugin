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
		wp_enqueue_style( 'image4io_style', $this->plugin_url . 'assets/css/image4io.css' );
		wp_enqueue_style('materialize',$this->plugin_url . 'assets/css/card.css');
		$assetPath=array('staticImages'=>"$this->plugin_url" .'assets/img/' );
		//wp_enqueue_style( 'bootstrapjs', $this->plugin_url . 'assets/bootstrap/css/bootstrap.min.css');
		wp_enqueue_script( 'image4io_script', $this->plugin_url . 'assets/js/image4io.js' );
		//wp_enqueue_scripts('materialize-js',$this->plugin_url . 'assets/materialize/js/materialize.min.js');
		wp_localize_script( 'image4io_script', 'assetPath', $assetPath );
	}
}