<?php
class inventory extends inventory_count {

    protected $allowedFields = array(
        "add" => array(
            "title",
            "cost",
            "qty_desc",
            "inventory_added",
            "category_id"
        ),
        "edit" => array(
            "ref",
            "title",
            "cost",
            "qty_desc",
            "category_id"
        ),
        "stock" => array(
            "ref",
            "action",
            "count"
        )
    );

    public function getByCategory( $category_id ) {
        return $this->getSortedList( $category_id, "category_id" );
    }

    public function create( $array ) {
        global $inventory_category;
        if (!$this->validateInput($array, "add")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        $insert['title'] = $array['title'];
        $insert['cost'] = $array['cost'];
        $insert['qty_desc'] = $array['qty_desc'];
        $insert['category_id'] = $array['category_id'];
        $insert['discount'] = 0;
        $insert['status'] = "ACTIVE";
        $insert['sku'] = $this->confirmSKU($this->createSKU($array['category_id']), $array['category_id']);;
        $insert['created_by'] = $insert['last_modified_by'] = $this->admin_id;
        
        if ($inventory_category->getCount()) {
            $id = $this->insert(table_name_prefix."inventory", $insert);

            if (intval($id) > 0) {
                $countAdd['inventory_id'] = $id;
                $countAdd['inventory_added'] = $array['inventory_added'];
                $countAdd['inventory_before_added'] = 0;
                $countAdd['added_by'] = $this->admin_id;
                if ($this->createCount($countAdd)) {
                    $this->successResponse['data'] = $this->formatResult( $this->listOne( $id), true );
                    return $this->successResponse;
                } else {
                    $this->delete(table_name_prefix."inventory", $id);
                    return $this->internalServerError;
                }
            } else {
                return $this->internalServerError;
            }
        } else {
            $this->RequiredSettingsNotFound['error']['additional_message'] = "No Valid Inventory category configured";
            return $this->RequiredSettingsNotFound;
        }
    }

    public function edit($array) {
        if (!$this->validateInput($array, "edit")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        unset($array['inventory_added']);

        $ref = $array['ref'];
        if ( $this->update(table_name_prefix."inventory", $array, ["ref" => $ref])) {

            $this->successResponse['data'] = $this->formatResult( $this->listOne( $ref), true );
            return $this->successResponse;
        } else {
            return $this->NotModified;
        }
    }

    public function manageStock($array) {
        if (!$this->validateInput($array, "stock")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }
        $data = $this->listOne($array['ref']);

        if ($data) {
            $balance = $this->getBalance( $array['ref'] );

            if ($array['action'] == "remove") {
                if ($balance < $array['count']) {
                    $this->NotModified['error']['additional_message'] = "You don't have up to " . $array['count'] . " items in this inventory";
                    return $this->NotModified;
                }
                $inventory_added = 0 - $array['count'];
            } else if ($array['action'] == "add") {
                $inventory_added = $array['count'];
            } else {
                $this->NotModified['error']['additional_message'] = "Unknown action specified ";
                return $this->NotModified;
            }

            $countAdd['inventory_id'] = $array['ref'];
            $countAdd['inventory_added'] = $inventory_added;
            $countAdd['inventory_before_added'] = $balance;
            $countAdd['added_by'] = $this->admin_id;

            if ($this->createCount($countAdd)) {
                $this->successResponse['data'] = $this->formatResult( $this->listOne( $array['ref']), true );
                return $this->successResponse;
            } else {
                return $this->internalServerError;
            }

        } else {
            $this->return['success'] = false;
            $this->return['error']['code'] = 10066;
            $this->return['error']["message"] = "Can not retrieve inventory";
        }

        return $this->return;
    }

    public function changeStatus($array)
    {
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
                    $this->return['error']["message"] = "This inventory can not be activated";
                }
            } else if ($array['status'] == "deactivate") {  
                if ($data['status'] == "ACTIVE") {
                    $this->modifyOne("status", "IN-ACTIVE", $array['ref']);
                    $this->modifyOne("last_modified_by", $this->admin_id, $array['ref']);
                    $this->return['success'] = true;
                    $this->return['results'] = "OK";
                    $this->return['data'] = $this->formatResult($this->listOne($data['ref']), true);
                } else {
                    $this->return['success'] = false;
                    $this->return['error']['code'] = 10064;
                    $this->return['error']["message"] = "This inventory can not be deactivated";
                }
            } else {
                $this->return['success'] = false;
                $this->return['error']['code'] = 10017;
                $this->return['error']["message"] = "Invalid status change action";
            }
        } else {
            $this->return['success'] = false;
            $this->return['error']['code'] = 10066;
            $this->return['error']["message"] = "Can not retrieve inventory";
        }

