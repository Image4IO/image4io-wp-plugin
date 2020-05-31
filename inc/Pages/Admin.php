<?php
/**
 * @package image4ioPlugin
 */

 namespace Image4io\Pages;

 use Image4io\Api\SettingsApi;
 use Image4io\Base\BaseController;
 use Image4io\Api\Callbacks\AdminCallbacks;

 class Admin extends BaseController {

	public $settings;
	public $callbacks;
	public $pages = array();
    
     public function register(){
         $this->settings=SettingsApi::instance();
		 $this->callbacks=new AdminCallbacks();

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
				'icon_url' => 'data:image/svg+xml;base64,' . base64_encode('<svg version="1.0" xmlns="http://www.w3.org/2000/svg" width="150" height="150" viewBox="0 0 150 150"><g transform="translate(0,150) scale(0.1,-0.1)" fill="#000000" stroke="none"><path d="M0 1340 l0 -160 25 0 25 0 0 140 0 140 135 0 c128 0 135 1 135 20 0 19 -7 20 -160 20 l-160 0 0 -160z"/><path d="M1345 1425 c-266 -72 -506 -239 -667 -465 -43 -61 -118 -195 -118 -212 0 -11 179 -188 191 -188 17 0 89 40 176 96 159 103 313 272 401 441 46 88 112 275 112 317 0 30 -16 32 -95 11z m-335 -370 c92 -48 67 -178 -37 -192 -32 -4 -45 0 -71 22 -57 48 -52 127 10 165 40 24 59 25 98 5z"/><path d="M926 964 c-10 -25 -7 -33 15 -45 27 -14 69 3 69 28 -1 17 -1 17 -11 1 -14 -25 -27 -22 -41 7 -13 29 -23 32 -32 9z"/> <path d="M585 945 c-5 -2 -50 -15 -100 -30 -84 -25 -245 -98 -290 -131 -19 -14 -18 -16 30 -63 l50 -49 95 30 c52 17 100 32 107 34 7 2 25 31 41 66 16 35 41 82 55 106 15 23 26 42 25 41 -2 0 -7 -2 -13 -4z"/><path d="M489 654 c-40 -43 -155 -129 -219 -166 -25 -14 -82 -40 -127 -59 -60 -24 -83 -38 -83 -51 0 -16 6 -15 68 8 137 52 299 153 385 242 31 32 35 41 24 50 -11 9 -21 4 -48 -24z"/><path d="M326 351 c-214 -215 -265 -270 -254 -279 11 -9 68 43 282 257 214 215 265 270 254 279 -11 9 -68 -43 -282 -257z"/> <path d="M840 538 l-95 -50 -37 -106 -36 -107 46 -47 c25 -27 49 -48 52 -48 11 0 87 141 114 210 33 81 67 200 58 199 -4 0 -50 -23 -102 -51z"/><path d="M619 503 c-100 -107 -182 -239 -233 -375 -23 -63 -24 -68 -7 -68 13 0 24 15 36 48 50 132 129 262 220 360 44 48 52 62 41 71 -10 9 -23 1 -57 -36z"/><path d="M1460 186 l0 -133 -31 -7 c-17 -3 -80 -6 -140 -6 -102 0 -109 -1 -109 -20 0 -19 7 -20 160 -20 l160 0 0 160 c0 153 -1 160 -20 160 -19 0 -20 -7 -20 -134z"/> </g></svg>'), 
				'position' => 120
			)
		);
	}

	public function setSettings()
	{
		$args = array(
			array(
				'option_group' => 'dashboard_options_group',
				'option_name' => 'image4io_settings',
				'callback' => array( $this->callbacks, 'adminOptionGroup' )
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
					'option_name'=>'image4io_settings',
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
					'option_name'=>'image4io_settings',
					'label_for' => 'api_secret'
				)
			),
			array(
				'id' => 'cloudname',
				'title' => 'Image4io Cloudname',
				'callback' => array( $this->callbacks, 'image4ioCloudname' ),
				'page' => 'image4io_plugin',
				'section' => 'image4io_admin_dashboard_index',
				'args' => array(
					'option_name'=>'image4io_settings',
					'label_for' => 'cloudname'
				)
			),
			array(
				'id' => 'target_folder',
				'title' => '<div data-tooltip-location="right" data-tooltip="Images will be uploaded to a target folder path e.g. /myWebsite ">Target Folder<span class="dashicons dashicons-info"></span></div>',
				'callback' => array( $this->callbacks, 'image4ioTargetFolder' ),
				'page' => 'image4io_plugin',
				'section' => 'image4io_admin_dashboard_index',
				'args' => array(
					'option_name'=>'image4io_settings',
					'label_for' => 'target_folder'
				)
			),
			array(
				'id' => 'auto_upload',
				'title' => '<div data-tooltip-location="right" data-tooltip="If checked, all new images uploaded to the Media Library will be uploaded to image4io Storage">Auto Upload to Image4io<span class="dashicons dashicons-info"></span></div>',
				'callback' => array( $this->callbacks, 'image4ioAutoUpload' ),
				'page' => 'image4io_plugin',
				'section' => 'image4io_admin_dashboard_index',
				'args' => array(
					'option_name'=>'image4io_settings',
					'label_for' => 'auto_upload'
				)
			)
		);

		$this->settings->setFields( $args );
	}
 }