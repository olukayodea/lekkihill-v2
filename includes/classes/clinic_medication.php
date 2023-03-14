<?php
class clinic_medication extends common {
    public $patient_id;
    function create($array) {
        global $inventory;
        $array['invoice_id'] = 0;
        if (intval($array['inventory_id']) > 0) {
            $array['medication'] = $inventory->getSingle($array['inventory_id']);
        }
        return $this->insert(table_name_prefix."clinic_medication", $array);
    }
    
    public function recent_medication ($id) {
        return $this->getSortedList($id, "patient_id", false, false, false, false, "ref", "DESC", "AND", false, 1)[0];
    }

    public function listPages($start, $limit)
    {
        $where = "`patient_id` = ". $this->patient_id;
        $return['data'] = $this->lists(table_name_prefix . "clinic_medication", $start, $limit, "ref", "DESC", $where);
        $return['counts'] = $this->lists(table_name_prefix . "clinic_medication",  false, false, "ref", "DESC", $where, "count");

        return $return;
    }

    function modifyOne($tag, $value, $id, $ref="ref") {
        return $this->updateOne(table_name_prefix."clinic_medication", $tag, $value, $id, $ref);
    }
    
    function getList($start=false, $limit=false, $order="ref", $dir="DESC", $type="list") {
        return $this->lists(table_name_prefix."clinic_medication", $start, $limit, $order, $dir, false, $type);
    }

    function getSingle($name, $tag="patient_id", $ref="ref") {
        return $this->getOneField(table_name_prefix."clinic_medication", $name, $ref, $tag);
    }

    function listOne($id) {
        return $this->getOne(table_name_prefix."clinic_medication", $id, "ref");
    }

    function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "DESC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll(table_name_prefix."clinic_medication", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
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

    public function clean($data) {
        global $admin;
        global $patient;

        $return['ref'] = intval($data['ref']);
        $return['doctorsReport'] = intval($data['doctors_report_id']);
        $return['invoice'] = intval($data['invoice_id'] );
        $return['quantity'] = intval($data['quantity']);
        $return['route'] = $data['route'];
        $return['medication'] = $data['medication'];
        $return['inventory'] = intval( $data['inventory_id']);
        $return['dose'] = $data['dose'];
        $return['frequency'] = $data['frequency'];

        $status['notSold'] = (0 == $data['sales_status']) ? true : false;
        $status['unPaid'] = (1 == $data['sales_status']) ? true : false;
        $status['paid'] = (2 == $data['sales_status']) ? true : false;
        $return['status'] = $status;

        $return['status'];
        
        $return['patient'] = $patient->formatResult( $patient->listOne( $data['patient_id'] ), true, true);
        $return['createdBy'] = $admin->formatResult( $admin->listOne( $data['added_by']), true, true );
        $return['date']['created'] = $data['create_time'];
        $return['date']['modified'] = $data['modify_time'];
        
        return $return;
    }
}
?>