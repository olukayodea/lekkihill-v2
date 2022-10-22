<?php
class apiAdmin extends api {
    protected $page = 1;

    public function prepare($header, $request, $data, $file=false) {
        global $admin;
        global $settings;
        global $patient;

        // get all api url variables
        $urlData = explode("/", $request);
        $mode = strtolower($urlData[0]);
        $action = strtolower($urlData[1]);
        $string = strtolower($urlData[2]);
        $extra = strtolower($urlData[3]);
        $to = strtolower($urlData[4]);

        $this->page = intval($_GET['page']);
    
        //get additional data			
        $return = false;

        $array_data = json_decode($data, true);
  
        //service cron status
        //$data_refresh->spin();
        //check product version
        //if ($settings->get("product_ver") <= $product_ver) {
        //authenticate user
        if ($return == false) {
            if ($this->methodCheck($header['method'], $mode.":".$action)) {

                if (($mode == "admin") && ($action == "login")) {
                    $login = $admin->login($array_data);
                    if ($login) {
                        if (($login['accountStatus']['newAccount']) || ($login['accountStatus']['passwordChange'])) {
                            $return['success'] = true;
                            $return['results'] = "OK";
                            $this->admin_id = $login['ref'];
                            $return['token'] = $this->getToken();
                            $return['data']['accountStatus'] = $login['accountStatus'];
                        } else if ($login['accountStatus']['inactiveAccount']) {
                            $return['success'] = false;
                            $return['error']['code'] = 406;
                            $return['error']["message"] = "This account has been deactivated. PLease contact us at contactus@skrinad.com";
                        } else {
                            $return['success'] = true;
                            $return['results'] = "OK";
                            $this->admin_id = $login['ref'];
                            $return['data'] = $login;
                            $return['data']['token'] = $this->getToken();
                        }
                    } else {
                        $return['success'] = false;
                        $return['error']['code'] = 404;
                        $return['error']["message"] = "Unauthorized, No user with the email/username and password combination was found";
                    }
                } else if (($mode == "admin") && ($action == "password")) {
                    if ($admin->passwordReset(urldecode($string))) {
                        $return['success'] = true;
                        $return['results'] = "OK";
                        $return['message'] = "Your password reset request has been processed, if the email address is valid, we will send an email to the adresss provided with a temporary password to get you going";
                    } else {
                        $return['success'] = false;
                        $return['error']['code'] = 10005;
                        $return['error']["message"] = "An error occured while performing this action, please try again";
                    }
                } else if ($this->authenticate($header)) {
                    $this->admin_id = $this->userData['ref'];
                    
                    if (($mode == "admin") && ($action == "logout")) {
                        $admin->id = $this->admin_id;
                        $admin->logout();
                        $return['success'] = true;
                        $return['results'] = "OK";
                    } else if (($mode == "admin") && ($action == "setpassword")) {
                        $admin->id = $this->admin_id;
                        $admin->isActivate = true;

                        $return = $admin->updatePassword($array_data);
						$return['data']['token'] = $this->getToken();
                    } else if (($mode == "admin") && ($action == "profile") && ($header['method'] == "PUT")) {
                        $admin->admin_id = $this->admin_id;

                        $return = $admin->editProfile($array_data);
                    } else if (($mode == "admin") && ($action == "profile")) {
                        $admin->id = $this->admin_id;
                        $return['success'] = true;
                        $return['results'] = "OK";
                        $return['data'] = $this->userData;
                    } else if (($mode == "admin") && ($action == "updatepassword")) {
                        $admin->id = $this->admin_id;
                        $return = $admin->updatePassword($array_data);
                    } else if (($mode == "patient") && ($action == "manage") && ($header['method'] == "POST")) {
                        $patient->admin_id = $this->admin_id;
                        if ($this->findRight("manage_patient")) {
                            if ($this->userData['rights']['write']) {
                                $array_data['p_type'] = "regular";
                                $return = $patient->create($array_data);
                            } else {
                                $return['success'] = false;
                                $return['error']['code'] = 10003;
                                $return['error']["message"] = "You do not have permission to write data";
                            }
                        } else {
                            $return['success'] = false;
                            $return['error']['code'] = 10000;
                            $return['error']["message"] = "You do not have permission to view this page";
                        }
                    } else if (($mode == "patient") && ($action == "manage") && ($header['method'] == "GET")) {
                        $patient->admin_id = $this->admin_id;
                        if ($this->findRight("manage_patient")) {
                            if ($this->userData['rights']['read']) {
                                if (intval( $string  > 0)) {
                                    $patient->id = $string;
                                    $patient->filter = null;
                                } else {
                                    $patient->filter = $string;
                                    $patient->search = (trim($extra) == "") ? null : $extra;
                                }
                                $return = $patient->get($this->page);
                            } else {
                                $return['success'] = false;
                                $return['error']['code'] = 10003;
                                $return['error']["message"] = "You do not have permission to read data";
                            }
                        } else {
                            $return['success'] = false;
                            $return['error']['code'] = 10000;
                            $return['error']["message"] = "You do not have permission to view this page";
                        }
                    } else if (($mode == "patient") && ($action == "manage") && ($header['method'] == "PUT")) {
                        $patient->admin_id = $this->admin_id;
                        if ($this->findRight("manage_patient")) {
                            if ($this->userData['rights']['modify']) {
                                $array_data['p_type'] = "regular";
                                $return = $patient->edit($array_data);
                            } else {
                                $return['success'] = false;
                                $return['error']['code'] = 10003;
                                $return['error']["message"] = "You do not have permission to modify data";
                            }
                        } else {
                            $return['success'] = false;
                            $return['error']['code'] = 10000;
                            $return['error']["message"] = "You do not have permission to view this page";
                        }
                    } else if (($mode == "patient") && ($action == "manage") && ($header['method'] == "DELETE")) {
                    } else if (($mode == "invoice") && ($action == "manage") && ($header['method'] == "POST")) {
                    } else if (($mode == "invoice") && ($action == "manage") && ($header['method'] == "PUT")) {
                    } else if (($mode == "invoice") && ($action == "manage") && ($header['method'] == "GET")) {
                        $patient->admin_id = $this->admin_id;
                        if ($this->findRight("manage_patient")) {
                            if ($this->userData['rights']['read']) {
                                if (intval( $string  > 0)) {
                                    $patient->id = $string;
                                    $patient->filter = null;
                                } else {
                                    $patient->filter = $string;
                                    $patient->search = (trim($extra) == "") ? null : $extra;
                                }
                                $return = $patient->get($this->page);
                            } else {
                                $return['success'] = false;
                                $return['error']['code'] = 10003;
                                $return['error']["message"] = "You do not have permission to read data";
                            }
                        } else {
                            $return['success'] = false;
                            $return['error']['code'] = 10000;
                            $return['error']["message"] = "You do not have permission to view this page";
                        }
                    } else if (($mode == "invoice") && ($action == "manage") && ($header['method'] == "DELETE")) {

                    } else if (($mode == "settings") && ($header['method'] == "POST")) {

                        if ($this->findRight("manage_settings")) {
                            if ($this->userData['rights']['modify']) {
                                $return = $settings->setSettings($array_data);
                            } else {
                                $return['success'] = false;
                                $return['error']['code'] = 10003;
                                $return['error']["message"] = "You do not have permission to modify data";
                            }
                        } else {
                            $return['success'] = false;
                            $return['error']['code'] = 10000;
                            $return['error']["message"] = "You do not have permission to view this page";
                        }
                    } else if (($mode == "settings") && ($header['method'] == "GET")) {
                        if ($this->findRight("manage_settings")) {
                            if ($this->userData['rights']['read']) {
                                $return = $settings->getSettings();
                            } else {
                                $return['success'] = false;
                                $return['error']['code'] = 10003;
                                $return['error']["message"] = "You do not have permission to read data";
                            }
                        } else {
                            $return['success'] = false;
                            $return['error']['code'] = 10000;
                            $return['error']["message"] = "You do not have permission to view this page";
                        }
                    }  
                    
                } else {
                    $return['success'] = false;
                    $return['error']['code'] = 10018;
                    $return['error']["message"] = "Signed Out";
                }
            } else {
                $return['success'] = false;
                $return['error']['code'] = 10019;
                $return['error']["message"] = "Bad Request";
            }
        }
        //} else {
        //	$return['status'] = "ERROR";
        //	$return['code'] = "131";
        //}

        return $return;
 
    }

