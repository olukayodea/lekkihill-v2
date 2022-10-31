<?php
echo "<pre>";
	//$product_key = rand();
	$product_key = '32333518';
	
	$u = "http://127.0.0.1/lekkihill-v2/";
    $token = "283640767761666455991RFXDEBFMFHV";
	// $u = "https://api.dev.skrinad.me/";
    // $token = "12990305521622781738U332BS9TLBUJ";

    $gateway_passcode = base64_encode($product_key."_".$token);
    //common factors
    $header[] = "Content-Type: application/json";
    $header[] = "Authorization: Bearer ".$gateway_passcode;
	$header[] ='key: '.$product_key;
    $header[] ='device: SkrinAd.com';
	$header[] ='location: 105.112.96.216';


	// //main
	// $url = $u."2.1/main";
	// echo get($header, $url);

	// //login
	// 	$array['username'] = "test";
	// 	$array['password'] = "lolade";
	// 	$url = $u."v1/admin/login";
	// 	$json_data = json_encode($array);
	// 	echo post($header, $url, $json_data);

	// //set password
	// 		$array['password'] = "lolade";
	// 		$url = $u."v1/admin/setPassword";
	// 		$json_data = json_encode($array);
	// 		echo put($header, $url, $json_data);
		
	// //set password
		// $url = $u."2.1/admin/password/".urlencode("kayox007@yahoo.com");
		// $json_data = json_encode($array);
		// echo get($header, $url, $json_data);

	// // Logout
	// $url = $u."2.1/admin/logout";
	// echo get($header, $url, $json_data);

	// //getdetails
	// 	$url = $u."v1/admin/profile";
	// 	echo get($header, $url);

    // // visitors
    // // Add visitors
	// 	$array['last_name'] = "last_name_".rand();
	// 	$array['first_name'] = "first_name_".rand();
	// 	$array['phone_number'] = "080".rand(20000000, 99999999);
	// 	$array['email'] = "test_api_".rand()."@gmail.com";
	// 	$array['address'] = rand(100, 999)." test Address, API Street, Computer State";
	// 	$array['whom_to_see'] = "whom_to_see".rand();
	// 	$array['resason'] = "resason ".rand();
	// 	$url = $u."v1/visitors/manage";
	// 	$json_data = json_encode($array);
	// 	echo post($header, $url, $json_data);

	// //getdetails
	// 	$url = $u."v1/visitors/manage/1";
	// 	echo get($header, $url);

	// //getdetails
	// 	$url = $u."v1/visitors/manage/list";
	// 	echo get($header, $url);

	// //getdetails
	// 	$url = $u."v1/visitors/manage/search/last";
	// 	echo get($header, $url);

	// //remove
	// 	$url = $u."v1/visitors/manage/6";
	// 	echo delete($header, $url);

    // // Patient
    // // Add Patient
    //     $sex = array("Female", "Male");


	// 	$array['last_name'] = "last_name_".rand();
	// 	$array['first_name'] = "first_name_".rand();
	// 	$array['age'] = rand(18, 100);
	// 	$array['phone_number'] = "080".rand(20000000, 99999999);
	// 	$array['sex'] = $sex[rand(0, 1)];
	// 	$array['email'] = "test_api_".rand()."@gmail.com";
	// 	$array['address'] = rand(100, 999)." test Address, API Street, Computer State";
	// 	$array['next_of_Kin'] = "next_of_Kin_".rand();
	// 	$array['next_of_contact'] = "next_of_contact_".rand();
	// 	$array['next_of_address'] = "next_of_address_".rand();
	// 	$array['allergies'] = "allergies ".rand();
	// 	$url = $u."v1/patient/manage";
	// 	$json_data = json_encode($array);
	// 	echo post($header, $url, $json_data);

    // // edit Patient
    //     $sex = array("Female", "Male");


    //     $array['ref'] = 644;
    //     $array['last_name'] = "last_name_".rand();
    //     $array['first_name'] = "first_name_".rand();
    //     $array['age'] = rand(18, 100);
    //     $array['phone_number'] = "080".rand(20000000, 99999999);
    //     $array['sex'] = $sex[rand(0, 1)];
    //     $array['email'] = "test_api_".rand()."@gmail.com";
    //     $array['address'] = rand(100, 999)." test Address, API Street, Computer State";
    //     $array['next_of_Kin'] = "next_of_Kin_".rand();
    //     $array['next_of_contact'] = "next_of_contact_".rand();
    //     $array['next_of_address'] = "next_of_address_".rand();
    //     $array['allergies'] = "allergies ".rand();
    //     $url = $u."v1/patient/manage";
    //     $json_data = json_encode($array);
    //     echo put($header, $url, $json_data);

	// //getdetails
	// 	$url = $u."v1/patient/manage/129";
	// 	echo get($header, $url);

	// //getdetails
	// 	$url = $u."v1/patient/manage/list?page=33";
	// 	echo get($header, $url);

	// //getdetails
	// 	$url = $u."v1/patient/manage/search/ade";
	// 	echo get($header, $url);

	// // Appointments
    // // Add Appointments
    //     $location = array("Lagos", "Abuja", "Rivers", "Outside Nigeria");
    //     $procedure = array("Boady Confidence", "Facials", "Breast", "Skin");
	// 	$array['names'] = "names ".rand();
	// 	$array['email'] = "scentness@yahoo.com";
	// 	$array['phone'] = "080".rand(20000000, 99999999);
	// 	$array['location'] = $location[rand(0, 3)];
	// 	$array['procedure'] = $procedure[rand(0, 3)];
	// 	$array['message'] = rand(100, 999)." test Address, API Street, Computer State";
	// 	$array['next_appointment'] = "2023-".rand(1,12)."-".rand(10, 31)."T12:58";
	// 	$array['patient_id'] = rand(0, 644);
	// 	$url = $u."v1/appointments/manage";
	// 	$json_data = json_encode($array);
	// 	echo post($header, $url, $json_data);

    // // edit Appointments
	// 	$array['ref'] = 1604;
	// 	$location = array("Lagos", "Abuja", "Rivers", "Outside Nigeria");
	// 	$procedure = array("Boady Confidence", "Facials", "Breast", "Skin");
	// 	$array['names'] = "names ".rand();
	// 	$array['email'] = "scentness@yahoo.com";
	// 	$array['phone'] = "080".rand(20000000, 99999999);
	// 	$array['location'] = $location[rand(0, 3)];
	// 	$array['procedure'] = $procedure[rand(0, 3)];
	// 	$array['message'] = rand(100, 999)." test Address, API Street, Computer State";
	// 	$array['next_appointment'] = "2023-".rand(1,12)."-".rand(10, 31)."T12:58";
	// 	$array['patient_id'] = rand(0, 644);
    //     $url = $u."v1/appointments/manage";
    //     $json_data = json_encode($array);
    //     echo put($header, $url, $json_data);

	// //get Appointments
	// $url = $u."v1/appointments/manage/15";
	// echo get($header, $url);

	// //get Appointments
	// 	$url = $u."v1/appointments/manage/list?page=1";
	// 	echo get($header, $url);

	//search Appointments
		// $url = $u."v1/appointments/manage/list/new";
		// echo get($header, $url);

	// //search Appointments
	// 	$url = $u."v1/appointments/manage/list/scheduled";
	// 	echo get($header, $url);

	// //search Appointments
	// 	$url = $u."v1/appointments/manage/list/cancelled";
	// 	echo get($header, $url);

	// // search Appointments
	// 	$url = $u."v1/appointments/manage/view/today";
	// 	echo get($header, $url);

	// //search Appointments
	// 	$url = $u."v1/appointments/manage/view/past";
	// 	echo get($header, $url);

	// //search Appointments
	// 	$url = $u."v1/appointments/manage/view/upcoming";
	// 	echo get($header, $url);

	// //search Appointments
	// 	$url = $u."v1/appointments/manage/search/ade";
	// 	echo get($header, $url);

    // // schedule Appointments
    //     $array['ref'] = 15;
    //     $array['appointmentDate'] = "2022-10-28T12:58";
    //     $url = $u."v1/appointments/schedule";
    //     $json_data = json_encode($array);
    //     echo put($header, $url, $json_data);

    // //cancel
    //     $url = $u."v1/appointments/cancel/23";
    //     echo put($header, $url, '');

    // //delete
    //     $url = $u."v1/appointments/manage/23";
    //     echo delete($header, $url);

    // // invoice
    // // Add invoice
	// 	$array['patient_id'] = rand(1, 500);
    //     for($i = 0; $i < rand(1, 20); $i++) {
    //         $array['billing_component'][$i]['id'] = rand(1, 11);
    //         $array['billing_component'][$i]['quantity'] = rand(1, 10);
    //         $array['billing_component'][$i]['description'] = "random description ".rand();
    //     }

	// 	$url = $u."v1/invoice/manage";
	// 	$json_data = json_encode($array);
	// 	echo post($header, $url, $json_data);

    // // edit Patient
	// 	$array['ref'] = 22;
    //     for($i = 0; $i < rand(1, 20); $i++) {
    //         $array['billing_component'][$i]['id'] = rand(1, 11);
    //         $array['billing_component'][$i]['quantity'] = rand(1, 10);
    //         $array['billing_component'][$i]['description'] = "random description ".rand();
    //     }

    //     $url = $u."v1/invoice/manage";
    //     $json_data = json_encode($array);
    //     echo put($header, $url, $json_data);

	// // post payment
	// 	$array['ref'] = 4;
	// 	$array['amount'] = 200;

	// 	$url = $u."v1/invoice/pay";
	// 	$json_data = json_encode($array);
	// 	echo put($header, $url, $json_data);

    // //getdetails
    //     $url = $u."v1/invoice/component";
    //     echo get($header, $url);

	// //getdetails
	// 	$url = $u."v1/invoice/manage/4";
	// 	echo get($header, $url);

    // //getdetails
    //     $url = $u."v1/invoice/manage/list";
    //     echo get($header, $url);

    // //getdetails
    //     $url = $u."v1/invoice/manage/list/unpaid";
    //     echo get($header, $url);

    // //getdetails
	// 	$url = $u."v1/invoice/manage/search/INV10001";
	// 	echo get($header, $url);

    // //delete
    //     $url = $u."v1/invoice/manage/23";
    //     echo delete($header, $url);

    // // Billing Component
    // // Add Billing Component
		
    //     $array['title'] = "title".rand();
    //     $array['cost'] = rand(1008, 9000000);
    //     $array['description'] = "random description ".rand();

	// 	$url = $u."v1/billingComponent/manage";
	// 	$json_data = json_encode($array);
	// 	echo post($header, $url, $json_data);

    // // edit Billing Component
	// 	$array['ref'] = 14;
    //     $array['title'] = "title ".rand();
    //     $array['cost'] = rand(1008, 9000000);
    //     $array['description'] = "random description ".rand();

    //     $url = $u."v1/billingComponent/manage";
    //     $json_data = json_encode($array);
    //     echo put($header, $url, $json_data);

	// //get Billing Component
	// 	$url = $u."v1/billingComponent/manage/4";
	// 	echo get($header, $url);

    // //get Billing Component
    //     $url = $u."v1/billingComponent/manage/list";
    //     echo get($header, $url);

    // //get Billing Component
    //     $url = $u."v1/billingComponent/manage/search/reast";
    //     echo get($header, $url);

	// // change status
	// 	$array['ref'] = 14;
	// 	$array['status'] = "activate";
	// 	$url = $u."v1/billingComponent/status";
	// 	$json_data = json_encode($array);
	// 	echo put($header, $url, $json_data);

	// //remove
	// 	$url = $u."v1/billingComponent/manage/12";
	// 	echo delete($header, $url);

    // Inventory
    // Add Inventory
		
        $array['title'] = "title".rand();
        $array['cost'] = rand(1008, 9000000);
        $array['description'] = "random description ".rand();

		$url = $u."v1/inventory/manage";
		$json_data = json_encode($array);
		echo post($header, $url, $json_data);

    // // edit Inventory
	// 	$array['ref'] = 14;
    //     $array['title'] = "title ".rand();
    //     $array['cost'] = rand(1008, 9000000);
    //     $array['description'] = "random description ".rand();

    //     $url = $u."v1/inventory/manage";
    //     $json_data = json_encode($array);
    //     echo put($header, $url, $json_data);

	// //get Inventory
	// 	$url = $u."v1/inventory/manage/4";
	// 	echo get($header, $url);

    // //get Inventory
    //     $url = $u."v1/inventory/manage/list";
    //     echo get($header, $url);

    // //get Inventory
    //     $url = $u."v1/inventory/manage/search/reast";
    //     echo get($header, $url);

	// // change status
	// 	$array['ref'] = 14;
	// 	$array['status'] = "activate";
	// 	$url = $u."v1/inventory/status";
	// 	$json_data = json_encode($array);
	// 	echo put($header, $url, $json_data);

	// //remove
	// 	$url = $u."v1/inventory/manage/12";
	// 	echo delete($header, $url);

    // // Settings
    // // get settings
	// 	$url = $u."v1/settings";
	// 	$json_data = json_encode($array);
	// 	echo get($header, $url);

    // // Add settings
    //     $array = [
    //         "consultationFee-cost" => 20000,
    //         "consultationFee-component-id" => 1,
    //         "lateFee" => 5000,
    //         "registrationFee-cost" => 5000,
    //         "registrationFee-component-id" => 6,
    //         "medicationCategory" => 1,
    //         "lowInventoryCount" => 0,
    //         "alertGroup" => 'lekki_hill_admin'
    //     ];
    // 	$url = $u."v1/settings";
    // 	$json_data = json_encode($array);
    // 	echo post($header, $url, $json_data);


	print_r($header);
	echo "<br>";
	print_r($json_data);
	echo "<br>";
	echo $url;

function get($header,$url) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}

function post($header,$url, $data) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$output = curl_exec($ch);
	curl_close($ch);
	
	return $output;
}

function put($header,$url, $data) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$output = curl_exec($ch);
	curl_close($ch);
	
	return $output;
}

function delete($header,$url) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}
?>