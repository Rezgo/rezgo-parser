<?php 
	// any new page must start with the page_header, it will include the correct files
	// so that the rezgo parser classes and functions will be available to your templates
	
	require('rezgo/include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite($_REQUEST['sec']);
	
	// remove the 'mode=page_type' from the query string we want to pass on
	$_SERVER['QUERY_STRING'] = preg_replace("/([&|?])?mode=([a-zA-Z_]+)/", "", $_SERVER['QUERY_STRING']);
	
	$site->setPageTitle((($_REQUEST['title']) ? $_REQUEST['title'] : ucwords(str_replace("page_", "", $_REQUEST['mode']))));
	
	if($_REQUEST['mode'] == 'page_details') {
		
		/*
			this query searches for an item based on a com id (limit 1 since we only want one response)
			then adds a $f (filter) option by uid in case there is an option id, and adds a date in case there is a date set	
		*/
		
		$item = $site->getTours('t=com&q='.$_REQUEST['com'].'&f[uid]='.$_REQUEST['option'].'&d='.$_REQUEST['date'].'&limit=1', 0);
		
		// if the item does not exist, we want to generate an error message and change the page accordingly
		if(!$item) { 
			$item->unavailable = 1;
			$item->name = 'Item Not Available'; 
		}
		
		if ($item->seo->seo_title != '') {
			$site->setPageTitle($item->seo->seo_title);
		} else {
			$site->setPageTitle($item->item);
		}
		
		$site->setMetaTags('
			<meta name="description" content="' . $item->seo->introduction . '" /> 
			<meta property="og:title" content="' . $item->seo->seo_title . '" /> 
			<meta property="og:description" content="' . $item->seo->introduction . '" /> 
			<meta property="og:image" content="' . $item->media->image[0]->path . '" /> 
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
		');
		
	} elseif($_REQUEST['mode'] == 'index') {
		
		// expand to include keywords and dates
		$site->setPageTitle((($_REQUEST['tags']) ? ucwords($_REQUEST['tags']) : 'Home'));
		
	}
	
?>

<?=$site->getTemplate('header')?>

<script type="text/javascript">
// for iFrameResize native version
// MDN PolyFil for IE8 
if (!Array.prototype.forEach){
    Array.prototype.forEach = function(fun /*, thisArg */){
        "use strict";
        if (this === void 0 || this === null || typeof fun !== "function") throw new TypeError();

        var
            t = Object(this),
            len = t.length >>> 0,
            thisArg = arguments.length >= 2 ? arguments[1] : void 0;

        for (var i = 0; i < len; i++)
            if (i in t)
                fun.call(thisArg, t[i], i, t);
    };
}	
</script>

<div id="rezgo_content_container" style="width:100%;">
	<iframe id="rezgo_content_frame" src="/<?=$_REQUEST['mode']?>?<?=$_SERVER['QUERY_STRING']?>" style="width:100%; height:900px; padding:0px; margin:0px;" frameBorder="0" scrolling="no"></iframe>
</div>

<script type="text/javascript" src="/js/iframeResizer.min.js"></script>

<script type="text/javascript">
	iFrameResize ({
    enablePublicMethods: true,
    scrolling: true
	});
</script>
			
<?=$site->getTemplate('footer')?>

<!-- Start Alexa Certify Javascript -->
<script type="text/javascript" src="https://d31qbv1cthcecs.cloudfront.net/atrk.js"></script>
<script type="text/javascript">_atrk_opts = { atrk_acct: "51dve1aoim00G5", domain:"rezgo.com"}; atrk();</script>
<noscript><img src="https://d5nxst8fruw4z.cloudfront.net/atrk.gif?account=51dve1aoim00G5" style="display:none" height="1" width="1" alt="" /></noscript>
<!-- End Alexa Certify Javascript -->