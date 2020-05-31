<?php
/**
 * @package image4ioPlugin
 */

namespace Image4io\Api;

class Image4IOApi{

    private $apiKey;
    private $apiSecret;

    private $baseUrl="https://api.image4.io/";
    private $version="v1.0";
    private $urlWithVersion;

    public function __construct($_key,$_secret){
        $this->apiKey=$_key;
        $this->apiSecret=$_secret;
        $this->urlWithVersion=$this->baseUrl . $this->version . '/';
    }

    private function getHeaders(){
        return array(
            'Content-Type'=>'application/json',
            'Authorization'=>'Basic '. base64_encode($this->apiKey . ":" . $this->apiSecret)
        );
    }

    public function connect(){
        $args= array(
            'headers'=>$this->getHeaders(),
            'body'=>array('path'=>'/')
        );
        $response=wp_remote_get( $this->urlWithVersion . 'listFolder', $args );
        if($response['response']['code']==200){
            return true;
        }else{
            return false;
        }
    }

    public function get($name){
        $args= array(
            'headers'=>$this->getHeaders()
        );
        $response=wp_remote_get( $this->urlWithVersion . 'get?name=' . urlencode($name) , $args );
        return $response['body'];
    }

    public function fetch($from,$targetPath){

        $args= array(
            'headers'=>$this->getHeaders(),
            'body'=>json_encode(array(
                'from'=>$from,
                'targetPath'=>$targetPath,
                'useFilename'=>true
            )),
            'timeout'=>30
        );
        $response=wp_remote_post( $this->urlWithVersion . 'fetchImage' , $args );
        return $response['body'];
    }
    
    public function listFolder($path){
        $args= array(
            'headers'=>$this->getHeaders()
        );
        $response=wp_remote_get( $this->urlWithVersion . 'listfolder?path=' . urlencode($path) , $args );
        return $response['body'];
    }

    public function createFolder($path,$name){
        $args= array(
            'headers'=>$this->getHeaders()
        );
        $response=wp_remote_post( $this->urlWithVersion . 'createfolder?path=' . urlencode($path . '/' . $name), $args );
        return $response['body'];
    }

    public function deleteImage($name){
        $args= array(
            'headers'=>$this->getHeaders(),
            'method'=>'DELETE',
            'body'=>json_encode(array(
                'name'=>$name,
            ))
        );
        $response=wp_remote_request( $this->urlWithVersion . 'deleteImage', $args );
        return $response['body'];
    }

    public function deleteFolder($path){
        $args= array(
            'headers'=>$this->getHeaders(),
            'method'=>'DELETE'
        );
        $response=wp_remote_request( $this->urlWithVersion . 'deletefolder?path=' . urlencode($path), $args );
        return $response['body'];
    }

    public function getSubscription(){
        $args= array(
            'headers'=>$this->getHeaders()
        );
        $response=wp_remote_get( $this->urlWithVersion . 'subscription' , $args );
        return $response['body'];
    }

}

