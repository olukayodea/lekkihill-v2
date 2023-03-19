<?php
class clinic extends common {
    public $id;
    public $patient_id;
    public $filter = null;

    protected $allowedFields = array(
        "vitals" => array(
            "patient_id",
            "weight",
            "height",
            "spo2",
            "respiratory",
            "temprature",
            "pulse",
            "bp_sys",
            "bp_dia"
        ),
        "fluid_balance" => array(
            "patient_id",
            "iv_fluid",
            "amount",
            "oral_fluid",
            "ng_tube_feeding",
            "vomit",
            "urine",
            "drains",
            "ng_tube_drainage"
        ),
        "medication" => array(
            "patient_id",
            "doctors_report_id",
            "quantity",
            "route",
            "medication",
            "inventory_id",
            "dose",
            "frequency",
            "sales_status"
        ),
        "post_op" => array(
            "patient_id",
            "surgery",
            "surgery_category",
            "indication",
            "surgeon",
            "asst_surgeon",
            "per_op_nurse",
            "circulating_nurse",
            "anaesthesia",
            "anaesthesia_time",
            "knife_on_skin",
            "infiltration_time",
            "liposuction_time",
            "end_of_surgery",
            "procedure",
            "amt_of_fat_right",
            "amt_of_fat_left",
            "amt_of_fat_other",
            "ebl",
            "plan"
        ),
        "notes" => array(
            "patient_id",
            "report"
        ),
        "edit" => array(
            "ref",
            "invoice_id",
            "billing_component_id",
            "quantity",
            "description",
            "patient_id",
            "cost"
        ),
        "lab" => array(
            "patient_id",
            "doctors_report_id",
            "category_id"
        )
    );

    private function cleanMessage($meesgae, $int=false) {
        return ("" != trim($meesgae)) ? nl2br($meesgae) : "Not Available";
    }

