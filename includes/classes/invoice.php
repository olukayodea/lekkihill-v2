<?php
class invoice extends common {
    public $invoice;
    public $minify = false;

    protected $allowedFields = array(
        "add" => array(
            "patient_id",
            "amount",
            "due_date"
        ),
        "edit" => array(
            "ref",
            "patient_id",
            "amount",
            "due_date"
        )
    );

    public function create($array) {
        global $billing;

        if (!$this->validateInput($array, "add")) {
            $this->BadReques['error']['additional_message'] = "some input values are missing";
            return $this->BadReques;
        }

        $invData['amount'] = $invData['due'] = $array['amount'];
        $invData['patient_id'] = $array['patient_id'];
        if (isset($array['due_date'])) {
            $invData['due_date'] = $array['due_date'];
        }
        $invData['create_by'] = $array['added_by'];

        $this->invoice = $this->insert(table_name_prefix."invoice", $invData);

        $data['invoice_id'] = $this->invoice;
        $data['patient_id'] = $array['patient_id'];
        $data['added_by'] = $array['added_by'];

        $amount = 0;

        for ($i = 0; $i < count($array['billing_component']); $i++) {
            $data['billing_component_id'] = $array['billing_component'][$i]['id'];
            $data['cost'] = $array['billing_component'][$i]['cost'];
            $data['quantity'] = $array['billing_component'][$i]['quantity'];
            $data['type'] = $array['billing_component'][$i]['type'];
            $data['description'] = $array['billing_component'][$i]['description'];


            $amount = $amount+$data['quantity']*$data['cost'];
            $billing->create($data);
        }

        $this->updateOne(table_name_prefix."invoice", "amount", $amount, $this->invoice);
        $this->updateOne(table_name_prefix."invoice", "due", $amount, $this->invoice);
        //semd email

        $this->successResponse['data'] = $this->formatResult( $this->listOne( $this->invoice), true );
        return $this->successResponse;
    }


    public function getPending() {
        return $this->query("SELECT * FROM ".table_name_prefix."invoice WHERE `status` != 'PAID'", false, "list");
    }

    public function modifyOne($tag, $value, $id, $ref="ref") {
        return $this->updateOne(table_name_prefix."invoice", $tag, $value, $id, $ref);
    }

    public function getList($start=false, $limit=false, $order="title", $dir="ASC", $type="list") {
        return $this->lists(table_name_prefix."invoice", $start, $limit, $order, $dir, "`status` != 'DELETED'", $type);
    }

    public function getSingle($name, $tag="title", $ref="ref") {
        return $this->getOneField(table_name_prefix."invoice", $name, $ref, $tag);
    }

    public function listOne($id) {
        return $this->getOne(table_name_prefix."invoice", $id, "ref");
    }

    public function getSortedList($id, $tag, $tag2 = false, $id2 = false, $tag3 = false, $id3 = false, $order = 'ref', $dir = "ASC", $logic = "AND", $start = false, $limit = false) {
        return $this->sortAll(table_name_prefix."invoice", $id, $tag, $tag2, $id2, $tag3, $id3, $order, $dir, $logic, $start, $limit);
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
        global $patient;
        global $admin;
        global $billing;

        $data['patient'] = $patient->formatResult( $patient->listOne( $data['patient_id'] ), true);
        
        $amount['value'] = $data['amount'];
        $amount['label'] = "&#8358; ".number_format( $data['amount'] );
        $data['amount'] = $amount;

        $due['value'] = $data['due'];
        $due['label'] = "&#8358; ".number_format( $data['due'] );
        $data['due'] = $due;

        $data['invoice_data'] = $billing->formatResult( $billing->getSortedList($data['ref'], "invoice_id") );
        $data['createdBy'] = $admin->formatResult( $admin->listOne( $data['create_by'] ), true);
        
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