<?php
/**
 * @package image4ioPlugin
 */
/*
Plugin Name: image4io-plugin
Plugin URI: https://github.com/image4io/image4io-plugin
Description: Automatically uploads and optimizes images. Boosts site performance and simplifies workflows.
Author: Gokhan Kocyigit
Version: 0.0.1
Author URI: http://www.image4.io
*/

// Copyright (c) 2019 image4io. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************
if(!defined('ABSPATH')){
    die;
}

if(file_exists(dirname(__FILE__).'/vendor/autoload.php')){
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}

define( 'PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_URL', plugin_dir_url( __FILE__ ) );

function activate_image4io_plugin(){
    Inc\Base\Activate::activate();
}
register_activation_hook(__FILE__,'activate_image4io_plugin');

function deactivate_image4io_plugin(){
    Inc\Base\Deactivate::deactivate();
}

if(class_exists('Inc\\Init')){
    Inc\Init::register_services();
}