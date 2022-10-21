<?php
class patient extends common {



    private static function create($array, $user) {
        global $invoice;
        $replace[] = "last_name";
        $replace[] = "first_name";
        $replace[] = "age";
        $replace[] = "sex";
        $replace[] = "phone_number";
        $id = self::replace(table_name_prefix."patient", $array, $replace);

        if ($id) {
            $consultationFee_cost = get_option("lh-consultationFee-cost");
            $consultationFee_component_id = get_option("lh-consultationFee-component-id");
            $registrationFee_cost = get_option("lh-registrationFee-cost");
            $registrationFee_component_id = get_option("lh-registrationFee-component-id");
            $data['patient_id'] = $id;
            $data['added_by'] = $user;
            $data['type'] = "component";
            $data['amount'] = $registrationFee_cost+$consultationFee_cost;

            $data['billing_component'][0]['id'] = $consultationFee_component_id;
            $data['billing_component'][0]['cost'] = $consultationFee_cost;
            $data['billing_component'][0]['quantity'] = 1;
            $data['billing_component'][0]['type'] = "component";
            $data['billing_component'][0]['description'] = NULL;
            $data['billing_component'][1]['id'] = $registrationFee_component_id;
            $data['billing_component'][1]['cost'] = $registrationFee_cost;
            $data['billing_component'][1]['quantity'] = 1;
            $data['billing_component'][1]['type'] = "component";
            $data['billing_component'][1]['description'] = NULL;

            $invoice->create($data);
        }

        return $id;
    }
}
?>