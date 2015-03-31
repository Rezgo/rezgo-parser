<?php
	// This is the main booking page
	
	require('rezgo/include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite(secure);
	
?>

<?=$site->getTemplate('frame_header')?>

	<?=$site->getTemplate('book')?>

<?=$site->getTemplate('frame_footer')?>