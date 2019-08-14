<?php 
	// This script handles the booking requests made via ajax by book.php
	require('rezgo/include/page_header.php');

	// start a new instance of RezgoSite
	$site = new RezgoSite(secure);

	if ($_POST['rezgoAction'] == 'addGiftCard') {
		$result = $site->sendGiftOrder($_POST);
		
		if ($result->status == 'Card created') {
			$_SESSION["GIFT_CARD_KEY"] = $result->card;

			$result->response = 1;
		}
		else {
			// this booking failed, send a status code back to the requesting page
			if ($result->message == 'Availability Error' || $result->mesage == 'Fatal Error') 
			{
				$result->response = 2;
			} 
			else if (
			$result->message == 'Payment Declined' || $result->message == 'Invalid Card Checksum' || $result->message == 'Invalid Card Expiry') 
			{
				$result->response = 3;
			} 
			else if ($result->message == 'Account Error') 
			{
				// hard system error, no commit requests are allowed if there is no valid payment method
				$result->response = 5;
			} 
			else 
			{
				$result->response = 4;
			}
		}

		$json = json_encode((array)$result); 
		echo '|||' . $json;
	}

	if ($_POST['rezgoAction'] == 'getGiftCard') {
		$result = $site->getGiftCard($_POST['gcNum']);

		if (array_key_exists('card', $result)) {
			$result->card->status = 1;

			$result->card->number = $site->cardFormat($result->card->number);
		} 
		else {
			$result->card->status = 0;
		}

		echo '|||' . json_encode($result->card);
	}
?>