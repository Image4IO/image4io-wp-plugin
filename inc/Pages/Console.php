<?php
/**
 * @package image4ioPlugin
 */

namespace Inc\Pages;

use ImageIO;
use Inc\Base\BaseController;
use Inc\Api\SettingsApi;
use Inc\Api\Callbacks\AdminCallbacks;


class Console extends BaseController{
    public $settings;
    public $callbacks;
    public $subpages=array();

    public function register(){
        $this->settings=SettingsApi::instance();
        $this->callbacks=new AdminCallbacks();

        $this->setSubpages();
        $this->settings->addSubPages($this->subpages);
    }

    public function setSubpages()
	{
		$this->subpages = array(
			array(
				'parent_slug' => 'image4io_plugin', 
				'page_title' => 'Image4io Console', 
				'menu_title' => 'Console', 
				'capability' => 'manage_options', 
				'menu_slug' => 'image4io_console', 
				'callback' => array( $this->callbacks, 'adminConsole' )
			)
        );
    }
    
    
}