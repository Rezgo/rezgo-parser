<?php 
	// This is the about us page
	
	require('rezgo/include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite();
	
?>

<?php echo $site->getTemplate('frame_header')?>

<?php echo $site->getTemplate('404page')?>
			
<?php echo $site->getTemplate('frame_footer')?>