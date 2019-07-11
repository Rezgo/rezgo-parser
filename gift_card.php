<?php 
	// This is the gift card page
	require('rezgo/include/page_header.php');

	// start a new instance of RezgoSite
	$site = new RezgoSite(secure);
	
	echo $site->getTemplate('frame_header');

 echo $site->getTemplate('gift_card');

 echo $site->getTemplate('frame_footer');