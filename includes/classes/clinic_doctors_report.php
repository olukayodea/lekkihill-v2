<?php
class clinic_doctors_report extends common {
    public $patient_id;
    function create($array) {
        return self::insert(table_name_prefix."clinic_doctors_report", $array);
    }
    
    public function recent_note ($id) {
        return $this->getSortedList($id, "patient_id", false, false, false, false, "ref", "DESC", "AND", false, 1)[0];
    }

    public function listPages($start, $limit)
    {
        $where = "`patient_id` = ". $this->patient_id;
        $return['data'] = $this->lists(table_name_prefix . "clinic_doctors_report", $start, $limit, "ref", "DESC", $where);
        $return['counts'] = $this->lists(table_name_prefix . "clinic_doctors_report",  false, false, "ref", "DESC", $where, "count");

        return $return;
    }

    function modifyOne($tag, $value, $id, $ref="ref") {
        return self::updateOne(table_name_prefix."clinic_doctors_report", $tag, $value, $id, $ref);
    }
    
    function getList($start=false, $limit=false, $order="ref", $dir="DESC", $type="list") {
        return self::lists(table_name_prefix."clinic_doctors_report", $start, $limit, $order, $dir, false, $type);
    }

    function getSingle($name, $tag="patient_id", $ref="ref") {
        return self::getOneField(table_name_prefix."clinic_doctors_report", $name, $ref, $tag);
    }

    function listOne($id) {
        return self::getOne(table_name_prefix."clinic_doctors_report", $id, "ref");
    }

    function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "DESC", $logic = "AND", $start = false, $limit = false) {
        return self::sortAll(table_name_prefix."clinic_doctors_report", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
    }

    public function formatResult($data, $single=false) {
        if ($single == false) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i] = self::clean($data[$i]);
            }
        } else {
            $data = self::clean($data);
        }
        return $data;
    }

    public function clean($data) {
        global $admin;
        global $patient;
        global $clinic_medication;
        global $clinic_lab;

        $return['ref'] = intval($data['ref']);
        $return['report'] = $data['report'];
        $return['medication'] = $clinic_medication->formatResult( $clinic_medication->getSortedList( $data['ref'], "doctors_report_id", false, false, false, false, "ref", "desc"), false, true );
        $return['labTest'] = $clinic_lab->formatResult( $clinic_lab->getSortedList( $data['ref'], "doctors_report_id", false, false, false, false, "ref", "desc"), false, true );
        $return['patient'] = $patient->formatResult( $patient->listOne( $data['patient_id'] ), true, true);
        $return['createdBy'] = $admin->formatResult( $admin->listOne( $data['added_by']), true, true );
        $return['date']['created'] = $data['create_time'];
        $return['date']['modified'] = $data['modify_time'];
        
        return $return;
    }
}
?>