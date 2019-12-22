<?php
/**
 * @package image4ioPlugin
 */

namespace Inc\Api;

use Inc\Models\Folder;
use Inc\Models\Image;
use Inc\Api\Image4IOApi;
use Inc\Base\BaseController;

class Image4IOManager extends BaseController{

    public $apiClient;
    public $images;
    public $folders;

    public function setup(){
        if(!$this->isValidOptions()){
            return; //redirect to options page?
        }
        $values=get_option( "image4io_settings" );
        $this->apiClient=new Image4IOApi($values["api_key"],$values["api_secret"]);
    }

    public function isValidOptions(){
        $values=get_option( "image4io_settings" );
        return (isset($values)&&isset($values["api_key"])&&isset($values["api_secret"])&&isset($values["cloudname"]));
    }


    public function validateCredentials(){
        if(!$this->isValidOptions()){
            return; //redirect to options page?
        }
        return $this->apiClient->connect();
    }

    public function uploadToImage4IO($dirpath , $cloudPath){
        if(!$this->isValidOptions()){
            return; //redirect to options page?
        }
        if(!file_exists($dirpath)){
            return "File does not exists";
        }
        
        $result=$this->apiClient->uploadImage($dirpath,$cloudPath);
        return json_decode($result);
    }
    
    public function getImagesByFolder($folder){
        
        if(!$this->isValidOptions()){
            return; //redirect to options page?
        }
        $result=$this->apiClient->listFolder($folder);
        return json_decode($result,true)["files"];
    }

    public function getFolders($root){
        if($this->isValidOptions()){
            return; //redirect to options page?
        }
        $result=$this->apiClient->listFolder($root);
        return json_decode($result);
    }


    
    

    public function test(){
        //$baseDir=wp_upload_dir();
        //return $this->apiClient->uploadImage( $baseDir['path'] ."/image.png"  , "a3");
        //return $this->apiClient->connect();
        //return $this->apiClient->get("/a3/62731b18-a071-475f-8198-a703679e13d3.jpg");
        //return $this->apiClient->fetch("https://images.pexels.com/photos/414612/pexels-photo-414612.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=650&w=940","a3");
        //return $this->apiClient->listFolder("a3");
        //return $this->apiClient->createFolder("a3","a4");
        //return $this->apiClient->deleteFile("/a3/62731b18-a071-475f-8198-a703679e13d3.jpg");
        //return $this->apiClient->deleteFolder("a3/a4");
        //return $this->apiClient->getSubscription();
    }
    
}