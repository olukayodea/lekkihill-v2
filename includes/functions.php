<?php
	/**
	 * modify the country data for users API in $reward->selectActiveReward()
	 */
	session_start();
	date_default_timezone_set("Africa/Lagos");
	
    // ini_set('display_errors', 1);
    // error_reporting(E_ALL);
	
	$pageUR1 = $_SERVER["SERVER_NAME"];
	$curdomain = str_replace("www.", "", $pageUR1);
	$local = false;
	
    if ($curdomain == "127.0.0.1") {
		define("DOCUMENT_ROOT", "/Applications/MAMP/htdocs/lekkihill-v2");
	} else {
		define("DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"]);
	}
	include_once(DOCUMENT_ROOT."/cred.php");
	// include_once(DOCUMENT_ROOT."/vendor/autoload.php");

	define("URL", $URL);
	define("servername",  $servername);
	define("dbusername",  $dbusername);
	define("dbpassword",  $dbpassword);
	define("dbname",  $dbname);
	define("table_prefix", $table_prefix );

	define("URLAdmin", $admin);
	
	define("limit", 20);
	
	include_once("classes/config.php");

	$database = new database;
	
	define("replyMail", $replyMail);
	
	include_once("classes/mailer/class.phpmailer.php");
	//log and reports
	include_once("classes/common.php");
	include_once("classes/class-phpass.php");
	$common = new common;
	$wp_hasher = new PasswordHash( 8, true );


	include_once("classes/alerts.php");
	$alerts		= new alerts;

	include_once("classes/api.php");
	$api = new api;

	include_once("classes/admin.php");
	include_once("classes/patient.php");
	$admin = new admin;
	$patient = new patient;

	
?>