<?php
	class common extends database {
		public $return = [];
		public $countryList = [];
		public $selectedCountry;
		public $super = false;
		public $clientId;
		public $token_id;
		public $admin_id;

		public $message;
		public $error_message;
		public $successResponse = array("success" => true, "results" => "OK");
		public $notFound = array("success" => false, "error" => array(  "code" => 404, "message" => "Not Found"));
		public $NotModified = array("success" => false, "error" => array(  "code" => 304, "message" => "Not Modified"));
		public $Unauthorized = array("success" => false, "error" => array(  "code" => 401, "message" => "Unauthorized"));
		public $NotAcceptable = array("success" => false, "error" => array(  "code" => 406, "message" => "Not Acceptable"));
		public $RequiredSettingsNotFound = array("success" => false, "error" => array(  "code" => 404, "message" => "Required Settings not Configured"));
		public $BadReques = array("success" => false, "error" => array(  "code" => 400, "message" => "Bad Reques"));
		public $internalServerError = array("success" => false, "error" => array( "code" => 500, "message" => "Internal Server Error"));

		function readFile($key) {
			return $this->getOneField("accessTokens", $key, "tokenKey", "token");
		}
		
		function saveFile($key, $data) {
			return $this->replace("accessTokens", array("tokenKey"=>$key,"token"=>$data), array("token"));
		}
		
		function curlPost($url, $fields) {
			//extract data from the post
			extract($_POST);
			foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
			rtrim($fields_string,'&');
			
			//open connection
			$ch = curl_init();
			
			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_POST,count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			
			
			//execute post
			$result = curl_exec($ch);
			//close connection
			curl_close($ch);
			return $result;
		}
		
		function curl_file_get_contents($url) {
			if(strstr($url, "https") == 0) {
				return $this->curl_file_get_contents_https($url);
			}
			else {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
				$data = curl_exec($ch);
				// echo $err = curl_error($ch);
				curl_close($ch);
				return $data;
			}
		}
		
		private function curl_file_get_contents_https($url) {
			$res = curl_init();
			curl_setopt($res, CURLOPT_URL, $url);
			curl_setopt($res,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($res, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($res, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($res, CURLOPT_CONNECTTIMEOUT, 30);
			$out = curl_exec($res);
			// echo curl_error($res);
			curl_close($res);
			return $out;
		}
				
		function get_prep($value) {
			$value = urldecode(htmlentities(strip_tags($value)));
			
			return $value;
		}
		
		function get_prep2(&$item) {
			//$item = htmlentities($item);
			return $item;
		}
		
		function out_prep($array) {
			if (is_array($array)) {
				if (count($array) > 0) {
					array_walk_recursive($array, array($this, 'get_prep2'));
				}
			}
			return $array;
		}
		
		function createRandomPassword($len = 7) { 
			$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890"; 
			srand((double)microtime()*1000000); 
			$i = 0; 
			$pass = '' ; 
			$count = strlen($chars);
			while ($i <= $len) { 
				$num = rand() % $count; 
				$tmp = substr($chars, $num, 1); 
				$pass = $pass . $tmp; 
				$i++; 
			} 
			return $pass; 
		}
		
		function send_mail($from,$to,$subject,$body,$name=true) {
			global $mailUsername;
			global $mailPassword;
			global $servername;
			/*$headers = '';
			$headers .= "From: $from\r\n";
			$headers .= "Reply-to: ".replyMail."\r\n";
			$headers .= "Return-Path: ".replyMail."\r\n";
			$headers .= "Organization: SkrinAd\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= 'Content-Type: text/html; charset=utf-8' . "\r\n";
			$headers .= "Date: " . date('r', time()) . "\r\n";
		
			if (mail($to,$subject,$body,$headers)) {
				return true;
			} else {
				return false;
			}
			
			*/

			if ($servername == "localhost") {
				return true;
			}
			
			$from_data = explode("<", trim($from, ">"));
			$to_data = explode("<", trim($to, ">"));
			$to_email = $to_data[1];
			$to_name = $to_data[0];
			$mail = new PHPMailer();
			$mail->IsSMTP();
			//$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
			$mail->SMTPAuth = true; // authentication enabled
			$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
			$mail->Host = "email-smtp.us-west-2.amazonaws.com";
			//$mail->Host = "mail.skrinad.com";
			$mail->Port = 465; // or 587
			
			$mail->Username = $mailUsername;  // SMTP username
			$mail->Password = $mailPassword; // SMTP password
			//$mail->Username = "do-not-reply@skrinad.com";  // SMTP username
			//$mail->Password = "P@%%W)RD"; // SMTP password
			
			$mail->From = $from_data[1];
			$mail->FromName = $from_data[0];
			$mail->AddAddress($to_email,$to_name);                  // name is optional
			$mail->AddReplyTo($from_data[1], $from_data[0]);  
			
			$mail->WordWrap = 50;                                 // set word wrap to 50 characters
			$mail->IsHTML(true);                                  // set email format to HTML
			
			$mail->Subject = $subject;
			$mail->Body    = $body;
			$mail->AltBody = "This is email is readable only in an HTML enabled browser or reader";
			
			if(!$mail->Send()) {
				error_log("could not send");
				echo "Mailer Error: " . $mail->ErrorInfo;
				return false;
			} else {
				error_log("send");
				//echo "Mailer Error: " . $mail->ErrorInfo;
				return true;
			}
		}
		
		function get_time_stamp($post_time) {
			if (($post_time == "") || ($post_time <1)) {
				return false;
			} else {
				$difference = time() - $post_time;
				$periods = array("sec", "min", "hour", "day", "week",
				"month", "years", "decade","century","millenium");
				$lengths = array("60","60","24","7","4.35","12","10","100","1000");
				
				if ($difference >= 0) { // this was in the past
					$ending = "ago";
				} else { // this was in the future
					$difference = -$difference;
					$ending = "time";
				}
				
				for($j = 0; $difference >= $lengths[$j]; $j++)
				$difference = $difference/$lengths[$j];
				$difference = round($difference);
				
				if($difference != 1) $periods[$j].= "s";
				$text = "$difference $periods[$j] $ending";
				return $text;
			}
		}
		
		function getExtension($str) {
			$i = strrpos($str,".");
			if (!$i) { return ""; } 
			$l = strlen($str) - $i;
			$ext = substr($str,$i+1,$l);
			return $ext;
		}
		
		function get_tiny_url($url)  {  
			$ch = curl_init();  
			$timeout = 5;  
			curl_setopt($ch,CURLOPT_URL,'http://tinyurl.com/api-create.php?url='.$url);  
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);  
			
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$data = curl_exec($ch);  
			curl_close($ch);  
			return $data;  
		}
		
		function hashPass($string) {
			$count = strlen($string);
			$start = $count/2;
			$list = "";
			for ($i = 0; $i < $start; $i++) {
				$list .= "*";
			}
			$hasPass = substr_replace($string, $list, $start);
			
			return $hasPass;
		}
		
		function initials($string) {
			$string = trim($string);
			$words = explode(" ", $string);
			$words = array_filter($words);
			$letters = "";
			foreach ($words as $value) {
				$letters .= strtoupper(substr($value, 0, 1)).". ";
			}
			$letters = trim(trim($letters), ".");
			
			return $letters;
		}
		
		function numberPrintFormat($value) {
			if ($value > 999 && $value <= 999999) {
				$result = round(($value / 1000), 2) . ' K';
			} elseif ($value > 999999 && $value < 999999999) {
				$result = round(($value / 1000000), 2) . ' M';
			} elseif ($value > 999999999) {
				$result = round(($value / 1000000000), 2) . ' B';
			} else {
				$result = $value;
			}
			
			return $result;
		}
		
		function bankList() {
			$sql = $this->query("SELECT * FROM `bank` ORDER BY `title` ASC", false, "list");
			
			if ($sql) {
				$result = array();
				$count = 0;
				foreach($sql as $row) {
					$result[$count]['id'] = $row['sort_code'];
					$result[$count]['title'] = ucfirst(strtolower($row['title']));
					$count++;
				}
				return $result;
			}
		}
		
		function listOneBank($ref, $tag='title') {
			return $this->getOne("bank", $ref, $tag);
		}
		
		function getOneFieldBank($id, $tag='title', $ref='sort_code') {
			$data = $this->listOneBank($id, $tag);
			return $data[$ref];
		}

		private function clientDash() {
			global $advert;
			global $survey;
			global $advert_stat;
			$result['advert']['value'] = $advert->clientListAll($this->clientId, '', false, false, "count");
			$result['advert']['label'] = $this->numberPrintFormat($result['advert']['value']);	
			$result['survey']['value'] = $survey->clientListAll($this->clientId, '', false, false, "count");
			$result['survey']['label'] = $this->numberPrintFormat($result['survey']['value']);

			$result['running']['advert']['value'] = $advert->clientList($this->clientId, "count");
			$result['running']['advert']['label'] = $this->numberPrintFormat($result['running']['advert']['value']);	
			$result['running']['survey']['value'] = $survey->clientList($this->clientId, "count");
			$result['running']['survey']['label'] = $this->numberPrintFormat($result['running']['survey']['value']);
			$result['refund']['impression']['value'] = $advert->clientImpression($this->clientId);
			$result['refund']['impression']['label'] = $this->numberPrintFormat($result['refund']['impression']['value']);
			$result['refund']['wallet']['value'] = $advert_stat->getClientAdvertRefund($this->clientId);
			$result['refund']['wallet']['label'] = $this->numberPrintFormat($result['refund']['impression']['value']);

			

			return $result;
		}
		
		function http2https() {
			//If the HTTPS is not found to be "on"
			if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
				//Tell the browser to redirect to the HTTPS URL.
				header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
				//Prevent the rest of the script from executing.
				exit;
			}
		}

		public function CheckValidate($input, $check) {
			foreach ($check as $row) {
				if (!array_key_exists($row, $input)) {
					return false;
					break;
				}
			}
			return true;
		}

		public static function patienrNumber($id) {
			return "LH".(100000+$id);
		}
	
		public function idFromPatientNumber( $invoiceNumber) {
			$data = explode("lh", strtolower($invoiceNumber));
	
			return $data[1] - 10000;
		}

		public function invoiceNumber($id) {
			return "INV".(10000+$id);
		}
	
		public function idFromInvoiceNumber( $invoiceNumber) {
			$data = explode("inv", strtolower($invoiceNumber));
	
			return $data[1] - 10000;
		}
	}
?>