<?php
include_once("functions.php");

if (isset(apache_request_headers()['Authorization'])) {
    $uth = explode(" ", apache_request_headers()['Authorization'] ?? "");
} else {
    $uth = explode(" ", apache_request_headers()['authorization'] ?? "");
}
$data = file_get_contents('php://input');
$header['key'] = apache_request_headers()['key'] ?? "";
$header['location'] = apache_request_headers()['location'] ?? "";
$header['method'] = $_SERVER['REQUEST_METHOD'] ?? "";
$header['device'] = apache_request_headers()['device'] ?? "";
$header['auth'] = trim($uth[1] ?? "");

if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) {
    if (isset($_REQUEST['resource'])) {
        $generateDownloads = new generateDownloads;
        global $common;
        $resource = $common->get_prep( $_REQUEST['resource'] ) ;
        $generateDownloads->validateAccess($header, $resource);
    } else {
        header("HTTP/1.1 404 Not Found");
    }
}
?>