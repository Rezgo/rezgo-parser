<?php

// ------------------------------------------------------------------------
// Get current system version
// ------------------------------------------------------------------------
	$docroot = getenv("DOCUMENT_ROOT");
	$rezgo_version = file_get_contents($docroot."/README.md");
	define(REZGO_VERSION, $rezgo_version);

	/*
		---------------------------------------------------------------------------
			Custom config options
		---------------------------------------------------------------------------
	*/

	define( "REZGO_COUNTRY_PATH", $docroot."/rezgo/include/countries_list.php");

	/*
		---------------------------------------------------------------------------
			Basic configuration options
		---------------------------------------------------------------------------
	*/

	// Your company ID and your API KEY for the Rezgo API, they can both be found
	// on the main settings page on the Rezgo back-end.
	define(	"REZGO_CID", "552");
	define(	"REZGO_API_KEY", "1C6-O7B7-A1O0-E5A");
	//define(	"REZGO_CID", "2075");
	//define(	"REZGO_API_KEY", "7X6-R5I5-J3G9-I3H");

	// RECAPTCHA API keys for the contact page (get recaptcha: http://www.google.com/recaptcha)
	define("REZGO_CAPTCHA_PUB_KEY", "");
	define("REZGO_CAPTCHA_PRIV_KEY", "");

	// Path to the rezgo install on your server, the default is /rezgo in the root.
	// this is used by the template includes as well as fetching files in the templates
	define(	"REZGO_DIR","/rezgo");

	// The web root you want to precede links, the default is "" (empty) for root
	// to change to your own custom directory, add it like /my_directory or /my/directory
	define( "REZGO_URL_BASE","");

	// The top level frame you want to use as a destination for your links
	// works with top, blank, self, parent
	define( "REZGO_FRAME_TARGET","top"); // parent
		

	// Redirect page for fatal errors, set this to 0 to disable
	define(	"REZGO_FATAL_ERROR_PAGE","/error.php");			

	// The number of results per search page, this is used exclusively by the templates
	define(	"REZGO_RESULTS_PER_PAGE",10);

	/*
		---------------------------------------------------------------------------
			Advanced configuration options
		---------------------------------------------------------------------------
	*/

	// This determines which template directory will be used for loading page templates

	// handle the mobile-to-standard conversion
	if($_REQUEST['show_standard']) {
		setcookie("show_standard", $_REQUEST['show_standard'], 0, '/', $_SERVER['SERVER_NAME']);
		$_COOKIE['show_standard'] = $_REQUEST['show_standard'];
	}

	// detect a mobile browser
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	if($_COOKIE['show_standard'] != 'on' && (($_COOKIE['show_standard'] == 'mobile') || preg_match('/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))) {
		define( "REZGO_MOBILE_XML",					1																						);
	}

	// shopping cart lifespan
	define( "REZGO_CART_TTL",							86400																				);

	// For sites that want all site pages to be secure
	define("REZGO_ALL_SECURE", 						1																						);


	define( "REZGO_TEMPLATE", 						'default'																		);

	// Forward secure booking pages to the rezgo white-label, set to 0 if you want to
	// use your own domain for the secure pages
	define(	"REZGO_FORWARD_SECURE",			0																							);

	// By default, rezgo will use your own site as the secure site if forwarding is disabled,
	// if you want to use a different URL, then set it here (do not include https://)
	define( "REZGO_SECURE_URL",		''	);

	// Disable the header and footer passed from the API. Enable this if you are embedding
	// rezgo inside your own design.  This is only used by the header and footer templates
	define(	"REZGO_HIDE_HEADERS",				0																							);

	// The address of the Rezgo API, can use api.rezgo.com or api.beta.rezgo.com
	define( "REZGO_XML",	'api.rezgo.com'	);

	// The Rezgo API version you want to use, this setting should not be changed
	define(	"REZGO_XML_VERSION",				"current"																			);

	// The source of this API request, can take WL (white label) WP (wordpress) API or a custom string
	define(	"REZGO_ORIGIN",							"RP"																					);

	/*
		---------------------------------------------------------------------------
			Error and debug handling
		---------------------------------------------------------------------------
	*/

	// Send errors to console
	define(	"REZGO_FIREBUG_ERRORS",				0																						);

	// Display errors if they occur, disabled if you just want to send errors to console
	define(	"REZGO_DISPLAY_ERRORS",				0																						);

	// Stop the page loading if an error occurs
	define(	"REZGO_DIE_ON_ERROR",					0																						);

	define("DEBUG", 0);

	// Output all API transactions. THIS MUST BE SET TO 1 TO USE THE SETTINGS BELOW
	define(	"REZGO_TRACE_XML",						0																						);
	

	// Include calls to the API Cache (repeat queries) in the API output
	define(	"REZGO_INCLUDE_CACHE_XML",		0																						);

	// Send the API requests to console, to avoid disrupting the page design
	define(	"REZGO_FIREBUG_XML",					1																						);

	// Switch the commit API debug for one more suited for AJAX
	define(	"REZGO_SWITCH_COMMIT",				0																						);

	// Stop the commit request so booking AJAX responses can be checked
	define(	"REZGO_STOP_COMMIT",					0																						);

	// Display the API requests inline with the regular page content
	define(	"REZGO_DISPLAY_XML",					0																						);

	// Display the API responses inline with the regular page content
	define(	"REZGO_DISPLAY_RESPONSES",		0																						);

?>
