<?php 
	// This script handles the booking requests made via ajax by book.php
	
	require('rezgo/include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite(secure);
	
	if($_POST['rezgoAction'] == 'get_paypal_token') {
		
		// send a partial commit (a=get_paypal_token) to get a paypal token for the modal window
		// include the return url (this url), so the paypal API can use it in the modal window
		if($_POST['mode'] == 'mobile') {
			$result = $site->sendBooking(null, 'a=get_paypal_token&paypal_return_url=https://'.$_SERVER['HTTP_HOST'].REZGO_DIR.'/paypal');
		} else {
			$result = $site->sendBookingOrder(null, '<additional>get_paypal_token</additional><paypal_return_url>https://'.$_SERVER['HTTP_HOST'].REZGO_DIR.'/paypal</paypal_return_url>');
		}
		
		$response = ($site->exists($result->paypal_token)) ? $result->paypal_token : 0;
		
	} elseif($_POST['rezgoAction'] == 'book') {
	
		$result = $site->sendBookingOrder();
		
		if($result->status == 1) {
		
			// start a session so we can save the analytics code
			session_start();
		
			$response = $site->encode($result->trans_num);	
			
			// Set a session variable for the analytics to carry to the receipt's first view
			$_SESSION['REZGO_CONVERSION_ANALYTICS'] = $result->analytics_convert;
			
			// Add a blank script tag so that this session is detected on the receipt
			$_SESSION['REZGO_CONVERSION_ANALYTICS'] .= '<script></script>';
		
		} else {
			// this booking failed, send a status code back to the requesting page
			
			if($result->message == 'Availability Error' || $result->mesage == 'Fatal Error') {
				$response = 2;
			} else if($result->message == 'Payment Declined' || $result->message == 'Invalid Card Checksum' || $result->message == 'Invalid Card Expiry') {
				$response = 3;
			} else if($result->message == 'Account Error') {
				// hard system error, no commit requests are allowed if there is no valid payment method
				$response = 5;
			} else {
				$response = 4;
			}
		}
	}
	
	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		// ajax response if we requested this page correctly
echo $response;		
	} else {
		// if, for some reason, the ajax form submit failed, then we want to handle the user anyway
		
		die('Something went wrong during booking. Your booking may have still been completed.');
		
		if($result->status == 1) { $site->sendTo("/complete/".$trans_num); }
		else {
			echo 'ERROR: '.$result->message;
		}
		
	}
?>