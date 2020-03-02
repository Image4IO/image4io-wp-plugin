<?php
/**
 * @package image4ioPlugin
 */

namespace Image4io\Base;

use Image4io\Base\BaseController;

class SettingsLink extends BaseController {
    public function register(){
        add_filter("plugin_action_links_$this->plugin",array($this,'settings_link'));
    }

    public function settings_link($links){
        $settings_link='<a href=admin.php?page=image4io_plugin>Settings</a>';
        array_push($links,$settings_link);
        return $links;
    }
}

