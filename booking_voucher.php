<?php 
	// This is the booking voucher
	
	require('rezgo/include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite(secure);

	$site->setMetaTags('<meta name="robots" content="noindex, nofollow">');
?>
	
<?php echo $site->getTemplate('booking_voucher')?>