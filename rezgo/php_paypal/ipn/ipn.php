<?php
	/*
	 * ipn.php
	 *
	 * PHP Toolkit for PayPal v0.51
	 * http://www.paypal.com/pdn
	 *
	 * Copyright (c) 2004 PayPal Inc
	 *
	 * Released under Common Public License 1.0
	 * http://opensource.org/licenses/cpl.php
	 *
	*/
	
	//get global configuration information
	include_once('../includes/global_config.inc.php'); 
	
	//get pay pal configuration file
	include_once('../includes/config.inc.php'); 
	
	require($_SERVER['DOCUMENT_ROOT'].'/rezgo/include/page_header.php');
	
	$time = time();
	
	$db->query("INSERT INTO `ipn_log` SET 
		`timestamp` = '$time',
		`query` = 'RAW', 
		`request` = '".addslashes(print_r($_REQUEST, 1))."'
	");
	
	header('HTTP/1.1 200 OK');
	
	// clean the HTMLentity request array can validate the response with paypal
	// if the decoded htmlentities are present, paypal will reject the validation request
	foreach($_REQUEST as $k => $v) {
		$_REQUEST[$k] = htmlspecialchars_decode($v, ENT_QUOTES);
	}
	
	$site = new RezgoSite(secure);
	
	$trans = $_REQUEST['item_number'];
	$paypal_signature = md5($trans);
	$payer_email = $_REQUEST['payer_email'];
	$txn_id = $_REQUEST['txn_id'];
	$amount = $_REQUEST['mc_gross'];
	
	//decide which post method to use
	switch($paypal[post_method]) { 
	
	case "libCurl": //php compiled with libCurl support
	
	$result=libCurlPost($paypal[url],$_REQUEST); 
	
	
	break;
	
	
	case "curl": //cURL via command line
	
	$result=curlPost($paypal[url],$_REQUEST); 
	
	break; 
	
	
	case "fso": //php fsockopen(); 
	
	$result=fsockPost($paypal[url],$_REQUEST); 
	
	break; 
	
	
	default: //use the fsockopen method as default post method
	
	$result=fsockPost($paypal[url],$_REQUEST);
	
	break;
	
	}
	
	//check the ipn result received back from paypal
	
	// Transaction info
	$cid = REZGO_CID;
													
	// SUCCESSFUL IPN RESPONSE
	if(eregi("VERIFIED",$result)) { 
		$ipn_response_status = "VERIFIED";
		
		if(isset($paypal['business']) && $_REQUEST['payment_status'] == 'Completed') {
			$transaction_response_status = "Transaction confirmed";
			
			// Send Paypal Payment Verification to gateway
			$query = 'https://'.$site->xml_path.'&';
			
			if ($cid && $paypal_signature) {
				$paypal_success_query = $query."i=commit&paypal_amount=".$amount."&signature=".$paypal_signature;
				
				$site->fetchXML($paypal_success_query);
				$gateway_paypal_xml_response = $site->get;
		
				if ($gateway_paypal_xml_response) {
					$gateway_paypal_response = "Payment acknowledged";
				}
			}
			
		}
		else{
			$transaction_response_status = "Transaction error";
		}
	
	} 
	// FAILED IPN RESPONSE
	else {
		$ipn_response_status = "INVALID";
	}

	// Log
	$ipn_status = "IPN Response: ".$ipn_response_status." / Transaction Status Response: ".$transaction_response_status." / Gateway Paypal Response: ".$gateway_paypal_response;

	$pstr = "Business: ".$paypal[business]." / ".
		"Amount: ".$amount." / ".
		"cid: ".$cid." / ".
		"paypal_signature: ".$paypal_signature." / ".
		"Paypal Transaction ID: ".$txn_id." / ".
		"Paypal Success Query: ".$paypal_success_query." / ".
		"XML Response: ".$gateway_paypal_xml_response."\n\n";

	$db->query("INSERT INTO `ipn_log` SET 
		`trans` = '".addslashes($trans)."', 
		`timestamp` = '".addslashes($time)."', 
		`time` = '".addslashes(date("Y-m-d h:i:s A", $time))."', 
		`query` = '".addslashes($ipn_status)."', 
		`info` = '".addslashes($pstr)."',
		`request` = '".addslashes(print_r($_REQUEST, 1))."'
	");
?>