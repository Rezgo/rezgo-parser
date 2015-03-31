<?php 
	// This is the about us page
	
	require('rezgo/include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite();
	
?>

<?=$site->getTemplate('frame_header')?>

<?=$site->getTemplate('about')?>
			
<?=$site->getTemplate('frame_footer')?>