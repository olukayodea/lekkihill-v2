<?php
class inventory_category extends common
{
    public $id;
    public $minify = false;
    public $filter = null;
    public $search = null;

    protected $allowedFields = array(
        "add" => array(
            "title",
        ),
        "edit" => array(
            "ref",
            "title"
        )
    );

    public function create($array)
    {
        $replace = array();

        $replace[] = "title";
        $replace[] = "last_modified_by";

        if (!$this->validateInput($array, "add")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        $array['created_by'] = $array['last_modified_by'] = $this->admin_id;

        $id = $this->replace(table_name_prefix . "inventory_category", $array, $replace);
        if ($id) {
            $this->successResponse['data'] = $this->formatResult($this->listOne($id), true);
            return $this->successResponse;
        } else {
            return $this->internalServerError;
        }
    }

    public function changeStatus($array)
    {
        global $settings;
        $data = $this->listOne($array['ref']);
        if ($data) {
            if ($array['status'] == "activate") {
                if ($data['status'] == "IN-ACTIVE") {
                    $this->modifyOne("status", "ACTIVE", $array['ref']);
                    $this->modifyOne("last_modified_by", $this->admin_id, $array['ref']);
                    $this->return['success'] = true;
                    $this->return['results'] = "OK";
                    $this->return['data'] = $this->formatResult($this->listOne($data['ref']), true);
                } else {
                    $this->return['success'] = false;
                    $this->return['error']['code'] = 10063;
                    $this->return['error']["message"] = "This category can not be activated";
                }
            } else if ($array['status'] == "deactivate") {
                $medicationCategory = intval($settings->get("medicationCategory"));
                if ($data['ref'] == $medicationCategory) {
                    $this->return['success'] = false;
                    $this->return['error']['code'] = 10020;
                    $this->return['error']["message"] = "Invalid status change action, you can not deactivate this category,it is the default medication category in Settings";
                } else {
                    if ($data['status'] == "ACTIVE") {
                        $this->modifyOne("status", "IN-ACTIVE", $array['ref']);
                        $this->modifyOne("last_modified_by", $this->admin_id, $array['ref']);
                        $this->return['success'] = true;
                        $this->return['results'] = "OK";
                        $this->return['data'] = $this->formatResult($this->listOne($data['ref']), true);
                    } else {
                        $this->return['success'] = false;
                        $this->return['error']['code'] = 10064;
                        $this->return['error']["message"] = "This category can not be deactivated";
                    }
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

    public function remove($id)
    {
        global $inventory;
        global $settings;
        $data = $this->listOne($id);

        if ($data && $data['status'] != "DELETED") {
            $medicationCategory = intval($settings->get("medicationCategory"));
            if ($data['ref'] == $medicationCategory) {
                $this->return['success'] = false;
                $this->return['error']['code'] = 10020;
                $this->return['error']["message"] = "Invalid status change action, you can not dekete this category,it is the default medication category in Settings";

                return $this->return;
            }
            if (count($inventory->getByCategory($id)) > 0) {
                $update = $this->updateOne(table_name_prefix . "inventory_category", "status", "DELETED", $id);
            } else {
                $update = $this->delete(table_name_prefix . "inventory_category", $id);
            }

            if ($update) {
                $this->successResponse['additional_message'] = "Category deleted successfully";
                return $this->successResponse;
            } else {
                $this->internalServerError['additional_message'] = "there was an error performing this action";
                return $this->internalServerError;
            }
        } else {
            return $this->notFound;
        }
    }

    function modifyOne($tag, $value, $id)
    {
        if ($this->updateOne(table_name_prefix . "inventory_category", $tag, $value, $id, "ref", "`modify_time` = " . time())) {
            return true;
        } else {
            return false;
        }
    }

    public function getCount()
    {
        $query = "SELECT COUNT(`ref`) FROM " . table_prefix.table_name_prefix . "inventory_category WHERE `status` = 'ACTIVE'";
        return $this->query($query, false, "getCol");
    }

    public function getList($start = false, $limit = false, $order = "title", $dir = "ASC", $type = "list")
    {
        return $this->lists(table_name_prefix . "inventory_category", $start, $limit, $order, $dir, "`status` != 'DELETED'", $type);
    }

    public function getSingle($name, $tag = "title", $ref = "ref")
    {
        return $this->getOneField(table_name_prefix . "inventory_category", $name, $ref, $tag);
    }

    public function listOne($id)
    {
        return $this->getOne(table_name_prefix . "inventory_category", $id, "ref");
    }

    public function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'title', $dir = "ASC", $logic = "AND", $start = false, $limit = false)
    {
        return $this->sortAll(table_name_prefix . "inventory_category", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
    }

    private function listPages($start, $limit)
    {

        if ($this->search !== null) {
            if (strtolower($this->search) == "active") {
                $where = "`status` != 'DELETED' AND `status` = 'ACTIVE'";
            } else if (strtolower($this->search) == "inactive") {
                $where = "`status` != 'DELETED' AND `status` = 'IN-ACTIVE'";
            } else {
                $where = "`status` != 'DELETED'";
            }
        } else {
            $where = "`status` != 'DELETED'";
        }
        $return['data'] = $this->lists(table_name_prefix . "inventory_category", $start, $limit, "title", "ASC", $where);
        $return['counts'] = $this->lists(table_name_prefix . "inventory_category",  false, false, "title", "ASC", $where, "count");

        return $return;
    }

    private function search($search, $start, $limit)
    {
        $return['data'] = $this->runSearch($search, $start, $limit);
        $return['counts'] = $this->runSearch($search,  false, false, "count");

        return $return;
    }

    private function runSearch($search, $start, $limit, $type = "list")
    {
        if ($limit == true) {
            $add = " LIMIT " . $start . ", " . $limit;
        } else {
            $add = "";
        }

        return $this->query("SELECT * FROM `" . table_prefix . table_name_prefix . "inventory_category` WHERE (`title` LIKE :search OR `status` LIKE :search) AND `status` != 'DELETED' ORDER BY `title` ASC" . $add, array(':search' => "%" . $search . "%"), $type);
    }

    public function get($page = 1)
    {
        global $settings;

        if (intval($page) == 0) {
            $page = 1;
        }
        $current = (intval($page) > 0) ? (intval($page) - 1) : 0;
        $limit = intval($settings->get("resultPerPage"));
        $start = $current * $limit;

        $this->successResponse;
        if ($this->id > 0) {
            $data = $this->listOne($this->id);
            if ($data) {
                $this->successResponse['data'] = $this->formatResult($data, true);
                return $this->successResponse;
            } else {
                return $this->notFound;
            }
        } else {
            if ($this->filter != null) {
                if ($this->filter == "list") {
                    $result = $this->listPages($start, $limit);
                } else if ($this->filter == "search") {
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
                    $this->successResponse['counts']['totalPage'] = ceil($result['counts'] / $limit);
                    $this->successResponse['counts']['rowOnCurrentPage'] = count($result['data']);
                    $this->successResponse['counts']['maxRowPerPage'] = intval($limit);
                    $this->successResponse['counts']['totalRows'] = $result['counts'];
                    $this->successResponse['counts']['prevRow'] = (intval($page) * intval($limit)) - intval($limit);
                    $this->successResponse['data'] = $this->formatResult($result['data'], false);
                } else {
                    $this->successResponse['data'] = [];
                }

                return $this->successResponse;
            } else {
                return $this->NotAcceptable;
            }
        }
    }

    public function formatResult($data, $single = false, $mini = false)
    {
        if ($single == false) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i] = $this->clean($data[$i], $mini);
            }
        } else {
            $data = $this->clean($data, $mini);
        }
        return $data;
    }

    private function clean($data, $mini)
    {
        global $admin;

        $data['ref'] = intval($data['ref']);

        if ($mini === false) {
            $status['active'] = ("ACTIVE" == $data['status']) ? true : false;
            $status['inActive'] = ("IN-ACTIVE" == $data['status']) ? true : false;
            $data['status'] = $status;

            $data['createdBy'] = $admin->formatResult($admin->listOne($data['created_by']), true, true);
            $data['lastModifiedBy'] = $admin->formatResult($admin->listOne($data['last_modified_by']), true, true);

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

    private function validateInput($input, $type)
    {
        if (!$this->CheckValidate($input, $this->allowedFields[$type])) {
            return false;
        }
        return $input;
    }
}
