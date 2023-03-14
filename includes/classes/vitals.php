<?php
class vitals extends database {
    public $patient_id;

    function create($array) {
        return self::insert(table_name_prefix."vitals", $array);
    }

    function modifyOne($tag, $value, $id, $ref="ref") {
        return self::updateOne(table_name_prefix."vitals", $tag, $value, $id, $ref);
    }
    
    function getList($start=false, $limit=false, $order="ref", $dir="DESC", $type="list") {
        return self::lists(table_name_prefix."vitals", $start, $limit, $order, $dir, false, $type);
    }

    function getSingle($name, $tag="patient_id", $ref="ref") {
        return self::getOneField(table_name_prefix."vitals", $name, $ref, $tag);
    }

    function listOne($id) {
        return self::getOne(table_name_prefix."vitals", $id, "ref");
    }

    public function recent_vital ($id) {
        return $this->getSortedList($id, "patient_id", false, false, false, false, "ref", "DESC", "AND", false, 1)[0];
    }

    public function listPages($start, $limit)
    {
        $where = "`patient_id` = ". $this->patient_id;
        $return['data'] = $this->lists(table_name_prefix . "vitals", $start, $limit, "ref", "DESC", $where);
        $return['counts'] = $this->lists(table_name_prefix . "vitals",  false, false, "ref", "DESC", $where, "count");

        return $return;
    }

    function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return self::sortAll(table_name_prefix."vitals", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
    }

    public function formatResult($data, $single=false) {
        if ( $data ) {
            if ($single == false) {
                for ($i = 0; $i < count($data); $i++) {
                    $data[$i] = self::clean($data[$i]);
                }
            } else {
                $data = self::clean($data);
            }
            return $data;
        } else {
            return false;
        }
    }

    private function clean($data) {
        global $admin;
        $return['ref'] = intval($data['ref']);
        $return['weight'] = floatval($data['weight']);
        $return['height'] = floatval($data['height']);
        $return['bmi'] = floatval($data['bmi']);
        $return['spo2'] = floatval($data['spo2']);
        $return['respiratory'] = floatval($data['respiratory']);
        $return['temprature'] = floatval($data['temprature']);
        $return['pulse'] = floatval($data['pulse']);
        $return['bp_sys'] = floatval($data['bp_sys']);
        $return['bp_dia'] = floatval($data['bp_dia']);
        $return['createdBy'] = $admin->formatResult( $admin->listOne( $data['added_by']), true, true );
        $return['date']['created'] = $data['create_time'];
        $return['date']['modified'] = $data['modify_time'];
        
        return $return;
    }
}
?>