    public function add_notes($array) {
        global $clinic_doctors_report;

        if (!$this->validateInput($array, "notes")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        $array['added_by'] = $this->admin_id;

        $add = $clinic_doctors_report->create($array);
        if ($add) {
            $this->successResponse['data'] = $clinic_doctors_report->formatResult( $clinic_doctors_report->listOne( $add), true );
            return $this->successResponse;
        } else {
            return $this->internalServerError;
        }
    }

    public function add_medication($array) {
        global $clinic_medication;

        if (!$this->validateInput($array, "medication")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        $array['added_by'] = $this->admin_id;

        $add = $clinic_medication->create($array);
        if ($add) {
            $this->successResponse['data'] = $clinic_medication->formatResult( $clinic_medication->listOne( $add), true );
            return $this->successResponse;
        } else {
            return $this->internalServerError;
        }
    }

    public function add_lab($array) {
        global $clinic_lab;

        if (!$this->validateInput($array, "lab")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        $array['added_by'] = $this->admin_id;

        $add = $clinic_lab->create($array);
        if ($add) {
            $this->successResponse['data'] = $clinic_lab->formatResult( $clinic_lab->listOne( $add), true );
            return $this->successResponse;
        } else {
            return $this->internalServerError;
        }
    }

    public function add_fluid_balanceion($array) {
        global $clinic_fluid_balance;

        if (!$this->validateInput($array, "fluid_balance")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        $array['added_by'] = $this->admin_id;

        $add = $clinic_fluid_balance->create($array);
        if ($add) {
            $this->successResponse['data'] = $clinic_fluid_balance->formatResult( $clinic_fluid_balance->listOne( $add), true );
            return $this->successResponse;
        } else {
            return $this->internalServerError;
        }
    }

    public function add_post_op($array) {
        global $clinic_post_op;

        if (!$this->validateInput($array, "post_op")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        $array['added_by'] = $this->admin_id;

        $add = $clinic_post_op->create($array);
        if ($add) {
            $this->successResponse['data'] = $clinic_post_op->formatResult( $clinic_post_op->listOne( $add), true );
            return $this->successResponse;
        } else {
            return $this->internalServerError;
        }
    }

    public function add_vitals($array) {
        global $vitals;

        if (!$this->validateInput($array, "vitals")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        $array['added_by'] = $this->admin_id;
        $array['bmi'] =number_format((float) ($array['weight']/(($array['height']/100)*($array['height']/100))), 2, '.', '');

        $add = $vitals->create($array);
        if ($add) {
            $this->successResponse['data'] = $vitals->formatResult( $vitals->listOne( $add), true );
            return $this->successResponse;
        } else {
            return $this->internalServerError;
        }
    }

    public function getNotes($page = 1)
    {
        global $clinic_doctors_report;
        global $settings;

        if (intval($page) == 0) {
            $page = 1;
        }
        $current = (intval($page) > 0) ? (intval($page) - 1) : 0;
        $limit = intval($settings->get("resultPerPage"));
        $start = $current * $limit;

        $this->successResponse;
        
        if ($this->patient_id > 0) {
            if ($this->filter != null) {
                if ($this->filter == "recent") { 
                    $data = $clinic_doctors_report->recent_note($this->patient_id);
                    if ($data) {
                        $this->successResponse['data'] = $clinic_doctors_report->formatResult($data, true);
                        return $this->successResponse;
                    } else {
                        return $this->notFound;
                    }
                } else {
                    return $this->NotAcceptable;
                }
            } else {
                $clinic_doctors_report->patient_id = $this->patient_id;
                $result = $clinic_doctors_report->listPages($start, $limit);
                if ($result['counts'] > 0) {
                    $this->successResponse['counts']['currentPage'] = intval($page);
                    $this->successResponse['counts']['totalPage'] = ceil($result['counts'] / $limit);
                    $this->successResponse['counts']['rowOnCurrentPage'] = count($result['data']);
                    $this->successResponse['counts']['maxRowPerPage'] = intval($limit);
                    $this->successResponse['counts']['totalRows'] = $result['counts'];
                    $this->successResponse['counts']['prevRow'] = (intval($page) * intval($limit)) - intval($limit);
                    $this->successResponse['data'] = $clinic_doctors_report->formatResult($result['data'], false);
                } else {
                    $this->successResponse['data'] = [];
                }
                return $this->successResponse;
            }
        } else {
            return $this->NotAcceptable;
        } 
    }

    public function getVitals($page = 1)
    {
        global $vitals;
        global $settings;

        if (intval($page) == 0) {
            $page = 1;
        }
        $current = (intval($page) > 0) ? (intval($page) - 1) : 0;
        $limit = intval($settings->get("resultPerPage"));
        $start = $current * $limit;

        $this->successResponse;
        
        if ($this->patient_id > 0) {
            if ($this->filter != null) {
                if ($this->filter == "recent") { 
                    $data = $vitals->recent_vital($this->patient_id);
                    if ($data) {
                        $this->successResponse['data'] = $vitals->formatResult($data, true);
                        return $this->successResponse;
                    } else {
                        return $this->notFound;
                    }
                } else {
                    return $this->NotAcceptable;
                }
            } else {
                $vitals->patient_id = $this->patient_id;
                $result = $vitals->listPages($start, $limit);
                if ($result['counts'] > 0) {
                    $this->successResponse['counts']['currentPage'] = intval($page);
                    $this->successResponse['counts']['totalPage'] = ceil($result['counts'] / $limit);
                    $this->successResponse['counts']['rowOnCurrentPage'] = count($result['data']);
                    $this->successResponse['counts']['maxRowPerPage'] = intval($limit);
                    $this->successResponse['counts']['totalRows'] = $result['counts'];
                    $this->successResponse['counts']['prevRow'] = (intval($page) * intval($limit)) - intval($limit);
                    $this->successResponse['data'] = $vitals->formatResult($result['data'], false);
                } else {
                    $this->successResponse['data'] = [];
                }
                return $this->successResponse;
            }
        } else {
            return $this->NotAcceptable;
        } 
    }

    public function getFluidBalance($page = 1)
    {
        global $clinic_fluid_balance;
        global $settings;

        if (intval($page) == 0) {
            $page = 1;
        }
        $current = (intval($page) > 0) ? (intval($page) - 1) : 0;
        $limit = intval($settings->get("resultPerPage"));
        $start = $current * $limit;

        $this->successResponse;
        
        if ($this->patient_id > 0) {
            if ($this->filter != null) {
                if ($this->filter == "recent") { 
                    $data = $clinic_fluid_balance->recent_fluid_balance($this->patient_id);
                    if ($data) {
                        $this->successResponse['data'] = $clinic_fluid_balance->formatResult($data, true);
                        return $this->successResponse;
                    } else {
                        return $this->notFound;
                    }
                } else {
                    return $this->NotAcceptable;
                }
            } else {
                $clinic_fluid_balance->patient_id = $this->patient_id;
                $result = $clinic_fluid_balance->listPages($start, $limit);
                if ($result['counts'] > 0) {
                    $this->successResponse['counts']['currentPage'] = intval($page);
                    $this->successResponse['counts']['totalPage'] = ceil($result['counts'] / $limit);
                    $this->successResponse['counts']['rowOnCurrentPage'] = count($result['data']);
                    $this->successResponse['counts']['maxRowPerPage'] = intval($limit);
                    $this->successResponse['counts']['totalRows'] = $result['counts'];
                    $this->successResponse['counts']['prevRow'] = (intval($page) * intval($limit)) - intval($limit);
                    $this->successResponse['data'] = $clinic_fluid_balance->formatResult($result['data'], false);
                } else {
                    $this->successResponse['data'] = [];
                }
                return $this->successResponse;
            }
        } else {
            return $this->NotAcceptable;
        } 
    }

    public function getPostOp($page = 1)
    {
        global $clinic_post_op;
        global $settings;

        if (intval($page) == 0) {
            $page = 1;
        }
        $current = (intval($page) > 0) ? (intval($page) - 1) : 0;
        $limit = intval($settings->get("resultPerPage"));
        $start = $current * $limit;

        $this->successResponse;
        
        if ($this->patient_id > 0) {
            if ($this->filter != null) {
                if ($this->filter == "recent") { 
                    $data = $clinic_post_op->recent_post_op($this->patient_id);
                    if ($data) {
                        $this->successResponse['data'] = $clinic_post_op->formatResult($data, true);
                        return $this->successResponse;
                    } else {
                        return $this->notFound;
                    }
                } else {
                    return $this->NotAcceptable;
                }
            } else {
                $clinic_post_op->patient_id = $this->patient_id;
                $result = $clinic_post_op->listPages($start, $limit);
                if ($result['counts'] > 0) {
                    $this->successResponse['counts']['currentPage'] = intval($page);
                    $this->successResponse['counts']['totalPage'] = ceil($result['counts'] / $limit);
                    $this->successResponse['counts']['rowOnCurrentPage'] = count($result['data']);
                    $this->successResponse['counts']['maxRowPerPage'] = intval($limit);
                    $this->successResponse['counts']['totalRows'] = $result['counts'];
                    $this->successResponse['counts']['prevRow'] = (intval($page) * intval($limit)) - intval($limit);
                    $this->successResponse['data'] = $clinic_post_op->formatResult($result['data'], false);
                } else {
                    $this->successResponse['data'] = [];
                }
                return $this->successResponse;
            }
        } else {
            return $this->NotAcceptable;
        } 
    }

    public function getMedication($page = 1)
    {
        global $clinic_medication;
        global $settings;

        if (intval($page) == 0) {
            $page = 1;
        }
        $current = (intval($page) > 0) ? (intval($page) - 1) : 0;
        $limit = intval($settings->get("resultPerPage"));
        $start = $current * $limit;

        $this->successResponse;
        
        if ($this->patient_id > 0) {
            if ($this->filter != null) {
                if ($this->filter == "recent") { 
                    $data = $clinic_medication->recent_medication($this->patient_id);
                    if ($data) {
                        $this->successResponse['data'] = $clinic_medication->formatResult($data, true);
                        return $this->successResponse;
                    } else {
                        return $this->notFound;
                    }
                } else {
                    return $this->NotAcceptable;
                }
            } else {
                $clinic_medication->patient_id = $this->patient_id;
                $result = $clinic_medication->listPages($start, $limit);
                if ($result['counts'] > 0) {
                    $this->successResponse['counts']['currentPage'] = intval($page);
                    $this->successResponse['counts']['totalPage'] = ceil($result['counts'] / $limit);
                    $this->successResponse['counts']['rowOnCurrentPage'] = count($result['data']);
                    $this->successResponse['counts']['maxRowPerPage'] = intval($limit);
                    $this->successResponse['counts']['totalRows'] = $result['counts'];
                    $this->successResponse['counts']['prevRow'] = (intval($page) * intval($limit)) - intval($limit);
                    $this->successResponse['data'] = $clinic_medication->formatResult($result['data'], false);
                } else {
                    $this->successResponse['data'] = [];
                }
                return $this->successResponse;
            }
        } else {
            return $this->NotAcceptable;
        } 
    }

    public function getLab($page = 1)
    {
        global $clinic_lab;
        global $settings;

        if (intval($page) == 0) {
            $page = 1;
        }
        $current = (intval($page) > 0) ? (intval($page) - 1) : 0;
        $limit = intval($settings->get("resultPerPage"));
        $start = $current * $limit;

        $this->successResponse;
        
        if ($this->patient_id > 0) {
            if ($this->filter != null) {
                if ($this->filter == "recent") { 
                    $data = $clinic_lab->recent_lab($this->patient_id);
                    if ($data) {
                        $this->successResponse['data'] = $clinic_lab->formatResult($data, true);
                        return $this->successResponse;
                    } else {
                        return $this->notFound;
                    }
                } else {
                    return $this->NotAcceptable;
                }
            } else {
                $clinic_lab->patient_id = $this->patient_id;
                $result = $clinic_lab->listPages($start, $limit);
                if ($result['counts'] > 0) {
                    $this->successResponse['counts']['currentPage'] = intval($page);
                    $this->successResponse['counts']['totalPage'] = ceil($result['counts'] / $limit);
                    $this->successResponse['counts']['rowOnCurrentPage'] = count($result['data']);
                    $this->successResponse['counts']['maxRowPerPage'] = intval($limit);
                    $this->successResponse['counts']['totalRows'] = $result['counts'];
                    $this->successResponse['counts']['prevRow'] = (intval($page) * intval($limit)) - intval($limit);
                    $this->successResponse['data'] = $clinic_lab->formatResult($result['data'], false);
                } else {
                    $this->successResponse['data'] = [];
                }
                return $this->successResponse;
            }
        } else {
            return $this->NotAcceptable;
        } 
    }

    public function getDoctorsReport($page = 1)
    {
        global $clinic_doctors_report;
        global $settings;

        if (intval($page) == 0) {
            $page = 1;
        }
        $current = (intval($page) > 0) ? (intval($page) - 1) : 0;
        $limit = intval($settings->get("resultPerPage"));
        $start = $current * $limit;

        $this->successResponse;
        
        if ($this->patient_id > 0) {
            if ($this->filter != null) {
                if ($this->filter == "recent") { 
                    $data = $clinic_doctors_report->recent_note($this->patient_id);
                    if ($data) {
                        $this->successResponse['data'] = $clinic_doctors_report->formatResult($data, true);
                        return $this->successResponse;
                    } else {
                        return $this->notFound;
                    }
                } else {
                    return $this->NotAcceptable;
                }
            } else {
                $clinic_doctors_report->patient_id = $this->patient_id;
                $result = $clinic_doctors_report->listPages($start, $limit);
                if ($result['counts'] > 0) {
                    $this->successResponse['counts']['currentPage'] = intval($page);
                    $this->successResponse['counts']['totalPage'] = ceil($result['counts'] / $limit);
                    $this->successResponse['counts']['rowOnCurrentPage'] = count($result['data']);
                    $this->successResponse['counts']['maxRowPerPage'] = intval($limit);
                    $this->successResponse['counts']['totalRows'] = $result['counts'];
                    $this->successResponse['counts']['prevRow'] = (intval($page) * intval($limit)) - intval($limit);
                    $this->successResponse['data'] = $clinic_doctors_report->formatResult($result['data'], false);
                } else {
                    $this->successResponse['data'] = [];
                }
                return $this->successResponse;
            }
        } else {
            return $this->NotAcceptable;
        } 
    }

    private function drawVitals() {
        $data = $this->query("SELECT AVG(`weight`) as `weight`, AVG(`bmi`) as `bmi`, AVG(`bp_sys`) as `bp_sys`, AVG(`bp_dia`) as `bp_dia`, AVG(`pulse`) as `pulse`, DATE_FORMAT(`create_time`,'%d-%m-%Y') AS `testDate` FROM `wp_lekkihill_vitals` WHERE `patient_id` = ".$this->patient_id." GROUP BY `testDate` ORDER BY `create_time` ASC LIMIT 30;", false, "list");

        $label = array();
        $weight = array();
        $bmi = array();
        $bp_sys = array();
        $bp_dia = array();
        $pulse = array();
        $return = array();

        if ($data) {
            foreach($data as $row) {
                $label[] = $row['testDate'];
                $weight[] = intval($row['weight']);
                $bmi[] = intval($row['bmi']);
                $bp_sys[] = intval($row['bp_sys']);
                $bp_dia[] = intval($row['bp_dia']);
                $pulse[] = intval($row['pulse']);
            }
        }

        $return['label'] = $label;
        $return['weight'] = $weight;
        $return['bmi'] = $bmi;
        $return['sys'] = $bp_sys;
        $return['dia'] = $bp_dia;
        $return['pulse'] = $pulse;

        return $return;
    }

    public function get() {
        global $patient;
        $patientData = $patient->listOne($this->patient_id);

        if ($patientData) {
            $this->filter = "recent";
            $data = $patient->formatResult( $patientData, true, true);
            $vitals = $this->getDataFromResponse( $this->getVitals() );
            $fluidBalance = $this->getDataFromResponse( $this->getFluidBalance() );
            $postOp = $this->getDataFromResponse( $this->getPostOp() );
            $medications = $this->getDataFromResponse( $this->getMedication() );
            $doctorsReport = $this->getDataFromResponse( $this->getDoctorsReport() );
            $labouratory = $this->getDataFromResponse( $this->getLab() );;
            $vitalsGraph = $this->drawVitals();
            

            $this->successResponse['data'] = $data;
            $this->successResponse['vitals'] = $vitals;
            $this->successResponse['fluidBalance'] = $fluidBalance;
            $this->successResponse['postOp'] = $postOp;
            $this->successResponse['medications'] = $medications;
            $this->successResponse['doctorsReport'] = $doctorsReport;
            $this->successResponse['labouratory'] = $labouratory;
            $this->successResponse['vitalsGraph'] = $vitalsGraph;
            return $this->successResponse;
        } else {
            return $this->notFound;
        }

    }

    private function getDataFromResponse ( $data ) {
        return $data['data'];
    }

    private function validateInput($input, $type) {
        if (!$this->CheckValidate($input, $this->allowedFields[$type])) {
            return false;
        }
        return $input;
    }
}
?>