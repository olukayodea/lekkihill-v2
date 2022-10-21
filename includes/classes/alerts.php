<?php
	class alerts extends common {
		function sendEmail($array) {
			$from = $array['from'];
			$to = $array['to'];
			$subject = $array['subject'];
			$body = $array['body'];
			$send = $this->send_mail($from,$to,$subject,$body);
			
			if ($send) {
				return true;
			} else {
				return false;
			}

			return true;
		}
		
		function addToMailSpool($array) {
			$this->insert( "email_spool", array('subject' => $array['subject'],'body' => $array['body'],'user' => $array['user'],'email' => $array['email'],'create_time' => time(),'modify_time' => time(),'schedule_time' => $array['schedule_time']));
			return true;
		}
		
		function updateOneSpool($tag, $value, $id) {
			$this->update("email_spool", array($tag=>$value), array("ref"=>$id));
		}
		
		function sendBulkSystem() {
			global $users;
			$data = $this->spoolBatch(20);
			
			for ($i = 0; $i < count($data); $i++) {
				$array['from'] = $data[$i]['from'];
				$array['subject'] = $data[$i]['subject'];
				if (intval($data[$i]['user']) > 0) {
					$user_data	= $users->listOne($data[$i]['user']);
					$array['to']= $user_data['other_names']." ".$user_data['last_name']." <".$user_data['email'].">";

					// check the body and then add logic for survey and other things
					if ($data[$i]['body'] == "broadcast") {
						$mailUrl = URL."includes/emails/emailer.php?".$data[$i]['data'];
					} else {
						$mailUrl = URL."includes/emails/surveyAlert.php?".$data[$i]['data'];
					}
					$array['body'] = $this->curl_file_get_contents($mailUrl);
					if ($this->sendEmail($array)) {
						$this->updateOneSpool("status", "SENT", $data[$i]['ref']);
						$this->updateOneSpool("sent_time", time(), $data[$i]['ref']);
					} else {
						$this->updateOneSpool("status", "ERROR", $data[$i]['ref']);
						$this->updateOneSpool("sent_time", time(), $data[$i]['ref']);
					}
				}
			}
			return true;
		}
		
		function spoolBatch($limit) {
			return $this->query("SELECT * FROM `email_spool` WHERE `status` = 'NEW' AND `schedule_time` < '".time()."' ORDER BY `ref` DESC LIMIT ".$limit, false, "list");
		}
		
		function listAll() {
			return $this->lists("email_spool", false, false, "ref", "DESC");
		}
		
		function listOne($id, $tag='ref') {
			return $this->getOne("email_spool", $id, $tag);
		}
		
		//get one field from the dtails of one application
		function listOneField($id, $tag='ref', $ref='body') {
			$data = $this->listOne($id, $tag);
			return $data[$ref];
		}

		function sendAppNotification($array) {
			// $title = $array['title'];
			// $body = $array['body'];
			// $token= $array['token'];

			//   $msg = array(
			// 	'body' 	=> $body,
			// 	'title'	=> $title,
			// 	'action'	=> $title
			//   );
		 
			//  $fields = array(
			//    'to' => $token,
			//    'notification'	=> $msg,
			//    'collapse_key'	=> 'type_a'
			//  );
		 
			//  $headers = array(
			//    'Authorization: key=' . API_ACCESS_KEY,
			//    'Content-Type: application/json'
			//  );
		 
			//  $ch = curl_init();
			//  curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
			//  curl_setopt( $ch,CURLOPT_POST, true );
			//  curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
			//  curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
			//  curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
			//  curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		 
			//  curl_exec( $ch );
		}
	}
?>