        return $this->return;
    }

    function modifyOne($tag, $value, $id)
    {
        if ($this->updateOne(table_name_prefix . "inventory", $tag, $value, $id, "ref", "`modify_time` = " . time())) {
            return true;
        } else {
            return false;
        }
    }

    public function remove( $id ) {
        $data = $this->listOne($id);

        if ($data && $data['status'] != "DELETED") {
            if ($this->getUsedount($id) > 0) {
                $update = $this->updateOne(table_name_prefix . "inventory", "status", "DELETED", $id);
            } else {
                $update = $this->delete(table_name_prefix . "inventory", $id);
            }

            if ($update) {
                $this->successResponse['additional_message'] = "Inventory Item deleted successfully";
                return $this->successResponse;
            } else {
                $this->internalServerError['additional_message'] = "there was an error performing this action";
                return $this->internalServerError;
            }
        } else {
            return $this->notFound;
        }
    }

    private function createSKU($id) {
        global $inventory_category;
        return strtoupper(substr($inventory_category->getSingle($id), 0, 3).rand(100000, 999999));
    }

    private function confirmSKU( $key, $id ) {
        if ($this->checkExixst(table_name_prefix."inventory", "sku", $key, "sku") == 0) {
            return $key;
        } else {
            return $this->confirmSKU($this->createSKU($id), $id);
        }
    }

    public function getBalance($id) {
        return intval($this->getCount($id)-$this->getUsedount($id));
    }

    public function getList($start=false, $limit=false, $order="title", $dir="ASC", $type="list") {
        return $this->lists(table_name_prefix."inventory", $start, $limit, $order, $dir, "`status` != 'DELETED'", $type);
    }

    public function getSingle($name, $tag="title", $ref="ref") {
        return $this->getOneField(table_name_prefix."inventory", $name, $ref, $tag);
    }

    public function listOne($id) {
        return $this->getOne(table_name_prefix."inventory", $id, "ref");
    }

    public function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'sku', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll(table_name_prefix."inventory", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
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
        $return['data'] = $this->lists(table_name_prefix . "inventory", $start, $limit, "title", "ASC", $where);
        $return['counts'] = $this->lists(table_name_prefix . "inventory",  false, false, "title", "ASC", $where, "count");

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

        return $this->query("SELECT * FROM `" . table_prefix . table_name_prefix . "inventory` WHERE (`title` LIKE :search OR `sku` LIKE :search OR `cost` LIKE :search OR `qty_desc` LIKE :search OR `category_id` IN (SELECT `ref` FROM `" . table_prefix . table_name_prefix . "inventory_category` WHERE `title` LIKE :search) OR `status` LIKE :search) AND `status` != 'DELETED' ORDER BY `title` ASC" . $add, array(':search' => "%" . $search . "%"), $type);
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
        global $inventory_category;
        global $settings;
        $pdf = new generateDownloads($this->admin_id, "pdf", $data['sku']);
        $csv = new generateDownloads($this->admin_id, "csv", $data['sku']);

        $data['ref'] = intval($data['ref']);
        $amount['value'] = floatval($data['cost']);
        $amount['label'] = "&#8358;".number_format( $data['cost'] );
        $data['cost'] = $amount;
        $data['discount'] = (floatval($data['discount']) > 0) ? array("active" => true, "value" => floatval($data['discount'])) : array("active" => false, "value" => floatval($data['discount']));
        $data['category'] = $inventory_category->formatResult( $inventory_category->listOne( $data['category_id'] ), true, true);
        $data['quantity'] = intval( $this->getBalance( $data['ref'] ) );
        $status['active'] = ("ACTIVE" == $data['status']) ? true : false;
        $status['inActive'] = ("IN-ACTIVE" == $data['status']) ? true : false;
        $status['lowAlert'] = ($this->getBalance( $data['ref']) <= $settings->get("lowInventoryCount")) ? true : false;
        $data['status'] = $status;
        if ($mini === false) {
            $data['links']['barcode'] = URL . "barcode/" . $data['sku'];
            $data['links']['pdf'] = URL . "pdf/" . $pdf->generateToken() . "/inventory/" . $data['ref'];
            $data['links']['csv'] = URL . "csv/" . $csv->generateToken() . "/inventory/" . $data['ref'];
            $data['activities'] = $this->formatCountResult( $this->getSortedInventoryCount($data['ref'], "inventory_id") );

            $data['createdBy'] = $admin->formatResult( $admin->listOne( $data['created_by'] ), true, true);
            $data['lastModifiedBy'] = $admin->formatResult( $admin->listOne( $data['last_modified_by'] ), true, true);

            $data['date']['created'] = $data['create_time'];
            $data['date']['modified'] = $data['modify_time'];
        }
        unset($data['created_by']);
        unset($data['last_modified_by']);
        unset($data['modify_time']);
        unset($data['create_time']);
        unset($data['category_id']);

        return $data;
    }

    private function validateInput($input, $type) {
        if (!$this->CheckValidate($input, $this->allowedFields[$type])) {
            return false;
        }
        return $input;
    }

    public static function print_view() {
        // if (isset($_REQUEST['id'])) {
        //     global $pdf;
        //     $id = $_REQUEST['id'];
        //     $return = self::processView($id);
        //     $data = $return['data'];
        //     $list = $return['list'];
                              
        //     // set document information
        //     $pdf->SetCreator('LekkiHill');
        //     $pdf->SetAuthor('LekkiHill');
        //     $pdf->SetTitle('Inventory Report');
        //     $pdf->SetSubject('Inventory Report');
        //     $pdf->SetKeywords('LekkiHill, PDF, Inventory, Report');
        //     // set auto page breaks
        //     $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            
        //     $pdf->SetDefaultMonospacedFont("courier");

        //     $pdf->SetFont('dejavusans', '', 10);
                        
        //     // define barcode style
        //     $style = array(
        //         'position' => 'C',
        //     );

        //     // add a page
        //     $pdf->AddPage();
            
        //     $pdf->write1DBarcode($data['sku'], 'C39', '', '', '', 18, 0.4, $style, 'N');

        //     $pdf->Ln();

        //     // create some HTML content
        //     $html = '<h2>'.$data['title'].'</h2>
        //     <table width="100%" border="0">
        //     <tbody>
        //     <tr class="striped">
        //       <td width="25%">SKU</td>
        //       <td>'.$data['sku'].'</td>
        //     </tr>
        //     <tr>
        //       <td>Category</td>
        //       <td>'.inventory_category::getSingle( $data['category_id'] ).'</td>
        //      </tr>
        //     <tr>
        //       <td>Cost </td>
        //       <td>'.'&#8358; '.number_format($data['cost'], 2).'</td>
        //     </tr>
        //     <tr>
        //       <td>Quantity </td>
        //       <td>'.$data['quantity'].'</td>
        //     </tr>
        //     <tr>
        //       <td>Status</td>
        //       <td>'.$data['status'].'</td>
        //     </tr>
        //     <tr>
        //       <td>Created By</td>
        //       <td>'.self::getuser( $data['created_by'] ).'</td>
        //     </tr>
        //     <tr>
        //       <td>Created At</td>
        //       <td>'.$data['create_time'].'</td>
        //     </tr>
        //     <tr>
        //       <td>Last Modified by</td>
        //       <td>'.self::getuser( $data['last_modified_by'] ).'</td>
        //     </tr>
        //     <tr>
        //       <td>Modified At</td>
        //       <td>'.$data['modify_time'].'</td>
        //     </tr>
        //     </tbody>
        //   </table>
        // <h3>History</h3>
        // <table class="striped" id="datatable_list" border="1">
        // <thead>
        //     <tr>
        //     <td>#</td>
        //     <td>Date</td>
        //     <td>Quantity Left</td>
        //     <td>Amount Added/Removed</td>
        //     <td>Total Quantity</td>
        //     <td>Added By</td>
        //     </tr>
        // </thead>
        // <tbody>';
        //     $count = 1;
        //     for ($i = 0;  $i < count($list); $i++) {
        //     $html .= '<tr>
        //         <td>'.$count.'</td>
        //         <td>'.$list[$i]['create_time'].'</td>
        //         <td>'.number_format( $list[$i]['inventory_before_added'] ).'</td>
        //         <td>'.($list[$i]['inventory_added'] < 0 ? "(".number_format( abs( $list[$i]['inventory_added'] ) ).")" : number_format( abs( $list[$i]['inventory_added'] ) ) ).'</td>
        //         <td>'.number_format( $list[$i]['inventory_before_added']+$list[$i]['inventory_added'] ).'</td>
        //         <td>'.self::getuser( $list[$i]['added_by'] ).'</td>
        //         </tr>';
        //         $count++;
        //     }
        //     $html .= '</tbody>
        //     </table>';
        //     // output the HTML content
        //     $pdf->writeHTML($html, true, false, true, false, '');
        //     $pdf->Output('example_006.pdf', 'I');
        // }
    }
}
