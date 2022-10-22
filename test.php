<?php
echo "<pre>";
	//$product_key = rand();
	$product_key = '32333518';
	
	$u = "http://127.0.0.1/lekkihill-v2/";
    $token = "2763879251216663141164UDX98NSAQH";
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

    // // Invoice
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
	// 	$url = $u."v1/patient/manage/1";
	// 	echo get($header, $url);

	// //getdetails
	// 	$url = $u."v1/patient/manage/list";
	// 	echo get($header, $url);

	// //getdetails
	// 	$url = $u."v1/patient/manage/search/ade";
	// 	echo get($header, $url);

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

    // edit Patient
		$array['ref'] = 22;
        for($i = 0; $i < rand(1, 20); $i++) {
            $array['billing_component'][$i]['id'] = rand(1, 11);
            $array['billing_component'][$i]['quantity'] = rand(1, 10);
            $array['billing_component'][$i]['description'] = "random description ".rand();
        }

        $url = $u."v1/invoice/manage";
        $json_data = json_encode($array);
        echo put($header, $url, $json_data);

	// //getdetails
	// 	$url = $u."v1/invoice/manage/4";
	// 	echo get($header, $url);

    // //getdetails
    //     $url = $u."v1/invoice/manage/list";
    //     echo get($header, $url);

    // //getdetails
    //     $url = $u."v1/invoice/manage/search/nat";
    //     echo get($header, $url);

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