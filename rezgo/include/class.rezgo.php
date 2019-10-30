<?php

	/*
		This is the Rezgo parser class, it handles processing for the Rezgo API.
		
		VERSION:
				3.0.0
		
		- Documentation and latest version
				https://www.rezgo.com/rezgo-open-source-booking-engine/
		
		- Finding your Rezgo CID and API KEY
				https://www.rezgo.com/support-article/create-api-keys
		
		AUTHOR:
				Kevin Campbell
				John McDonald
		
		Copyright (c) 2012-2018, Rezgo (A Division of Sentias Software Corp.)
		All rights reserved.
		
		Redistribution and use in source form, with or without modification,
		is permitted provided that the following conditions are met:
		
		* Redistributions of source code must retain the above copyright
		notice, this list of conditions and the following disclaimer.
		* Neither the name of Rezgo, Sentias Software Corp, nor the names of
		its contributors may be used to endorse or promote products derived
		from this software without specific prior written permission.
		* Source code is provided for the exclusive use of Rezgo members who
		wish to connect to their Rezgo API. Modifications to source code
		may not be used to connect to competing software without specific
		prior written permission.
		
		THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
		"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
		LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
		A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
		HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
		SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
		LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
		DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
		THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
		(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
		OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	*/

	class RezgoSite {
	
		var $version = '3.0.1';
		
		var $requestID;
		var $instanceID;
		
		var $xml_path;
		var $contents;
		var $get;
		var $xml;
		var $secure = 'http://';
		var $obj;
		
		var $country_list;
		
		// indexes are used to split up response caches by different criteria
		var $tours_index = 0; // split up by search string
		var $company_index = 0; // split up by CID (for vendors only, suppliers use 0)
		var $currency_values;
		var $tour_limit;
		var $refid;
		var $promo_code;
		var $pageTitle;
		var $metaTags;
		
		// calendar specific values
		var $calendar_name;
		var $calendar_com;
		var $calendar_active;
		
		var $calendar_prev;
		var $calendar_next;
		
		var $calendar_months = array();
		var $calendar_years = array();
		
		// api result caches improve performance by not hitting the gateway multiple times
		// searches that have differing args sort them into arrays with the index variables above
		var $company_response;
		var $page_response = array();
		var $tags_response;		
		var $search_response;
		var $tour_availability_response;
		var $search_bookings_response;
		var $search_total;
		var $cart_total;
		var $commit_response;
		var $contact_response;
		var $ticket_response;
		var $waiver_response;
		var $signing_response;
		var $review_response;
		var $pickup_response;
		var $payment_response;
		
		var $tour_forms;
		var $all_required;
		var $cart = array();
		var $cart_ids;
		var $gift_card;
		
		// debug and error stacks
		var $error_stack;
		var $debug_stack;
		
		// ------------------------------------------------------------------------------
		// if the class was called with an argument then we use that as the object name
		// this allows us to load the object globalls for included templates.
		// ------------------------------------------------------------------------------
		
		function __construct($secure=null, $newID=null) {
			if(!$this->config('REZGO_SKIP_BUFFER')) ob_start();
			
			// check the config file to make sure it's loaded
			if(!$this->config('REZGO_CID')) $this->error('REZGO_CID definition missing, check config file', 1);
			
			if($newID) { $this->requestID = $this->setRequestID(); }
			else { $this->requestID = ($_SESSION['requestID']) ? $_SESSION['requestID'] : $this->setRequestID(); }
			
			// get request ID if it exists, otherwise generate a fresh one
			$this->requestID = ($_SESSION['requestID']) ? $_SESSION['requestID'] : $this->setRequestID();
			
			$this->origin = $this->config('REZGO_ORIGIN');
			
			// assemble API address
			$this->xml_path = REZGO_XML.'/xml?transcode='.REZGO_CID.'&key='.REZGO_API_KEY.'&req='.$this->requestID.'&g='.$this->origin;
			$this->api_post_string = 'xml='.urlencode('<request><transcode>'.REZGO_CID.'</transcode><key>'.REZGO_API_KEY.'</key>');
			$this->advanced_xml_path = REZGO_XML.'/xml?req='.$this->requestID.'&g='.$this->origin.'&'.$this->api_post_string;
			
			// assemble template and url path
			$this->path = REZGO_DIR.'/templates/'.REZGO_TEMPLATE;
			$this->base = REZGO_URL_BASE;
			
			if(!defined(REZGO_PATH)) define("REZGO_PATH", $this->path);
			
			// it's possible to define the document root manually if there is an issue with the _SERVER variable
			if(!defined(REZGO_DOCUMENT_ROOT)) define("REZGO_DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"]);
			
			// set the secure mode for this particular page
			if (REZGO_CUSTOM_DOMAIN) {
				$this->setSecure($secure);
			} else {
				$this->setSecure('secure');
			}
			
			// perform some variable filtering
			if($_REQUEST['start_date']) {
				if(strtotime($_REQUEST['start_date']) == 0) unset($_REQUEST['start_date']);
			}
			
			if($_REQUEST['end_date']) {
				if(strtotime($_REQUEST['end_date']) == 0) unset($_REQUEST['end_date']);
			}
			
			// handle the refID if one is set
			if($_REQUEST['refid'] || $_REQUEST['ttl'] || $_COOKIE['rezgo_refid_val'] || $_SESSION['rezgo_refid_val']) {
				
				if($_REQUEST['refid'] || $_REQUEST['ttl']) {
					
					$new_header = $_SERVER['REQUEST_URI'];
					
					// remove the refid information wherever it is
					$new_header = preg_replace("/&?refid=[^&\/]*/", "", $new_header);
					$new_header = str_replace("?&", "?", $new_header);
					$new_header = preg_replace("/&?ttl=[^&\/]*/", "", $new_header);
					$new_header = str_replace("?&", "?", $new_header);
					
					if(substr($new_header, -1) == '?') { $new_header = substr($new_header, 0, -1); }
					
					$refid = $this->requestStr('refid');
					
					$ttl = ($this->requestStr('ttl')) ? $this->requestStr('ttl') : 7200;
					
				} elseif($_SESSION['rezgo_refid_val']) { 
				
					$refid = $_SESSION['rezgo_refid_val']; 
					$ttl = $_SESSION['rezgo_refid_ttl']; 
				
				} elseif($_COOKIE['rezgo_refid_val']) { 
				
					$refid = $_COOKIE['rezgo_refid_val']; 
					$ttl = $_COOKIE['rezgo_refid_ttl']; 
				
				}
				
				setcookie("rezgo_refid_val", $refid, time() + $ttl, '/', $_SERVER['SERVER_NAME']);
				setcookie("rezgo_refid_ttl", $ttl, time() + $ttl, '/', $_SERVER['SERVER_NAME']);
					
				// we need to set the session here before we header the user off or the old session will override the new refid each time
				if($ttl > 0) {
					$_SESSION['rezgo_refid_val'] = $refid;
					$_SESSION['rezgo_refid_ttl'] = $ttl;
				} else {
					unset($_SESSION['rezgo_refid_val']);
					unset($_SESSION['rezgo_refid_ttl']);
				}
				
					if(isset($new_header)) $this->sendTo((($this->checkSecure()) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$new_header);
				
			}	
			
			// handle the promo code if one is set
			if(isset($_REQUEST['promo']) || $_COOKIE['rezgo_promo'] || $_SESSION['rezgo_promo']) {
				
				$ttl = 1209600; // two weeks is the default time-to-live for the promo cookie
				
				if(isset($_REQUEST['promo']) && !$_REQUEST['promo']) {
					$_REQUEST['promo'] = ' '; // force a request below
					$ttl = -1; // set the ttl to -1, removing the promo code
				}
				
				if($_REQUEST['promo']) {
					if($_REQUEST['promo'] == ' ') unset($_REQUEST['promo']);
				
					$new_header = $_SERVER['REQUEST_URI'];
					
					// remove the promo information wherever it is
					$new_header = preg_replace("/&?promo=[^&\/]*/", "", $new_header);
					$new_header = str_replace("?&", "?", $new_header);
					
					// in case the format is /promo/text and the htaccess isn't reformatting it for above
					$new_header = preg_replace("/promo\/[^\/]*/", "", $new_header);
					$new_header = str_replace("//", "/", $new_header);
					
					if(substr($new_header, -1) == '?') { $new_header = substr($new_header, 0, -1); }
					
					$promo = $this->requestStr('promo');
				} 
				elseif($_SESSION['rezgo_promo']) { $promo = $_SESSION['rezgo_promo']; }
				elseif($_COOKIE['rezgo_promo']) { $promo = $_COOKIE['rezgo_promo']; }
				
				setcookie("rezgo_promo", $promo, time() + $ttl, '/', $_SERVER['SERVER_NAME']);
				
				// we need to set the session here before we header the user off or the old session will override the new promo code each time
				if($ttl > 0) {
					$_SESSION['rezgo_promo'] = $promo;
				} else {
					unset($_SESSION['rezgo_promo']);
				}
				
				if(isset($new_header)) $this->sendTo((($this->checkSecure()) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$new_header);	
			}
			
			// handle the add to cart request if one is set
			if(is_array($_REQUEST['add']) || $_COOKIE['rezgo_cart_'.REZGO_CID] || $_SESSION['rezgo_cart_'.REZGO_CID] || $_REQUEST[order] == 'clear') {
				
				$ttl = (REZGO_CART_TTL > 0 || REZGO_CART_TTL === 0) ? REZGO_CART_TTL : 86400;
				
				$clear = 0;
				if($_REQUEST['order'] == 'clear') {
					$new_header = urldecode($_SERVER['REQUEST_URI']);
					
					// remove the clear cart information wherever it is
					$new_header = preg_replace("/&?order=clear/", "", $new_header);
					$new_header = str_replace("?&", "?", $new_header);
					
					if(substr($new_header, -1) == '?') { $new_header = substr($new_header, 0, -1); }
					
					$this->clearCart();
					
					$clear = 1;
				}
				
				if(is_array($_REQUEST['add'])) {
				
					if(!$new_header) $new_header = urldecode($_SERVER['REQUEST_URI']); // urldecode is needed to catch the [ marks on the array
					
					// remove the cart information wherever it is
					$new_header = preg_replace("/&?add\[[^&\/]*\]=[^&\/]*/", "", $new_header);
					$new_header = str_replace("?&", "?", $new_header);
					
					if(substr($new_header, -1) == '?') { $new_header = substr($new_header, 0, -1); }
					
					$cart = $this->addToCart($_REQUEST['add'], $clear);
					
				}
				elseif($_SESSION['rezgo_cart_'.REZGO_CID]) { $cart = $_SESSION['rezgo_cart_'.REZGO_CID]; }
				elseif($_COOKIE['rezgo_cart_'.REZGO_CID]) { $cart = unserialize(stripslashes($_COOKIE['rezgo_cart_'.REZGO_CID])); }
				
				setcookie("rezgo_cart_".REZGO_CID, serialize($cart), time() + $ttl, '/', $_SERVER['SERVER_NAME']);
					
				// we need to set the session here before we header the user off or the old session will override the new cart each time
				$_SESSION['rezgo_cart_'.REZGO_CID] = $cart;
				
				if(isset($new_header)) $this->sendTo((($this->checkSecure()) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$new_header);
			}
			
			// handle the edit cart request if one is set
			if(is_array($_REQUEST['edit']) || $_COOKIE['rezgo_cart_'.REZGO_CID] || $_SESSION['rezgo_cart_'.REZGO_CID]) {
				
				$ttl = (REZGO_CART_TTL > 0 || REZGO_CART_TTL === 0) ? REZGO_CART_TTL : 86400;
				
				if(is_array($_REQUEST['edit'])) {
					
					if(!$new_header) $new_header = urldecode($_SERVER['REQUEST_URI']); //urldecode is needed to catch the [ marks on the array
					
					// remove the cart information wherever it is
					$new_header = preg_replace("/&?edit\[[^&\/]*\]=[^&\/]*/", "", $new_header);
					$new_header = str_replace("?&", "?", $new_header);
					
					if(substr($new_header, -1) == '?') { $new_header = substr($new_header, 0, -1); }
					
					$cart = $this->editCart($_REQUEST['edit'], $clear);
					
				}
				elseif($_SESSION['rezgo_cart_'.REZGO_CID]) { $cart = $_SESSION['rezgo_cart_'.REZGO_CID]; }
				elseif($_COOKIE['rezgo_cart_'.REZGO_CID]) { $cart = unserialize(stripslashes($_COOKIE['rezgo_cart_'.REZGO_CID])); }
				
				setcookie("rezgo_cart_".REZGO_CID, serialize($cart), time() + $ttl, '/', $_SERVER['SERVER_NAME']);
					
				// we need to set the session here before we header the user off or the old session will override the new cart each time
				$_SESSION['rezgo_cart_'.REZGO_CID] = $cart;
				
				if(isset($new_header)) $this->sendTo((($this->checkSecure()) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$new_header);
			}
			
			// registering global events, these can be manually changed later with the same methods
			$this->setRefId($_COOKIE['rezgo_refid_val']);
			$this->setPromoCode($_COOKIE['rezgo_promo']);
			$this->setShoppingCart($_COOKIE['rezgo_cart_'.REZGO_CID]);
		}
		
		function config($arg) {
			if(!defined($arg)) {
				return 0;
			} else {
				if(constant($arg) == '') { return 0; }
				else { return constant($arg); }
			}
		}
		
		function error($message, $exit=null) {
			$stack = debug_backtrace();
			$stack = $stack[count($stack)-1]; // get the origin point
			
			$error = '<b>[Rezgo error:</b> '.$message.' for <b>'.$stack['class'].'::'.$stack['function'].'</b> in <b>'.$stack['file'].'</b> on line <b>'.$stack['line'].']</b>';
			if($this->config('REZGO_FIREBUG_ERRORS')) echo '<script>if(window.console != undefined) { console.error("'.strip_tags($error).'"); }</script>';
			if($this->config('REZGO_DISPLAY_ERRORS')) echo ($this->config('REZGO_DIE_ON_ERROR')) ? die($error) : $error;
		
			if($exit) exit;
		}
		
		function debug($message, $i=null) {
			$stack = debug_backtrace();
			$stack = $stack[count($stack)-1]; // get the origin point
			
			$message = '['.urldecode($message).' for '.$stack['class'].'::'.$stack['function'].' in '.$stack['file'].' on line '.$stack['line'].']';
			if($this->config('REZGO_FIREBUG_XML')) {
				if(($i == 'commit' || $i == 'commitOrder' || $i == 'add_transaction') && $this->config('REZGO_SWITCH_COMMIT')) { if($this->config('REZGO_STOP_COMMIT')) { echo $_SESSION['error_catch'] = 'STOP::'.$message.'<br><br>'; } }
				else { echo '<script>if(window.console != undefined) { console.info("'.addslashes($message).'"); }</script>'; }
			}
			if($this->config('REZGO_DISPLAY_XML'))	{
				if(($i == 'commit' || $i == 'commitOrder' || $i == 'add_transaction') && $this->config('REZGO_SWITCH_COMMIT')) { die('STOP::'.$message); }
				else { echo '<textarea rows="2" cols="25">'.$message.'</textarea>'; }
			}
		}
		
		function setRequestID() {
			$this->requestID = $_SESSION['requestID'] = $this->config('REZGO_CID').'-'.time().'-'.$this->randstring(4);
			return $this->requestID;
		}
		
		function getRequestID() {
			return $this->requestID;
		}
		
		// generate a random string
		function randstring($len = 10) {
			$len = $len / 2;
			
			$timestring = microtime();
			$secondsSinceEpoch=(integer) substr($timestring, strrpos($timestring, " "), 100);
			$microseconds=(double) $timestring;
			$seed = mt_rand(0,1000000000) + 10000000 * $microseconds + $secondsSinceEpoch;
			mt_srand($seed);
			$randstring = "";
			for($i=0; $i < $len; $i++) {
				$randstring .= mt_rand(0, 9);
				$randstring .= chr(ord('A') + mt_rand(0, 24));
			}
		 	return($randstring);
		}
		
		function secureURL() {
			if($this->config('REZGO_FORWARD_SECURE')) {
				// forward is set, so we want to direct them to their .rezgo.com domain	
				$secure_url = $this->getDomain().'.rezgo.com';
			} else {
				// forward them to this page or our external URL
				if($this->config('REZGO_SECURE_URL')) {
					$secure_url = $this->config('REZGO_SECURE_URL');
				} else {
					$secure_url = $_SERVER["HTTP_HOST"];
				}	
			}
			return $secure_url;
		}
		
		function isVendor() {
			$res = (strpos(REZGO_CID, 'p') !== false) ? 1 : 0;
			return $res;
		}
		
		// clean slashes from the _REQUEST superglobal if escape strings is set in php
		function cleanRequest() {
			array_walk_recursive($_REQUEST, create_function('&$val', '$val = stripslashes($val);'));
		}
		
		// output a fixed number from a request variable
		function requestNum($request) {
			$r = $_REQUEST[$request];
			$r = preg_replace("/[^0-9.]*/", "", $r);
			return $r;
		}
		
		function requestStr($request) {
			$r = $_REQUEST[$request];
			
			$r = strip_tags($r);
			$r = preg_replace("/[;<>]*/", "", $r);
			
			return $r;
		}
		
		// remove all attributes from a user-entered field
		function cleanAttr($request) {
			$r = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $request);
			$r = strip_tags($r, '<br><strong><p>');
			return $r;
		}
		
		// ------------------------------------------------------------------------------
		// read a tour item object into the cache so we can format it later
		// ------------------------------------------------------------------------------
		function readItem(&$obj) {
			$this->obj = $obj;
		}
		
		function getItem() {
			$obj = $this->obj;
			if(!$obj) $this->error('no object found, expecting read object or object argument');
			return $obj;
		}
		
		// ------------------------------------------------------------------------------
		// format a currency response to the standards of this company
		// ------------------------------------------------------------------------------
		function formatCurrency($num, &$obj=null) {
			if(!$obj) $obj = $this->getItem();
			return str_replace(" ", "&nbsp;", (($hide) ? '' : $obj->currency_symbol).number_format((float)$num, (int)$obj->currency_decimals, '.', (string)$obj->currency_separator));
		}
		
		// ------------------------------------------------------------------------------
		// Check if an object has any content in it, for template if statements
		// ------------------------------------------------------------------------------
		function exists($string) {
			$str = (string) $string;
			return (strlen(trim($str)) == 0) ? 0 : 1;	
		}
		
		// ------------------------------------------------------------------------------
		// Direct a user to a different page
		// ------------------------------------------------------------------------------
		function sendTo($path) {
			$this->debug('PAGE FORWARDING ('.$path.')');
			echo '<script>'.REZGO_FRAME_TARGET.'.location.href = "'.$path.'";</script>';
			exit;
		}
		
		// ------------------------------------------------------------------------------
		// Format a string for passing in a URL
		// ------------------------------------------------------------------------------
		function seoEncode($string) {
			$str = trim($string);
			$str = str_replace(" ", "-", $str);
			$str = preg_replace('/[^A-Za-z0-9\-]/','', $str);
			$str = preg_replace('/[\-]+/','-', $str);
			if(!$str) $str = urlencode($string);
			return strtolower($str);	
		}
		
		// ------------------------------------------------------------------------------
		// Save tour search info
		// ------------------------------------------------------------------------------
		function saveSearch() {
			$search_array = array('pg', 'start_date', 'end_date', 'tags', 'search_in', 'search_for');
			
			foreach($search_array as $v) { if($_REQUEST[$v]) $search[] = $v.'='.rawurlencode($this->requestStr($v)); }
			
			if($search) $search = '?'.implode("&", $search);
			setcookie("rezgo_search", $search, strtotime('now +1 week'), '/', $_SERVER['SERVER_NAME']);
		}
		
		// ------------------------------------------------------------------------------
		// Toggles secure (https) or insecure (http) mode for API queries. Secure mode
		// is required when making all commit or modification requests.
		// ------------------------------------------------------------------------------		
		function checkSecure() {
			if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
				return true;
			} else { 
				return false; 
			}
		}
		
		function setSecureXML($set) {
			if($set) { $this->secure = 'https://'; }
			else { $this->secure = 'http://'; }
		}
		
		function setSecure($set) {
			$this->setSecureXML($set);
			
			if($set) { 
				if(!$this->checkSecure()) {
					if($this->config('REZGO_FORWARD_SECURE')) {
						// since we are directing to a white label address, clean the request up
						$request = '/book?'.$_SERVER['QUERY_STRING'];
					} else {
						$request = $_SERVER['REQUEST_URI'];
					}
				
					$this->sendTo($this->secure.$this->secureURL().$request); 
				} 
			} else { 
				// switch to non-https on the current domain
				if($this->checkSecure() && REZGO_ALL_SECURE !== 1) {
					$this->sendTo($this->secure.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
				} 
			}
		}
		
		// ------------------------------------------------------------------------------
		// fetch a requested template from the templates directory and load it into a
		// variable for display.	If fullpath is set, fetch it from that location instead.
		// ------------------------------------------------------------------------------
		function getTemplate($req, $fullpath=false) {
			reset($GLOBALS);
			foreach($GLOBALS as $key => $val) {
				if(($key != strstr($key,"HTTP_")) && ($key != strstr($key, "_")) && ($key != 'GLOBALS')) {
					global $$key;
				} 
			}
			
			// wordpress document root includes the install path so we change the path for wordpress installs			
			$path = ($this->config('REZGO_USE_ABSOLUTE_PATH')) ? REZGO_DOCUMENT_ROOT : REZGO_DOCUMENT_ROOT.REZGO_DIR;
			$path .= '/templates/'.REZGO_TEMPLATE.'/';
			
			$ext = explode(".", $req);
			$ext = (!$ext[1]) ? '.php' : '';
			
			$filename = ($fullpath) ? $req : $path.$req.$ext;
			
			if(is_file($filename)) {
				ob_start();
				include $filename;
				$contents = ob_get_contents();
				ob_end_clean();
			} else {
				$this->error('"'.$req.'" file not found'.(($fullpath) ? '' : ' in "'.$path.'"'));
			}
			
			return $contents;
		}
		
		// ------------------------------------------------------------------------------
		// general request functions for country lists
		// ------------------------------------------------------------------------------
		function countryName($iso) {
			$path = ($this->config('REZGO_USE_ABSOLUTE_PATH')) ? REZGO_DOCUMENT_ROOT : REZGO_DOCUMENT_ROOT.REZGO_DIR;
			
			if(!$this->country_list) {
				if($this->config('REZGO_COUNTRY_PATH')) {
					include(REZGO_COUNTRY_PATH);
				} else {
					include($path.'/include/countries_list.php');	
				}
				$this->country_list = $countries_list;
			}
			$iso = (string)$iso;
			return ($this->country_list[$iso]) ? ucwords($this->country_list[$iso]) : $iso; 
		}
		
		function getRegionList($node=null) {
			$path = ($this->config('REZGO_USE_ABSOLUTE_PATH')) ? REZGO_DOCUMENT_ROOT : REZGO_DOCUMENT_ROOT.REZGO_DIR;
		
			if($this->config('REZGO_COUNTRY_PATH')) {
				include(REZGO_COUNTRY_PATH);
			} else {
				include($path.'/include/countries_list.php');	
			}
			
			if($node) {
				$n = $node.'_state_list';
				if($$n) {
					return $$n;
				} else {
					$this->error('"'.$node.'" region node not found');
				}
			} else {
				return $countries_list;
			}
		}
		
		// ------------------------------------------------------------------------------
		// encode scripts for trans numbers
		// ------------------------------------------------------------------------------
		function encode($enc_text, $iv_len = 16) {
			$var = base64_encode($enc_text);
			return str_replace("=", "", base64_encode($var.'|'.$var));
		}
		function decode($enc_text, $iv_len = 16) {
			$var = base64_decode($enc_text.'=');
			$var = explode("|", $var);
			return base64_decode($var[0]);
		}
 
		// ------------------------------------------------------------------------------
		// encode trans numbers for waivers
		// ------------------------------------------------------------------------------
		function waiver_encode($string) {
			$key = hash('sha256', 'rz|key');
			$iv = substr( hash('sha256', 'rz|secret'), 0, 16 );
			return base64_encode( openssl_encrypt( $string, 'AES-256-CBC', $key, 0, $iv));
		}
		function waiver_decode( $string ) {
			$key = hash( 'sha256', 'rz|key' );
			$iv = substr( hash( 'sha256', 'rz|secret' ), 0, 16 );
			return openssl_decrypt( base64_decode( $string ), 'AES-256-CBC', $key, 0, $iv );
		} 
		
		// ------------------------------------------------------------------------------
		// Make an API request to Rezgo. $i supports all arguments that the API supports
		// for pre-generated queries, or a full query can be passed directly
		// ------------------------------------------------------------------------------
		function getFile($url, $post='') {
			include('fetch.rezgo.php');
			return $result;
		}
		
		function fetchXML($i, $post='') {
			
			$file = $this->getFile($i, $post);
			
			// sanity check for response, ensure the API response is valid
			if(strpos($file, '<!0!>') !== false) {
				$this->debug('XML ERROR RETURNED: '.$file, $i);
				return false;
			}
			
			// attempt to filter out any junk data
			$file = strstr($file, '<response');
			
			$this->get = $file;
			
			if($this->config('REZGO_DISPLAY_RESPONSES')) {
				echo '<textarea>'.$this->get.'</textarea>';
			}
			
			$res = $this->xml = simplexml_load_string($this->get);
			
			if(!$res && strpos($i, 'i=company') === false) {
				
				foreach(libxml_get_errors() as $error) {
					$error_set .= '<br>ERROR: '.$error->message;
				}
				$this->error('FATAL ERROR WITH XML PARSER ('.$i.') '.$error_set);
				
				// there has been a fatal error with the API, report the error to the gateway
				$this->getFile($i.'&action=report');
				
				// send the user to the fatal error page
				if(REZGO_FATAL_ERROR_PAGE) {
					$this->sendTo(REZGO_FATAL_ERROR_PAGE);
				}
			}
			
			return $res;
		}
		
		function XMLRequest($i, $arguments=null, $advanced=null) {
			
			if($i == 'company') {
				if(!$this->company_response[$this->company_index]) {	
					$arg = ($this->company_index) ? '&q='.$this->company_index : '';
					$query = $this->secure.$this->xml_path.'&i=company'.$arg;
					
					if($arguments == 'voucher') { 
						$query .= '&a=voucher';
					} elseif($arguments == 'ticket') {
						$query .= '&a=ticket';
					} elseif($this->config('REZGO_MOBILE_XML')) {
						$query .= '&a=mobile';
					}
					
					$xml = $this->fetchXML($query);
					
					if($xml) {
						$this->company_response[$this->company_index] = $xml;	
					}
				}
			}
			// !i=page
			if($i == 'page') {
				if(!$this->page_response[$arguments]) {
					$query = $this->secure.$this->xml_path.'&i=page&q='.$arguments;
					
					$xml = $this->fetchXML($query);
					
					if($xml) {
						$this->page_response[$arguments] = $xml;
					}	
				}			
			}
			// !i=tags
			if($i == 'tags') {
				if(!$this->tags_response) {
					$query = $this->secure.$this->xml_path.'&i=tags';
				
					$xml = $this->fetchXML($query);
					
					if($xml) {
						if($xml->total > 1) {
							foreach($xml->tag as $v) {
								$this->tags_response[] = $v;
							}
						} else {
							$this->tags_response[] = $xml->tag;
						}	
					}
					
				}
			}	
			// !i=search
			if($i == 'search') {		
				if(!$this->search_response[$this->tours_index]) {
					$query = $this->secure.$this->xml_path.'&i=search&'.$this->tours_index;
					
					if(is_array($this->cart)) $query .= '&cart='.$this->getCartIDs();
					
					$xml = $this->fetchXML($query);
					
					$this->search_total = $xml->total;
					
					$c = 0;
					if($xml && $xml->total != 0) {
						if($xml->total > 1) {
							foreach($xml->item as $v) {
								$this->search_response[$this->tours_index][$c] = $v;
								$this->search_response[$this->tours_index][$c++]->index = $this->tours_index;
							}
						} else {
							$this->search_response[$this->tours_index][$c] = $xml->item;
							$this->search_response[$this->tours_index][$c++]->index = $this->tours_index;
						}	
					}
				
				}
			}
			// !i=cart
			if($i == 'cart') {		
				if(!$this->cart_response) {
					
					$query = 'https://'.$this->advanced_xml_path.urlencode('<instruction>cart</instruction><cart>'.$this->getCartIDs().'</cart>'.$arguments.'</request>');
					
					$xml = $this->fetchXML($query);
					
					$this->cart_total = $xml->total;
					
					$c = 0;
					if($xml && $xml->total != 0) {
						if($xml->total > 1) {
							foreach($xml->item as $v) {
								$this->cart_response[$c++] = $v;
							}
						} else {
							$this->cart_response[$c++] = $xml->item;
						}
					}
				
				}
			}
			// !i=search_bookings
			if($i == 'search_bookings') {
				if(!$this->search_bookings_response[$this->bookings_index]) {
					$query = $this->secure.$this->xml_path.'&i=search_bookings&'.$this->bookings_index;
					$xml = $this->fetchXML($query);
					
					$c = 0;
					if($xml && $xml->total != 0) {
						if($xml->total > 1) {
							foreach($xml->booking as $v) {
								$this->search_bookings_response[$this->bookings_index][$c] = $v;
								$this->search_bookings_response[$this->bookings_index][$c++]->index = $this->bookings_index;
							}
						} else {
							$this->search_bookings_response[$this->bookings_index][$c] = $xml->booking;
							$this->search_bookings_response[$this->bookings_index][$c++]->index = $this->bookings_index;
						}	
					}
				
				}
			}
			// !i=commit
			if($i == 'commit') {
				$query = 'https://'.$this->xml_path.'&i=commit&cart='.$this->getCartIDs().'&'.$arguments;
				
				$xml = $this->fetchXML($query);
				
				if($xml) {
					$this->commit_response = new stdClass();
					foreach($xml as $k => $v) {
						$this->commit_response->$k = trim((string)$v);	
					}
				}
			}
			// !new commit mode
			if($i == 'commitOrder') {
				$query = 'https://'.$this->xml_path;
				
				$post = $this->api_post_string.urlencode('<instruction>commit</instruction><cart>'.$this->getCartIDs().'</cart>'.$arguments.'</request>');
				
				$xml = $this->fetchXML($query, $post);
				
				if($xml) {
					$this->commit_response = new stdClass();
					foreach($xml as $k => $v) {
						$this->commit_response->$k = trim((string)$v);	
					}
				}
			}
			// !i=add gift card
			if($i == 'addGiftCard') {

				$query = 'https://'.$this->xml_path;

				$post = $this->api_post_string.urlencode('<instruction>add_card</instruction>'.$arguments.'</request>');

				$xml = $this->fetchXML($query, $post);
				
				if($xml) {
					$this->commit_response = new stdClass();

					foreach ($xml as $k => $v) {
						$this->commit_response->$k = trim((string)$v);	
					}
				}
			}
			// !i=search gift card
			if($i == 'searchGiftCard') {
				$query = $this->secure.$this->xml_path.'&i=cards&q='.$arguments;
				$res = $this->fetchXML($query);
				$this->gift_card = $res;
			}
			// !i=contact
			if($i == 'contact') {
				$query = 'https://'.$this->xml_path.'&i=contact&'.$arguments;
			
				$xml = $this->fetchXML($query);
				
				if($xml) {
					$this->contact_response = new stdClass();
					foreach($xml as $k => $v) {
						$this->contact_response->$k = trim((string)$v);	
					}
				}
			}
			// !i=tickets
			if($i == 'tickets') {
				if(!$this->ticket_response[$arguments]) {
					$query = $this->secure.$this->xml_path.'&i=tickets&q='.$arguments;
					
					$xml = $this->fetchXML($query);
					
					if($xml) {
						$this->ticket_response[$arguments] = $xml;
					}	
				}			
			}	
			// !i=waiver
			if($i == 'waiver') {
				if(!$this->waiver_response[$arguments]) {
					
					if ($advanced == 'com') $target = '&t=com';
					
					$query = $this->secure.$this->xml_path.'&i=waiver&q='.$arguments.$target;
					
					$xml = $this->fetchXML($query);
					
					if($xml) {
						$this->waiver_response[$arguments] = $xml;
					}	
				}			
			}	
			// !i=sign
			if($i == 'sign') {

				$query = 'https://'.$this->xml_path;

				$post = $this->api_post_string.urlencode('<instruction>sign</instruction>'.$arguments.'</request>');

				$xml = $this->fetchXML($query, $post);
				
				if($xml) {
					$this->signing_response = new stdClass();

					foreach ($xml as $k => $v) {
						$this->signing_response->$k = trim((string)$v);	
					}
				}
			}	
			// !i=review
			if($i == 'review') {
				$query = 'https://'.$this->xml_path.'&i=review&'.$arguments;
			
				$xml = $this->fetchXML($query);
				
				if($xml) {
					$this->review_response = new stdClass();
					$this->review_response = $xml;
				}
			}	
			// !i=pickup
			if($i == 'pickup') {
				$query = 'https://'.$this->xml_path.'&i=pickup&'.$arguments;
			
				$xml = $this->fetchXML($query);
				
				if($xml) {
					$this->pickup_response = new stdClass();
					$this->pickup_response = $xml;
				}
			}	
			
			// !i=add_transaction
			if($i == 'add_transaction') {

				$query = 'https://'.$this->xml_path;

				$post = $this->api_post_string.urlencode('<instruction>add_transaction</instruction>'.$arguments.'</request>');

				$xml = $this->fetchXML($query, $post);
				
				if($xml) {
					$this->commit_response = new stdClass();

					foreach ($xml as $k => $v) {
						$this->commit_response->$k = trim((string)$v);	
					}
				}
			}
			// !i=payment
			if($i == 'payment') {
				$query = 'https://'.$this->xml_path.'&i=payment&'.$arguments;
			
				$xml = $this->fetchXML($query);
				
				if($xml) {
					$this->payment_response = new stdClass();
					$this->payment_response = $xml;
				}
			}	
			
			if(REZGO_TRACE_XML) {
				if(!$query && REZGO_INCLUDE_CACHE_XML) $query = 'called cached response';
				if($query) {
					$message = $i.' ('.$query.(($post) ? '&'.$post : '').')';
					$this->debug('XML REQUEST: '.$message, $i); // pass the $i as well so we can freeze on commit
				}
			}
		}
		
		// ------------------------------------------------------------------------------
		// Set specific data
		// ------------------------------------------------------------------------------
		function setTourLimit($limit, $start=null) {
			$str = ($start) ? $start.','.$limit : $limit;
			$this->tour_limit = $str;
		}
		
		function setRefId($id) {
			$this->refid = $id;
		}
		
		function setPromoCode($id) {
			$this->promo_code = urlencode($id);
		}
		
		function setShoppingCart($array) {
			$this->cart = unserialize(stripslashes($array));
		}
		
		function setPageTitle($str) {
			$this->pageTitle = str_replace('_', ' ', $str);
		}
		
		function setMetaTags($str) {
			$this->metaTags = $str;
		}
		
		// ------------------------------------------------------------------------------
		// Fetch specific data
		// ------------------------------------------------------------------------------
		function getSiteStatus() {
			$this->XMLRequest(company);
			return $this->company_response[0]->site_status;
		}
		
		function getHeader() {
			$this->XMLRequest(company);
			$header = $this->company_response[0]->header;
			// handle the tags in the template
			return $this->tag_parse($header);
		}
		
		function getFooter() {
			$this->XMLRequest(company);
			$footer = $this->company_response[0]->footer;
			// handle the tags in the template
			return $this->tag_parse($footer);
		}
		
		function getVoucherHeader() {
			$this->XMLRequest(company, 'voucher');
			$header = $this->company_response[0]->header;
			// handle the tags in the template
			return $this->tag_parse($header);
		}
		
		function getVoucherFooter() {
			$this->XMLRequest(company, 'voucher');
			return $this->company_response[0]->footer;
		}
		
		function getTicketHeader() {
			$this->XMLRequest(company, 'ticket');
			$header = $this->company_response[0]->header;
			return $this->tag_parse($header);
		}
		
		function getTicketFooter() {
			$this->XMLRequest(company, 'ticket');
			return $this->company_response[0]->footer;
		}

		function getTicketContent($trans_num) {
			$this->XMLRequest(tickets, $trans_num);
			return $this->ticket_response[$trans_num];
		}

		function getWaiverContent($args=null, $target=null) {
			$this->XMLRequest(waiver, $args, $target);
			return $this->waiver_response[$args]->waiver;
		}

		function getWaiverForms($args=null) {
			$this->XMLRequest(waiver, $args);
			return $this->waiver_response[$args]->forms->form;
		}
		
		function getStyles() {
			$this->XMLRequest(company);
			return $this->company_response[0]->styles;
		}
		
		function getPageName($page) {
			$this->XMLRequest(page, $page);
			return $this->page_response[$page]->name;
		}
		
		function getPageContent($page) {
			$this->XMLRequest(page, $page);
			return $this->page_response[$page]->content;
		}
		
		function getAnalytics() {
			$this->XMLRequest(company);
			return $this->company_response[0]->analytics_general;
		}
		
		function getAnalyticsConversion() {
			$this->XMLRequest(company);
			return $this->company_response[0]->analytics_convert;
		}
		
		function getTriggerState() {
			$this->XMLRequest(company);
			return $this->exists($this->company_response[0]->trigger);
		}
		
		function getBookNow() {
			$this->XMLRequest(company);
			return $this->company_response[0]->book_now;
		}
		
		function getCartState() {
			$this->XMLRequest(company);
			return ((int) $this->company_response[0]->cart == 1) ? 1 : 0;
		}
		
		function getTwitterName() {
			$this->XMLRequest(company);
			return $this->company_response[0]->social->twitter_name;
		}
		
		function getPaymentMethods($val=null, $a=null) {
			$this->company_index = ($a) ? (string) $a : 0; // handle multiple company requests for vendor
			$this->XMLRequest(company);
			
			if($this->company_response[$this->company_index]->payment->method[0]) {
				foreach($this->company_response[$this->company_index]->payment->method as $v) {
					$ret[] = array('name' => (string)$v, 'add' => (string)$v->attributes()->add);
					if($val && $val == $v) { return 1; }	
				}
			} else {
				$ret[] = array(
					'name' => (string)$this->company_response[$this->company_index]->payment->method, 
					'add' => (string)$this->company_response[$this->company_index]->payment->method->attributes()->add
				);
				if($val && $val == (string)$this->company_response[$this->company_index]->payment->method) { return 1; }				
			}
			
			// if we made it this far with a $val set, return false
			if($val) { return false; }
			else { return $ret; }
		}
		
		function getPaymentCards($a=null) {
			$this->company_index = ($a) ? (string) $a : 0;
			$this->XMLRequest(company);
			$split = explode(",", $this->company_response[$this->company_index]->cards);
			foreach((array) $split as $v) {
				if(trim($v)) $ret[] = strtolower(trim($v));
			}
			return $ret;
		}
		
		function getCVV($a=null) {
			$this->company_index = ($a) ? (string) $a : 0;
			$this->XMLRequest(company);
			return (int) $this->company_response[$this->company_index]->get_cvv;
		}
		
		function getGateway($a=null) {
			$this->company_index = ($a) ? (string) $a : 0;
			$this->XMLRequest(company);
			return (int) $this->company_response[$this->company_index]->using_gateway;
		}
		
		function getDomain($a=null) {
			$this->company_index = ($a) ? (string) $a : 0;
			$this->XMLRequest(company);
			return $this->company_response[$this->company_index]->domain;
		}
		
		function getCompanyName($a=null) {
			$this->company_index = ($a) ? (string) $a : 0;
			$this->XMLRequest(company);
			return $this->company_response[$this->company_index]->company_name;
		}
		
		function getCompanyCountry($a=null) {
			$this->company_index = ($a) ? (string) $a : 0;
			$this->XMLRequest(company);
			return $this->company_response[$this->company_index]->country;
		}
		
		function getCompanyPaypal($a=null) {
			$this->company_index = ($a) ? (string) $a : 0;
			$this->XMLRequest(company);
			return $this->company_response[$this->company_index]->paypal_email;
		}
		
		function getCompanyDetails($a=null) {
			$this->company_index = ($a) ? (string) $a : 0;
			$this->XMLRequest(company);
			return $this->company_response[$this->company_index];
		}
		
		// get a list of calendar data		
		function getCalendar($item_id, $date=null) {
			if(!$date) { // no date? set a default date (today)
				$date = $default_date = strtotime(date("Y-m-15"));
				$available = ',available'; // get first available date from month API
			} else {
				$date = date("Y-m-15", strtotime($date));
				$date = strtotime($date);
			}
			
			$promo = ($this->promo_code) ? '&trigger_code='.$this->promo_code : '';	
			
			$query = $this->secure.$this->xml_path.'&i=month&q='.$item_id.'&d='.date("Y-m-d", $date).'&a=group'.$available.$promo;	
			
			$xml = $this->fetchXML($query);
			
			if(REZGO_TRACE_XML) {
				if($query) {
					$message = 'month'.' ('.$query.')';
					$this->debug('XML REQUEST: '.$message, 'month'); 
				}
			}
			
			// update the date with the one provided from the API response
			// this is done in case we hopped ahead with the API search (a=available)
			$date = $xml->year.'-'.$xml->month.'-15';
			
			$year = date("Y", strtotime($date));
			$month = date("m", strtotime($date));
			
			$date = $base_date = date("Y-m-15", strtotime($date));
			$date = strtotime($date);
				
			$next_partial = date("Y-m", strtotime($base_date.' +1 month'));
			$prev_partial = date("Y-m", strtotime($base_date.' -1 month'));
				
			$this->calendar_next = $next_date = date("Y-m-d", strtotime($base_date.' +1 month'));
			$this->calendar_prev = $prev_date = date("Y-m-d", strtotime($base_date.' -1 month'));
			
			
			$this->calendar_name = (string) $xml->name;
			$this->calendar_com = (string) $xml->com;
			$this->calendar_active = (int) $xml->active;
			
			$days = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
			
			$months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"); 
			
			$start_day = 1;
			$end_day = date("t", $date);
			
			$start_dow = date("D", strtotime(date("Y-m-1", $date)));
			
			$n = 0;
			foreach($months as $k => $v) {
				$this->calendar_months[$n] = new stdClass();
				$this->calendar_months[$n]->selected = ($v == date("F", $date)) ? 'selected' : '';
				$this->calendar_months[$n]->value = $year.'-'.$v.'-15';
				$this->calendar_months[$n]->label = $k;
				$n++;
			}
		
			for($y=date("Y", strtotime(date("Y").' -1 year')); $y<=date("Y", strtotime(date("Y").' +4 years')); $y++) {
				$this->calendar_years[$n] = new stdClass();
				$this->calendar_years[$n]->selected = ($y == date("Y", $date)) ? 'selected' : '';
				$this->calendar_years[$n]->value = $y.'-'.$month.'-15';
				$this->calendar_years[$n]->label = $y;
				$n++;
			}
						
			$c = 0;
			foreach($days as $v) {
				$c++;
				if($start_dow == $v) $start_offset = $c;
			}
			
			if($start_offset) {
				// this will display the lead-up days from last month
				$last_display = date("t", strtotime($prev_date)) - ($start_offset-2);
				
				for($d=1; $d<$start_offset; $d++) {
					$obj = isset($obj) ? $obj : new stdClass();
					$obj->day = $last_display;
					$obj->lead = 1; // mark as lead up day, so it's not counted in getCalendarDays($day) calls
					$this->calendar_days[] = $obj;				
					$last_display++;
					unset($obj);
				}	
			}
			
			$w = $start_offset;
			for($d=1; $d<=$end_day; $d++) {		
				$obj = isset($obj) ? $obj : new stdClass();
				
				$xd = $d - 1;			
				$obj->type = 1;
				
				if($xml->day->$xd) {
					$obj->cond = $cond = (string) $xml->day->$xd->attributes()->condition;
					if($xml->day->$xd->item[0]) {
						// we want to convert the attribute to something easier to use in the template
						$n=0;
						foreach($xml->day->$xd->item as $i) {
							if($i) {
								$obj->items[$n] = new stdClass();
								$obj->items[$n]->uid = $i->uid;
								$obj->items[$n]->name = $i->name;
								$obj->items[$n]->availability = $i->attributes()->value;
								$n++;
							}
						}
					} else {
						if($xml->day->$xd->item) {
							$obj->items[0] = new stdClass();
							$obj->items[0]->uid = $xml->day->$xd->item->uid;
							$obj->items[0]->name = $xml->day->$xd->item->name;
							$obj->items[0]->availability = $xml->day->$xd->item->attributes()->value;
						}
					}
				}
				
				$obj->date = strtotime($year.'-'.$month.'-'.$d);
				
				$obj->day = $d;		
				$this->calendar_days[] = $obj;			
				unset($obj);
				
				if($w == 7) { $w = 1; } else { $w++; }
			}
				
			if($w != 8 && $w != 1) {
				$d = 0;
				// this will display the lead-out days for next month
				while($w != 8) {
					$d++;
					$w++;
					$obj = isset($obj) ? $obj : new stdClass();
					$obj->day = $d;			
					$this->calendar_days[] = $obj;
					unset($obj);
				}
			}
		}
		
		function getCalendarActive() {
			return $this->calendar_active;
		}
		
		function getCalendarPrev() {
			return $this->calendar_prev;
		}
		
		function getCalendarNext() {
			return $this->calendar_next;
		}
		
		function getCalendarMonths() {
			return $this->calendar_months;
		}
		
		function getCalendarYears() {
			return $this->calendar_years;
		}
		
		function getCalendarDays($day=null) {
			if($day) {
				foreach($this->calendar_days as $v) {
					if((int)$v->day == $day && !(int)$v->lead) {
						$day_response = $v; break;
					}
				}
				
				return (object) $day_response;
			} else {
				return $this->calendar_days;
			}
		}
		
		function getCalendarId() {
			return $this->calendar_com;
		}
		
		function getCalendarName() {
			return $this->calendar_name;
		}
    
		function getCalendarDiff($date1, $date2) {
			// $date1 and $date2 must have format: Y-M-D 
			
			$date1 = new DateTime($date1);
			$date2 = new DateTime($date2);
			$interval = $date1->diff($date2);
			
			$res = ''; 
			
			if($interval->y > 0) $res .= $interval->y . ' year' . (($interval->y > 1) ? 's' : '') . (($interval->y > 0)?', ':'');
			if($interval->m > 0) $res .= $interval->m . ' month' . (($interval->m > 1) ? 's' : '') . (($interval->m > 0)?', ':'');
			if($interval->d > 0) $res .= $interval->d . ' day' . (($interval->d > 1) ? 's' : '');
			
			return $res;
		}
		
		// get a list of tour data
		function getTours($a=null, $node=null) {
			// generate the search string
			// if no search is specified, find searched items (grouped)
			if(!$a || $a == $_REQUEST) {
				if($this->requestStr('search_for')) $str .= ($this->requestStr('search_in')) ? '&t='.urlencode($this->requestStr('search_in')) : '&t=smart';	
				if($this->requestStr('search_for')) $str .= '&q='.urlencode(stripslashes($this->requestStr('search_for')));
				if($this->requestStr('tags')) $str .= '&f[tags]='.urlencode($this->requestStr('tags'));
				
				if($this->requestNum('cid')) $str .= '&f[cid]='.urlencode($this->requestNum('cid')); // vendor only
				
				// details pages
				if($this->requestNum('com')) $str .= '&t=com&q='.urlencode($this->requestNum('com'));
				if($this->requestNum('uid')) $str .= '&t=uid&q='.urlencode($this->requestNum('uid'));
				if($this->requestStr('option')) $str .= '&t=uid&q='.urlencode($this->requestStr('option'));
				if($this->requestStr('date')) $str .= '&d='.urlencode($this->requestStr('date'));
				
				$a = ($a) ? $a : 'a=group'.$str;
			}
			
			$promo = ($this->promo_code) ? '&trigger_code='.$this->promo_code : '';	
				
			$limit = '&limit='.$this->tour_limit;
			
			// attach the search as an index including the limit value and promo code
			$this->tours_index = $a.$promo.$limit;
				
			$this->XMLRequest(search);
			
			$return = ($node === null) ? (array) $this->search_response[$this->tours_index] : $this->search_response[$this->tours_index][$node];
			
			return $return;
		}
		
		function getTourAvailability(&$obj=null, $start=null, $end=null) {
			if(!$obj) $obj = $this->getItem();
			
			// check the object, create a list of com ids
			// search the API with those ids and the date search
			// create a list of dates and relevant options to return
		
			$loop = (string) $obj->index;
			
			$d[] = ($start) ? date("Y-M-d", strtotime($start)) : date("Y-M-d", strtotime($this->requestStr(start_date)));
			$d[] = ($end) ? date("Y-M-d", strtotime($end)) : date("Y-M-d", strtotime($this->requestStr(end_date)));
			if($d) { $d = implode(',', $d); } else { return false; }
			
			if(!$this->tour_availability_response[$loop])	{
				if($this->search_response[$loop]) {
					foreach((array)$this->search_response[$loop] as $v) {
						$uids[] = (string)$v->com;
					}
					
					$this->tours_index = 't=com&q='.implode(',', array_unique($uids)).'&d='.$d;
					
					$this->XMLRequest(search);
					
					$c=0;
					foreach((array)$this->search_response[$this->tours_index] as $i) {
						if($i->date) {
							if($i->date[0]) {
								foreach($i->date as $d) {
									$res[(string)$i->com][strtotime((string)$d->attributes()->value)] = new stdClass();
									
									$res[(string)$i->com][strtotime((string)$d->attributes()->value)]->id = $c;
									
									$res[(string)$i->com][strtotime((string)$d->attributes()->value)]->items[$c] = new stdClass();
									
									$res[(string)$i->com][strtotime((string)$d->attributes()->value)]->items[$c]->name = (string)$i->time;
									$res[(string)$i->com][strtotime((string)$d->attributes()->value)]->items[$c]->availability = (string)$d->availability;
									$res[(string)$i->com][strtotime((string)$d->attributes()->value)]->items[$c]->hide_availability = (string)$d->hide_availability;
									$res[(string)$i->com][strtotime((string)$d->attributes()->value)]->items[$c++]->uid = (string)$i->uid;
									$res[(string)$i->com][strtotime((string)$d->attributes()->value)]->date = strtotime((string)$d->attributes()->value);
								}
							} else {
								$res[(string)$i->com][strtotime((string)$i->date->attributes()->value)] = new stdClass();
								
								$res[(string)$i->com][strtotime((string)$i->date->attributes()->value)]->id = $c;
								
								$res[(string)$i->com][strtotime((string)$i->date->attributes()->value)]->items[$c] = new stdClass();
								
								$res[(string)$i->com][strtotime((string)$i->date->attributes()->value)]->items[$c]->name = (string)$i->time;
								$res[(string)$i->com][strtotime((string)$i->date->attributes()->value)]->items[$c]->availability = (string)$i->date->availability;
								$res[(string)$i->com][strtotime((string)$i->date->attributes()->value)]->items[$c]->hide_availability = (string)$i->date->hide_availability;
								$res[(string)$i->com][strtotime((string)$i->date->attributes()->value)]->items[$c++]->uid = (string)$i->uid;
								$res[(string)$i->com][strtotime((string)$i->date->attributes()->value)]->date = strtotime((string)$i->date->attributes()->value);
							}
							
							// sort by date so the earlier dates always appear first, the api will return them in that order
							// but if the first item found has a later date than a subsequent item, the dates will be out of order
							ksort($res[(string)$i->com]);
						}
					}
					$this->tour_availability_response[$loop] = $res;	
				}
			}
			
			return (array) $this->tour_availability_response[$loop][(string)$obj->com];
		}
		
		function getTourPrices(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			$com = (string) $obj->com;
			
			$c=0;
			$all_required = 0;
			$valid_count = 0;
			$strike_prices = array();
			
			foreach ($obj->prices->price as $price) {
				if($price->strike) {
					$strike_prices[(int) $price->id] = (string) $price->strike;
				}
			}
			
			if($this->exists($obj->date->price_adult)) {
				$ret[$c] = new stdClass();
				$ret[$c]->name = 'adult';
				$ret[$c]->label = (string) $obj->adult_label;
				$ret[$c]->required = (string) $obj->adult_required;
				if($ret[$c]->required) $all_required++;
				($obj->date->base_prices->price_adult) ? $ret[$c]->base = (string) $obj->date->base_prices->price_adult : 0;
				$ret[$c]->price = (string) $obj->date->price_adult;
				$ret[$c]->strike = $strike_prices[1];
				$ret[$c++]->total = (string) $obj->total_adult;
				$valid_count++;
			}
			if($this->exists($obj->date->price_child)) {
				$ret[$c] = new stdClass();
				$ret[$c]->name = 'child';
				$ret[$c]->label = (string) $obj->child_label;
				$ret[$c]->required = (string) $obj->child_required;
				if($ret[$c]->required) $all_required++;
				($obj->date->base_prices->price_child) ? $ret[$c]->base = (string) $obj->date->base_prices->price_child : 0;
				$ret[$c]->price = (string) $obj->date->price_child;
				$ret[$c]->strike = $strike_prices[2];
				$ret[$c++]->total = (string) $obj->total_child;
				$valid_count++;
			}
			if($this->exists($obj->date->price_senior)) {
				$ret[$c] = new stdClass();
				$ret[$c]->name = 'senior';
				$ret[$c]->label = (string) $obj->senior_label;
				$ret[$c]->required = (string) $obj->senior_required;
				if($ret[$c]->required) $all_required++;
				($obj->date->base_prices->price_senior) ? $ret[$c]->base = (string) $obj->date->base_prices->price_senior : 0;
				$ret[$c]->price = (string) $obj->date->price_senior;
				$ret[$c]->strike = $strike_prices[3];
				$ret[$c++]->total = (string) $obj->total_senior;
				$valid_count++;
			}	
			
			for($i=4; $i<=9; $i++) {
				$val = 'price'.$i;
				if($this->exists($obj->date->$val)) {
					$ret[$c] = new stdClass();
					$ret[$c]->name = 'price'.$i;
					$val = 'price'.$i.'_label';
					$ret[$c]->label = (string) $obj->$val;
					$val = 'price'.$i.'_required';
					$ret[$c]->required = (string) $obj->$val;
					if($ret[$c]->required) $all_required++;
					$val = 'price'.$i;
					($obj->date->base_prices->$val) ? $ret[$c]->base = (string) $obj->date->base_prices->$val : 0;
					$ret[$c]->price = (string) $obj->date->$val;
					$ret[$c]->strike = $strike_prices[$i];
					$val = 'total_price'.$i;
					$ret[$c++]->total = (string) $obj->$val;
					$valid_count++;
				}
			}
			
			// if the total required count is the same as the total price points, or if no prices are required
			// we want to set the all_required flag so that the parser won't display individual required marks			
			if($all_required == $valid_count || $all_required == 0) {
				$this->all_required = 1;
			} else {
				$this->all_required = 0;
			}			
			
			return (array) $ret;
		}
		
		function getTourBundles(&$obj=null) {
			
			if(!$obj) $obj = $this->getItem();
			$com = (string) $obj->com;
			
			$c = 0;
			
			if($this->exists($obj->bundles->bundle->name)) {
					
				$bundle_prices = array();
				
				foreach ($obj->bundles->bundle as $bundle) {
					
					$ret[$c] = new stdClass();
					
					$ret[$c]->id = $c + 1;
					$ret[$c]->name = $bundle->name;
					
					$ret[$c]->label = $this->seoEncode($bundle->name);
					
					$ret[$c]->price = $bundle->price;
					$ret[$c]->visible = $bundle->visible;
					$ret[$c]->pax = $bundle->pax;
					
					unset($bundle_prices);
					$bundle_makeup = '';
					$bundle_total = 0;
					
					foreach ($bundle->pax->price as $count) {
						
						$bundle_total += (int) $count;
												
						if ($count['id'] == 1) {
							
							$bundle_makeup .= $count . ' ' . $obj->adult_label . ', ';
							$bundle_prices['adult'] = (string) $count;
							
						} elseif ($count['id'] == 2) {
							
							$bundle_makeup .= $count . ' ' . $obj->child_label . ', ';
							$bundle_prices['child'] = (string) $count;
							
						} elseif ($count['id'] == 3) {
							
							$bundle_makeup .= $count . ' ' . $obj->senior_label . ', ';
							$bundle_prices['senior'] = (string) $count;
							
						} else {
							
							$price_label = 'price'.$count['id'].'_label';
							$bundle_makeup .= $count . ' ' . $obj->$price_label . ', ';
							$bundle_prices['price'.$count['id']] = (string) $count;
							
						}
						
					}
						
					$bundle_makeup = rtrim($bundle_makeup, ', ');
					$ret[$c]->makeup = $bundle_makeup;
					
					$ret[$c]->prices = $bundle_prices;
					
					$ret[$c]->total = $bundle_total;
					
					$c++;
					
				}
				
			}
			
			return (array) $ret;
		}
		
		function getTourRequired() {
			return ($this->all_required) ? 0 : 1;
		}
		
		function getTourPriceNum(&$obj=null, $order=null) {
			if(!$obj) $obj = $this->getItem();
			// get the value from either the order object or the _REQUEST var
			$val = (is_object($order)) ? $order->{$obj->name.'_num'} : $_REQUEST[$obj->name.'_num'];
			for($n=1; $n<=$val; $n++) {
				$ret[] = $n;
			}
			return (array) $ret;
		}
		
		function getTourTags(&$obj=null) {
			if(!$obj) $obj = $this->getItem();			
			if($this->exists($obj->tags)) {
				$split = explode(',', $obj->tags);
				foreach((array) $split as $v) {
					$ret[] = trim($v);
				}
			}
			return (array) $ret;
		}
		
		function getTourLocations(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			$c = 0;
			if($obj->additional_locations->location) {
				if(!$obj->additional_locations->location[0]) {
					$ret[$c]->country = $obj->additional_locations->location->loc_country;
					$ret[$c]->state = $obj->additional_locations->location->loc_state;
					$ret[$c++]->city = $obj->additional_locations->location->loc_city;
				} else {
					foreach($obj->additional_locations->location as $v) {
						$ret[$c]->country = $v->loc_country;
						$ret[$c]->state = $v->loc_state;
						$ret[$c++]->city = $v->loc_city;
					}
				}
			}
			return (array) $ret;
		}
		
		function getTourMedia(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			$c = 0;
			if($obj->media->image) {
				foreach($obj->media->image as $v) {
					$ret[$c] = new stdClass();
					$ret[$c]->image = $v->path;
					$ret[$c]->path = $v->path;
					$ret[$c]->caption = $v->caption;
					$ret[$c++]->type = 'image';
				}
				return (array) $ret;
			} else { return false; }
		}
		
		function getTourRelated(&$obj=null) {
			if(!$obj) $obj = $this->getItem();			
			$c = 0;
			if($obj->related->items) {
				foreach($obj->related->items->item as $v) {
					$ret[$c] = new stdClass();
					
					$ret[$c]->com = $v->com;
					$ret[$c]->image = $v->image;
					$ret[$c]->starting = $v->starting;
					$ret[$c]->overview = $v->overview;
					$ret[$c++]->name = $v->name;
				}
				return (array) $ret;
			} else { return false; }
		}
		
		function getTourLineItems(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			
			if($obj->line_items) {
				if($obj->line_items->line_item[0]) {
					foreach($obj->line_items->line_item as $v) {
						$ret[] = $v;
					}
				} else {
					$ret[] = $obj->line_items->line_item;
				}
			}
			
			return (array) $ret;
		}
		
		function getTourForms($type='primary', &$obj=null) {
			if(!$obj) $obj = $this->getItem();
			$com = (string) $obj->com;
			$uid = (string) $obj->uid;
			
			$type = strtolower($type);
			if($type != 'primary' && $type != 'group') $this->error('unknown type, expecting "primary" or "group"');

			if(!$this->tour_forms[$com.'-'.$uid][$type]) {
				if($obj->{$type.'_forms'}) {
					
					foreach($obj->{$type.'_forms'}->form as $f) {
						$res[$type][(string)$f->id] = new stdClass();
						
						$res[$type][(string)$f->id]->id = (string)$f->id;
						$res[$type][(string)$f->id]->type = (string)$f->type;
						$res[$type][(string)$f->id]->title = (string)$f->title;
						$res[$type][(string)$f->id]->require = (string)$f->require;
						$res[$type][(string)$f->id]->value = (string)$f->value;
						$res[$type][(string)$f->id]->instructions = (string)$f->instructions;
						
						if((string)$f->price) {
							if(strpos((string)$f->price, '-') !== false) { 
								$res[$type][(string)$f->id]->price = str_replace("-", "", (string)$f->price);
								$res[$type][(string)$f->id]->price_mod = '-';
							} else {
								$res[$type][(string)$f->id]->price = str_replace("+", "", (string)$f->price);
								$res[$type][(string)$f->id]->price_mod = '+';
							}
						}
						
						if((string)$f->options) {
							$opt = explode(",", (string)$f->options);
							foreach((array)$opt as $v) {
								$res[$type][(string)$f->id]->options[] = $v;
							}
						}
						
						if((string)$f->options_instructions) {
							$opt_inst = explode(",", (string)$f->options_instructions);
							foreach((array)$opt_inst as $v) {
								$res[$type][(string)$f->id]->options_instructions[] = $v;
							}
						}
						
					}
					
				}
				
				$this->tour_forms[$com.'-'.$uid] = $res;
			}
			
			return (array) $this->tour_forms[$com.'-'.$uid][$type];
		}
		
		function getTagSizes() {
			$this->XMLRequest(tags);
			
			foreach($this->tags_response as $v) {
				$valid_tags[((string)$v->name)] = (string) $v->count;
			}
			// returns high [0] and low [1] for a list()
			rsort($valid_tags);
			$ret[] = $valid_tags[0];
			sort($valid_tags);
			$ret[] = $valid_tags[0];
						
			return (array) $ret;
		}
		
		function getTags() {
			$this->XMLRequest(tags);
			return (array) $this->tags_response;
		}
		
		// get a list of booking data
		function getBookings($a=null, $node=null) {
			if(!$a) $this->error('No search argument provided, expected trans_num or formatted search string');
			
			if(strpos($a, '=') === false) $a = 'q='.$a; // in case we just passed the trans_num by itself
			
			$this->bookings_index = $a;
			
			$this->XMLRequest(search_bookings);
			
			$return = ($node === null) ? (array) $this->search_bookings_response[$this->bookings_index] : $this->search_bookings_response[$this->bookings_index][$node];
			
			return $return;
		}
		
		function getBookingPrices(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			
			$c=0;
			if($obj->adult_num >= 1) {
				$ret[$c] = new stdClass();
				
				$ret[$c]->name = 'adult';
				$ret[$c]->label = (string) $obj->adult_label;
				($obj->prices->base_prices->price_adult) ? $ret[$c]->base = (string) $obj->prices->base_prices->price_adult : 0;
				$ret[$c]->price = ((string) $obj->prices->price_adult) / ((string) $obj->adult_num);
				$ret[$c]->number = (string) $obj->adult_num;
				$ret[$c++]->total = (string) $obj->prices->price_adult;
			}
			if($obj->child_num >= 1) {
				$ret[$c] = new stdClass();
				
				$ret[$c]->name = 'child';
				$ret[$c]->label = (string) $obj->child_label;
				($obj->prices->base_prices->price_child) ? $ret[$c]->base = (string) $obj->prices->base_prices->price_child : 0;
				$ret[$c]->price = ((string) $obj->prices->price_child) / ((string) $obj->child_num);
				$ret[$c]->number = (string) $obj->child_num;
				$ret[$c++]->total = (string) $obj->prices->price_child;
			}
			if($obj->senior_num >= 1) {
				$ret[$c] = new stdClass();
				
				$ret[$c]->name = 'senior';
				$ret[$c]->label = (string) $obj->senior_label;
				($obj->prices->base_prices->price_senior) ? $ret[$c]->base = (string) $obj->prices->base_prices->price_senior : 0;
				$ret[$c]->price = ((string) $obj->prices->price_senior) / ((string) $obj->senior_num);
				$ret[$c]->number = (string) $obj->senior_num;
				$ret[$c++]->total = (string) $obj->prices->price_senior;
			}	
			
			for($i=4; $i<=9; $i++) {
				$val = 'price'.$i.'_num';
				if($obj->$val >= 1) {
					$ret[$c] = new stdClass();
					
					$ret[$c]->name = 'price'.$i;
					$val = 'price'.$i.'_label';
					$ret[$c]->label = (string) $obj->$val;
					$val = 'price'.$i;
					$val2 = 'price'.$i.'_num';
					($obj->prices->base_prices->$val) ? $ret[$c]->base = (string) $obj->prices->base_prices->$val : 0;
					$ret[$c]->price = ((string) $obj->prices->$val) / ((string) $obj->$val2);
					$val = 'price'.$i.'_num';
					$ret[$c]->number = (string) $obj->$val;
					$val = 'price'.$i;
					$ret[$c++]->total = (string) $obj->prices->$val;
				}
			}
		
			return (array) $ret;
		}
		
		function getBookingLineItems(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			
			if($obj->line_items) {
				if($obj->line_items->line_item[0]) {
					foreach($obj->line_items->line_item as $v) {
						$ret[] = $v;
					}
				} else {
					$ret[] = $obj->line_items->line_item;
				}
			}
			
			return (array) $ret;				
		}
		
		function getBookingFees(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			
			if($obj->triggered_fees->triggered_fee[0]) {
				foreach($obj->triggered_fees->triggered_fee as $v) {
					$ret[] = $v;
				}
			} else {
				$ret[] = $obj->triggered_fees->triggered_fee;
			}
			
			return (array) $ret;				
		}
		
		function getBookingPassengers(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			
			$c=0;
			if($obj->passengers->passenger[0]) {
				foreach($obj->passengers->passenger as $v) {
					// do the forms on the passenger only have one question? if so, fix the formatting so it matches multiple questions
					if($v->total_forms > 0) {
						if(!$v->forms->form[0]) {
							$t = $v->forms->form;
							unset($v->forms->form); // remove the string value
							$v->forms->form[] = $t; // replace it with array value
						}
					} else {
						// no forms, fill the value with a blank array so the templates can use it
						// we add a supress @ modifier to prevent the complex-types error
						@$v->forms->form = array();
					}
					
					$ret[$c] = $v;
					@$ret[$c]->num = $v->type->attributes()->num;
					$val = $v->type.'_label';
					$ret[$c++]->label = $obj->$val;
				}
			} else {
				// do the forms on the passenger only have one question? if so, fix the formatting so it matches multiple questions
				if($obj->passengers->passenger->total_forms > 0) {
					if(!$obj->passengers->passenger->forms->form[0]) {
						$t = $obj->passengers->passenger->forms->form;
						unset($obj->passengers->passenger->forms->form); // remove the string value
						$obj->passengers->passenger->forms->form[] = $t; // replace it with array value
					}
				} else {
					// no forms, fill the value with a blank array so the templates can use it
					@$obj->passengers->passenger->forms->form = array();
				}
				
				$ret[$c] = $obj->passengers->passenger;
				@$ret[$c]->num = $obj->passengers->passenger->type->attributes()->num;
				$val = $obj->passengers->passenger->type.'_label';
				$ret[$c++]->label = $obj->$val;
			}
			
			// unset it if the value is empty because we have no group info	
			$count = count($ret);
			if($count == 1 && !(string)$ret[0]->num) unset($ret);
			
			
			
			// check to make sure the entire array isn't empty of data
			if($count > 1) {
				foreach((array)$ret as $k => $v) {
					
					
					if((string)$v->first_name) { $present = 1; break; }
					if((string)$v->last_name) { $present = 1; break; }

					if((string)$v->phone_number) { $present = 1; break; }
					if((string)$v->email_address) { $present = 1; break; }
					if((string)$v->total_forms > 0) { $present = 1; break; }
				}
				
				// if(!$present) unset($ret);
			}
			
			return (array) $ret;				
		}
		
		function getBookingForms(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			
			if($obj->primary_forms->total_forms > 0) {
				if($obj->primary_forms->form[0]) {
					foreach($obj->primary_forms->form as $v) {
						$ret[] = $v;
					}
				} else {
					$ret[] = $obj->primary_forms->form;
				}
			}
						
			return (array) $ret;
		}
		
		function getBookingCounts(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			
			$list = array('adult', 'child', 'senior', 'price4', 'price5', 'price6', 'price7', 'price8', 'price9');
			$c=0;
			foreach($list as $v) {
				$num = $v.'_num';
				$label = $v.'_label';
				if($obj->$num > 0) {
					$ret[$c] = new stdClass();
					$ret[$c]->label = $obj->$label;
					$ret[$c++]->num = $obj->$num;
				}
			}
			return (array) $ret;
		}
		
		function getBookingCurrency(&$obj=null) {
			if(!$obj) $obj = $this->getItem();
			
			return (string) $obj->currency_base;
		}
		
		function getPaxString() {
			if($this->requestNum('adult_num')) $pax_list .= '&adult_num='.$this->requestNum('adult_num');
			if($this->requestNum('child_num')) $pax_list .= '&child_num='.$this->requestNum('child_num');
			if($this->requestNum('senior_num')) $pax_list .= '&senior_num='.$this->requestNum('senior_num');
			if($this->requestNum('price4_num')) $pax_list .= '&price4_num='.$this->requestNum('price4_num');
			if($this->requestNum('price5_num')) $pax_list .= '&price5_num='.$this->requestNum('price5_num');
			if($this->requestNum('price6_num')) $pax_list .= '&price6_num='.$this->requestNum('price6_num');
			if($this->requestNum('price7_num')) $pax_list .= '&price7_num='.$this->requestNum('price7_num');
			if($this->requestNum('price8_num')) $pax_list .= '&price8_num='.$this->requestNum('price8_num');
			if($this->requestNum('price9_num')) $pax_list .= '&price9_num='.$this->requestNum('price9_num');
			
			return $pax_list;
		}
		
		// ------------------------------------------------------------------------------
		// Handle parsing the rezgo pseudo tags
		// ------------------------------------------------------------------------------
		function tag_parse($str) {
			$val = ($GLOBALS['pageHeader']) ? $GLOBALS['pageHeader'] : $this->pageTitle;
			$tags = array('[navigation]', '[navigator]');
			$str = str_replace($tags, $val, $str);	
			
			$val = ($GLOBALS['pageMeta']) ? $GLOBALS['pageHeader'] : $this->metaTags;
			$tags = array('[meta]', '[meta_tags]', '[seo]');
			$str = str_replace($tags, $val, $str);
			
			return (string) $str;
		}
		
		// ------------------------------------------------------------------------------
		// Create an outgoing commit request based on the _REQUEST data
		// ------------------------------------------------------------------------------
		function sendBooking($var=null, $arg=null) {
			$r = ($var) ? $var : $_REQUEST;
			
			if($arg) $res[] = $arg; // extra API options
			
			($r['date']) ? $res[] = 'date='.urlencode($r['date']) : 0;
			($r['book']) ? $res[] = 'book='.urlencode($r['book']) : 0;
			
			($r['trigger_code']) ? $res[] = 'trigger_code='.urlencode($r['trigger_code']) : 0;
			
			($r['adult_num']) ? $res[] = 'adult_num='.urlencode($r['adult_num']) : 0;
			($r['child_num']) ? $res[] = 'child_num='.urlencode($r['child_num']) : 0;
			($r['senior_num']) ? $res[] = 'senior_num='.urlencode($r['senior_num']) : 0;
			($r['price4_num']) ? $res[] = 'price4_num='.urlencode($r['price4_num']) : 0;
			($r['price5_num']) ? $res[] = 'price5_num='.urlencode($r['price5_num']) : 0;
			($r['price6_num']) ? $res[] = 'price6_num='.urlencode($r['price6_num']) : 0;
			($r['price7_num']) ? $res[] = 'price7_num='.urlencode($r['price7_num']) : 0;
			($r['price8_num']) ? $res[] = 'price8_num='.urlencode($r['price8_num']) : 0;
			($r['price9_num']) ? $res[] = 'price9_num='.urlencode($r['price9_num']) : 0;
			
			($r['tour_first_name']) ? $res[] = 'tour_first_name='.urlencode($r['tour_first_name']) : 0;
			($r['tour_last_name']) ? $res[] = 'tour_last_name='.urlencode($r['tour_last_name']) : 0;
			($r['tour_address_1']) ? $res[] = 'tour_address_1='.urlencode($r['tour_address_1']) : 0;
			($r['tour_address_2']) ? $res[] = 'tour_address_2='.urlencode($r['tour_address_2']) : 0;
			($r['tour_city']) ? $res[] = 'tour_city='.urlencode($r['tour_city']) : 0;
			($r['tour_stateprov']) ? $res[] = 'tour_stateprov='.urlencode($r['tour_stateprov']) : 0;
			($r['tour_country']) ? $res[] = 'tour_country='.urlencode($r['tour_country']) : 0;
			($r['tour_postal_code']) ? $res[] = 'tour_postal_code='.urlencode($r['tour_postal_code']) : 0;
			($r['tour_phone_number']) ? $res[] = 'tour_phone_number='.urlencode($r['tour_phone_number']) : 0;
			($r['tour_email_address']) ? $res[] = 'tour_email_address='.urlencode($r['tour_email_address']) : 0;
			
			if($r['tour_group']) {
				foreach((array) $r['tour_group'] as $k => $v) {
					foreach((array) $v as $sk => $sv) {
						$res[] = 'tour_group['.$k.']['.$sk.'][first_name]='.urlencode($sv['first_name']);
						$res[] = 'tour_group['.$k.']['.$sk.'][last_name]='.urlencode($sv['last_name']);
						$res[] = 'tour_group['.$k.']['.$sk.'][phone]='.urlencode($sv['phone']);
						$res[] = 'tour_group['.$k.']['.$sk.'][email]='.urlencode($sv['email']);
					
						foreach((array) $sv['forms'] as $fk => $fv) {
							if(is_array($fv)) $fv = implode(", ", $fv); // for multiselects
							$res[] = 'tour_group['.$k.']['.$sk.'][forms]['.$fk.']='.urlencode(stripslashes($fv));
						}
					}		
				}
			}
			
			if($r['tour_forms']) {
				foreach((array) $r['tour_forms'] as $k => $v) {
					if(is_array($v)) $v = implode(", ", $v); // for multiselects
					$res[] = 'tour_forms['.$k.']='.urlencode(stripslashes($v));
				} 
			}
			
			($r['payment_method']) ? $res[] = 'payment_method='.urlencode($r['payment_method']) : 0;
			
			($r['payment_method_add']) ? $res[] = 'payment_method_add='.urlencode($r['payment_method_add']) : 0;
			
			($r['payment_method'] == 'Credit Cards' && $r['tour_card_token']) ? $res[] = 'tour_card_token='.urlencode($r['tour_card_token']) : 0;
			($r['payment_method'] == 'PayPal' && $r['paypal_token']) ? $res[] = 'paypal_token='.urlencode($r['paypal_token']) : 0;
			($r['payment_method'] == 'PayPal' && $r['paypal_payer_id']) ? $res[] = 'paypal_payer_id='.urlencode($r['paypal_payer_id']) : 0;
			
			($r['agree_terms']) ? $res[] = 'agree_terms='.urlencode($r['agree_terms']) : 0;
			
			($r['review_sent']) ? $res[] = 'review_sent='.urlencode($r['review_sent']) : 0;
			
			($r['marketing_consent']) ? $res[] = 'marketing_consent='.urlencode($r['marketing_consent']) : 0;
			
			// add in external elements
			($this->refid) ? $res['refid'] = '&refid='.$this->refid : 0;
			($this->promo_code) ? $res['promo'] = '&trigger_code='.$this->promo_code : 0;
			
			// add in requesting IP
			$res['ip'] = $_SERVER["REMOTE_ADDR"];
			
			$request = '&'.implode('&', $res);
			
			$this->XMLRequest(commit, $request);
			
			return $this->commit_response;
		}
		
		// ------------------------------------------------------------------------------
		// Create an outgoing commit request based on the CART data
		// ------------------------------------------------------------------------------
		function sendBookingOrder($var=null, $arg=null) {
			$r = ($var) ? $var : $_REQUEST;
			
			if($arg) $res[] = $arg; // extra API options
			
			if(!is_array($r['booking'])) $this->error('sendBookingOrder failed. Booking array was not found', 1);
			
			foreach($r['booking'] as $b) {
			
				$res[] = '<booking>';
			
				($b['date']) ? $res[] = '<date>'.urlencode($b['date']).'</date>' : $this->error('sendBookingOrder failed. book element "date" is empty', 1);
				($b['book']) ? $res[] = '<book>'.urlencode($b['book']).'</book>' : $this->error('sendBookingOrder failed. book element "book" is empty', 1);
				
				($b['adult_num']) ? $res[] = '<adult_num>'.urlencode($b['adult_num']).'</adult_num>' : 0;
				($b['child_num']) ? $res[] = '<child_num>'.urlencode($b['child_num']).'</child_num>' : 0;
				($b['senior_num']) ? $res[] = '<senior_num>'.urlencode($b['senior_num']).'</senior_num>' : 0;
				($b['price4_num']) ? $res[] = '<price4_num>'.urlencode($b['price4_num']).'</price4_num>' : 0;
				($b['price5_num']) ? $res[] = '<price5_num>'.urlencode($b['price5_num']).'</price5_num>' : 0;
				($b['price6_num']) ? $res[] = '<price6_num>'.urlencode($b['price6_num']).'</price6_num>' : 0;
				($b['price7_num']) ? $res[] = '<price7_num>'.urlencode($b['price7_num']).'</price7_num>' : 0;
				($b['price8_num']) ? $res[] = '<price8_num>'.urlencode($b['price8_num']).'</price8_num>' : 0;
				($b['price9_num']) ? $res[] = '<price9_num>'.urlencode($b['price9_num']).'</price9_num>' : 0;
				
				if($b['tour_group']) {
					
					$res[] = '<tour_group>';
					
					foreach((array) $b['tour_group'] as $k => $v) {
						foreach((array) $v as $sk => $sv) {
							$res[] = '<'.$k.' num="'.$sk.'">';
							
							$res[] = '<first_name>'.urlencode($sv['first_name']).'</first_name>';
							$res[] = '<last_name>'.urlencode($sv['last_name']).'</last_name>';
							$res[] = '<phone>'.urlencode($sv['phone']).'</phone>';
							$res[] = '<email>'.urlencode($sv['email']).'</email>';
							
							if(is_array($sv['forms'])) {
								$res[] = '<forms>';
															
								foreach((array) $sv['forms'] as $fk => $fv) {
									if(is_array($fv)) $fv = implode(", ", $fv); // for multiselects
									$res[] = '<form num="'.$fk.'">'.urlencode(stripslashes($fv)).'</form>';
								}
								
								$res[] = '</forms>';
							}
							
							$res[] = '</'.$k.'>';
							
						}		
					}
					
					$res[] = '</tour_group>';
					
				}
				
				if($b['tour_forms']) {
					$res[] = '<tour_forms>';
				
					foreach((array) $b['tour_forms'] as $k => $v) {
						if(is_array($v)) $v = implode(", ", $v); // for multiselects
						$res[] = '<form num="'.$k.'">'.urlencode(stripslashes($v)).'</form>';
					}
					
					$res[] = '</tour_forms>';
				}
				
				if($b['pickup']) {
					
					$pickup_split = explode("-", stripslashes($b['pickup']));
					
					$res[] = '<pickup>'.$pickup_split[0].'</pickup>';
					if($pickup_split[1]) $res[] = '<pickup_source>'.$pickup_split[1].'</pickup_source>';
					
				}
				
				$res[] = '</booking>';
				
			} // cart loop
			
			$res[] = '<payment>';
			
			($r['trigger_code']) ? $res[] = '<trigger_code>'.$r['trigger_code'].'</trigger_code>' : 0;
				
			($r['tour_first_name']) ? $res[] = '<tour_first_name>'.$r['tour_first_name'].'</tour_first_name>' : 0;
			($r['tour_last_name']) ? $res[] = '<tour_last_name>'.$r['tour_last_name'].'</tour_last_name>' : 0;
			($r['tour_address_1']) ? $res[] = '<tour_address_1>'.$r['tour_address_1'].'</tour_address_1>' : 0;
			($r['tour_address_2']) ? $res[] = '<tour_address_2>'.$r['tour_address_2'].'</tour_address_2>' : 0;
			($r['tour_city']) ? $res[] = '<tour_city>'.$r['tour_city'].'</tour_city>' : 0;
			($r['tour_stateprov']) ? $res[] = '<tour_stateprov>'.$r['tour_stateprov'].'</tour_stateprov>' : 0;
			($r['tour_country']) ? $res[] = '<tour_country>'.$r['tour_country'].'</tour_country>' : 0;
			($r['tour_postal_code']) ? $res[] = '<tour_postal_code>'.$r['tour_postal_code'].'</tour_postal_code>' : 0;
			($r['tour_phone_number']) ? $res[] = '<tour_phone_number>'.$r['tour_phone_number'].'</tour_phone_number>' : 0;
			($r['tour_email_address']) ? $res[] = '<tour_email_address>'.$r['tour_email_address'].'</tour_email_address>' : 0;
			($r['sms']) ? $res[] = '<sms>'.$r['sms'].'</sms>' : 0;
			
			($r['payment_method']) ? $res[] = '<payment_method>'.urlencode(stripslashes($r['payment_method'])).'</payment_method>' : 0;
			
			($r['payment_method_add']) ? $res[] = '<payment_method_add>'.urlencode(stripslashes($r['payment_method_add'])).'</payment_method_add>' : 0;
			
			($r['payment_method'] == 'Credit Cards' && $r['tour_card_token']) ? $res[] = '<tour_card_token>'.$r['tour_card_token'].'</tour_card_token>' : 0;
			($r['payment_method'] == 'PayPal' && $r['paypal_token']) ? $res[] = '<paypal_token>'.$r['paypal_token'].'</paypal_token>' : 0;
			($r['payment_method'] == 'PayPal' && $r['paypal_payer_id']) ? $res[] = '<paypal_payer_id>'.$r['paypal_payer_id'].'</paypal_payer_id>' : 0;
			
			($r['agree_terms']) ? $res[] = '<agree_terms>'.$r['agree_terms'].'</agree_terms>' : 0;
			
			($r['review_sent']) ? $res[] = '<review_sent>'.$r['review_sent'].'</review_sent>' : 0;
			
			($r['marketing_consent']) ? $res[] = '<marketing_consent>'.$r['marketing_consent'].'</marketing_consent>' : 0;
			
			// add in external elements
			($this->refid) ? $res[] = '<refid>'.$this->refid.'</refid>' : 0;
			($this->promo_code) ? $res[] = '<trigger_code>'.$this->promo_code.'</trigger_code>' : 0;
			
			// add in requesting IP
			$res[] = '<ip>'.$_SERVER["REMOTE_ADDR"].'</ip>';
			
			// GIFT-CARD
			$res[] = '<expected>'.$r['expected'].'</expected>';
			($r['gift_card']) ? $res[] = '<gift_card>'.$r['gift_card'].'</gift_card>' : 0;
			
			$res[] = '</payment>';
			
			// ticketguardian
			($r['tour_tg_insurance_coverage']) ? $res[] = '<tg>'.$r['tour_tg_insurance_coverage'].'</tg>' : 0;
			
			($r['waiver']) ? $res[] = '<waiver>'.str_replace('data:image/png;base64,', '', $r['waiver']).'</waiver>' : 0;
			
			$request = implode('', $res);
			
			$this->XMLRequest(commitOrder, $request, 1);
			
			return $this->commit_response;
		}

		// ------------------------------------------------------------------------------
		// GIFT CARD
		// ------------------------------------------------------------------------------
		function sendGiftOrder($request=null) {
			$r = $request;
			$res = array();

			if($r['rezgoAction'] != 'addGiftCard') {
				$this->error('sendGiftOrder failed. Card array was not found', 1);
			}
			
			$res[] = '<card>';
				$res[] = '<number></number>';
				// AMOUNT
				if($r['billing_amount'] == 'custom') {
					$amnt = $r['custom_billing_amount'];
				}
				else {
					$amnt = $r['billing_amount'];
				}
				$res[] = '<amount>'.$amnt.'</amount>';
				$res[] = '<cash_value></cash_value>';
				$res[] = '<expires></expires>';
				$res[] = '<max_uses></max_uses>';
				// NAME
				$recipient_name = explode(" ", $r['recipient_name'], 2);
				$res[] = '<first_name>'.$recipient_name[0].'</first_name>';
				$res[] = '<last_name>'.$recipient_name[1].'</last_name>';
				$res[] = '<email>'.$r['recipient_email'].'</email>';
				// MSG
				$res[] = '<message>'.urlencode(htmlspecialchars_decode($r['recipient_message'], ENT_QUOTES)).'</message>';
				
				$res[] = '<send>1</send>';
				$res[] = '<payment>';
					$res[] = '<token>'.$r['gift_card_token'].'</token>';
					$res[] = '<first_name>'.$r['billing_first_name'].'</first_name>';
					$res[] = '<last_name>'.$r['billing_last_name'].'</last_name>';
					$res[] = '<address_1>'.$r['billing_address_1'].'</address_1>';
					$res[] = '<address_2>'.$r['billing_address_2'].'</address_2>';
					$res[] = '<city>'.$r['billing_city'].'</city>';
					$res[] = '<state>'.$r['billing_stateprov'].'</state>';
					$res[] = '<country>'.$r['billing_country'].'</country>';
					$res[] = '<postal>'.$r['billing_postal_code'].'</postal>';
					$res[] = '<phone>'.$r['billing_phone'].'</phone>';
					$res[] = '<email>'.$r['billing_email'].'</email>';
					if($r['terms_agree'] == 'on') {
						$res[] = '<agree_terms>1</agree_terms>';
					} 
					else {
						$res[] = '<agree_terms>0</agree_terms>';
					}
					$res[] = '<ip>'.$_SERVER['REMOTE_ADDR'].'</ip>';
				$res[] = '</payment>';
			$res[] = '</card>';

			$request = implode('', $res);
			
			$this->XMLRequest('addGiftCard', $request, 1);

			return $this->commit_response;
		}		

		// ------------------------------------------------------------------------------
		// Sign Waiver
		// ------------------------------------------------------------------------------
		function signWaiver($request=null) {
			$r = ($request) ? $request : $_REQUEST;
			$res = array();
			
			$birthdate = $r['pax_birthdate']['year'].'-'.sprintf('%02d', $r['pax_birthdate']['month']).'-'.sprintf('%02d', $r['pax_birthdate']['day']);
			
			if ($r['pax_type'] != 'general') {
			
				$pax_forms = $r['pax_group'][$r['pax_type']][$r['pax_type_num']]['forms'];
				
				foreach((array) $pax_forms as $form_id => $form_answer) {
					if(is_array($form_answer)) { // for multiselects
						$form_answer = implode(', ', $form_answer); 
						$r['pax_group'][$r['pax_type']][$r['pax_type_num']]['forms'][$form_id] = $form_answer;
					}
				}
			
			}
			
			$group_forms = serialize($r['pax_group']);
			
			$res[] = '<sign>';
				$res[] = '<type>pax</type>'; // change to dynamic later
				$res[] = '<child>'.$r['child'].'</child>';
				$res[] = '<pax_type>'.$r['pax_type'].'</pax_type>';
				$res[] = '<pax_type_num>'.$r['pax_type_num'].'</pax_type_num>';
				$res[] = '<order_code>'.$r['order_code'].'</order_code>';
				$res[] = '<trans_num>'.$r['trans_num'].'</trans_num>';
				$res[] = '<item_id>'.$r['pax_item'].'</item_id>';
				$res[] = '<pax_id>'.$r['pax_id'].'</pax_id>';
				$res[] = '<first_name>'.$r['pax_first_name'].'</first_name>';
				$res[] = '<last_name>'.$r['pax_last_name'].'</last_name>';
				$res[] = '<phone_number>'.$r['pax_phone'].'</phone_number>';
				$res[] = '<email_address>'.$r['pax_email'].'</email_address>';
				$res[] = '<birthdate>'.$birthdate.'</birthdate>';
				$res[] = '<group_forms>'.$group_forms.'</group_forms>';
				$res[] = '<waiver_text>'.$r['pax_waiver_text'].'</waiver_text>';
				$res[] = '<signature>'.str_replace('data:image/png;base64,', '', $r['pax_signature']).'</signature>';
			$res[] = '</sign>';
           
			$request = implode('', $res);   //echo "<script> console.log($request); </script>";
			
			$this->XMLRequest('sign', $request, 1);

			return $this->signing_response;
		}		

		function getGiftCard($request=null) {
			if(!$request) {
				$this->error('No search argument provided, expected card number');
			}

			$this->XMLRequest('searchGiftCard', $request);

			return $this->gift_card;
		}

		// this function is for sending a partial commit request, it does not add any values itself
		function sendPartialCommit($var=null) {
			$request = '&'.$var;
			
			$this->XMLRequest(commit, $request);
			
			return $this->commit_response;
		}
		
		// send results of contact form
		function sendContact($var=null) {
			$r = ($var) ? $var : $_REQUEST;
			
			// we use full_name instead of name because of a wordpress quirk
			($r['full_name']) ? $res[] = 'name='.urlencode($r['full_name']) : $this->error('contact element "full_name" is empty', 1);
			($r['email']) ? $res[] = 'email='.urlencode($r['email']) : $this->error('contact element "email" is empty', 1);
			($r['body']) ? $res[] = 'body='.urlencode($r['body']) : $this->error('contact element "body" is empty', 1);
			
			($r['phone']) ? $res[] = 'phone='.urlencode($r['phone']) : 0;
			($r['address']) ? $res[] = 'address='.urlencode($r['address']) : 0;
			($r['address2']) ? $res[] = 'address2='.urlencode($r['address2']) : 0;		
			($r['city']) ? $res[] = 'city='.urlencode($r['city']) : 0;
			($r['state_prov']) ? $res[] = 'state_prov='.urlencode($r['state_prov']) : 0;
			($r['country']) ? $res[] = 'country='.urlencode($r['country']) : 0;

			$request = '&'.implode('&', $res);

			$this->XMLRequest(contact, $request);

			return $this->contact_response;
		}
		
		// get review data
		function getReview($q=null, $type=null, $limit=null ) {
			
			// 'type' default is booking // 'limit' default is 5
			
			($q) ? $res[] = 'q='.urlencode($q) : 0;	
			($type) ? $res[] = 'type='.urlencode($type) : 0;	
			($limit) ? $res[] = 'limit='.urlencode($limit) : 0;		
			
			$request = implode('&', $res);

			$this->XMLRequest(review, $request);

			return $this->review_response;
		}
		
		// get pickup list
		function getPickupList($option_id=null) { 
			
			($option_id) ? $res[] = 'q='.urlencode($option_id) : 0;		
			
			$request = implode('&', $res);

			$this->XMLRequest(pickup, $request);

			return $this->pickup_response;
		}
		
		// get pickup location
		function getPickupItem($option_id=null, $pickup_id=null) { 
			
			($option_id) ? $res[] = 'q='.urlencode($option_id) : 0;	
			($pickup_id) ? $res[] = 'pickup='.urlencode($pickup_id) : 0;	
			
			$request = implode('&', $res);

			$this->XMLRequest(pickup, $request);

			return $this->pickup_response->pickup;
		}
		

		// add an item to the shopping cart
		function addToCart($item, $clear=0) {
			
			if(!$clear) {
				// don't load the existing cart if we are clearing it
				if($_SESSION['rezgo_cart_'.REZGO_CID]) { $cart = $_SESSION['rezgo_cart_'.REZGO_CID]; }
				elseif($_COOKIE['rezgo_cart_'.REZGO_CID]) { $cart = unserialize(stripslashes($_COOKIE['rezgo_cart_'.REZGO_CID])); }
			}
			
			// add the new item to the cart
			foreach((array) $item as $v) {
				
				// at least 1 price point must be set to add this item
				// this works to remove items as well, if they match the date (above) but have no prices set
				if(
					$v['adult_num'] || $v['child_num'] || $v['senior_num'] || 
					$v['price4_num'] || $v['price5_num'] || $v['price6_num'] || 
					$v['price7_num'] || $v['price8_num'] || $v['price9_num']
				) {
					$cart[] = $v;
				}
			}
				
			return $cart;
		}
		
		// add an item to the shopping cart
		function editCart($item) {
			
			// load the existing cart
			if($_SESSION['rezgo_cart_'.REZGO_CID]) { $cart = $_SESSION['rezgo_cart_'.REZGO_CID]; }
			elseif($_COOKIE['rezgo_cart_'.REZGO_CID]) { $cart = unserialize(stripslashes($_COOKIE['rezgo_cart_'.REZGO_CID])); }
			
			// add the new item to the cart
			foreach((array) $item as $k => $v) {
				
				if($cart[$k]) {
				
					// at least 1 price point must be set to edit this item
					// this works to remove items as well, if they match the date (above) but have no prices set
					if(
						$v['adult_num'] || $v['child_num'] || $v['senior_num'] || 
						$v['price4_num'] || $v['price5_num'] || $v['price6_num'] || 
						$v['price7_num'] || $v['price8_num'] || $v['price9_num']
					) {
						$cart[$k] = $v;
					} else {
						unset($cart[$k]);
					}
				
				}
				
			}
			
			return $cart;
		}
		
		// add pickup to the shopping cart
		function pickupCart($index, $pickup) {
			
			// load the existing cart
			if($_SESSION['rezgo_cart_'.REZGO_CID]) { $cart = $_SESSION['rezgo_cart_'.REZGO_CID]; }
			elseif($_COOKIE['rezgo_cart_'.REZGO_CID]) { $cart = unserialize(stripslashes($_COOKIE['rezgo_cart_'.REZGO_CID])); }
				
			if($cart[$index]) {
				
				$cart[$index]['pickup'] = $pickup;
			
				// update cart
				$ttl = (REZGO_CART_TTL > 0 || REZGO_CART_TTL === 0) ? REZGO_CART_TTL : 86400;
				setcookie("rezgo_cart_".REZGO_CID, serialize($cart), time() + $ttl, '/', $_SERVER['SERVER_NAME']);
				
				$this->setShoppingCart(serialize($cart));
			
			}
			
			return $cart;
		}
		
		function clearCart() {
			unset($_SESSION['rezgo_cart_'.REZGO_CID]);
			unset($_COOKIE['rezgo_cart_'.REZGO_CID]);
			setcookie("rezgo_cart_".REZGO_CID, '', time() + $ttl, '/', $_SERVER['SERVER_NAME']);
		}
		
		// get a list of all the item IDs that are currently in the cart (used for search queries)
		function getCartIDs() {
			$n = 1;
			if(is_array($this->cart)) {
				foreach($this->cart as $k => $v) {
					$ids[] = $v['uid'];
					$n++;
				}
				$ids = ($ids) ? implode(",", $ids) : '';
			}
			return $ids;
		}
		
		// fetch the full shopping cart including detailed tour info
		function getCart($hide=null) {
			
			$types = array('adult', 'child', 'senior', 'price4', 'price5', 'price6', 'price7', 'price8', 'price9');
			
			if(is_array($this->cart)) {	
				
				foreach($this->cart as $k => $v) {
					$a .= '<item>';
						$a .= '<id>'.$v[uid].'</id>';
						$a .= '<date>'.$v[date].'</date>';
						$a .= '<adult_num>'.$v['adult_num'].'</adult_num>';
						$a .= '<child_num>'.$v['child_num'].'</child_num>';
						$a .= '<senior_num>'.$v['senior_num'].'</senior_num>';
						$a .= '<price4_num>'.$v['price4_num'].'</price4_num>';
						$a .= '<price5_num>'.$v['price5_num'].'</price5_num>';
						$a .= '<price6_num>'.$v['price6_num'].'</price6_num>';
						$a .= '<price7_num>'.$v['price7_num'].'</price7_num>';
						$a .= '<price8_num>'.$v['price8_num'].'</price8_num>';
						$a .= '<price9_num>'.$v['price9_num'].'</price9_num>';
						$a .= '<pickup>'.$v['pickup'].'</pickup>';
					$a .= '</item>';
					
					$cart_ids[] = $k; // translate cart ID for the output below
				}
				$promo = ($this->promo_code) ? '<trigger_code>'.$this->promo_code.'</trigger_code>' : '';		
				
				// attach the search as an index including the limit value and promo code
				$request = $a.$promo.$limit;
				
				$this->XMLRequest(cart, $request);
				
				if($this->cart_response) {
					
					$tour = $this->cart_response;
					
					$c = 0;
					foreach($tour as $k => $v) {
						
						$c = $cart_ids[$k];
						
						$cart[$c] = $v;
						
						$cart[$c]->cartID = $c;
							
						$cart[$c]->booking_date = strtotime((string)$v->date[0]->attributes()->value);
						
						$cart[$c]->availability = (string) $v->date[0]->availability;
						
						//$element_total = 0;
						$cart[$c]->pax_count = (int)$v->adult_num + 
						(int)$v->child_num + 
						(int)$v->senior_num + 
						(int)$v->price4_num + 
						(int)$v->price5_num + 
						(int)$v->price6_num + 
						(int)$v->price7_num + 
						(int)$v->price8_num + 
						(int)$v->price9_num;
						
						// if we are hiding empty elements and this element doesn't have enough space, remove it
						if($hide && $cart[$c]->availability < $cart[$c]->pax_count) {
							unset($cart[$c]);
						} else {
						//	$c++;
						}
						
					}
					
					
				}	
			}
			
			return (array) $cart;
			
		}
		
		// FORMAT CARD
		function cardFormat($num) {
			$cc = str_replace(array('-', ' '), '', $num);
			$cc_length = strlen($cc);
			$new_card = substr($cc, -4);

			for ($i = $cc_length - 5; $i >= 0; $i--) {
				if((($i + 1) - $cc_length) % 4 == 0) {
					$new_card = '-' . $new_card;
				}

				$new_card = $cc[$i] . $new_card;
			}

			return $new_card;
		}		

		// ------------------------------------------------------------------------------
		// CLEAR PROMO CODE
		// ------------------------------------------------------------------------------
		function resetPromoCode() {
			unset($_REQUEST['promo']);
			unset($_SESSION['rezgo_promo']);
			setcookie('rezgo_promo', '', time() - 3600, '/', $_SERVER['SERVER_NAME']);
		}
	}