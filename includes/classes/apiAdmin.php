<?php
class apiAdmin extends api {
    protected $page = 1;

    public function prepare($header, $request, $data, $file=false) {
        global $admin;
        global $appointments;
        global $settings;
        global $patient;
        global $invoice;
        global $inventory;
        global $clinic;
        global $inventory_category;
        global $billing_component;
        global $labouratory_component;
        global $visitors;

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
                    } else if (($mode == "visitors") && ($action == "manage") && ($header['method'] == "POST")) {
                        $visitors->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_visitors"])) {
                            if ($this->userData['rights']['write']) {
                                $return = $visitors->create($array_data);
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
                    } else if (($mode == "visitors") && ($action == "manage") && ($header['method'] == "GET")) {
                        $visitors->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_visitors"])) {
                            if ($this->userData['rights']['read']) {
                                if (intval( $string  > 0)) {
                                    $visitors->id = $string;
                                    $visitors->filter = null;
                                } else {
                                    $visitors->filter = $string;
                                    $visitors->search = (trim($extra) == "") ? null : $extra;
                                }
                                $return = $visitors->get($this->page);
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
                    } else if (($mode == "visitors") && ($action == "manage") && ($header['method'] == "DELETE")) {
                        $visitors->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_visitors"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $visitors->remove($string);
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
                    } else if (($mode == "patient") && ($action == "manage") && ($header['method'] == "POST")) {
                        $patient->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_patient"])) {
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
                        if ($this->findRight(["manage_patient"])) {
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
                        if ($this->findRight(["manage_patient"])) {
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
                    } else if (($mode == "patient") && ($action == "massage") && ($header['method'] == "POST")) {
                        $patient->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_patient"])) {
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
                    } else if (($mode == "patient") && ($action == "massage") && ($header['method'] == "GET")) {
                        $patient->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_patient"])) {
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
                    } else if (($mode == "patient") && ($action == "massage") && ($header['method'] == "PUT")) {
                        $patient->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_patient"])) {
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
                    } else if (($mode == "patient") && ($action == "massage") && ($header['method'] == "DELETE")) {
                    } else if (($mode == "invoice") && ($action == "manage") && ($header['method'] == "POST")) {
                        $invoice->admin_id = $this->admin_id;
                        if ($this->findRight(["mamange_accounts"])) {
                            if ($this->userData['rights']['write']) {
                                $return = $invoice->create($array_data);
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
                    } else if (($mode == "invoice") && ($action == "manage") && ($header['method'] == "PUT")) {
                        $invoice->admin_id = $this->admin_id;
                        if ($this->findRight(["mamange_accounts"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $invoice->edit($array_data);
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
                    } else if (($mode == "invoice") && ($action == "pay") && ($header['method'] == "PUT")) {
                        $invoice->admin_id = $this->admin_id;
                        if ($this->findRight(["mamange_accounts"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $invoice->payInvoice($array_data);
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
                    } else if (($mode == "invoice") && ($action == "multipay") && ($header['method'] == "PUT")) {
                        $invoice->admin_id = $this->admin_id;
                        if ($this->findRight(["mamange_accounts"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $invoice->payMultiInvoice($array_data);
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
                    } else if (($mode == "invoice") && ($action == "manage") && ($header['method'] == "GET")) {
                        $invoice->admin_id = $this->admin_id;
                        if ($this->findRight(["mamange_accounts"])) {
                            if ($this->userData['rights']['read']) {
                                if (intval( $string  > 0)) {
                                    $invoice->id = $string;
                                    $invoice->filter = null;
                                } else {
                                    $invoice->filter = $string;
                                    $invoice->search = (trim($extra) == "") ? null : $extra;
                                }
                                $return = $invoice->get($this->page);
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
                    } else if (($mode == "invoice") && ($action == "component") && ($header['method'] == "GET")) {
                        $invoice->admin_id = $this->admin_id;
                        if ($this->findRight(["mamange_accounts"])) {
                            if ($this->userData['rights']['read']) {
                                $return = $invoice->getComponent();
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
                        $billing_component->admin_id = $this->admin_id;
                        if ($this->findRight(["mamange_accounts"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $invoice->remove($string);
                            } else {
                                $return['success'] = false;
                                $return['error']['code'] = 10003;
                                $return['error']["message"] = "You do not have permission to delete data";
                            }
                        } else {
                            $return['success'] = false;
                            $return['error']['code'] = 10000;
                            $return['error']["message"] = "You do not have permission to view this page";
                        }
                    } else if (($mode == "appointments") && ($action == "manage") && ($header['method'] == "POST")) {
                        $appointments->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_patient"])) {
                            if ($this->userData['rights']['write']) {
                                $return = $appointments->create($array_data);
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
                    } else if (($mode == "appointments") && ($action == "manage") && ($header['method'] == "PUT")) {
                        $appointments->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_patient"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $appointments->edit($array_data);
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
                    } else if (($mode == "appointments") && ($action == "cancel") && ($header['method'] == "PUT")) {
                        $appointments->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_patient"])) {
                            if ($this->userData['rights']['modify']) {
                                $appointments->id = $string;
                                $return = $appointments->cancel();
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
                    } else if (($mode == "appointments") && ($action == "schedule") && ($header['method'] == "PUT")) {
                        $appointments->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_patient"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $appointments->schedule($array_data);
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
                    } else if (($mode == "appointments") && ($action == "manage") && ($header['method'] == "GET")) {
                        $appointments->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_patient"])) {
                            if ($this->userData['rights']['read']) {
                                if (intval( $string  > 0)) {
                                    $appointments->id = $string;
                                    $appointments->filter = null;
                                } else {
                                    $appointments->filter = $string;
                                    $appointments->search = (trim($extra) == "") ? null : $extra;
                                }
                                $return = $appointments->get($this->page);
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
                    } else if (($mode == "appointments") && ($action == "manage") && ($header['method'] == "DELETE")) {

                        $appointments->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_patient"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $appointments->removeNew($string);
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
                    } else if (($mode == "billingcomponent") && ($action == "manage") && ($header['method'] == "POST")) {
                        $billing_component->admin_id = $this->admin_id;
                        if ($this->findRight(["mamange_accounts"])) {
                            if ($this->userData['rights']['write']) {
                                $return = $billing_component->create($array_data);
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
                    } else if (($mode == "billingcomponent") && ($action == "manage") && ($header['method'] == "PUT")) {
                        $billing_component->admin_id = $this->admin_id;
                        if ($this->findRight(["mamange_accounts"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $billing_component->create($array_data);
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
                    } else if (($mode == "billingcomponent") && ($action == "status")) {
                        $billing_component->admin_id = $this->admin_id;
                        if ($this->findRight(["mamange_accounts"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $billing_component->changeStatus($array_data);
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
                    } else if (($mode == "billingcomponent") && ($action == "manage") && ($header['method'] == "GET")) {
                        $billing_component->admin_id = $this->admin_id;
                        if ($this->findRight(["mamange_accounts"])) {
                            if ($this->userData['rights']['read']) {
                                if (intval( $string  > 0)) {
                                    $billing_component->id = $string;
                                    $billing_component->filter = null;
                                } else {
                                    $billing_component->filter = $string;
                                    $billing_component->search = (trim($extra) == "") ? null : $extra;
                                }
                                $return = $billing_component->get($this->page);
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
                    } else if (($mode == "billingcomponent") && ($action == "manage") && ($header['method'] == "DELETE")) {

                        $billing_component->admin_id = $this->admin_id;
                        if ($this->findRight(["mamange_accounts"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $billing_component->remove($string);
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
                    } else if (($mode == "labcomponent") && ($action == "manage") && ($header['method'] == "POST")) {
                        $labouratory_component->admin_id = $this->admin_id;
                        if ($this->findRight(["mamange_accounts", "manage_inventory"])) {
                            if ($this->userData['rights']['write']) {
                                $return = $labouratory_component->create($array_data);
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
                    } else if (($mode == "labcomponent") && ($action == "manage") && ($header['method'] == "PUT")) {
                        $labouratory_component->admin_id = $this->admin_id;
                        if ($this->findRight(["mamange_accounts", "manage_inventory"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $labouratory_component->create($array_data);
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
                    } else if (($mode == "labcomponent") && ($action == "status")) {
                        $labouratory_component->admin_id = $this->admin_id;
                        if ($this->findRight(["mamange_accounts", "manage_inventory"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $labouratory_component->changeStatus($array_data);
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
                    } else if (($mode == "labcomponent") && ($action == "manage") && ($header['method'] == "GET")) {
                        $labouratory_component->admin_id = $this->admin_id;
                        if ($this->findRight(["mamange_accounts", "manage_inventory"])) {
                            if ($this->userData['rights']['read']) {
                                if (intval( $string  > 0)) {
                                    $labouratory_component->id = $string;
                                    $labouratory_component->filter = null;
                                } else {
                                    $labouratory_component->filter = $string;
                                    $labouratory_component->search = (trim($extra) == "") ? null : $extra;
                                }
                                $return = $labouratory_component->get($this->page);
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
                    } else if (($mode == "labcomponent") && ($action == "manage") && ($header['method'] == "DELETE")) {

                        $labouratory_component->admin_id = $this->admin_id;
                        if ($this->findRight(["mamange_accounts", "manage_inventory"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $labouratory_component->remove($string);
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
                    } else if (($mode == "inventory") && ($action == "manage") && ($header['method'] == "POST")) {
                        $inventory->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_inventory"])) {
                            if ($this->userData['rights']['write']) {
                                $return = $inventory->create($array_data);
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
                    } else if (($mode == "inventory") && ($action == "manage") && ($string == "stock") && ($header['method'] == "PUT")) {
                        $inventory->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_inventory"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $inventory->manageStock($array_data);
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
                    } else if (($mode == "inventory") && ($action == "manage") && ($string == "status") && ($header['method'] == "PUT")) {
                        $inventory->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_inventory"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $inventory->changeStatus($array_data);
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
                    } else if (($mode == "inventory") && ($action == "manage") && ($header['method'] == "PUT")) {
                        $inventory->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_inventory"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $inventory->edit($array_data);
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
                    } else if (($mode == "inventory") && ($action == "manage") && ($header['method'] == "GET")) {
                        $inventory->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_inventory"])) {
                            if ($this->userData['rights']['read']) {
                                if (intval( $string  > 0)) {
                                    $inventory->id = $string;
                                    $inventory->filter = null;
                                } else {
                                    $inventory->filter = $string;
                                    $inventory->search = (trim($extra) == "") ? null : $extra;
                                }
                                $return = $inventory->get($this->page);
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
                    } else if (($mode == "inventory") && ($action == "manage") && ($header['method'] == "DELETE")) {

                        $inventory->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_inventory"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $inventory->remove($string);
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
                    } else if (($mode == "inventory") && ($action == "category") && ($header['method'] == "POST")) {
                        $inventory_category->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_inventory_category"])) {
                            if ($this->userData['rights']['write']) {
                                $return = $inventory_category->create($array_data);
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
                    } else if (($mode == "inventory") && ($action == "category") && ($string == "active")) {
                        
                        $inventory_category->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_inventory"])) {
                            if ($this->userData['rights']['read']) {
                                $return = $inventory_category->getActiveCategory();
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
                    } else if (($mode == "inventory") && ($action == "category") && ($string == "status")) {
                        $inventory_category->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_inventory_category"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $inventory_category->changeStatus($array_data);
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
                    } else if (($mode == "inventory") && ($action == "category") && ($header['method'] == "PUT")) {
                        $inventory_category->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_inventory_category"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $inventory_category->create($array_data);
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
                    } else if (($mode == "inventory") && ($action == "category") && ($header['method'] == "GET")) {
                        $inventory_category->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_inventory_category"])) {
                            if ($this->userData['rights']['read']) {
                                if (intval( $string  > 0)) {
                                    $inventory_category->id = $string;
                                    $inventory_category->filter = null;
                                } else {
                                    $inventory_category->filter = $string;
                                    $inventory_category->search = (trim($extra) == "") ? null : $extra;
                                }
                                $return = $inventory_category->get($this->page);
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
                    } else if (($mode == "inventory") && ($action == "category") && ($header['method'] == "DELETE")) {

                        $inventory_category->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_inventory_category"])) {
                            if ($this->userData['rights']['modify']) {
                                $return = $inventory_category->remove($string);
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
                    } else if (($mode == "clinic") && ($action == "manage") && ($header['method'] == "GET")) {
                        $clinic->admin_id = $this->admin_id;
                        $clinic->patient_id = intval($string);
                        if ($this->findRight(["manage_clinic"])) {
                            if ($this->userData['rights']['read']) {
                                $return = $clinic->get();
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
                    } else if (($mode == "clinic") && ($action == "notes") && ($header['method'] == "POST")) {
                        $clinic->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_clinic"])) {
                            if ($this->userData['rights']['write']) {
                                $return = $clinic->add_notes($array_data);
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
                    } else if (($mode == "clinic") && ($action == "notes") && ($header['method'] == "PUT")) {
                    } else if (($mode == "clinic") && ($action == "notes") && ($header['method'] == "GET")) {
                        $clinic->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_clinic"])) {
                            if ($this->userData['rights']['read']) {
                                if (intval( $string  > 0)) {
                                    $clinic->patient_id = $string;
                                    $clinic->filter = null;
                                } else {
                                    $clinic->filter = $string;
                                    $clinic->patient_id = intval($extra);
                                }
                                $return = $clinic->getNotes($this->page);
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
                    } else if (($mode == "clinic") && ($action == "notes") && ($header['method'] == "DELETE")) {
                    } else if (($mode == "clinic") && ($action == "postop") && ($header['method'] == "POST")) {
                        $clinic->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_clinic"])) {
                            if ($this->userData['rights']['write']) {
                                $return = $clinic->add_post_op($array_data);
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
                    } else if (($mode == "clinic") && ($action == "postop") && ($header['method'] == "PUT")) {
                    } else if (($mode == "clinic") && ($action == "postop") && ($header['method'] == "GET")) {
                        $clinic->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_clinic"])) {
                            if ($this->userData['rights']['read']) {
                                if (intval( $string  > 0)) {
                                    $clinic->patient_id = $string;
                                    $clinic->filter = null;
                                } else {
                                    $clinic->filter = $string;
                                    $clinic->patient_id = intval($extra);
                                }
                                $return = $clinic->getPostOp($this->page);
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
                    } else if (($mode == "clinic") && ($action == "postop") && ($header['method'] == "DELETE")) {
                    } else if (($mode == "clinic") && ($action == "medication") && ($header['method'] == "POST")) {
                        $clinic->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_clinic"])) {
                            if ($this->userData['rights']['write']) {
                                $return = $clinic->add_medication($array_data);
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
                    } else if (($mode == "clinic") && ($action == "medication") && ($header['method'] == "PUT")) {
                    } else if (($mode == "clinic") && ($action == "medication") && ($header['method'] == "GET")) {
                        $clinic->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_clinic"])) {
                            if ($this->userData['rights']['read']) {
                                if (intval( $string  > 0)) {
                                    $clinic->patient_id = $string;
                                    $clinic->filter = null;
                                } else {
                                    $clinic->filter = $string;
                                    $clinic->patient_id = intval($extra);
                                }
                                $return = $clinic->getMedication($this->page);
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
                    } else if (($mode == "clinic") && ($action == "medication") && ($header['method'] == "DELETE")) {
                    } else if (($mode == "clinic") && ($action == "fluid") && ($header['method'] == "POST")) {
                        $clinic->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_clinic"])) {
                            if ($this->userData['rights']['write']) {
                                $return = $clinic->add_fluid_balanceion($array_data);
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
                    } else if (($mode == "clinic") && ($action == "fluid") && ($header['method'] == "PUT")) {
                    } else if (($mode == "clinic") && ($action == "fluid") && ($header['method'] == "GET")) {
                        $clinic->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_clinic"])) {
                            if ($this->userData['rights']['read']) {
                                if (intval( $string  > 0)) {
                                    $clinic->patient_id = $string;
                                    $clinic->filter = null;
                                } else {
                                    $clinic->filter = $string;
                                    $clinic->patient_id = intval($extra);
                                }
                                $return = $clinic->getFluidBalance($this->page);
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
                    } else if (($mode == "clinic") && ($action == "fluid") && ($header['method'] == "DELETE")) {
                    } else if (($mode == "clinic") && ($action == "discharge") && ($header['method'] == "POST")) {
                    } else if (($mode == "clinic") && ($action == "discharge") && ($header['method'] == "PUT")) {
                    } else if (($mode == "clinic") && ($action == "discharge") && ($header['method'] == "GET")) {
                    } else if (($mode == "clinic") && ($action == "discharge") && ($header['method'] == "DELETE")) {
                    } else if (($mode == "clinic") && ($action == "vitals") && ($header['method'] == "POST")) {
                        $clinic->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_clinic"])) {
                            if ($this->userData['rights']['write']) {
                                $return = $clinic->add_vitals($array_data);
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
                    } else if (($mode == "clinic") && ($action == "vitals") && ($header['method'] == "GET")) {
                        $clinic->admin_id = $this->admin_id;
                        if ($this->findRight(["manage_clinic", "manage_patient"])) {
                            if ($this->userData['rights']['read']) {
                                if (intval( $string  > 0)) {
                                    $clinic->patient_id = $string;
                                    $clinic->filter = null;
                                } else {
                                    $clinic->filter = $string;
                                    $clinic->patient_id = intval($extra);
                                }
                                $return = $clinic->getVitals($this->page);
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
                    } else if (($mode == "clinic") && ($action == "glucose") && ($header['method'] == "POST")) {
                    } else if (($mode == "clinic") && ($action == "glucose") && ($header['method'] == "PUT")) {
                    } else if (($mode == "clinic") && ($action == "glucose") && ($header['method'] == "GET")) {
                    } else if (($mode == "clinic") && ($action == "glucose") && ($header['method'] == "DELETE")) {
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

    private function findRight(array $data) {
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
            $array[] = "patient:massage";
            $array[] = "invoice:manage";
            $array[] = "inventory:manage";
            $array[] = "inventory:category";
            $array[] = "billingcomponent:manage";
            $array[] = "labcomponent:manage";
            $array[] = "visitors:manage";
            $array[] = "appointments:manage";
            $array[] = "clinic:notes";
            $array[] = "clinic:postop";
            $array[] = "clinic:medication";
            $array[] = "clinic:fluid";
            $array[] = "clinic:discharge";
            $array[] = "clinic:vitals";
            $array[] = "clinic:glucose";
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
            $array[] = "patient:massage";
            $array[] = "invoice:manage";
            $array[] = "invoice:component";
            $array[] = "inventory:manage";
            $array[] = "inventory:category";
            $array[] = "visitors:manage";
            $array[] = "appointments:manage";
            $array[] = "billingcomponent:manage";
            $array[] = "labcomponent:manage";
            $array[] = "clinic:manage";
            $array[] = "clinic:notes";
            $array[] = "clinic:postop";
            $array[] = "clinic:medication";
            $array[] = "clinic:fluid";
            $array[] = "clinic:discharge";
            $array[] = "clinic:vitals";
            $array[] = "clinic:glucose";
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
            $array[] = "patient:massage";
            $array[] = "invoice:manage";
            $array[] = "invoice:pay";
            $array[] = "invoice:multipay";
            $array[] = "inventory:manage";
            $array[] = "inventory:category";
            $array[] = "appointments:manage";
            $array[] = "appointments:cancel";
            $array[] = "appointments:schedule";
            $array[] = "billingcomponent:manage";
            $array[] = "billingcomponent:status";
            $array[] = "labcomponent:manage";
            $array[] = "labcomponent:status";
            $array[] = "clinic:notes";
            $array[] = "clinic:postop";
            $array[] = "clinic:medication";
            $array[] = "clinic:fluid";
            $array[] = "clinic:discharge";
            $array[] = "clinic:vitals";
            $array[] = "clinic:glucose";
            if (array_search($type, $array) === false) {
                return false;
            } else {
                return true;
            }
        } else if ($method == "DELETE") {
            $array[] = "main:";
            $array[] = "admin:remove";
            $array[] = "patient:manage";
            $array[] = "patient:massage";
            $array[] = "invoice:manage";
            $array[] = "inventory:manage";
            $array[] = "inventory:category";
            $array[] = "visitors:manage";
            $array[] = "appointments:manage";
            $array[] = "billingcomponent:manage";
            $array[] = "labcomponent:manage";
            $array[] = "clinic:notes";
            $array[] = "clinic:postop";
            $array[] = "clinic:medication";
            $array[] = "clinic:fluid";
            $array[] = "clinic:discharge";
            $array[] = "clinic:vitals";
            $array[] = "clinic:glucose";
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