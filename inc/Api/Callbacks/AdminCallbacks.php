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
        echo "<h4>To get to use image4io plugin, you need to fill out and save API Key, API Secret and Cloudname of your Image4io Account. <br/>
        You can find out your account informations from <a href='https://console.image4.io/'>here</a>. <br/>
        If you don't have image4io account, you can <a href='https://console.image4.io/Auth/SignUp'>sign up</a> for free.</h4>";
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

    public function image4ioTargetFolder($args){
        $name=$args['label_for'];
        $optionName=$args['option_name'];
        $options=get_option($optionName);
        $value=isset($options[$name])?$options[$name]:"";

        echo "<input type='text' id='$name' class='regular-text' name='" . $optionName . "[" . $name . "]' value='$value' placeholder='/'>";
    }

    public function image4ioAutoUpload($args){
        $name=$args['label_for'];
        $optionName=$args['option_name'];
        $options=get_option($optionName);
        $value=isset($options[$name])?$options[$name]:0;

        echo "<input type='checkbox' id='$name' class='regular-text' name='" . $optionName . "[" . $name . "]' value='1' ". checked( 1, $value, false )  ." >";
    }

}