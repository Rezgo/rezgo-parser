<?php 
	// This is the waiver page
	require('rezgo/include/page_header.php');

	// start a new instance of RezgoSite
	$site = $_REQUEST['sec'] ? new RezgoSite(secure) : new RezgoSite();

	// Page title
	$site->setPageTitle($_REQUEST['title'] ? $_REQUEST['title'] : 'Print Waiver');
?>


<?php echo $site->getTemplate('waiver_print')?>

