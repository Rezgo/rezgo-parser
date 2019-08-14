<?php

if(!$site->config('REZGO_HIDE_HEADERS') && !$_REQUEST['headless']) {
	
	header('Cache-Control: no-cache');
	header('Pragma: no-cache');
	header('Content-Type: text/html; charset=utf-8');
	
	echo $site->getHeader();

} else {

?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $site->pageTitle?></title>
</head>

<body>
<?php } ?>