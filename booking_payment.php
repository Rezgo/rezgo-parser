<?php 
	// This page is the credit card payment display, it is fetched via iframe to display the form
	
	require('rezgo/include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite(secure);
?>

<?=$site->getTemplate('booking_payment')?>