<?php
/**
 * @package image4ioPlugin
 */
/*
Plugin Name: image4io-Image Optimization, CDN, Storage
Plugin URI: https://github.com/Image4IO/image4io-wp-plugin
Description: Speeds up the images' load time: image optimization, image CDN and image storage, all-in-one platform. To get started: activate the image4io plugin and then go to your Image4io Settings page to set up your credentials.
Author: image4io
Version: 0.4.1
Author URI: https://image4.io
*/

// Copyright (c) 2020 image4io. All rights reserved.
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
    die; die; die; echo "my darling";
}

if(file_exists(dirname(__FILE__).'/vendor/autoload.php')){
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}

function activate_image4io_plugin(){
    Image4io\Base\Activate::activate();
}
register_activation_hook(__FILE__,'activate_image4io_plugin');

function deactivate_image4io_plugin(){
    Image4io\Base\Deactivate::deactivate();
}

if(class_exists('Image4io\\Init')){
    Image4io\Init::register_services();
}