<?php
/**
 * @package image4ioPlugin
 */

namespace Image4io\Api;

use Image4io\Api\Image4IOApi;
use Image4io\Base\BaseController;

class Image4IOManager{

    public $apiClient;
    public $images;
    public $folders;

    public function setup(){
        if(!$this->isValidOptions()){
            return "Please set API Key, API Secret and Cloudname from image4io Console";
        }
        $values=get_option( "image4io_settings" );
        $this->apiClient=new Image4IOApi($values["api_key"],$values["api_secret"]);
    }

    public function isValidOptions(){
        $values=get_option( "image4io_settings" );
        return (isset($values)&&isset($values["api_key"])&&isset($values["api_secret"])&&isset($values["cloudname"]));
    }


    public function validateCredentialsWithOptions(){
        if(!$this->isValidOptions()){
            return "Please set API Key, API Secret and Cloudname from image4io Console";
        }
        $values=get_option( "image4io_settings" );
        $res=$this->apiClient->getSubscription();
        $response=json_decode($res);
        if($response->cloudname!=$values['cloudname']){
            return "Cloudname is not valid. Please check your image4io cloudname.";
        }
    }
    public function validateCredentials($key,$secret,$cloudname){
        if((empty($key)||empty($secret)||empty($cloudname))){
            return "Please set API Key, API Secret and Cloudname from image4io Console.";
        }
        $client=new Image4IOApi($key,$secret);
        $canClientConnect=$client->connect();
        if(!$canClientConnect){
            return "API Key or API Secret is invalid. Cannot establish any connection.";
        }
        $res=$client->getSubscription();
        $response=json_decode($res);
        if($response->cloudname!=$cloudname){
            return "Cloudname is not valid. Please check your image4io cloudname.";
        }
    }
/*
    public function uploadToImage4IO($dirpath , $cloudPath){
        if(!$this->isValidOptions()){
            return; //redirect to options page?
        }
        if(!file_exists($dirpath)){
            return "File does not exists";
        }
        
        $result=$this->apiClient->uploadImage($dirpath,$cloudPath);
        return json_decode($result);
    }*/

    public function uploadToImage4ioFromUrl($from_url,$target_path){
        if(!$this->isValidOptions()){
            return; //redirect to options page?
        }
        $result=$this->apiClient->fetch($from_url,$target_path);
        return json_decode( $result );
    }
    
    public function getImagesByFolder($folder){
        
        if(!$this->isValidOptions()){
            return; //redirect to options page?
        }
        $result=$this->apiClient->listFolder($folder);
        //return json_decode($result,true)["files"];
        return $result;
    }

    public function getFolders($root){
        if($this->isValidOptions()){
            return; //redirect to options page?
        }
        $result=$this->apiClient->listFolder($root);
        return json_decode($result);
    }

    public function deleteImage($name){
        if(!$this->isValidOptions()){
            return; //redirect to options page?
        }
        $result=$this->apiClient->deleteImage($name);
        return json_decode( $result );
    }
}