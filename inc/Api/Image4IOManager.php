<?php
/**
 * @package image4ioPlugin
 */

namespace Inc\Api;

use Inc\Models\Folder;
use Inc\Models\Image;
use Inc\Api\Image4IOApi;

class Image4IOManager{

    public $apiClient;
    public $images;
    public $folders;

    public function __construct(){
        $this->apiClient=new Image4IOApi("AZk/jotDUNmCGSN63awlRA==","gLOgvaLMHU6y+Wu2H2c6WkLtZ3Yjke+50n5GH5M8pJE=");
    }

    public function getFolders(){
        return $this->apiClient->listFolder("/");
    }




    //init the image4io api
    //get folders and images for given path
    //crud operations on images metadata in db
    
}