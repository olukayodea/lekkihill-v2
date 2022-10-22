<?php
class billing_component extends common {
    public $minify = false;
    protected $allowedFields = array(
        "add" => array(
            "title",
            "cost",
            "description"
        ),
        "edit" => array(
            "ref",
            "title",
            "cost",
            "description"
        )
    );

    public function create($array) {
        $replace = array();
        
        if (!$this->validateInput($array, "add")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        $replace[] = "title";
        $replace[] = "description";
        $replace[] = "cost";
        $replace[] = "last_modified_by";
        $replace[] = "status";
        
        if ($array['ref'] == 0) {
            unset($array['ref']);
        }
        return $this->replace(table_name_prefix."billing_component", $array, $replace);
    }

    public function getCount() {
        $query = "SELECT COUNT(`ref`) FROM ".table_name_prefix."billing_component WHERE `status` = 'ACTIVE'";
        return $this->query($query, false, "getCol");
    }

    public function getList($start=false, $limit=false, $order="title", $dir="ASC", $type="list") {
        return $this->lists(table_name_prefix."billing_component", $start, $limit, $order, $dir, "`status` != 'DELETED'", $type);
    }

    public function getSingle($name, $tag="title", $ref="ref") {
        return $this->getOneField(table_name_prefix."billing_component", $name, $ref, $tag);
    }

    public function listOne($id) {
        return $this->getOne(table_name_prefix."billing_component", $id, "ref");
    }

    public function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'title', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll(table_name_prefix."billing_component", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
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

        $data['createdBy'] = $admin->formatResult( $admin->listOne( $data['created_by'] ), true);
        $data['createdBy'] = $admin->formatResult( $admin->listOne( $data['last_modified_by'] ), true);
        unset($data['created_by']);
        unset($data['last_modified_by']);
        
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