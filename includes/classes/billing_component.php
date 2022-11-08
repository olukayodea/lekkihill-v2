<?php
class billing_component extends common {
    public $id;
    public $minify = false;
    public $filter = null;
    public $search = null;

    protected $allowedFields = array(
        "add" => array(
            "title",
            "cost"
        ),
        "edit" => array(
            "ref",
            "title",
            "cost"
        )
    );

    public function create($array) {
        $replace = array();
        
        if (!$this->validateInput($array, "add")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        $array['created_by'] = $array['last_modified_by'] = $this->admin_id;

        $replace[] = "title";
        $replace[] = "description";
        $replace[] = "cost";
        $replace[] = "last_modified_by";
        
        if ($array['ref'] == 0) {
            unset($array['ref']);
        }
        $id = $this->replace(table_name_prefix."billing_component", $array, $replace);

        if ($id) {
            $this->successResponse['data'] = $this->formatResult( $this->listOne( $id), true );
            return $this->successResponse;
        } else {
            return $this->internalServerError;
        }
    }
		
    function modifyOne($tag, $value, $id) {
        if ($this->updateOne(table_name_prefix."billing_component", $tag, $value, $id, "ref", "`modify_time` = ".time())) {
            return true;
        } else {
            return false;
        }
    }

    public function remove ( $id ) {
        if ($this->modifyOne("status", "DELETED", $id)) {
            return $this->successResponse;
        } else {
            return $this->internalServerError;
        }
    }

    public function changeStatus($array) {
        $data = $this->listOne($array['ref']);
        if ($data) {
            if ($array['status'] == "activate") {
                if ($data['status'] == "IN-ACTIVE") {
                    $this->modifyOne("status", "ACTIVE", $array['ref']);
                    $this->modifyOne("last_modified_by", $this->admin_id, $array['ref']);
                    $this->return['success'] = true;
                    $this->return['results'] = "OK";
                    $this->return['data'] = $this->formatResult( $this->listOne($data['ref']), true);
                } else {
                    $this->return['success'] = false;
                    $this->return['error']['code'] = 10063;
                    $this->return['error']["message"] = "This component can not be activated";
                }
            } else if ($array['status'] == "deactivate") {
                if ($data['status'] == "ACTIVE") {
                    $this->modifyOne("status", "IN-ACTIVE", $array['ref']);
                    $this->modifyOne("last_modified_by", $this->admin_id, $array['ref']);
                    $this->return['success'] = true;
                    $this->return['results'] = "OK";
                    $this->return['data'] = $this->formatResult( $this->listOne($data['ref']), true);
                } else {
                    $this->return['success'] = false;
                    $this->return['error']['code'] = 10064;
                    $this->return['error']["message"] = "This component can not be deactivated";
                }
            } else {
                $this->return['success'] = false;
                $this->return['error']['code'] = 10017;
                $this->return['error']["message"] = "Invalid status change action";
            }
        } else {
            $this->return['success'] = false;
            $this->return['error']['code'] = 10066;
            $this->return['error']["message"] = "Can not retrieve category";
        }

        return $this->return;
    }

    public function getCount() {
        $query = "SELECT COUNT(`ref`) FROM ".table_name_prefix."billing_component WHERE `status` = 'ACTIVE'";
        return $this->query($query, false, "getCol");
    }

    public function getList($start=false, $limit=false, $order="title", $dir="ASC", $type="list") {
        return $this->lists(table_name_prefix."billing_component", $start, $limit, $order, $dir, "`status` != 'DELETED'", $type);
    }

    public function getSingle($val, $tag="title", $ref="ref") {
        return $this->getOneField(table_name_prefix."billing_component", $val, $ref, $tag);
    }

    public function listOne($id) {
        return $this->getOne(table_name_prefix."billing_component", $id, "ref");
    }

    public function getActive() {
        return $this->query("SELECT * FROM `".table_prefix.table_name_prefix."billing_component` WHERE `status` = 'ACTIVE' ORDER BY `title` ASC", false, "list");
    }

    public function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'title', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll(table_name_prefix."billing_component", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
    }

    private function listPages($start, $limit) {
        $return['data'] = $this->lists(table_name_prefix."billing_component", $start, $limit, "title", "ASC", "`status` != 'DELETED'");
        $return['counts'] = $this->lists(table_name_prefix."billing_component",  false, false, "title", "ASC", "`status` != 'DELETED'", "count");

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

        return $this->query("SELECT * FROM `".table_prefix.table_name_prefix."billing_component` WHERE (`title` LIKE :search OR `cost` LIKE :search OR `description` LIKE :search OR `status` LIKE :search) AND `status` != 'DELETED' ORDER BY `title` ASC".$add, array(':search' => "%".$search."%"), $type);


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
        $admin->minify = true;

        $data['ref'] = intval($data['ref']);

        $cost['value'] = $data['cost'];
        $cost['label'] = "&#8358;".number_format( $data['cost'] );
        $data['cost'] = $cost;

        if ($mini === false) {
            $status['active'] = ("ACTIVE" == $data['status']) ? true : false;
            $status['inActive'] = ("IN-ACTIVE" == $data['status']) ? true : false;
            $data['status'] = $status;

            $data['createdBy'] = $admin->formatResult( $admin->listOne( $data['created_by'] ), true, true);
            $data['lastModifiedBy'] = $admin->formatResult( $admin->listOne( $data['last_modified_by'] ), true, true);

            $data['date']['created'] = $data['create_time'];
            $data['date']['modified'] = $data['modify_time'];
        } else {
            unset($data['ref']);
            unset($data['status']);
        }
        unset($data['created_by']);
        unset($data['last_modified_by']);
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