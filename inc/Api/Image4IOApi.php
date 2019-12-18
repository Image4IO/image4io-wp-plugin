<?php
/**
 * @package image4ioPlugin
 */

namespace Inc\Api;

class Image4IOApi{

    private $apiKey;
    private $apiSecret;

    private $baseUrl="https://api.image4.io/";
    private $version="v0.1";
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

    private function createMultipartImagePayload($path,$name){
        //$path -> path to a local file on your server
        $boundary = '--' . wp_generate_password( 24 );
        $headers  = array(
            'content-type' => 'multipart/form-data; boundary=' . $boundary
        );
        $payload = '';
        // Upload the file
        if ( $path ) {
            $payload .= $boundary;
            $payload .= "\r\n";
            $payload .= 'Content-Disposition: form-data; name="' . $name . '"; filename="' . basename( $local_file ) . '"' . "\r\n";
            $payload .= 'Content-Type: image/jpeg' . "\r\n";
            $payload .= "\r\n";
            $payload .= file_get_contents( $local_file );
            $payload .= "\r\n";
        }
        $payload .= $boundary . '--';

        return array(
            'headers'    => $headers,
            'body'       => $payload,
        );
    }

    public function connect(){
        $args= array(
            'headers'=>$this->getHeaders()
        );
        $response=wp_remote_get( $this->urlWithVersion . 'listfolder', $args );
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
        $response=wp_remote_get( $this->urlWithVersion . 'get?name=' . $name , $args );
        return $response['body'];
    }

    public function uploadImage($filePath, $name, $queryPath){
        $payload=$this->createMultipartImagePayload($filePath,$name);
        $args=array(
            'headers'=>array_merge($payload['headers'],array(
                'Authorization'=>'Basic '. base64_encode($this->apiKey . ":" . $this->apiSecret)
            )),
            'body'=>$payload['body']
        );
        $response=wp_remote_post( $this->urlWithVersion . 'upload?path=' . $queryPath, $args );
    }

    public function fetch($from,$targetPath){
        
        $args= array(
            'headers'=>$this->getHeaders()
        );
        $response=wp_remote_post( $this->urlWithVersion . 'fetch?from=' . urldecode($from) . '&target_path=' . urldecode($targetPath) , $args );
        return $response['body'];
    }

    public function copyFile($source,$targetPath){

    }
    public function moveFile($source,$targetPath){

    }

    public function listFolder($path){
        $args= array(
            'headers'=>$this->getHeaders()
        );
        $response=wp_remote_get( $this->urlWithVersion . 'listfolder?path=' . $path , $args );
        return $response['body'];
    }

    public function createFolder($path,$name){
        $args= array(
            'headers'=>$this->getHeaders()
        );
        $response=wp_remote_post( $this->urlWithVersion . 'createfolder?path=' . $path . '%2F' . $name, $args );
        return $response['body'];
    }

    

    public function deleteFile($name){
        $args= array(
            'headers'=>$this->getHeaders(),
            'method'=>'DELETE'
        );
        $response=wp_remote_request( $this->urlWithVersion . 'deletefile?name=' . $name, $args );
        return $response['body'];
    }

    public function deleteFolder($path){
        $args= array(
            'headers'=>$this->getHeaders(),
            'method'=>'DELETE'
        );
        $response=wp_remote_request( $this->urlWithVersion . 'deletefolder?path=' . $path, $args );
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