<?php 
	// This is the printable version of booking_complete.php
	
	require('rezgo/include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite();
	
?>

<?=$site->getTemplate('booking_complete_print.php')?>