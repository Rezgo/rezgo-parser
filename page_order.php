<?php 
	// This is the order page
	
	require('rezgo/include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite();
	
?>

<?=$site->getTemplate('frame_header')?>

<?=$site->getTemplate('order')?>

<?=$site->getTemplate('frame_footer')?>