<?php
	// This file handles the fetching of external files (like the Rezgo XML)
	// The input is $url and the output should be returned with $result.
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);  
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_TIMEOUT,30);
	
	$result = curl_exec($ch);	
	
	if(curl_error($ch)) {
		$this->error(curl_error($ch).' (outbound: '.$url.')');
	}
	
	curl_close($ch);
	
?>