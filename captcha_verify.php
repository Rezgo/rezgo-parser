<?php
	// any new page must start with the page_header, it will include the correct files
	// so that the rezgo parser classes and functions will be available to your templates
	
	require('rezgo/include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite();
	
	function verify_captcha($captcha_token) {
		
		$captcha_endpoint = 'https://www.google.com/recaptcha/api/siteverify';
		
		$captcha_params = array('secret' => REZGO_CAPTCHA_PRIV_KEY, 'response' => $captcha_token);
		
		// initialize connection 
		$ch = curl_init($captcha_endpoint);
		// standard i/o streams 
		curl_setopt($ch, CURLOPT_VERBOSE, 1); 
		// turn off the server and peer verification 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
		// set to return data to string ($response) 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		// regular post
		curl_setopt($ch, CURLOPT_POST, 1);  
		
		// send the query	
		// curl_setopt($ch, CURLOPT_POSTFIELDS, $captcha_token); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($captcha_params)); 
		
		// execute cUrl session 
		$response = curl_exec($ch);
		
		// check if any error occurred
		if(!curl_errno($ch)) {
			return $response;
		} 
		
		curl_close($ch);		
	
	}
	
	echo verify_captcha($_REQUEST['token']);

?>