<?php
    $nofollow = true;
    include_once("../includes/functions.php");

    if (apache_request_headers()['Authorization']) {
        $uth = explode(" ", apache_request_headers()['Authorization']);
    } else {
        $uth = explode(" ", apache_request_headers()['authorization']);
    }
    $data = file_get_contents('php://input');
    $header['key'] = apache_request_headers()['key'];
    $header['location'] = apache_request_headers()['location'];
    $header['method'] = $_SERVER['REQUEST_METHOD'];
    $header['device'] = apache_request_headers()['device'];
    $header['auth'] = trim($uth[1]);
    
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: *");
    header("Access-Control-Allow-Headers: Authorization, Content-Type, location, key, device, HTTP_KEY, HTTP_DEVICE, HTTP_FCM_TOKEN");

    error_log("====== Admin Request ======");
    error_log($_REQUEST['request']);
    error_log("====== Admin Header ======");
    error_log(json_encode($header, JSON_PRETTY_PRINT));
    error_log("====== Admin Data ======");
    error_log($data);

    $response = $apiAdmin->prepare($header, $_REQUEST['request'], $data, $_FILES);

    echo $api->convert_to_json($response);
?>