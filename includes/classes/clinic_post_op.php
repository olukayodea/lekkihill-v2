<?php
class clinic_post_op extends common {
    public $patient_id;
    function create($array) {
        return $this->insert(table_name_prefix."clinic_post_op", $array);
    }
    
    public function recent_post_op ($id) {
        return $this->getSortedList($id, "patient_id", false, false, false, false, "ref", "DESC", "AND", false, 1)[0];
    }

    public function listPages($start, $limit)
    {
        $where = "`patient_id` = ". $this->patient_id;
        $return['data'] = $this->lists(table_name_prefix . "clinic_post_op", $start, $limit, "ref", "DESC", $where);
        $return['counts'] = $this->lists(table_name_prefix . "clinic_post_op",  false, false, "ref", "DESC", $where, "count");

        return $return;
    }

    function modifyOne($tag, $value, $id, $ref="ref") {
        return $this->updateOne(table_name_prefix."clinic_post_op", $tag, $value, $id, $ref);
    }
    
    function getList($start=false, $limit=false, $order="ref", $dir="DESC", $type="list") {
        return $this->lists(table_name_prefix."clinic_post_op", $start, $limit, $order, $dir, false, $type);
    }

    function getSingle($name, $tag="patient_id", $ref="ref") {
        return $this->getOneField(table_name_prefix."clinic_post_op", $name, $ref, $tag);
    }

    function listOne($id) {
        return $this->getOne(table_name_prefix."clinic_post_op", $id, "ref");
    }

    function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll(table_name_prefix."clinic_post_op", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
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

        $data['patient'] = $patient->formatResult( $patient->listOne( $data['patient_id'] ), true, true);
        $data['createdBy'] = $admin->formatResult( $admin->listOne( $data['added_by']), true, true );
        $data['date']['created'] = $data['create_time'];
        $data['date']['modified'] = $data['modify_time'];

        unset($data['patient_id']);
        unset($data['added_by']);
        unset($data['create_time']);
        unset($data['modify_time']);
        
        return $data;
    }
}
?>