<?php
/**
 * @package image4ioPlugin
 */

namespace Inc\Api;

class Image4IOApi{

    private $apiKey;
    private $apiSecret;

    private $base_url="https://api.image4.io/";
    private $version="v0.1";

    public function __construct($_key,$_secret){
        $this->apiKey=$_key;
        $this->apiSecret=$_secret;
    }

    public function connect(){

    }

    public function listFolder($path){

    }

    public function get($name){

    }

    public function createFolder($path,$name){

    }

    public function uploadImage($file,$folder){

    }

    public function delete($name){

    }

    public function deleteFolder($path){

    }

    public function getSubscription(){

    }

    //copy, move
}