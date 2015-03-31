<?php 
	// This page is the calendar display, it is fetched via AJAX to display the calendar
	
	require('rezgo/include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite();
	
	/*
		this query searches for an item based on a com id (limit 1 since we only want one response)
		then adds a $f (filter) option by uid in case there is an option id, and adds a date in case there is a date set	
	*/
	
	$item = $site->getTours('t=com&q='.$_REQUEST['com'].'&d='.$_REQUEST['date'].'&limit=1', 0);
	
	// if the item does not exist, we want to generate an error message and change the page accordingly
	if(!$item) { 
		$item->unavailable = 1;
		$item->name = 'Item Not Available'; 
	}	
	
?>

<?=$site->getTemplate('calendar_month')?>