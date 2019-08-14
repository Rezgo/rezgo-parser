<?php
	// any new page must start with the page_header, it will include the correct files
	// so that the rezgo parser classes and functions will be available to your templates

	require('rezgo/include/page_header.php');

	// start a new instance of RezgoSite
	$site = new RezgoSite();

	$company = $site->getCompanyDetails();

?>

<?php echo $site->getTemplate('frame_header')?>

<?php echo $site->getTemplate('privacy')?>

<?php echo $site->getTemplate('frame_footer')?>
