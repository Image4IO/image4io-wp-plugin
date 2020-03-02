<?php
/**
 * @package image4ioPlugin
 */

namespace Image4io\Api\Callbacks;

use Image4io\Base\BaseController;

class AdminCallbacks extends BaseController{
    public function adminDashboard(){
        return require_once("$this->plugin_path/templates/admin.php");
    }

    public function adminOptionGroup($input){
        return $input;   
    }
    
    public function dashboardSettingsSection(){
        echo "If you want to use image4io plugin, you should fill out these informations from image4io console.";
    }

    public function image4ioApiKey($args){
        $name=$args['label_for'];
        $optionName=$args['option_name'];
        $options=get_option($optionName);
        $value=isset($options[$name])?$options[$name]:"";

        echo "<input type='text' id='$name' class='regular-text' name='" . $optionName . "[" . $name . "]' value='$value' placeholder='Your API Key'>";
    }

    public function image4ioApiSecret($args){
        $name=$args['label_for'];
        $optionName=$args['option_name'];
        $options=get_option($optionName);
        $value=isset($options[$name])?$options[$name]:"";
        
        echo "<input type='password' id='$name' class='regular-text' name='" . $optionName . "[" . $name . "]' value='$value' placeholder='Your API Secret'>";
    }

    public function image4ioCloudname($args){
        $name=$args['label_for'];
        $optionName=$args['option_name'];
        $options=get_option($optionName);
        $value=isset($options[$name])?$options[$name]:"";

        echo "<input type='text' id='$name' class='regular-text' name='" . $optionName . "[" . $name . "]' value='$value' placeholder='Your cloudname'>";
    }
    public function image4ioFolder($args){
        $name=$args['label_for'];
        $optionName=$args['option_name'];
        $options=get_option($optionName);
        $value=isset($options[$name])?$options[$name]:"";

        echo "<input type='text' id='$name' class='regular-text' name='" . $optionName . "[" . $name . "]' value='$value' placeholder='Which folder to show? (Root is /)'>";
    }

}