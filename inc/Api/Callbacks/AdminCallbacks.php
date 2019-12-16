<?php
/**
 * @package image4ioPlugin
 */

namespace Inc\Api\Callbacks;

use Inc\Base\BaseController;

class AdminCallbacks extends BaseController{
    public function adminDashboard(){
        return require_once("$this->plugin_path/templates/admin.php");
    }

    public function adminOptionGroup($input){
        return $input;
    }
    
    public function dashboardSettingsSection(){
        echo "Image4io - Speeds up the images' load time: optimization, delivery, storage; all-in-one platform";
    }

    public function image4ioApiKey(){
        $value=esc_attr(get_option('api_key'));
        echo '<input type="text" class="regular-text" name="api_key" value="' . $value . '" placeholder="Api Key">';
    }

    public function image4ioApiSecret(){
        $value=esc_attr(get_option('api_secret'));
        echo '<input type="password" class="regular-text" name="api_secret" value="' . $value . '" placeholder="Api Secret">';
    }


}