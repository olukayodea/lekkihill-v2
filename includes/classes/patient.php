<?php
class patient extends common {
    public $id;
    public $minify = false;
    public $filter = null;
    public $search = null;

    protected $allowedFields = array(
        "add" => array(
            "last_name",
            "first_name",
            "age",
            "sex",
            "phone_number",
            "email",
            "address",
            "next_of_Kin",
            "next_of_contact",
            "next_of_address",
            "allergies"
        ),
        "edit" => array(
            "ref",
            "last_name",
            "first_name",
            "phone_number",
            "email",
            "address"
        )
    );

    public function create($array) {
        global $invoice;
        global $settings;

        $replace[] = "last_name";
        $replace[] = "first_name";
        $replace[] = "age";
        $replace[] = "sex";
        $replace[] = "phone_number";

        if (($settings->get("consultationFee-cost") === NULL) || ($settings->get("consultationFee-component-id") === NULL) || ($settings->get("registrationFee-cost") === NULL) || ($settings->get("registrationFee-component-id") === NULL)) {
            return $this->RequiredSettingsNotFound;
        }

        if (!$this->validateInput($array, "add")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        $array['create_by'] = $this->admin_id;
        $id = $this->replace(table_name_prefix."patient", $array, $replace);

        if ($id) {
            $consultationFee_cost = $settings->get("consultationFee-cost");
            $consultationFee_component_id = $settings->get("consultationFee-component-id");
            $registrationFee_cost = $settings->get("registrationFee-cost");
            $registrationFee_component_id = $settings->get("registrationFee-component-id");
            $data['patient_id'] = $id;
            $data['added_by'] = $this->admin_id;
            $data['type'] = "component";
            $data['amount'] = $registrationFee_cost+$consultationFee_cost;

            $data['billing_component'][0]['id'] = $consultationFee_component_id;
            $data['billing_component'][0]['cost'] = $consultationFee_cost;
            $data['billing_component'][0]['quantity'] = 1;
            $data['billing_component'][0]['type'] = "component";
            $data['billing_component'][0]['description'] = NULL;
            $data['billing_component'][1]['id'] = $registrationFee_component_id;
            $data['billing_component'][1]['cost'] = $registrationFee_cost;
            $data['billing_component'][1]['quantity'] = 1;
            $data['billing_component'][1]['type'] = "component";
            $data['billing_component'][1]['description'] = NULL;

            $invoice->create($data);

            $this->successResponse['data'] = $this->formatResult( $this->listOne( $id), true );
            return $this->successResponse;
        } else {
            return $this->internalServerError;
        }
    }

    public function edit($array) {
        global $settings;
        if (($settings->get("consultationFee-cost") === NULL) || ($settings->get("consultationFee-component-id") === NULL) || ($settings->get("registrationFee-cost") === NULL) || ($settings->get("registrationFee-component-id") === NULL)) {
            return $this->RequiredSettingsNotFound;
        }

        if (!$this->validateInput($array, "edit")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }
        $ref = $array['ref'];
        if ( $this->update(table_name_prefix."patient", $array, ["ref" => $ref])) {

            $this->successResponse['data'] = $this->formatResult( $this->listOne( $ref), true );
            return $this->successResponse;
        } else {
            return $this->NotModified;
        }
    }
		
    public function checkAccount($email) {
        return $this->select(table_name_prefix."patient", false, "list", "`email` = :email", array(':email' => $email), false, false, "ref");
    }

    public function modifyOne($tag, $value, $id, $ref="ref") {
        return $this->updateOne(table_name_prefix."patient", $tag, $value, $id, $ref);
    }
    
    public function getList($start=false, $limit=false, $order="last_name", $dir="ASC", $type="list") {
        return $this->lists(table_name_prefix."patient", $start, $limit, $order, $dir, false, $type);
    }

    public function getSingle($name, $tag="last_name", $ref="ref") {
        return $this->getOneField(table_name_prefix."patient", $name, $ref, $tag);
    }

    public function listOne($id) {
        return $this->getOne(table_name_prefix."patient", $id, "ref");
    }

    public function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll(table_name_prefix."patient", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
    }

    private function listPages($start, $limit) {
        $return['data'] = $this->lists(table_name_prefix."patient", $start, $limit, "last_name", "ASC");
        $return['counts'] = $this->lists(table_name_prefix."patient",  false, false, "last_name", "ASC", false, "count");

        return $return;
    }

    private function search($search, $start, $limit) {
        $return['data'] = $this->runSearch($search, $start, $limit);
        $return['counts'] = $this->runSearch($search,  false, false, "count");

        return $return;
    }

    private function runSearch($search, $start, $limit, $type="list") {
        if ($limit == true) {
            $add = " LIMIT ".$start.", ".$limit;
        } else {
            $add = "";
        }

        if (strpos(strtolower($search), "lh") !== false) {
            return $this->query("SELECT * FROM `".table_prefix.table_name_prefix."patient` WHERE `ref` = :search  ORDER BY `last_name`, `ref` DESC".$add, array(':search' => $this->idFromPatientNumber($search)), $type);
        }

        return $this->query("SELECT * FROM `".table_prefix.table_name_prefix."patient` WHERE (`last_name` LIKE :search OR `first_name` LIKE :search OR `sex` LIKE :search OR `phone_number` LIKE :search OR `email` LIKE :search) ORDER BY `last_name` ASC".$add, array(':search' => "%".$search."%"), $type);

    }

    public function get($page=1)  {
        global $settings;

        if (intval($page) == 0) {
            $page = 1;
        }
        $current = (intval($page) > 0) ? (intval($page)-1) : 0;
        $limit = intval($settings->get("resultPerPage"));
        $start = $current*$limit;

        $this->successResponse;
        if ($this->id > 0) {
            $data = $this->listOne($this->id);
            if ($data) {
                $this->successResponse['data'] = $this->formatResult( $data, true );
                return $this->successResponse;
            } else {
                return $this->notFound;
            }
        } else {
            if ($this->filter != null ) {
                if ($this->filter == "list" ) {
                    $result = $this->listPages($start, $limit);
                } else if ($this->filter == "search" ) {
                    if ($this->search !== null) {
                    $result = $this->search($this->search, $start, $limit);
                    } else {
                        $result['counts'] = 0;
                    }
                } else {
                    return $this->NotAcceptable;
                }

                if ($result['counts'] > 0) {
                    $this->successResponse['counts']['currentPage'] = intval($page);
                    $this->successResponse['counts']['totalPage'] = ceil($result['counts']/$limit);
                    $this->successResponse['counts']['rowOnCurrentPage'] = count($result['data']);
                    $this->successResponse['counts']['maxRowPerPage'] = intval($limit);
                    $this->successResponse['counts']['totalRows'] = $result['counts'];
                    $this->successResponse['counts']['prevRow'] = (intval($page) * intval($limit)) - intval($limit);
                    $this->successResponse['data'] = $this->formatResult( $result['data'] );
                } else {
                    $this->successResponse['data'] = [];
                }

                return $this->successResponse;
            } else {
                return $this->NotAcceptable;
            }
        }

    }

    public function formatResult($data, $single=false, $mini=false) {
        if ($single == false) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i] = $this->clean($data[$i], $mini);
            }
        } else {
            $data = $this->clean($data, $mini);
        }
        return $data;
    }

    private function clean($data, $mini) {
        global $admin;
        global $appointments;
        global $billing;
        global $invoice;
        global $vitals;

        $pendingInvoice = false;

        $upcoming = $appointments->get_upcoming($data['ref']);

        $billing->get_due_invoice($data['ref']);
        
        $return['ref'] = intval($data['ref']);
        $return['patienrNumber'] = $this->patienrNumber( $data['ref'] );
        $return['lastName'] = $data['last_name'];
        $return['firstName'] = $data['first_name'];
        $return['age'] = $data['age'];
        $return['sex'] = $data['sex'];
        $return['phoneNumber'] = $data['phone_number'];
        $return['email'] = $data['email'];
        if ($mini === false) {
            $return['address'] = $data['address'];
            $return['kin']['name'] = $data['next_of_Kin'];
            $return['kin']['contact'] = $data['next_of_contact'];
            $return['kin']['address'] = $data['next_of_address'];
            $return['allergies'] = $data['allergies'];
            $return['type'] = $data['p_type'];
            $return['appointments'] = $appointments->formatResult( $appointments->getSortedList( $data['ref'], "patient_id"), false, true );
            $return['invoice'] = $invoice->formatResult( $invoice->getSortedList( $data['ref'], "patient_id", false, false, false, false, "ref", "desc"), false, true );
            $return['vitals'] = $vitals->formatResult( $vitals->recent_vital($data['ref']), true);
            $return['medication'] = [];
            $return['notification'] = [];

            if ($billing->balance > 0) {
                $return['notification'][] = array(
                    'type' => "invoice",
                    'alert' => "danger",
                    'count' => count($billing->list_invoice),
                    'details' => count($billing->list_invoice) . " pending ". $this->addS( "payment", count($billing->list_invoice)) ." of &#8358; ".number_format($billing->balance)." is due"
                ); 
                $pendingInvoice = true;
            }
            if (count($upcoming) > 0) {
                $return['notification'][] = array(
                    'type' => "appointment",
                    'alert' => "info",
                    'count' => count($upcoming),
                    'details' => count($upcoming) . " upcoming ". $this->addS( "appointment", count($upcoming))
                ); 
            }
            $return['flags']['pendingInvoice'] = $pendingInvoice;
            $return['createdBy'] = $admin->formatResult( $admin->listOne( $data['create_by']), true, true );
            $return['date']['created'] = $data['create_time'];
            $return['date']['modified'] = $data['modify_time'];
        }

        return $return;
    }

    private function validateInput($input, $type) {
        if (!$this->CheckValidate($input, $this->allowedFields[$type])) {
            return false;
        }
        return $input;
    }
}
?>