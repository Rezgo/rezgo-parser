<?php 
	// This is the about us page
	
	require('rezgo/include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite();
	
	$title = $site->getPageName($page);
	
	$site->setPageTitle($title);
?>

<?=$site->getTemplate('frame_header')?>

<?=$site->getTemplate('content_page')?>

<?=$site->getTemplate('frame_footer')?>