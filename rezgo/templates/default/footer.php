<?php
	// this is your footer template, you can either grab the Rezgo footer from XML or create your own here
	
	echo $site->getAnalytics();
	
	if(!$site->config('REZGO_HIDE_HEADERS') && !$_REQUEST['headless']) {
		echo $site->getFooter();
	} else {
?>
</body>
</html>
<?php } ?>