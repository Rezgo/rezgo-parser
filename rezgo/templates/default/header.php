<?php

if(!$site->config('REZGO_HIDE_HEADERS')) {
	
	header('Cache-Control: no-cache');
	header('Pragma: no-cache');
	header('Content-Type: text/html; charset=utf-8');
	
	echo $site->getHeader();

}

?>