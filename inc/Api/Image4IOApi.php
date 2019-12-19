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
        //$boundary = '--' . wp_generate_password( 24 ,false);
        $boundary = '--' . '123456789012345678901234';
        $headers  = array(
            'content-type' => 'application/x-www-form-urlencoded; boundary=' . $boundary
        );
        //$file=@fopen($path,"r");
        //$content=fread($file,filesize($path));

        $payload = '';
        //standard POST fields
        $payload .= $boundary;
        $payload .= "\r\n";
        $payload .= 'Content-Disposition: form-data; name="' . $name .
            '"' . "\r\n\r\n";
        $payload .= basename( $path );
        $payload .= "\r\n";

        // Upload the file
        if ( $path ) {
            $payload .= $boundary;
            $payload .= "\r\n";
            $payload .= 'Content-Disposition: form-data; name="' . $name . '"; filename="' . basename( $path ) . '"' . "\r\n";
            $payload .= 'Content-Type: image/jpeg' . "\r\n";
            $payload .= "\r\n";
            $payload .= file_get_contents( $path );
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
        $response=wp_remote_get( $this->urlWithVersion . 'get?name=' . urlencode($name) , $args );
        return $response['body'];
    }

    public function uploadImage($file,$queryPath){

        $filetype = wp_check_filetype($file);
        $headers = array(
			'Content-Type: multipart/form-data',
			'Authorization: Basic '. base64_encode($this->apiKey . ":" . $this->apiSecret)
		);
		$data['file'] = curl_file_create( $file , $filetype['type'], basename($file));
        $query = $this->query($this->urlWithVersion . 'upload?path='. $queryPath, 'POST', $data, $headers);
        
		return $query;  
    }

    public function fetch($from,$targetPath){

        $args= array(
            'headers'=>$this->getHeaders()
        );
        $response=wp_remote_post( $this->urlWithVersion . 'fetch?from=' . urlencode($from) . '&target_path=' . urlencode($targetPath) , $args );
        return $response['body'];
    }

    public function copyFile($source,$targetPath){
        //TODO
    }
    public function moveFile($source,$targetPath){
        //TODO
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

    public function deleteFile($name){
        $args= array(
            'headers'=>$this->getHeaders(),
            'method'=>'DELETE'
        );
        $response=wp_remote_request( $this->urlWithVersion . 'deletefile?name=' . urlencode($name), $args );
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

    private function query($uri, $method='GET', $data=null, $curl_headers=array(), $curl_options=array()) {
		$default_curl_options = array(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HEADER => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 3,
		);
		$default_headers = array();
		$method = trim($method);
		$allowed_methods = array('GET', 'POST', 'PUT', 'DELETE');

		if(!in_array($method, $allowed_methods))
			throw new \Exception("'$method' is not valid cURL HTTP method.");

		if(!empty($data) && !is_array($data))
			throw new \Exception("Invalid data for cURL request '$method $uri'");

		$curl = curl_init($uri);
		curl_setopt_array($curl, $default_curl_options);
		switch($method) {
			case 'GET':
				break;
			case 'POST':
				if(!is_array($data))
					throw new \Exception("Invalid data for cURL request '$method $uri'");
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				break;
			case 'PUT':
				if(!is_array($data))
					throw new \Exception("Invalid data for cURL request '$method $uri'");
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				break;
			case 'DELETE':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
				break;
        }
		curl_setopt_array($curl, $curl_options);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array_merge($default_headers, $curl_headers));
		$raw = rtrim(curl_exec($curl));
		$lines = explode("\r\n", $raw);
		$headers = array();
		$content = '';
		$write_content = false;
		if(count($lines) > 3) {
			foreach($lines as $h) {
				if($h == '')
					$write_content = true;
				else {
					if($write_content)
						$content .= $h."\n";
					else
						$headers[] = $h;
				}
			}
		}
		$error = curl_error($curl);
		curl_close($curl);
		return array(
			'raw' => $raw,
			'headers' => $headers,
			'content' => $content,
			'error' => $error
		);
    }
    
    public function test($file,$queryPath){
        $url = $this->urlWithVersion;
        // The file is stored on your system/host
        $path_to_uploaded_file = $file;
        $form_fields = [ 'path' => $queryPath ];
        if ( file_exists( $path_to_uploaded_file ) ) {
            $form_fields['image'] = curl_file_create( $path_to_uploaded_file );
        }
        /*
        Use an anonymous function and pass the local variable that we want to post to that function since 
        the http_api_curl hook does not pass the data that we actually want to POST with the hook
        If $form_fields is global variable, no need to pass it to the anonymous function using the 'use' keyword since 'use' is great for local scope variable
        */
        $file_upload_request = function( $handle_or_parameters, $request = '', $url = '' ) use ( $form_fields ) {
             $this->updateWPHTTPRequest( $handle_or_parameters, $form_fields );
        };
        // handle cURL requests if we have cURL installed
        add_action( 'http_api_curl', $file_upload_request, 10 );
        // handle fsockopen requests if we don't have cURL installed
        add_action( 'requests-fsockopen.before_send', $file_upload_request, 10, 3 );
        $request = wp_remote_post( $url, [
            'body' => $form_fields,
            'headers' => [
                'Authorization: Basic '. base64_encode($this->apiKey . ":" . $this->apiSecret),
                'content-type' => 'multipart/form-data', // set Content-type: multipart/form-data header for file upload
            ]
        ] );
        $body = wp_remote_retrieve_body( $request );
        $response_code = wp_remote_retrieve_response_code( $request );
        // Remove actions so we don't modify any other WP_Http requests
        remove_action( 'http_api_curl', $file_upload_request );
        remove_action( 'requests-fsockopen.before_send', $file_upload_request );
        // Let's upload another photo cause a possum had just attacked me in that photo. I want a cooler photo.
        
        return array(
            'body'=>$body,
            'code'=>$response_code
        );

    }

    private function updateWPHTTPRequest( &$handle_or_parameters, $form_body_arguments ) {
        if ( function_exists( 'curl_init' ) && function_exists( 'curl_exec' ) ) {
            foreach ( $form_body_arguments as $value ) {
                // Only do this if we are using PHP 5.5+ CURLFile file to upload a file
                if ( 'object' === gettype( $value ) && $value instanceof CURLFile ) {
                    /* 
                    Use the request body as an array to force cURL make a requests using 'multipart/form-data' 
                    as the Content-type header instead of WP's default habit of converting the request to 
                    a string using http_build_query function
                    */
                    curl_setopt( $handle_or_parameters, CURLOPT_POSTFIELDS, $form_body_arguments );
                    break;
                }
            }
        } elseif ( function_exists( 'fsockopen' ) ) {
            // UNTESTED SINCE I HAVE cURL INSTALLED AND CANNOT TEST THIS
            $form_fields = [];
            $form_files = [];
            foreach ( $form_body_arguments as $name => $value ) {
                if ( file_exists( $value ) ) {
                    // Not great for large files since it dumps into memory but works well for small files
                    $form_files[$name] = file_get_contents( $value );
                } else {
                    $form_fields[$name] = $value;
                }
            }
            
            $boundary = uniqid();
            $handle_or_parameters = $this->build_data_files( $boundary, $form_fields, $form_files );
        }
    }

    /**
     * Convert form fields arrays to a string that fsockopen requests can understand
     * 
     * @see https://gist.github.com/maxivak/18fcac476a2f4ea02e5f80b303811d5f
     */
    function build_data_files( $boundary, $fields, $files ){
        $data = '';
        $eol = "\r\n";
        $delimiter = '-------------' . $boundary;
        foreach ( $fields as $name => $content ) {
            $data .= "--" . $delimiter . $eol
                . 'Content-Disposition: form-data; name="' . $name . "\"".$eol.$eol
                . $content . $eol;
        }
        foreach ( $files as $name => $content ) {
            $data .= "--" . $delimiter . $eol
                . 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $name . '"' . $eol
                //. 'Content-Type: image/png'.$eol
                . 'Content-Transfer-Encoding: binary'.$eol
                ;
            $data .= $eol;
            $data .= $content . $eol;
        }
        $data .= "--" . $delimiter . "--".$eol;
        return $data;
    }
}

