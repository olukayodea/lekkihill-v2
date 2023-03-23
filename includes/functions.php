<?php
	/**
	 * modify the country data for users API in $reward->selectActiveReward()
	 */
	session_start();
	date_default_timezone_set("Africa/Lagos");
	
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
	
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
	define("table_name_prefix", "lekkihill_");

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

	define( 'LH_PLUGIN_DIR', dirname(  __FILE__ ) )."/";

	require_once 'classes/pdf/tcpdf.php';
	$pdf = new TCPDF("P", "mm", "A4", true, 'UTF-8', false);

	include_once("classes/admin.php");
	include_once("classes/patient.php");
	include_once('classes/clinic_post_op.php');
	include_once('classes/vitals.php');
	include_once('classes/clinic_fluid_balance.php');
	include_once('classes/clinic_medication.php');
	include_once('classes/clinic_lab.php');
	include_once('classes/clinic_doctors_report.php');
	include_once("classes/clinic.php");
	include_once("classes/billing.php");
	include_once("classes/billing_component.php");
	include_once("classes/labouratory_component.php");
	include_once("classes/invoice.php");
	include_once("classes/invoiceLog.php");
	include_once("classes/appointments.php");
	include_once("classes/inventory_used.php");
	include_once("classes/inventory_count.php");
	include_once("classes/inventory.php");
	include_once("classes/generateDownloads.php");
	include_once("classes/inventory_category.php");
	include_once("classes/visitors.php");
	include_once("classes/settings.php");

	$admin = new admin;
	$patient = new patient;
	$clinic_post_op = new clinic_post_op;
	$vitals = new vitals;
	$clinic_fluid_balance = new clinic_fluid_balance;
	$clinic_medication =  new clinic_medication;
	$clinic_lab =  new clinic_lab;
	$clinic_doctors_report = new clinic_doctors_report;
	$clinic = new clinic;
	$billing = new billing;
	$billing_component = new billing_component;
	$labouratory_component = new labouratory_component;
	$invoice = new invoice;
	$invoiceLog = new invoiceLog;
	$appointments = new appointments;
	$inventory = new inventory;
	$inventory_category = new inventory_category;
	$settings = new settings;
	$visitors = new visitors;
	
?>