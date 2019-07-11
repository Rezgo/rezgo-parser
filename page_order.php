<?php 
	// This is the order page
	
	require('rezgo/include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite();
	
?>

<?php echo $site->getTemplate('frame_header')?>

<?php echo $site->getTemplate('order')?>

<?php echo $site->getTemplate('frame_footer')?>