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

    public function image4ioApiKey($args){
        $name=$args['label_for'];
        $optionName=$args['option_name'];
        $options=get_option($optionName);
        $value=isset($options[$name])?$options[$name]:"";

        echo "<input type='text' id='$name' class='regular-text' name='" . $optionName . "[" . $name . "]' value='$value' placeholder='Api Key'>";
    }

    public function image4ioApiSecret($args){
        $name=$args['label_for'];
        $optionName=$args['option_name'];
        $options=get_option($optionName);
        $value=isset($options[$name])?$options[$name]:"";
        
        echo "<input type='password' id='$name' class='regular-text' name='" . $optionName . "[" . $name . "]' value='$value' placeholder='Api Secret'>";
    }

    public function image4ioCloudname($args){
        $name=$args['label_for'];
        $optionName=$args['option_name'];
        $options=get_option($optionName);
        $value=isset($options[$name])?$options[$name]:"";

        echo "<input type='text' id='$name' class='regular-text' name='" . $optionName . "[" . $name . "]' value='$value' placeholder='Cloudname'>";
    }
    public function image4ioFolder($args){
        $name=$args['label_for'];
        $optionName=$args['option_name'];
        $options=get_option($optionName);
        $value=isset($options[$name])?$options[$name]:"";

        echo "<input type='text' id='$name' class='regular-text' name='" . $optionName . "[" . $name . "]' value='$value' placeholder='Folder'>";
    }

}