<?
	// this file is called from the short-url bookmark button, it calls rezgo.me (the rezgo shortening service)
	// and fetches a short url to be displayed in the dropdown.  This script can also be used for any other
	// url shortening api call (bit.ly, tinyurl, etc)
	//  &#10549;

	if($_REQUEST[url]) {
		$url = file_get_contents('http://rezgo.me/api?format=simple&action=shorturl&url='.urlencode($_REQUEST[url]));
		//echo '<span id="rezgo-short-url-label">Quick link to this page</span><br />'.$url;
		echo '<span id="rezgo-short-url-label">Quick link to this page</span><br /><input type="text" id="rezgo-short-url" class="form-control" onclick="this.select();" value="'.$url.'" />';
	}
?>