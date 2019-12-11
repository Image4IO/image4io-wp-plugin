<?php
/*
	Image4.io Api
*/
	class Image4IO {
		public $api;
		public $apiKey;
		
		public function __construct($_api,$_apiKey) {
			$this->api = $_api;
			$this->apiKey = $_apiKey;
		}
		
		public function query($uri, $method='GET', $data=null, $curl_headers=array(), $curl_options=array()) {
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
		
		public function connect() {
			$headers = array(
				'Content-Type: application/json',
				'Authorization: Basic '. base64_encode($this->api.":".$this->apiKey)
			);
			$query = $this->query('https://api.image4.io/v0.1/listfolder','GET','',$headers);
			return $query;
		}
		
		public function listfolder($path='') {
			$headers = array(
				'Content-Type: application/json',
				'Authorization: Basic '. base64_encode($this->api.":".$this->apiKey)
			);
			$query = $this->query('https://api.image4.io/v0.1/listfolder?path='.$path,'GET','',$headers);
			return $query;
		}

		public function get($name='') {
			$headers = array(
				'Content-Type: application/json',
				'Authorization: Basic '. base64_encode($this->api.":".$this->apiKey)
			);
			$query = $this->query('https://api.image4.io/v0.1/get?name='.$name,'GET','',$headers);
			return $query;
		}
		
		public function createfolder($path='') {
			$headers = array(
				'Content-Type: application/json',
				'Authorization: Basic '. base64_encode($this->api.":".$this->apiKey)
			);
			$query = $this->query('https://api.image4.io/v0.1/createfolder?path='.$path,'POST',array('path' => $path),$headers);
			return $query;
		}
		
		public function upload($files='',$folder='') {
			$headers = array(
				'Content-Type: multipart/form-data',
				'Authorization: Basic '. base64_encode($this->api.":".$this->apiKey)
			);
			$data['file'] = new CurlFile( $files['tmp_name'], $files['type'],$files['name']);
			$query = $this->query('https://api.image4.io/v0.1/upload?path='.$folder,'POST',$data,$headers);
			return $query;
		}
		
			public function fetch($from='',$target='') {
			$headers = array(
				'Content-Type: application/json',
				'Authorization: Basic '. base64_encode($this->api.":".$this->apiKey)
			);
			$query = $this->query('https://api.image4.io/v0.1/fetch?from='.$from.'&target_path='.$target,'POST',array('from' => $from),$headers);
			return $query;
		}

		public function delete($name='') {
			$headers = array(
				'Content-Type: application/json',
				'Authorization: Basic '. base64_encode($this->api.":".$this->apiKey)
			);
			$query = $this->query('https://api.image4.io/v0.1/deletefile?name='.$name,'DELETE','',$headers);
			return json_decode($query);
		}

		public function deletefolder($path='') {
			$headers = array(
				'Content-Type: application/json',
				'Authorization: Basic '. base64_encode($this->api.":".$this->apiKey)
			);
			$query = $this->query('https://api.image4.io/v0.1/deletefolder?path='.$path,'DELETE','',$headers);
			return $query;
		}

		public function copys($source='',$target='') {
			$headers = array(
				'Content-Type: application/json',
				'Authorization: Basic '. base64_encode($this->api.":".$this->apiKey)
			);
			$query = $this->query('https://api.image4.io/v0.1/copy?source='.$source.'&target_path='.$target,'PUT','',$headers);
			return $query;
		}
		
		public function move($source='',$target='') {
			$headers = array(
				'Content-Type: application/json',
				'Authorization: Basic '. base64_encode($this->api.":".$this->apiKey)
			);
			$query = $this->query('https://api.image4.io/v0.1/move?source='.$source.'&target_path='.$target,'PUT','',$headers);
			return $query;
		}

		
		public function getSubscription() {
			$headers = array(
				'Content-Type: application/json',
				'Authorization: Basic '. base64_encode($this->api.":".$this->apiKey)
			);
			$query = $this->query('https://api.image4.io/v0.1/subscription','GET','',$headers);
			return $query;
		}
		
	}