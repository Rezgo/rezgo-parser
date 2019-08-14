<?php 
	// any new page must start with the page_header, it will include the correct files
	// so that the rezgo parser classes and functions will be available to your templates
	
	require('rezgo/include/page_header.php');
	  
	// start a new instance of RezgoSite
	$site = new RezgoSite();
	
?>

<?php echo $site->getTemplate('frame_header')?>

<?php if ($_REQUEST['trans_num'] && $_REQUEST['trans_num'] != 'all') { ?>

	<?php echo $site->getTemplate('review')?>

<?php } else { ?>

	<?php echo $site->getTemplate('review_list')?>

<?php } ?>

<?php echo $site->getTemplate('frame_footer')?>