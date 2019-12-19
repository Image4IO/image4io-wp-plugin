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

    public function __construct(){
        $this->apiClient=new Image4IOApi("AZk/jotDUNmCGSN63awlRA==","gLOgvaLMHU6y+Wu2H2c6WkLtZ3Yjke+50n5GH5M8pJE=");
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