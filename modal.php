<?php 
	// This is the waiver modal
	require('rezgo/include/page_header.php');

	// start a new instance of RezgoSite
	$site = $_REQUEST['sec'] ? new RezgoSite(secure) : new RezgoSite();

	// Page title
	$site->setPageTitle($_REQUEST['title'] ? $_REQUEST['title'] : 'Waiver');
?>

<?php echo $site->getTemplate('frame_header')?>

<?php echo $site->getTemplate('modal')?>

<?php echo $site->getTemplate('frame_footer')?>
