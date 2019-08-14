<?php
	// any new page must start with the page_header, it will include the correct files
	// so that the rezgo parser classes and functions will be available to your templates

	require('rezgo/include/page_header.php');

	// start a new instance of RezgoSite
	$site = new RezgoSite($_REQUEST['sec'], 1);
	// GET COMPANY DETAILS
	$company = $site->getCompanyDetails();
	// remove the 'mode=page_type' from the query string we want to pass on
	$_SERVER['QUERY_STRING'] = preg_replace("/([&|?])?mode=([a-zA-Z_]+)/", "", $_SERVER['QUERY_STRING']);

	// set a default page title
	$site->setPageTitle((($_REQUEST['title']) ? $_REQUEST['title'] : ucwords(str_replace("page_", "", $_REQUEST['mode']))));
	$site->setMetaTags('<link rel="canonical" href="'.(string) $company->primary_domain.'" />');

	if ($_REQUEST['mode'] == 'page_details') {
		/*
			this query searches for an item based on a com id (limit 1 since we only want one response)
			then adds a $f (filter) option by uid in case there is an option id, and adds a date in case there is a date set
		*/

		$item = $site->getTours('t=com&q='.$_REQUEST['com'].'&f[uid]='.$_REQUEST['option'].'&d='.$_REQUEST['date'].'&limit=1', 0);

		// if the item does not exist, we want to generate an error message and change the page accordingly
		if (!$item) {
			$item = new stdClass();
			$item->unavailable = 1;
			$item->name = 'Item Not Available';
		}

		if ($item->seo->seo_title != '') {
			$page_title = $item->seo->seo_title;
		} else {
			$page_title = $item->item;
		}

		if ($item->seo->introduction != '') {
			$page_description = $item->seo->introduction;
		} else {
			$page_description = strip_tags($item->details->overview);
		}

		$site->setPageTitle($page_title);

		$site->setMetaTags('
			<meta name="description" content="' . $page_description . '" />
			<meta property="og:url" content="https://' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']  . '" />
			<meta property="og:title" content="' . $page_title . '" />
			<meta property="og:description" content="' . $page_description . '" />
			<meta property="og:image" content="' . $item->media->image[0]->path . '" />
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<link rel="canonical" href="https://'.(string) $company->primary_domain.$_SERVER['REQUEST_URI'].'" />
		');
	} elseif ($_REQUEST['mode'] == 'page_content') {
		$title = $site->getPageName($page);

		$site->setPageTitle($title);

	} elseif ($_REQUEST['mode'] == 'index') {

		// expand to include keywords and dates
		$site->setPageTitle((($_REQUEST['tags']) ? ucwords($_REQUEST['tags']) : 'Home'));

	}

	$_SERVER['QUERY_STRING'] .= '&title=' . $site->pageTitle;
	
	// output site header
	echo $site->getTemplate('header');
	
	if ($site->config('REZGO_COUNTRY_PATH')) {
		include(REZGO_COUNTRY_PATH);
		} else {
		include($site->path.'/include/countries_list.php');
	}

?>
<script>
	// load jQuery if not loaded
	window.onload = function() {
		if(typeof jQuery == 'undefined') { 
			// load jQuery
			var script = document.createElement('script');
			script.src = 'https://code.jquery.com/jquery-1.11.3.min.js';
			script.type = 'text/javascript';
			script.onload = function() {
					var $ = window.jQuery;
			};
			document.getElementsByTagName('head')[0].appendChild(script);
		}
	}
</script>

<?php if(in_array((string) $company->country, $eu_countries)) { ?>

<script>
		window.cookieconsent_options = {
				message: 'This website uses cookies to improve user experience. By using our website you consent to all cookies in accordance with our Cookie Policy. Click &lsquo;Accept&rsquo; to allow all cookies from this website.',
				theme: '<?php echo $site->path;?>/css/cookieconsent.css',
				learnMore: 'Read more',
				link: '/cookie-policy',
				dismiss: 'Accept'
		};
</script>

<script src="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/1.0.9/cookieconsent.min.js"></script>
<?php } ?>
<script type="text/javascript" src="<?php echo $site->base; ?>/js/ie8.polyfils.min.js"></script>
<script type="text/javascript" src="<?php echo $site->base; ?>/js/iframeResizer.contentWindow.min.js"></script>

<div id="rezgo_content_container" style="width:100%;">
	<iframe id="rezgo_content_frame" name="rezgo_content_frame" src="<?php echo $site->base;?>/<?php echo $_REQUEST['mode']?>?<?php echo $_SERVER['QUERY_STRING']?>" style="width:100%; height:900px; padding:0px; margin:0px;" frameBorder="0" scrolling="no"></iframe>
</div>

<script type="text/javascript" src="<?php echo $site->base; ?>/js/iframeResizer.min.js"></script>

<script type="text/javascript">
	iFrameResize ({
		enablePublicMethods: true,
		scrolling: true,
		messageCallback : function(msg){ // send message for scrolling
			var scroll_to = msg.message;
			jQuery('html, body').animate({
					scrollTop: scroll_to
			}, 600);
		}
	});
</script>

<?php if($_REQUEST['mode'] == 'page_book' || $_SERVER['SCRIPT_NAME'] == '/page_payment.php') { ?>

  <!-- waiver modal -->
  <div id="rezgo-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 id="rezgo-modal-title" class="modal-title"></h4>
        </div>

        <iframe id="rezgo-modal-iframe" frameborder="0" scrolling="no" style="width:100%; padding:0px; margin:0px;"></iframe>

        <div id="rezgo-modal-loader" style="display:none">
          <div class="modal-loader"></div>
        </div>
      </div>
    </div>
  </div>

  <link href="<?php echo $site->path;?>/css/bootstrap-modal.css" rel="stylesheet" />
  <link href="<?php echo $site->path;?>/css/rezgo-modal.css" rel="stylesheet" />
  <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<?php } ?>

<?php echo $site->getTemplate('footer')?>
