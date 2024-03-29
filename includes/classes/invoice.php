<?php
class invoice extends common {
    public $id;
    public $minify = false;
    public $filter = null;
    public $search = null;

    protected $allowedFields = array(
        "add" => array(
            "patient_id",
            "billing_component"
        ),
        "edit" => array(
            "ref",
            "billing_component"
        )
    );

    public function create($array) {
        global $billing;
        global $billing_component;

        $array['added_by'] = $this->admin_id;

        if (!$this->validateInput($array, "add")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        $invData['amount'] = $invData['due'] = 0;
        $invData['patient_id'] = $array['patient_id'];
        if (isset($array['due_date'])) {
            $invData['due_date'] = $array['due_date'];
        }
        $invData['create_by'] = $array['added_by'];

        $this->id = $this->insert(table_name_prefix."invoice", $invData);

        if ($this->id > 0) {
            $data['invoice_id'] = $this->id;
            $data['patient_id'] = $array['patient_id'];
            $data['added_by'] = $array['added_by'];

            $amount = 0;

            for ($i = 0; $i < count($array['billing_component']); $i++) {
                $data['billing_component_id'] = $array['billing_component'][$i]['id'];
                $data['cost'] = $billing_component->getSingle( $array['billing_component'][$i]['id'], "cost", "ref");
                $data['quantity'] = $array['billing_component'][$i]['quantity'];
                $data['description'] = $array['billing_component'][$i]['description'];


                $amount = $amount+$data['quantity']*$data['cost'];

                
                $billing->create($data);
            }

            $this->updateOne(table_name_prefix."invoice", "amount", $amount, $this->id);
            $this->updateOne(table_name_prefix."invoice", "due", $amount, $this->id);
            //semd email

            $this->successResponse['data'] = $this->formatResult( $this->listOne( $this->id), true );
            return $this->successResponse;
        } else {
            return $this->internalServerError;
        }
    }

    public function edit($array) {
        global $billing;
        global $billing_component;

        $array['added_by'] = $this->admin_id;

        if (!$this->validateInput($array, "edit")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        $fetch = $this->listOne($array['ref']);

        if ($fetch > 0) {
            $data['invoice_id'] = $fetch['ref'];
            $data['patient_id'] = $fetch['patient_id'];
            $data['added_by'] = $array['added_by'];

            $amount = 0;

            $this->delete(table_name_prefix."billing", $fetch['ref'], "invoice_id");

            for ($i = 0; $i < count($array['billing_component']); $i++) {
                $data['billing_component_id'] = $array['billing_component'][$i]['id'];
                $data['cost'] = $billing_component->getSingle( $array['billing_component'][$i]['id'], "cost", "ref");
                $data['quantity'] = $array['billing_component'][$i]['quantity'];
                $data['description'] = $array['billing_component'][$i]['description'];


                $amount = $amount+$data['quantity']*$data['cost'];

                
                $billing->create($data);
            }

            $this->updateOne(table_name_prefix."invoice", "amount", $amount, $fetch['ref']);
            $this->updateOne(table_name_prefix."invoice", "due", $amount, $fetch['ref']);
            //semd email

            $this->successResponse['data'] = $this->formatResult( $fetch, true );
            return $this->successResponse;
        } else {
            return $this->notFound;
        }
    }

    public function payInvoice($data) {
        global $billing;
        global $invoiceLog;
        if ($this->query("UPDATE ".table_prefix.table_name_prefix."invoice SET `due` = (`due`-".$data['amount'].") WHERE `ref` = ".$data['ref'])) {
            $this->modifyOne("status", "PARTIALLY-PAID", $data['ref']);
            $billing->modifyOne("status", "PARTIALLY-PAID", $data['ref'], "invoice_id");

            $getOne = self::listOne($data['ref']);

            if ($getOne['due'] <= 0) {
                $this->modifyOne("status", "PAID", $data['ref']);
                $billing->modifyOne("status", "PAID", $data['ref'], "invoice_id");
            }

            $array['create_by'] = $this->admin_id;
            $array['invoice_id'] = $data['ref'];
            $array['amount'] = $data['amount'];
            $invoiceLog->create( $array );

            //send reciept

            $this->successResponse['data'] = $this->formatResult( $this->listOne( $data['ref']), true );
            return $this->successResponse;
        } else {
            return $this->internalServerError;
        }
    }

    public function payMultiInvoice($multiData) {
        global $billing;
        global $invoiceLog;

        $success = 0;
        $error = 0;


        foreach ($multiData as $data) {
            if ($this->query("UPDATE ".table_prefix.table_name_prefix."invoice SET `due` = (`due`-".$data['amount'].") WHERE `ref` = ".$data['ref'])) {
                $this->modifyOne("status", "PARTIALLY-PAID", $data['ref']);
                $billing->modifyOne("status", "PARTIALLY-PAID", $data['ref'], "invoice_id");

                $getOne = self::listOne($data['ref']);

                if ($getOne['due'] <= 0) {
                    $this->modifyOne("status", "PAID", $data['ref']);
                    $billing->modifyOne("status", "PAID", $data['ref'], "invoice_id");
                }

                $array['create_by'] = $this->admin_id;
                $array['invoice_id'] = $data['ref'];
                $array['amount'] = $data['amount'];

                $invoiceLog->create( $array );

                //send reciept

                $this->successResponse['data'][] = $this->formatResult( $this->listOne( $data['ref']), true );
                $success++;
            } else {
                $this->successResponse['error'][] = $this->formatResult( $this->listOne( $data['ref']), true );
                $error++;
            }
        }

        $this->successResponse['additional_message'] = "Action completed. Modified: ".$success . ", Failed: " . $error;
        return $this->successResponse;
    }

    public function remove($id) {
        $data = $this->listOne($id);

        if ($data['status'] == "UN-PAID") {
            if ($this->delete(table_name_prefix."invoice", $id)) {
                return $this->successResponse;
            } else {
                return $this->internalServerError;
            }
        } else {
            $this->NotAcceptable['error']['message'] .= ". Invoice " . $this->invoiceNumber( $data['ref'] ) . " can not be deleted, You can only delete an Unpaid Invoice.";

            return $this->NotAcceptable;
        }
    }

    public function getPending() {
        return $this->query("SELECT * FROM ".table_prefix.table_name_prefix."invoice WHERE `status` != 'PAID'", false, "list");
    }

    public function modifyOne($tag, $value, $id, $ref="ref") {
        return $this->updateOne(table_name_prefix."invoice", $tag, $value, $id, $ref);
    }

    public function getList($start=false, $limit=false, $order="title", $dir="ASC", $type="list") {
        return $this->lists(table_name_prefix."invoice", $start, $limit, $order, $dir, "`status` != 'DELETED'", $type);
    }

    public function getSingle($name, $tag="title", $ref="ref") {
        return $this->getOneField(table_name_prefix."invoice", $name, $ref, $tag);
    }

    public function listOne($id) {
        return $this->getOne(table_name_prefix."invoice", $id, "ref");
    }

    public function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll(table_name_prefix."invoice", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
    }

    private function listPages($start, $limit) {

        if ($this->search !== null) {
            if (strtolower($this->search) == "paid") {
                $where = "`status` = 'PAID'";
            } else if (strtolower($this->search) == "partiallypaid") {
                $where = "`status` = 'PARTIALLY-PAID'";
            } else if (strtolower($this->search) == "unpaid") {
                $where = "`status` = 'UN-PAID'";
            } else {
                $where = false;
            }
            
        } else {
            $where = false;
        }
        $return['data'] = $this->lists(table_name_prefix."invoice", $start, $limit, "ref", "DESC", $where);
        $return['counts'] = $this->lists(table_name_prefix."invoice",  false, false, "ref", "DESC", $where, "count");

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

        if (strpos(strtolower($search), "inv") !== false) {
            return $this->query("SELECT * FROM `".table_prefix.table_name_prefix."invoice` WHERE `ref` = :search  ORDER BY `due_date`, `ref` DESC".$add, array(':search' => $this->idFromInvoiceNumber($search)), $type);
        }

        return $this->query("SELECT * FROM `".table_prefix.table_name_prefix."invoice` WHERE (`ref` LIKE :search OR `amount` LIKE :search OR `due` LIKE :search OR `due_date` LIKE :search OR `status` LIKE :search OR `patient_id` IN (SELECT `ref` FROM `".table_prefix.table_name_prefix."patient` WHERE (`last_name` LIKE :search OR `first_name` LIKE :search OR `sex` LIKE :search OR `phone_number` LIKE :search OR `email` LIKE :search))) ORDER BY `due_date`, `ref` DESC".$add, array(':search' => "%".$search."%"), $type);
    }

    public function getComponent() {
        global $billing_component;
        $data = $billing_component->getActive();

        $this->successResponse['data'] = $billing_component->formatResult( $data );
        return $this->successResponse;
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
        global $patient;
        global $admin;
        global $billing;
        global $invoiceLog; 
        
        $data['ref'] = intval($data['ref']);
        $data['invoiceNumber'] = $this->invoiceNumber($data['ref']);
        $data['patient'] = $patient->formatResult( $patient->listOne( $data['patient_id'] ), true, true);
        
        $amount['value'] = floatval($data['amount']);
        $amount['label'] = "&#8358;".number_format( $data['amount'] );
        $data['amount'] = $amount;

        $due['value'] = floatval($data['due']);
        $due['label'] = "&#8358;".number_format( $data['due'] );
        $data['due'] = $due;

        $data['payments'] = $invoiceLog->formatResult( $invoiceLog->getList($data['ref']), false, true );

        $status['unPaid'] = ("UN-PAID" == $data['status']) ? true : false;
        $status['partiallyPaid'] = ("PARTIALLY-PAID" == $data['status']) ? true : false;
        $status['paid'] = ("PAID" == $data['status']) ? true : false;
        $data['status'] = $status;

        if ($mini === false) {
            $data['invoiceComponent'] = $billing->formatResult( $billing->getSortedList($data['ref'], "invoice_id"), false, true );
            $data['createdBy'] = $admin->formatResult( $admin->listOne( $data['create_by'] ), true, true);

        }
        $data['date']['due'] = $data['due_date'];
        $data['date']['created'] = $data['create_time'];
        $data['date']['modified'] = $data['modify_time'];
        
        unset($data['create_by']);
        unset($data['patient_id']);
        unset($data['due_date']);
        unset($data['create_time']);
        unset($data['modify_time']);
        
        return $data;
    }

    private function validateInput($input, $type) {
        if (!$this->CheckValidate($input, $this->allowedFields[$type])) {
            return false;
        }
        return $input;
    }
}
?>