<?php
class appointments extends common {
    public $id;
    public $minify = false;
    public $filter = null;
    public $search = null;

    protected $allowedFields = array(
        "add" => array(
            "names",
            "email",
            "phone",
            "location",
            "procedure"
        ),
        "edit" => array(
            "ref",
            "names",
            "email",
            "phone",
            "procedure"
        ),
        "schedule" => array(
            "ref",
            "appointmentDate"
        )
    );

    public function create($array) {
        global $patient;
        if (!$this->validateInput($array, "add")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        if ($array['next_appointment']) {
            $array['status'] = "SCHEDULED";
        }

        $checkPatient = $patient->checkAccount($array['email']);
        if ($checkPatient['ref'] > 0) {
            $array['patient_id'] = $checkPatient['ref'];
        }

        $id = $this->insert(table_name_prefix."appointments", $array);

        if ($id) {
            $this->successResponse['data'] = $this->formatResult( $this->listOne( $id), true );
            return $this->successResponse;
        } else {
            return $this->internalServerError;
        }
    }

    public function edit($array) {
        global $patient;
        if (!$this->validateInput($array, "add")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        if ($array['next_appointment']) {
            $array['status'] = "SCHEDULED";
        }

        $checkPatient = $patient->checkAccount($array['email']);
        if ($checkPatient['ref'] > 0) {
            $array['patient_id'] = $checkPatient['ref'];
        }

        $ref = $array['ref'];
        if ( $this->update(table_name_prefix."appointments", $array, ["ref" => $ref])) {

            $this->successResponse['data'] = $this->formatResult( $this->listOne( $ref), true );
            return $this->successResponse;
        } else {
            return $this->NotModified;
        }
    }

    public function modifyOne($tag, $value, $id, $ref="ref") {
        return $this->updateOne(table_name_prefix."appointments", $tag, $value, $id, $ref);
    }
    
    public function getList($start=false, $limit=false, $order="last_name", $dir="ASC", $type="list") {
        return $this->lists(table_name_prefix."appointments", $start, $limit, $order, $dir, false, $type);
    }

    public function getSingle($name, $tag="last_name", $ref="ref") {
        return $this->getOneField(table_name_prefix."appointments", $name, $ref, $tag);
    }

    public function listOne($id) {
        return $this->getOne(table_name_prefix."appointments", $id, "ref");
    }

    public function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll(table_name_prefix."appointments", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
    }

    public function removeNew($id) {
        $data = $this->listOne($id);

        if ($data['status'] == "NEW") {
            if ($this->delete(table_name_prefix."appointments", $id)) {
                return $this->successResponse;
            } else {
                return $this->internalServerError;
            }
        } else {
            $this->NotAcceptable['error']['message'] .= ". Appointment can not be deleted, You can only delete an Unscheduled Appointments.";

            return $this->NotAcceptable;
        }
    }

    public function cancel() {
        $data = $this->listOne($this->id);

        if ($data['status'] == "SCHEDULED") {
            if ($this->modifyOne("status", "CANCELLED", $this->id)) {
                return $this->successResponse;
            } else {
                return $this->internalServerError;
            }
        } else {
            $this->NotAcceptable['error']['message'] .= ". Appointment can not be cancelled, You can only cancel a scheduled Appointments.";

            return $this->NotAcceptable;
        }
    }

    public function schedule( $array ) {

        if (!$this->validateInput($array, "schedule")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        $data = $this->listOne($array['ref']);
        
        if ($data['status'] != "CANCELLED") {
            if ($this->modifyOne("status", "SCHEDULED", $array['ref'])) {
                $this->modifyOne("next_appointment", $array['appointmentDate'], $array['ref']);

                // send email 
                $this->successResponse['data'] = $this->formatResult( $data, true );
                return $this->successResponse;
            } else {
                return $this->internalServerError;
            }
        } else {
            $this->NotAcceptable['error']['message'] .= ". Appointment can not be scheduled, You can only not rechedyle a cancelled appointment.";

            return $this->NotAcceptable;
        }
    }

    private function viewPages($start, $limit) {
        if ($this->search !== null) {
            if (strtolower($this->search) == "today") {
                $from = date("Y-m-d 00:00:00");
                $to = date("Y-m-d 23:59:59");
                $where = "`status` = 'SCHEDULED' AND `next_appointment` BETWEEN '".$from."' AND '".$to."'";
            } else if (strtolower($this->search) == "past") {
                $to = date("Y-m-d H:i:s", time());
                $where = "`status` = 'SCHEDULED' AND `next_appointment` < '".$to."'";
            } else if (strtolower($this->search) == "upcoming") {
                $to = date("Y-m-d H:i:s", time());
                $where = "`status` = 'SCHEDULED' AND `next_appointment` > '".$to."'";
            } else {
                return false;
            }
            
        } else {
           return false;
        }
        $return['data'] = $this->lists(table_name_prefix."appointments", $start, $limit, "ref", "DESC", $where);
        $return['counts'] = $this->lists(table_name_prefix."appointments",  false, false, "ref", "DESC", $where, "count");

        return $return;
    }

    private function listPages($start, $limit) {
        $to = date("Y-m-d H:i:s", time());
        if ($this->search !== null) {
            if (strtolower($this->search) == "new") {
                $where = "`status` = 'NEW'";
            } else if (strtolower($this->search) == "scheduled") {
                $where = "`status` = 'SCHEDULED' AND `next_appointment` > '".$to."'";
            } else if (strtolower($this->search) == "cancelled") {
                $where = "`status` = 'CANCELLED'";
            } else {
                $where = false;
            }
            
        } else {
            $where = false;
        }
        $return['data'] = $this->lists(table_name_prefix."appointments", $start, $limit, "ref", "DESC", $where);
        $return['counts'] = $this->lists(table_name_prefix."appointments",  false, false, "ref", "DESC", $where, "count");

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
            return $this->query("SELECT * FROM `".table_prefix.table_name_prefix."appointments` WHERE `patient_id` = :search  ORDER BY `names`, `ref` DESC".$add, array(':search' => $this->idFromPatientNumber($search)), $type);
        }

        return $this->query("SELECT * FROM `".table_prefix.table_name_prefix."appointments` WHERE (`names` LIKE :search OR `email` LIKE :search OR `phone` LIKE :search OR `location` LIKE :search OR `procedure` LIKE :search) ORDER BY `names` ASC".$add, array(':search' => "%".$search."%"), $type);

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
                if ($this->filter == "view" ) {
                    if ($this->search !== null) {
                        $result = $this->viewPages($start, $limit);
                    } else {
                        return $this->NotAcceptable;
                    }
                } else if ($this->filter == "list" ) {
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

    public function formatResult($data, $single=false) {
        if ($single == false) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i] = $this->clean($data[$i]);
            }
        } else {
            $data = $this->clean($data);
        }
        return $data;
    }

    private function clean($data) {
        global $admin;
        global $patient;
        $admin->minify = true;
        $patient->minify = true;
        $return['ref'] = intval($data['ref']);
        $return['returning'] = ($data['patient_id'] > 0) ? true : false;
        $return['names'] = $data['names'];
        $return['email'] = $data['email'];
        $return['phone'] = $data['phone'];
        $return['location'] = $data['location'];
        $return['procedure'] = $data['procedure'];
        $return['message'] = $data['message'];

        $status['new'] = ("NEW" == $data['status']) ? true : false;
        $status['scheduled'] = ("SCHEDULED" == $data['status']) ? true : false;
        $status['cancelled'] = ("CANCELLED" == $data['status']) ? true : false;
        $status['passed'] = (time() > strtotime( $data['next_appointment'])) ? true : false;
        $return['status'] = $status;
        if ($this->minify === false) {
            $return['patient'] = ($data['patient_id'] > 0) ? $patient->formatResult( $patient->listOne( $data['patient_id'] ), true) : [];
            $return['createdBy'] = $admin->formatResult( $admin->listOne( $data['create_by']), true );
            $return['lastModifiedBy'] = $admin->formatResult( $admin->listOne( $data['last_modify']), true );
            $return['date']['next'] = $data['next_appointment'];
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