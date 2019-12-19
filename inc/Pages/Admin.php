<?php
/**
 * @package image4ioPlugin
 */

 namespace Inc\Pages;

 use Inc\Api\SettingsApi;
 use Inc\Base\BaseController;
 use Inc\Api\Callbacks\AdminCallbacks;
 use Inc\Api\Image4IOManager;

 class Admin extends BaseController {

	public $settings;
	public $callbacks;
	public $pages = array();
    
     public function register(){
         $this->settings=SettingsApi::instance();
		 $this->callbacks=new AdminCallbacks();

		 /*$manager=new Image4IOManager();
		 include_once(ABSPATH . 'wp-includes/pluggable.php');
		 $res=$manager->test();
		 var_dump($res);
		 die;*/

         $this->setPages();
         
         $this->setSettings();
         $this->setSections();
         $this->setFields();

		 $this->settings->addPages($this->pages)->withSubPage('Dashboard');
     }
     
     public function setPages() 
	{
		$this->pages = array(
			array(
				'page_title' => 'Image4io Plugin', 
				'menu_title' => 'Image4io', 
				'capability' => 'manage_options', 
                'menu_slug' => 'image4io_plugin', 
				'callback' => array( $this->callbacks, 'adminDashboard' ), 
				'icon_url' => 'dashicons-store', 
				'position' => 120
			)
		);
	}

	public function setSettings()
	{
		$args = array(
			array(
				'option_group' => 'dashboard_options_group',
				'option_name' => 'api_key',
				'callback' => array( $this->callbacks, 'dashboardOptionGroup' )
			),
			array(
				'option_group' => 'dashboard_options_group',
				'option_name' => 'api_secret',
				'callback' => array( $this->callbacks, 'dashboardOptionGroup' )
			)
		);

		$this->settings->setSettings( $args );
	}

	public function setSections()
	{
		$args = array(
			array(
				'id' => 'image4io_admin_dashboard_index',
				'title' => 'Settings',
				'callback' => array( $this->callbacks, 'dashboardSettingsSection' ),
				'page' => 'image4io_plugin'
			)
		);

		$this->settings->setSections( $args );
	}

	public function setFields()
	{
		$args = array(
			array(
				'id' => 'api_key',
				'title' => 'Image4io Api Key',
				'callback' => array( $this->callbacks, 'image4ioApiKey' ),
				'page' => 'image4io_plugin',
				'section' => 'image4io_admin_dashboard_index',
				'args' => array(
					'label_for' => 'api_key'
				)
			),
			array(
				'id' => 'api_secret',
				'title' => 'Image4io Api Secret',
				'callback' => array( $this->callbacks, 'image4ioApiSecret' ),
				'page' => 'image4io_plugin',
				'section' => 'image4io_admin_dashboard_index',
				'args' => array(
					'label_for' => 'api_secret'
				)
			)
		);

		$this->settings->setFields( $args );
	}
 }