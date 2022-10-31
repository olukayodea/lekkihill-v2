<?php
class invoiceLog extends common {
    public function create( $array ) {
        $this->id = $this->insert(table_name_prefix."invoice_log", $array);

        if ($this->id > 0) {
            return $this->successResponse;
        } else {
            return $this->internalServerError;
        }
    }

    public function getList($id, $tag="invoice_id") {
        return $this->sortAll(table_name_prefix."invoice_log", $id, $tag);
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
        
        $amount['value'] = floatval($data['amount']);
        $amount['label'] = "&#8358;".number_format( $data['amount'] );
        $result['amount'] = $amount;

        $result['createdBy'] = $admin->formatResult( $admin->listOne( $data['create_by'] ), true);

        $result['date']['created'] = $data['create_time'];
        $result['date']['modified'] = $data['modify_time'];
        
        return $result;
    }
}
?>