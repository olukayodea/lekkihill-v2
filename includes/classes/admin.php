<?php
	class admin extends common {
        public $minify = false;

        function wp_hash_password( $password ) {
            global $wp_hasher;
    
            return $wp_hasher->HashPassword( trim( $password ) );
        }

		public function login($array) {
			$username = $array['username'];
			$password = $array['password'];
			
			$query = "SELECT * FROM `".table_prefix."users` WHERE (`user_login` = :username OR `user_email` = :username) AND `app_token` = :password";
			$prepare[':username'] = $username;
			$prepare[':password'] = md5(sha1($password));
	
			$row = $this->query($query, $prepare, "getRow");
	
			if ((is_array($row)) && ($row != false)) {
				return $this->formatResult($row, true);
			} else {
				return false;
			}
		}

		public function logout() {
			session_destroy();
			$this->modifyOne("user_token", NULL, $this->id);
			$this->modifyOne("auth_token_expire", NULL, $this->id);
		}
		
		private function checkAccount($email) {
			return $this->select("users", array("ID"), "count", "`email` = :email", array(':email' => $email));
		}
		
		function passwordReset($email) {
			$check = $this->checkAccount($email);
			
			if ($check == 1) {
				$data = $this->listOne($email, "email");
				$password = $this->createRandomPassword();
				
				$dataRow = array(
                    "user_pass" => $this->wp_hash_password( $password),
                    "app_token" => md5( sha1($password)), 'app_status'=>'2'
                );
				$where = array("ID" => $data['ID']);
				if ($this->update("users", $dataRow, $where)) {
					$client = $data['name'];
					$subjectToClient = "Password Reset Notification";
					$contact = "LekkiHill Admin <".replyMail.">";
					
					$fields = 'subject='.urlencode($subjectToClient).
						'&last_name='.urlencode($data['client_name']).
						'&email='.urlencode($data['email']).
						'&username='.urlencode($data['username']).
						'&password='.urlencode($password);
					$mailUrl = URL."includes/emails/passwordNotificationAdmin.php?".$fields;
					$messageToClient = $this->curl_file_get_contents($mailUrl);
					
					$mail['from'] = $contact;
					$mail['to'] = $client." <".$data['email'].">";
					$mail['subject'] = $subjectToClient;
					$mail['body'] = $messageToClient;
					
					$alerts = new alerts;
					$alerts->sendEmail($mail);
				} else {
					return false;
				}
			}
			return true;
		}

		function editProfile ($array) {
			$sqlData = array('display_name' => $array['display_name'],'user_url' => $array['user_url']);

			$sqlWhere['ID'] = $this->admin_id;;

			$sql = $this->update("users", $sqlData, $sqlWhere);
							
			if ($sql) {
				$this->return['success'] = true;
				$this->return['results'] = "OK";
				$this->addToken = true;
				$this->return['data'] = $this->formatResult( $this->listOne($this->admin_id), true);
			} else {
				$this->return['success'] = false;
				$this->return['error']['code'] = 10015;
				$this->return['error']['message'] = "Not Implemented, An error occured while profile data";
			}
			return $this->return;
		}

		public function updatePassword($array) {
			$data = $this->listOne($this->id);

			if ($data) {
				if ((sha1($array['oldPassword']) == $data['password']) || ($this->isActivate)) {

					$sqlData['app_token'] = ($this->isActivate) ? md5(sha1($array['password'])) : md5(sha1($array['newPassword']));
                    $sqlData['user_pass'] = ($this->isActivate) ? $this->wp_hash_password($array['password']) : $this->wp_hash_password($array['newPassword']);

					$sqlWhere['ID'] = $this->id;

					$sql = $this->update("users", $sqlData, $sqlWhere);
					
					if ($sql) {
						if ($this->isActivate) {
							$this->modifyOne("app_status", 1, $this->id);
						}				
						$client = $data['name'];
						$subjectToClient = "Password Reset Notification";
						$contact = "LekkiHill Administrator <".replyMail.">";
						
						$fields = 'subject='.urlencode($subjectToClient).
							'&last_name='.urlencode($data['name']).
							'&username='.urlencode($data['username']).
							'&email='.urlencode($data['email']).
							'&password='.urlencode($this->hashPass($array['newPassword']));
						$mailUrl = URL."includes/emails/passwordNotificationAdmin.php?".$fields;
						$messageToClient = $this->curl_file_get_contents($mailUrl);
						
						$mail['from'] = $contact;
						$mail['to'] = $client." <".$data['email'].">";
						$mail['subject'] = $subjectToClient;
						$mail['body'] = $messageToClient;
						
						$alerts = new alerts;
						$alerts->sendEmail($mail);
						
						$this->return['success'] = true;
						$this->return['results'] = "OK";
						$this->return['data'] = $this->formatResult( $this->listOne( $this->id ), true);
					} else {
						$this->return['success'] = false;
						$this->return['error']['code'] = 10015;
						$this->return['error']['message'] = "Not Implemented, An error occured while creating this user";
					}
				} else {
					$this->return['success'] = false;
					$this->return['error']['code'] = 10023;
					$this->return['error']['message'] = "Invalid password";
				}
			} else {
				$this->return['success'] = false;
				$this->return['error']['code'] = 10014;
				$this->return['error']['message'] = "User not found";
			}
			return $this->return;
		}
		
		function modifyOne($tag, $value, $id) {
			if ($this->updateOne("users", $tag, $value, $id, "id")) {
				return true;
			} else {
				return false;
			}
		}
		
		public function sortAllCalls($id, $tag, $tag2=false, $id2=false, $tag3=false, $id3=false, $start=0, $limit=30, $order='id', $type="list") {
			if (trim($this->selectedCountry) != "") {
				$added = " AND `country` LIKE '%".$this->selectedCountry."%' ";
			} else {
				$added = false;
			}

			return $this->sortAll("users", $id, $tag, $tag2, $id2, $tag3, $id3, $order, "DESC", "AND", $start, $limit, $type, $added);
		}


        public function getSingle($name, $tag="user_login", $ref="ID") {
            global $wpdb;
            return $this->getOneField($wpdb->prefix."users", $name, $ref, $tag);
        }

		public function countAl() {
			return $this->query("SELECT COUNT(*) FROM `".table_prefix."users` WHERE `status` != 'DELETED'", false, "getCol");
		}
		
		public function sortList($tag, $id) {
			return $this->query("SELECT * FROM `".table_prefix."users` WHERE `".$tag."` = :id AND `status` != 'DELETED'", array(':id' => $id), "list");
		}
		
		public function listOne($id, $tag='id') {
			return $this->getOne(""."users", $id, $tag);
		}
		
		public function listOneField($id, $tag='id', $ref='name') {
			$data = $this->listOne($id, $tag);
			return $data[$ref];
		}
        
		public function listOneType($id, $tag='slug') {				
			return $this->getOne("lekkihill_roles", $id, $tag);
		}

		public function formatResult($data, $single=false, $mini=false) {
			if ($data) {
				if ($single === false) {
					for ($i = 0; $i < count($data); $i++) {
						$data[$i] = $this->clean($data[$i], $mini);
					}
				} else {
					$data = $this->clean($data, $mini);
				}
			} else {
				return [];
			}
			return $data;
		}
	
		private function clean($row, $mini) {
			$return['ref'] = intval($row['ID']);
			if ($this->addToken == true) {
				$return['token'] = $row['user_token'];
			}
			$return['username'] = $row['user_login'];
			$return['name'] = $row['display_name'];
			$return['email'] = $row['user_email'];

            if ($mini === false) {
                $adminType = $this->listOneType($row['user_role']);
                
                $return['rights'] = $this->formatRightResult( $adminType, true);
                $account['activeAccount'] = (1 == $row['app_status']) ? true : false;
                $account['newAccount'] = (0 == $row['app_status']) ? true : false;
                $account['inactiveAccount'] = (3 == $row['app_status']) ? true : false;
                $account['passwordChange'] = (2 == $row['app_status']) ? true : false;
                $return['accountStatus'] = $account;
                $return['created'] = $row['user_registered'];
            }

			return $return;
		}

		public function formatRightResult($data, $single=false) {
			if ($data) {
				if ($single === false) {
					for ($i = 0; $i < count($data); $i++) {
						$data[$i] = $this->cleanRights($data[$i]);
					}
				} else {
					$data = $this->cleanRights($data);
				}
			} else {
				return [];
			}
			return $data;
		}
	
		private function cleanRights($row) {
			$return['ref'] = intval($row['ref']);

			$return['adminType'] = $row['title'];
			$return['read'] = (1 == $row['read']) ? true : false;
			$return['write'] = (1 == $row['write']) ? true : false;
			$return['modify'] = (1 == $row['modify']) ? true : false;
			$return['pages'] = explode(",", $row['pages']);
			
			$return['created'] = date('d-m-Y h:i:s A', $row['createTime']);
			return $return;
		}
    }