    private function findRight($list) {
        $data = explode(",", $list);
        foreach($data as $row) {
            if (in_array($row, $this->userRoles)) {
                return true;
            }
        }
        return false;
    }

    private function authenticate($header) {
        global $admin;
        $split = explode("_", base64_decode($header['auth']));
        $token = $split[1];
        if ($header['key'] == $split[0]) {
            if ($this->checkExixst("users", "user_token", $token, "count", "ID") == 1) {
                
                $this->userData = $admin->formatResult( $admin->listOne($token, "user_token"), true);
                if (($this->userData['accountStatus']['activeAccount'] == true) || ($this->userData['accountStatus']['passwordChange'] == true) || ($this->userData['accountStatus']['newAccount'] == true)) {
                    $this->admin_id = $this->userData['ID'];
                    $this->userRoles = $this->userData['rights']['pages'];
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function methodCheck($method, $type) {
        $array = array();
        if ($method == "POST") {
            $array[] = "admin:create";
            $array[] = "admin:right";
            $array[] = "admin:login";
            $array[] = "patient:manage";
            $array[] = "invoice:manage";
            $array[] = "settings:";
            if (array_search($type, $array) === false) {
                return false;
            } else {
                return true;
            }
        } else if ($method == "GET") {
            $array[] = "admin:password";
            $array[] = "admin:logout";
            $array[] = "admin:profile";
            $array[] = "admin:location";
            $array[] = "admin:dashboard";
            $array[] = "admin:refreshbalance";
            $array[] = "admin:resetpassword";
            $array[] = "admin:get";
            $array[] = "admin:right";
            $array[] = "admin:getdata";
            $array[] = "patient:manage";
            $array[] = "invoice:manage";
            $array[] = "settings:";
            if (array_search($type, $array) === false) {
                return false;
            } else {
                return true;
            }
        } else if ($method == "PUT") {
            $array[] = "admin:right";
            $array[] = "admin:profile";
            $array[] = "admin:edit";
            $array[] = "admin:updatepassword";
            $array[] = "admin:setpassword";
            $array[] = "patient:manage";
            $array[] = "invoice:manage";
            if (array_search($type, $array) === false) {
                return false;
            } else {
                return true;
            }
        } else if ($method == "DELETE") {
            $array[] = "main:";
            $array[] = "admin:remove";
            $array[] = "patient:manage";
            $array[] = "invoice:manage";
            if (array_search($type, $array) === false) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    private function getToken () {
        global $admin;
        $userData = $admin->listOne($this->admin_id);
        if (($userData['user_token'] != "") && ($userData['auth_token_expire'] > time())) {
            $admin->modifyOne("auth_token_expire", time()+(60*60*24*180), $this->admin_id, "ID");
            return $userData['user_token'];
        } else {
            $token = substr( $this->admin_id.rand().time().$this->createRandomPassword(15).rand(), 0, 32);
            $admin->modifyOne("user_token", $token, $this->admin_id, "ID");
            $admin->modifyOne("auth_token_expire", time()+(60*60*24*180), $this->admin_id, "ID");
            return $token;
        }
    }
}
?>