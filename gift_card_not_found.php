<?php 
	// This is the gift card page
	require('rezgo/include/page_header.php');

	// start a new instance of RezgoSite
	$site = new RezgoSite();
?>

<?php echo $site->getTemplate('frame_header')?>

<?php echo $site->getTemplate('gift_card_not_found')?>

<?php echo $site->getTemplate('frame_footer')?>