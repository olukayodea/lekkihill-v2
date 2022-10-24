<?php
class visitors extends common {
    public $id;
    public $minify = false;
    public $filter = null;
    public $search = null;

    protected $allowedFields = array(
        "add" => array(
            "last_name",
            "first_name",
            "phone_number",
            "email",
            "address",
            "whom_to_see",
            "resason"
        )
    );

    public function create($array) {
        if (!$this->validateInput($array, "add")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        $array['added_by'] = $this->admin_id;
        $id = $this->insert(table_name_prefix."visitors", $array);

        if ($id) {
            $this->successResponse['data'] = $this->formatResult( $this->listOne( $id), true );
            return $this->successResponse;
        } else {
            return $this->internalServerError;
        }
    }

    public function remove ( $id ) {
        if ($this->delete(table_name_prefix."visitors", $id)) {
            return $this->successResponse;
        } else {
            return $this->internalServerError;
        }
    }

    public function modifyOne($tag, $value, $id, $ref="ref") {
        return $this->updateOne(table_name_prefix."visitors", $tag, $value, $id, $ref);
    }
    
    public function getList($start=false, $limit=false, $order="last_name", $dir="ASC", $type="list") {
        return $this->lists(table_name_prefix."visitors", $start, $limit, $order, $dir, false, $type);
    }

    public function getSingle($name, $tag="last_name", $ref="ref") {
        return $this->getOneField(table_name_prefix."visitors", $name, $ref, $tag);
    }

    public function listOne($id) {
        return $this->getOne(table_name_prefix."visitors", $id, "ref");
    }

    public function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll(table_name_prefix."visitors", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
    }

    private function listPages($start, $limit) {
        $return['data'] = $this->lists(table_name_prefix."visitors", $start, $limit, "last_name", "ASC");
        $return['counts'] = $this->lists(table_name_prefix."visitors",  false, false, "last_name", "ASC", false, "count");

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

        return $this->query("SELECT * FROM `".table_prefix.table_name_prefix."visitors` WHERE (`last_name` LIKE :search OR `first_name` LIKE :search OR `phone_number` LIKE :search OR `email` LIKE :search) ORDER BY `last_name` ASC".$add, array(':search' => "%".$search."%"), $type);

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
        $admin->minify = true;
        $return['ref'] = intval($data['ref']);
        $return['lastName'] = $data['last_name'];
        $return['firstName'] = $data['first_name'];
        $return['phoneNumber'] = $data['phone_number'];
        $return['email'] = $data['email'];
        $return['address'] = $data['address'];
        $return['whomToSee']['name'] = $data['whom_to_see'];
        $return['whomToSee']['resason'] = $data['resason'];
        $return['createdBy'] = $admin->formatResult( $admin->listOne( $data['added_by']), true );
        $return['date']['created'] = $data['create_time'];
        $return['date']['modified'] = $data['modify_time'];
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