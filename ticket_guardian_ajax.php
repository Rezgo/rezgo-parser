<?php
	// This script handles the booking requests made via ajax by book.php

	require('rezgo/include/page_header.php');
	require('rezgo/include/class.ticketguardian.rezgo.php');

	// start a new instance of RezgoSite
	$site = new RezgoSite(secure);

  // start new ticket guardian instance
  $ticketguardian = new RezgoTicketGuardian();

	$response = 'empty';

	// Get the quote
	if ($_REQUEST['action'] == 'quote') {
    $data = $_REQUEST['quote_data'];
    $quote = $ticketguardian->postQuote($data);
    echo json_encode($quote);
	}
