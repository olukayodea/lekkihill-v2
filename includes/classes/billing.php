<?php
class billing extends common {
    public $minify = false;
    public $list = array();
    public $list_component = array();
    public $list_invoice = array();
    public $viewData = array();
    public $balance;
    public $tag;
    public $id;
    public $url;

    protected $allowedFields = array(
        "add" => array(
            "invoice_id",
            "billing_component_id",
            "quantity",
            "description",
            "patient_id",
            "cost",
            "added_by"
        ),
        "edit" => array(
            "ref",
            "invoice_id",
            "billing_component_id",
            "quantity",
            "description",
            "patient_id",
            "cost"
        )
    );

    public function get_due_invoice($id=false, $email=false) {
        if ($id !== false) {
            $tag = '`patient_id` = '.$id.' AND ';
        } else if ($email !== false) {
            $tag = "`patient_id` = (SELECT `ref` FROM `wp_lekkihill_patient` WHERE `email` = '".$email."' ) AND ";
        }

        $this->balance = $this->query("SELECT SUM(`due`) FROM `wp_lekkihill_invoice` WHERE ". $tag ."`status` != 'PAID'", false, "getCol");
        $this->list_invoice = $this->query("SELECT * FROM `wp_lekkihill_invoice` WHERE ". $tag ."`status` != 'PAID'", false, "list");
    }

    public function report() {

    }

    public function create($array) {
        if (!$this->validateInput($array, "add")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        return $this->insert(table_name_prefix."billing", $array);
    }

    public function modifyOneBill($tag, $value, $id, $ref="ref") {
        return $this->updateOne(table_name_prefix."billing", $tag, $value, $id, $ref);
    }

    public function listOne($id) {
        return $this->getOne(table_name_prefix."billing", $id, "ref");
    }

    public function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll(table_name_prefix."billing", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
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
        global $billing_component;
        $billing_component->minify = true;

        $data['ref'] = intval($data['ref']);
        $data['quantity'] = intval($data['quantity']);


        $status['unPaid'] = ("NEW" == $data['status']) ? true : false;
        $status['partiallyPaid'] = ("PARTIALLY-PAID" == $data['status']) ? true : false;
        $status['paid'] = ("PAID" == $data['status']) ? true : false;
        $data['status'] = $status;

        $billingComponent = $billing_component->formatResult($billing_component->listOne( $data['billing_component_id'] ), true);
        $data['component'] = $billingComponent;

        $cost['value'] = $data['cost'];
        $cost['label'] = "&#8358; ".number_format( $data['cost'] );
        $data['cost'] = $cost;

        $data['date']['created'] = $data['create_time'];
        $data['date']['modified'] = $data['modify_time'];
        
        unset( $data['patient_id'] );
        unset( $data['added_by'] );
        unset( $data['billing_component_id'] );
        unset($data['invoice_id']);
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