<?php 
$ta_key = '2E2B919141464E31B384DE1026A2DE7B'; 
?>
<!-- fonts -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:300,400,700">
<!-- calendar.css -->
<link href="<?php echo $this->path?>/css/responsive-calendar.css" rel="stylesheet">
<link href="<?php echo $this->path?>/css/responsive-calendar.rezgo.css?v=<?php echo REZGO_VERSION?>" rel="stylesheet">

<script type="text/javascript" src="<?php echo $this->path?>/js/responsive-calendar.min.js"></script>

<script type="text/javascript" src="<?php echo $this->path?>/js/jquery.readmore.min.js"></script>

<div class="tour-details-wrp container-fluid rezgo-container">

<?php
$items = $site->getTours('t=com&q='.$_REQUEST['com'].'&f[uid]='.$_REQUEST['option'].'&d='.$_REQUEST['date']);

if(!$items) { ?>
	<div class="jumbotron">
		<h3><i class="fa fa-exclamation-triangle"></i> Item not found</h3>
		<p class="lead">Sorry, the item you are looking for is not available or has no available options.</p>
		<p><a class="btn btn-lg btn-info" href="<?php echo $site->path;?>/" role="button">Return to home</a></p>
	</div>
<?php } else { ?>
	<?php 
	function date_sort($a, $b) {
		if ($a['start_date'] == $b['start_date']) {
				return 0;
		}
		return ($a['start_date'] < $b['start_date']) ? -1 : 1;
	}

	function recursive_array_search($needle,$haystack) {
		foreach($haystack as $key=>$value) {
				$current_key=$key;
				if($needle===$value OR (is_array($value) && recursive_array_search($needle,$value) !== false)) {
					return $current_key;
				}
		}
		return false;
	}	

	$day_options = array();
	$single_dates = 0;
	$calendar_dates = 0;
	$open_dates = 0;
	$item_count = 1;

	foreach($items as $item) {
		$site->readItem($item);
		
		$day_start = (int) $item->start_date;
		
		if (recursive_array_search($day_start, $day_options) === FALSE) {
			$day_options[(int) $item->uid]['start_date'] = $day_start;
		}
		
		// calendar availability types
		$calendar_selects = array('always', 'range', 'week', 'days');
		
		// open availability types
		$open_selects = array('never', 'number', 'specific');
		
		$date_selection = (string) $item->date_selection;
		
		// get option availability types (single, open or calendar)
		if ($date_selection == 'single') { 
			$single_dates++; 
		} elseif (in_array($date_selection, $open_selects)) { 
			$open_dates++; 
		} elseif (in_array($date_selection, $calendar_selects)) { 
			$calendar_dates++; 
		}
		
		// prepare media gallery
		if ($item_count == 1) {
			$media_count = $item->media->attributes()->value;

			$item_cutoff = $item->cutoff;

			if($media_count > 0) {
				$m = 0;
				foreach( $site->getTourMedia($item) as $media ) { 
					if ($m == 0) {
						$pinterest_img_path = $media->path;
					}
				
					$indicators .= '
					<li data-target="#rezgo-img-carousel" data-slide-to="'.$m.'"'.($m==0 ? ' class="active"' : '').'></li>'."\n";
					
					$media_items .= '
						<div class="item'.($m==0 ? ' active' : '').'">
							<img src="'.$media->path.'" alt="'.$media->caption.'">
							<div class="carousel-caption">'.$media->caption.'</div>
						</div>
					';				
				
					$m++;
				} 
			}
		}
		
		$item_count++;
	}

	// resort by date
	usort($day_options, 'date_sort'); 

	// setup calendar start days
	$company = $site->getCompanyDetails();

	// set defaults for start of availability
	$start_day = date('j', strtotime('+'.$item_cutoff.' days '.$company->time_format.' hours'));
	$open_cal_day = date('Y-m-d', strtotime('+'.$item_cutoff.' days '.$company->time_format.' hours'));

	// get the available dates
	$site->getCalendar($item->uid, $_REQUEST['date']); 

	$cal_day_set = FALSE;
	$calendar_events = '';

	foreach($site->getCalendarDays() as $day) {
		
		if ($day->cond == 'a') { $class = ''; } // available
		elseif ($day->cond == 'p') { $class = 'passed'; }
		elseif ($day->cond == 'f') { $class = 'full'; }
		elseif ($day->cond == 'i' || $day->cond == 'u') { $class = 'unavailable'; }
		elseif ($day->cond == 'c') { $class = 'cutoff'; }
		
		if ($day->date) { // && (int)$day->lead != 1
			$calendar_events .= '"'.date('Y-m-d', $day->date).'":{"class": "'.$class.'"},'."\n"; 
		}
		
		if ($_REQUEST['date']) {
			$request_date = strtotime($_REQUEST['date']);
			$calendar_start = date('Y-m', $request_date);
			$start_day =	date('j', $request_date);
			$open_cal_day =	date('Y-m-d', $request_date);
			$cal_day_set = TRUE;
		} else {
			if ($day->date) {
				$calendar_start = date('Y-m', (int) $day->date);
			}

			// redefine start days
			if ($day->cond == 'a' && !$cal_day_set) { 
				$start_day =	date('j', $day->date);
				$open_cal_day =	date('Y-m-d', $day->date);
				$cal_day_set = TRUE;
			} 
		}
	}

	$calendar_events = trim($calendar_events, ','."\n");
	
	if($site->isVendor()) { 
		$supplier = $site->getCompanyDetails($item->cid);
		$show_reviews = $supplier->reviews;
	} else {
		$show_reviews = $company->reviews;
	}
	
	// prepare average star rating
	$star_rating_display = '';
	
	if($show_reviews == 1 && $item->rating_count >= 1) {
						
		$avg_rating = round(floatval($item->rating) * 2) / 2;	
		
		for($n=1; $n<=5; $n++) {
			if($avg_rating == ($n-0.5)) $star_rating_display .= '<i class="rezgo-star fa fa-star-half-o rezgo-star-half"></i>';
			elseif($avg_rating >= $n) $star_rating_display .= '<i class="rezgo-star fa fa-star rezgo-star-full"></i>';
			else $star_rating_display .= '<i class="rezgo-star fa fa-star-o rezgo-star-empty"></i>';
		}	
		
	}
	
	?>

	<div class="row tour-details-title-wrp" itemscope itemtype="http://schema.org/Product">
		<div class="col-md-8 col-sm-7 col-xs-12">
			<h1 itemprop="name" id="rezgo-item-name">
				<span id="rezgo-item-name-text"><?php echo $item->item?></span>&nbsp;
        <span id="rezgo-item-star-rating" class="rezgo-show-reviews" data-toggle="tooltip" data-placement="right" title="Click to view reviews"><?php echo $star_rating_display?></span>
			</h1>
		</div>

		<div class="col-md-4 col-sm-5 col-xs-12">
			<div class="row">
				<div class="rezgo-cart-link-wrp col-xs-5 col-sm-6">
					<span>&nbsp;</span>

					<?php if($site->getCartState()) {
						$cart = $site->getCart();

						if($cart) {
							echo '<a class="rezgo-cart-link badge pull-left" href="'.$site->base.'/order"><span><i class="fa fa-shopping-cart"></i>&nbsp;<span class="hidden-xs">'.count($cart).' item'.((count($cart) > 1) ? 's' : '').' in </span>order<span class="visible-xs-inline"> ('.count($cart).')</span></span></a>';
						}
					} ?>
				</div>

				<div class="col-xs-7 col-sm-6">
					<div class="rezgo-social-box">
						<span id="rezgo-social-links">
							<a href="javascript:void(0);" title="Pin this on Pinterest" id="social_pinterest" onclick="window.open('http://www.pinterest.com/pin/create/button/?url=<?php echo urlencode('https://'.$_SERVER['HTTP_HOST'].$site->base.'/details/'.$item->com.'/'.$site->seoEncode($item->item))?>&media=<?php echo $pinterest_img_path?>&description=<?php echo urlencode($item->item).'%0A'.urlencode(strip_tags($item->details->overview))?>','pinterest','location=0,status=0,scrollbars=1,width=750,height=320');">
								<i class="fa fa-pinterest-square" id="pinterest_icon">&nbsp;</i>
							</a>					
							<a href="javascript:void(0);" title="Share this on Twitter" id="social_twitter" onclick="window.open('http://twitter.com/share?text=<?php echo urlencode('I found this great thing to do! "'.$item->item.'"')?>&url=' + escape(top.location.href)<?php if($site->exists($site->getTwitterName())) { ?> + '&via=<?php echo $site->getTwitterName()?>'<?php } ?>,'tweet','location=1,status=1,scrollbars=1,width=500,height=350');">
								<i class="fa fa-twitter-square" id="social_twitter_icon">&nbsp;</i>
							</a>
							<a href="javascript:void(0);" title="Share this on Facebook" id="social_facebook" onclick="window.open('http://www.facebook.com/sharer.php?u=' + escape(top.location.href) + '&t=<?php echo urlencode($item->item)?>','facebook','location=1,status=1,scrollbars=1,width=600,height=400');">
								<i class="fa fa-facebook-square" id="social_facebook_icon">&nbsp;</i>
							</a>
							<a href="javascript:void(0);" id="social_url" data-toggle="popover" data-ajaxload="<?php echo $site->base?>/shorturl_ajax.php?url=<?php echo  urlencode('https://'.$_SERVER['HTTP_HOST'].$site->base.'/details/'.$item->com.'/'.$site->seoEncode($item->item)) ?>">
								<i class="fa fa-share-alt-square" id="social_url_icon">&nbsp;</i>
							</a>
						</span>
					</div>
				</div>
			
			</div>
		</div><!-- // promocode/cart -->
	</div>

	<div class="row">
		<div class="col-md-8 col-sm-7 col-xs-12 rezgo-left-wrp">
			<?php if($media_count > 0) { ?>
				<div id="rezgo-img-carousel" class="carousel slide" data-ride="carousel">
					<ol class="carousel-indicators">
						<?php echo $indicators?>
					</ol>
					<div class="carousel-inner">
						<?php echo $media_items?>
					</div>
					<a class="left carousel-control" data-target="#rezgo-img-carousel" data-slide="prev">
						<span class="glyphicon glyphicon-chevron-left"></span>
					</a>
					<a class="right carousel-control" data-target="#rezgo-img-carousel" data-slide="next">
						<span class="glyphicon glyphicon-chevron-right"></span>
					</a>
				</div>
			<?php } ?>
		</div>

		<div class="col-md-4 col-sm-5 col-xs-12 rezgo-right-wrp pull-right">
			<?php if ($open_dates > 0) { ?>
				<div class="rezgo-calendar-wrp">
					<div class="rezgo-open-header">
						<span>Open Options</span>
					</div>

					<div class="rezgo-open-container">
						<?php $open_date = date('Y-m-d', strtotime('+1 day')); ?>
					
						<div class="rezgo-open-options" id="rezgo-open-option-<?php echo $opt?>" style="display:none;">
							<div class="rezgo-open-selector" id="rezgo-open-date-<?php echo $opt?>"></div>

							<script type="text/javascript">						
								$(document).ready(function(){
									$.ajax({
										url: '<?php echo $site->base?>/calendar_day.php?com=<?php echo $item->com?>&date=<?php echo $open_date?>&type=open',
										context: document.body,
										success: function(data) {
											if (data.indexOf('rezgo-order-none-available') == -1) {
												$('#rezgo-open-date-<?php echo $opt?>').html(data).slideDown('fast');
												$('#rezgo-open-option-<?php echo $opt?>').fadeIn('fast');
											}
										}
									});
								});
							</script> 
						</div>
					
						<div id="rezgo-open-memo"></div>
					</div>
				</div>
			<?php } // end if $open_dates > 0 ?>

			<?php if ( $calendar_dates > 0 || $single_dates > 10 ) { ?>
				<div class="hidden visible-xs">
					<span>&nbsp;</span>
				</div>

				<div class="rezgo-calendar-wrp">
					<div class="rezgo-calendar-header">
						<span>Choose a Date</span>
					</div>
					<div class="rezgo-calendar">
						<div class="responsive-calendar" id="rezgo-calendar">
							<div class="controls">
								<a class="pull-left" data-go="prev"><div class="glyphicon glyphicon-chevron-left"></div></a>
								<h4><span><span data-head-year></span> <span data-head-month></span></span></h4>
								<a class="pull-right" data-go="next"><div class="glyphicon glyphicon-chevron-right"></div></a>
							</div>
							<?php if ($company->start_week == 'mon') { ?>
							<div class="day-headers">
								<div class="day header">Mon</div>
								<div class="day header">Tue</div>
								<div class="day header">Wed</div>
								<div class="day header">Thu</div>
								<div class="day header">Fri</div>
								<div class="day header">Sat</div>
								<div class="day header">Sun</div>
							</div>
							<?php } else { ?>
							<div class="day-headers">
								<div class="day header">Sun</div>
								<div class="day header">Mon</div>
								<div class="day header">Tue</div>
								<div class="day header">Wed</div>
								<div class="day header">Thu</div>
								<div class="day header">Fri</div>
								<div class="day header">Sat</div>
							</div>
							<?php } ?>
							<div class="days" data-group="days"></div>
						</div>
						<div class="rezgo-calendar-legend">
							<span class="available">&nbsp;</span><span class="text-available"><span>&nbsp;Available&nbsp;&nbsp;</span></span>
							<span class="full">&nbsp;</span><span class="text-full"><span>&nbsp;Full&nbsp;&nbsp;</span></span>
							<span class="unavailable">&nbsp;</span><span class="text-unavailable"><span>&nbsp;Unavailable</span></span>
							<div id="rezgo-calendar-memo"></div>
						</div>
						<div id="rezgo-scrollto-options"></div>
						<div class="rezgo-date-selector" style="display:none;">
							<!-- available options will populate here -->
							<div class="rezgo-date-options"></div>
						</div>
						<div id="rezgo-date-script" style="display:none;">
							<!-- ajax script will be inserted here -->
						</div>
					</div>
				</div>
			<?php } elseif ( ($calendar_dates == 0 || $single_dates <= 10) && $open_dates == 0 ) { // single day options ?>
				<div class="rezgo-calendar-wrp">
					<?php $opt = 1; // pass an option counter to calendar day ?>

					<?php foreach ($day_options as $option) { ?>
						<div class="rezgo-calendar-single" id="rezgo-calendar-single-<?php echo $opt?>" style="display:none;">
  						<div class="rezgo-calendar-single-head">
                <?php
                $available_day = date('D', $option['start_date']);
                $available_date = date((string) $company->date_format, $option['start_date']);
                ?>
                <span class="rezgo-calendar-avail">
                  <span>Availability&nbsp;for:&nbsp;</span>
                </span>
                <strong><span class="rezgo-avail-day"><?php echo $available_day?>,&nbsp;</span><span class="rezgo-avail-date"><?php echo $available_date?></span></strong>
              </div>

  						<div class="rezgo-date-selector" id="rezgo-single-date-<?php echo $opt?>"></div>
						
						  <script type="text/javascript">
							$(document).ready(function(){
								$.ajax({
									url: '<?php echo $site->base?>/calendar_day.php?com=<?php echo $item->com?>&date=<?php echo date('Y-m-d', $option['start_date'])?>&option_num=<?php echo $opt?>&type=single',
									context: document.body,
									success: function(data) {
										if (data.indexOf('rezgo-order-none-available') == -1) {
											$('#rezgo-single-date-<?php echo $opt?>').html(data).slideDown('fast');
											$('#rezgo-calendar-single-<?php echo $opt?>').fadeIn('fast');
										}
									}
								});
							});
						  </script> 
						</div>
						<?php $opt++; ?>
					<?php } // end foreach ($day_options) ?> 
					
					<div id="rezgo-single-memo"></div>
				</div><!-- // .rezgo-calendar-wrp -->
			<!-- // single day booking -->
			<?php } // end single dates > 0 ?>

			<?php if (!$site->isVendor() && $site->getGateway()) { ?>
				<div id="rezgo-gift-link-use" class="rezgo-gift-link-wrp">
					<a class="rezgo-gift-link" href="<?php echo $site->base?>/gift-card">
						<span>
							<i class="fa fa-gift"></i>
							<span>&nbsp;Buy a gift card</span>
						</span>
					</a>
				</div>
			<?php } ?>

			<?php if (!$site->isVendor()) { ?>
				<div class="clear">
					<span>&nbsp;</span>
				</div>
        
        <?php
				$ref_parts = explode('/?', $_SERVER['HTTP_REFERER']);
				$promo_form_url = $ref_parts[0];
				?>

				<div id="rezgo-details-promo"><!-- hidden by default -->
					<div class="rezgo-form-group-short">
						<?php if (!$_SESSION['rezgo_promo']) { ?>
							<form class="form-inline" id="rezgo-promo-form" role="form" onsubmit="top.location.href= '<?php echo $promo_form_url?>/?promo=' + $('#rezgo-promo-code').val(); return false;" target="rezgo_content_frame"><!---->
								<label for="rezgo-promo-code">
									<span>
										<i class="fa fa-tags"></i>
										<span>&nbsp;</span>
										<span class="rezgo-promo-label">
											<span>Promo code</span>
										</span>
									</span>
								</label>
								<span>&nbsp;</span>
								<div class="input-group">
									<input type="text" class="form-control" id="rezgo-promo-code" name="promo" placeholder="Enter Promo Code" value="<?php echo ($_SESSION['rezgo_promo'] ? $_SESSION['rezgo_promo'] : '')?>" />
									<div class="input-group-btn">
										<button class="btn rezgo-btn-default" type="submit">
											<span>Apply</span>
										</button>
									</div>
								</div>
							</form>
						<?php } else { ?>
								<div class="input-group">
									<label for="rezgo-promo-code">
									<i class="fa fa-tags"></i>
									<span>&nbsp;</span>
									<span class="rezgo-promo-label">
										<span>Promo code</span>
									</span>
									</label>
									<span>&nbsp;</span>
									<span id="rezgo-promo-value"><?php echo $_SESSION['rezgo_promo']?></span>
									<span>&nbsp;</span> 
									<button id="rezgo-promo-clear" class="btn rezgo-btn-default btn-sm" onclick="top.location.href='<?php echo $promo_form_url?>/?promo='" target="_parent">clear</button>
								</div>

						<?php } ?>
					</div>
				</div>
			<?php } // end promo form ?>
		</div>

		<div class="col-md-8 col-sm-7 col-xs-12 rezgo-left-wrp pull-left" id="rezgo-details">
			<?php if($site->exists($item->details->highlights)) { ?> 
				<div class="rezgo-tour-highlights"><?php echo $item->details->highlights?></div>
			<?php } ?>
			
			<div class="rezgo-tour-description">
				<?php if($site->exists($item->details->overview)) { ?> 
					<div class="lead" id="rezgo-tour-overview"><?php echo $item->details->overview?></div>
				<?php } ?>	
					
				<?php
					unset($location);
					if($site->exists($item->location_name)) $location['name'] = $item->location_name;
					if($site->exists($item->location_address)) $location['address'] = $item->location_address;
					if($site->exists($item->city)) $location['city'] = $item->city;
					if($site->exists($item->state)) $location['state'] = $item->state;
					if($site->exists($item->country)) $location['country'] = ucwords($site->countryName(strtolower($item->country)));
				?>
				
				<?php if (count($location) > 0) { ?>
					<div id="rezgo-tour-location">
						<label id="rezgo-tour-location-label">
							<span>Location:&nbsp;</span>
						</label>

						<?php
							if ($location['address'] != '') {
								echo '
								'.($location['name'] != '' ? '<span class="rezgo-location-name">'.$location['name'].' - </span>' : '').'
								<span class="rezgo-location-address">'.$location['address'].'</span>';
							} else {
								echo '
								'.($location['city'] != '' ? '<span class="rezgo-location-city">'.$location['city'].', </span>' : '').'
								'.($location['state'] != '' ? '<span class="rezgo-location-state">'.$location['state'].', </span>' : '').'
								'.($location['country'] != '' ? '<span class="rezgo-location-country">'.$location['country'].'</span>' : '');
							}
						?>
					</div>
				<?php } ?>
								
				<?php if($site->isVendor()) { ?>
					<div id="rezgo-provided-by">
						<label id="rezgo-provided-by-label">
							<span>Provided by:&nbsp;</span>
						</label>

						<a href="<?php echo $site->base?>/supplier/<?php echo $item->cid?>">
							<span><?php echo $site->getCompanyName($item->cid)?></span>
						</a>
					</div>
				<?php } ?>
			</div>
			
			<?php if(!$site->config('REZGO_MOBILE_XML')) {
				// add 'in' class to expand collapsible for non-mobile devices
				$mclass = ' in';
			} ?>

			<div class="panel-group rezgo-desc-panel" id="rezgo-tour-panels">
				<?php if($site->exists($item->details->itinerary)) { ?> 
					<div class="panel panel-default rezgo-panel" id="rezgo-panel-itinerary">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section-link" data-target="#itinerary">
									<div class="rezgo-section-icon"><i class="fa fa-bars fa-lg"></i></div>
									<div class="rezgo-section-text"><span>Itinerary</span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="itinerary" class="panel-collapse collapse<?php echo $mclass?>">
							<div class="panel-body rezgo-panel-body"><?php echo $item->details->itinerary?></div>
						</div>
					</div>
				<?php } ?>

				<?php if($site->exists($item->details->pick_up)) { ?> 
					<div class="panel panel-default rezgo-panel" id="rezgo-panel-pickup">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section-link" data-target="#pickup">
									<div class="rezgo-section-icon"><i class="fa fa-map-marker fa-lg"></i></div>
									<div class="rezgo-section-text"><span>Pickup</span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="pickup" class="panel-collapse collapse<?php echo $mclass?>">
						<div class="panel-body rezgo-panel-body"><?php echo $item->details->pick_up?></div>
						</div>
					</div> 
				<?php } ?>

				<?php if($site->exists($item->details->drop_off)) { ?> 
					<div class="panel panel-default rezgo-panel" id="rezgo-panel-dropoff">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section-link" data-target="#dropoff">
									<div class="rezgo-section-icon"><i class="fa fa-location-arrow fa-lg"></i></div>
									<div class="rezgo-section-text"><span>Drop Off</span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="dropoff" class="panel-collapse collapse<?php echo $mclass?>">
						<div class="panel-body rezgo-panel-body"><?php echo $item->details->drop_off?></div>
						</div>
					</div> 
				<?php } ?>

				<?php if($site->exists($item->details->bring)) { ?> 
					<div class="panel panel-default rezgo-panel" id="rezgo-panel-thingstobring">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section-link" data-target="#thingstobring">
									<div class="rezgo-section-icon"><i class="fa fa-suitcase fa-lg"></i></div>
									<div class="rezgo-section-text"><span>Things To Bring</span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="thingstobring" class="panel-collapse collapse<?php echo $mclass?>">
						<div class="panel-body rezgo-panel-body"><?php echo $item->details->bring?></div>
						</div>
					</div> 
				<?php } ?>

				<?php if($site->exists($item->details->inclusions)) { ?> 
					<div class="panel panel-default rezgo-panel" id="rezgo-panel-inclusion">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section-link" data-target="#inclusion">
									<div class="rezgo-section-icon"><i class="fa fa-plus-square fa-lg"></i></div>
									<div class="rezgo-section-text"><span>Inclusions</span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="inclusion" class="panel-collapse collapse<?php echo $mclass?>">
						<div class="panel-body rezgo-panel-body"><?php echo $item->details->inclusions?></div>
						</div>
					</div> 
				<?php } ?>

				<?php if($site->exists($item->details->exclusions)) { ?> 
					<div class="panel panel-default rezgo-panel" id="rezgo-panel-exclusion">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section-link" data-target="#exclusion">
									<div class="rezgo-section-icon"><i class="fa fa-minus-square fa-lg"></i></div>
									<div class="rezgo-section-text"><span>Exclusions</span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="exclusion" class="panel-collapse collapse<?php echo $mclass?>">
						<div class="panel-body rezgo-panel-body"><?php echo $item->details->exclusions?></div>
						</div>
					</div> 
				<?php } ?>
        
				<?php if($site->exists($item->details->checkin)) { ?> 
					<div class="panel panel-default rezgo-panel" id="rezgo-panel-checkin">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section-link" data-target="#checkin">
									<div class="rezgo-section-icon"><i class="fa fa-check fa-lg"></i></div>
									<div class="rezgo-section-text"><span>Check-In</span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="checkin" class="panel-collapse collapse<?php echo $mclass?>">
						<div class="panel-body rezgo-panel-body"><?php echo $item->details->checkin?></div>
						</div>
					</div> 
				<?php } ?>

				<?php if($site->exists($item->details->description)) { ?> 
					<div class="panel panel-default rezgo-panel" id="rezgo-panel-addinfo">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section-link" data-target="#addinfo">
									<div class="rezgo-section-icon"><i class="fa fa-info-circle fa-lg"></i></div>
									<div class="rezgo-section-text"><span><?php echo $item->details->description_name?></span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="addinfo" class="panel-collapse collapse<?php echo $mclass?>">
						<div class="panel-body rezgo-panel-body"><?php echo $item->details->description?></div>
						</div>
					</div> 
				<?php } ?>

				<?php if($site->exists($item->details->cancellation)) { ?> 
					<div class="panel panel-default rezgo-panel" id="rezgo-panel-cancellation">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section-link" data-target="#cancellation">
									<div class="rezgo-section-icon"><i class="fa fa-exclamation-circle fa-lg"></i></div>
									<div class="rezgo-section-text"><span>Cancellation Policy</span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="cancellation" class="panel-collapse collapse<?php echo $mclass?>">
						<div class="panel-body rezgo-panel-body"><?php echo $item->details->cancellation?></div>
						</div>
					</div> 
				<?php } ?>

				<?php if (count($item->details->specifications->specification) >= 1) { ?>
					<?php $s=1; ?>

					<?php foreach ($item->details->specifications->specification as $spec) { ?>
						<?php $spec_id = $site->seoEncode($spec->name); ?>

						<div class="panel panel-default rezgo-panel rezgo-spec-panel" id="rezgo-spec-<?php echo $spec_id?>">
							<div class="panel-heading rezgo-section">
								<h4 class="panel-title">
									<a data-toggle="collapse" class="rezgo-section-link" data-target="#spec-<?php echo $s?>">
										<div class="rezgo-section-icon"><i class="fa fa-circle-o fa-lg"></i></div>
										<div class="rezgo-section-text"><span><?php echo $spec->name?></span></div>
										<div class="clearfix"></div>
									</a>
								</h4>
							</div>

							<div id="spec-<?php echo $s?>" class="panel-collapse collapse<?php echo $mclass?>">
								<div class="panel-body rezgo-panel-body"><?php echo $spec->value?></div>
							</div>
						</div>

						<?php $s++; ?>
					<?php } ?>
				<?php } ?>
			
				<?php if($show_reviews == 1 && $item->rating_count >= 1) { ?>
					<div class="panel panel-default rezgo-panel" id="rezgo-panel-reviews">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section-link collapsed" data-target="#reviews" id="reviews-load">
									<div class="rezgo-section-icon"><i class="fa fa-star-o fa-lg"></i></div>
									<div class="rezgo-section-text"><span><?php echo $item->rating_count?> <span class="hidden-xxs">Verified </span><span class="hidden-xs">Guest </span> Review<?php echo ($item->rating_count > 1 ? 's' : '')?> </span>&nbsp;
                    <span id="rezgo-rating-average" class="rezgo-show-reviews" data-toggle="tooltip" data-placement="right" title="Click to view reviews"><?php echo $star_rating_display?></span>
                  </div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="reviews" class="panel-collapse collapse">
							<div class="panel-body rezgo-panel-body" id="reviews-list">&nbsp;<div class="rezgo-wait-div"></div></div>
						</div>
					</div>
				<?php } ?>
			
				<?php if($company->tripadvisor_url != '') { 
					$ta_id = (string) $company->tripadvisor_url;
					$ta_api_url = 'http://api.tripadvisor.com/api/partner/2.0/location/'.$ta_id.'?key='.$ta_key;
					$ta_contents = $site->getFile($ta_api_url);
					$ta_json = json_decode($ta_contents);		
				?>
					<div class="panel panel-default rezgo-panel" id="rezgo-panel-tripadvisor">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section-link collapsed" data-target="#tripadvisor">
									<div class="rezgo-section-icon"><i class="fa fa-tripadvisor fa-lg"></i></div>
									<div class="rezgo-section-text"><span>TripAdvisor<span class="hidden-xxs"> Reviews</span></span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="tripadvisor" class="panel-collapse collapse">
							<div class="panel-body rezgo-panel-body tripadvisor-panel-body">
								<div id="TA_selfserveprop753" class="TA_selfserveprop"></div>
								<script src="https://www.jscache.com/wejs?wtype=selfserveprop&amp;uniq=753&amp;locationId=<?php echo $ta_id?>&amp;lang=en_US&amp;rating=true&amp;nreviews=4&amp;writereviewlink=true&amp;popIdx=true&amp;iswide=true&amp;border=true&amp;display_version=2"></script>
							</div>
						</div>
					</div>

					<style> 
						#CDSWIDSSP, #CDSWIDERR { width:100% !important; } 
						.widSSPData { border:none !important; }
						.widErrCnrs { display:none; }
						.widErrData { margin:1px }
						#CDSWIDERR.widErrBx .widErrData .widErrBranding dt { width: 100%; }
					</style>
				<?php } ?>
        
        <div class="clearfix" id="scroll_reviews">&nbsp;</div>
        
			</div><!-- //	#rezgo-tour-panels -->

			<?php if($site->getTourRelated()) { ?>
				<div class="rezgo-related rezgo-related-details">
					<div class="rezgo-related-label">
						<span>Related products</span>
					</div>
				
					<?php foreach($site->getTourRelated() as $related) { ?>
						<a href="<?php echo $site->base?>/details/<?php echo $related->com?>/<?php echo $site->seoEncode($related->name)?>" class="rezgo-related-link">
							<span><?php echo $related->name?></span>
						</a>
						<br>
					<?php } ?>
				</div>
			<?php } ?>
		</div><!-- // .rezgo-left-wrp -->

		<div class="col-md-4 col-sm-5 col-xs-12 rezgo-right-wrp pull-right">
			<?php if($site->exists($item->lat)) { ?>

				<?php 
				
				if (!$site->exists($item->zoom)) { 
					$map_zoom = 8; 
				} else { 
					$map_zoom = $item->zoom; 
				}
				
				if ($item->map_type == 'ROADMAP') {
					$embed_type = 'roadmap';
				} else {
					$embed_type = 'satellite';
				} 
				
				?>

				<div style="position:relative;">
					<div class="rezgo-map" id="rezgo-tour-map">
          	<iframe width="100%" height="500" frameborder="0" style="border:0;margin-bottom:0;margin-top:-105px;" src="https://www.google.com/maps/embed/v1/place?key=<?php echo GOOGLE_API_KEY?>&maptype=<?php echo $embed_type?>&q=<?php echo $item->lat?>,<?php echo $item->lon?>&center=<?php echo $item->lat?>,<?php echo $item->lon?>&zoom=<?php echo $map_zoom?>"></iframe>
          </div>
					<div class="rezgo-map-labels">
						<?php if($item->location_name != '') { ?>
							<div class="rezgo-map-marker pull-left">
								<i class="fa fa-map-marker"></i>
							</div>
							<span> <?php echo $item->location_name?></span>
							<div class="rezgo-map-hr"></div>
						<?php } ?>
						
						<?php if($item->location_address != '') { ?>
							<div class="rezgo-map-marker pull-left">
								<i class="fa fa-location-arrow"></i>
							</div>
							<span> <?php echo $item->location_address?></span>
							<div class="rezgo-map-hr"></div>
						<?php } else { ?>
							<div class="rezgo-map-marker pull-left">
								<i class="fa fa-location-arrow"></i>
							</div>
							<?php
								echo '
								'.($item->city != '' ? $item->city.', ' : '').'
								'.($item->state != '' ? $item->state.', ' : '').'
								'.($item->country != '' ? ucwords($site->countryName(strtolower($item->country))) : '');
							?>
							<div class="rezgo-map-hr"></div>
						<?php } ?>
					</div>
				</div>
			<?php } ?>

			<?php // include ('sidebar_reviews.php'); ?>
			
			<?php if(count($site->getTourTags()) > 0) { ?>
				<div id="rezgo-tour-tags">
					<label id="rezgo-tour-tags-label">
						<span>Tags:&nbsp;</span>
					</label>
					<?php 
						foreach($site->getTourTags() as $tag) { 
							if ($tag != '') {
								$taglist .= '<a href="'.$site->base.'/tag/'.urlencode($tag).'">'.$tag.'</a>, ';
							}
						}
						$taglist = trim($taglist, ', ');
						echo $taglist;
					?>
				</div>
			<?php } ?>
		</div>
	</div>

	<script type="text/javascript">
		$(document).ready(function(){
			
			// current JS timestamp
			var js_timestamp = Math.round(new Date().getTime()/1000);
			
			// function returns Y-m-d date format
			(function() {
					Date.prototype.toYMD = Date_toYMD;
					function Date_toYMD() {
							var year, month, day;
							year = String(this.getFullYear());
							month = String(this.getMonth() + 1);
							if (month.length == 1) {
									month = "0" + month;
							}
							day = String(this.getDate());
							if (day.length == 1) {
									day = "0" + day;
							}
							return year + "-" + month + "-" + day;
					}
			})();			
			
			// new Date() object for tracking months
			var rezDate = new Date('<?php echo $calendar_start?>-15');			
			
			function addLeadingZero(num) {
				if (num < 10) {
					return "0" + num;
				} else {
					return "" + num;
				}
			}
			
			// only animate month changes if not using Safari
			var isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0;
			
			if (isSafari) {
				monthAnimate = false;
			} else {
				monthAnimate = true;
			}
			
			$('.responsive-calendar').responsiveCalendar({
					time: '<?php echo $calendar_start?>', 
					startFromSunday: <?php echo (($company->start_week == 'mon') ? 'false' : 'true') ?>,
					allRows: false,
					monthChangeAnimation: monthAnimate,
										
					onDayClick: function(events) { 
						
						var this_date, this_class;
						
						this_date = $(this).data('year')+'-'+ addLeadingZero($(this).data('month')) +'-'+ addLeadingZero($(this).data('day'));
						
						this_class = events[this_date].class;
						
						/*var css_class = $(this).parent().attr('class');
						$(this).parent().addClass('select');*/
						
						if (this_class == 'passed') {
							//$('.rezgo-date-selector').html('<p class="lead">This day has passed.</p>').show();
						} else if (this_class == 'cutoff') {
							//$('.rezgo-date-selector').html('<p class="lead">Inside the cut-off.</p>').show();
						} else if (this_class == 'unavailable') {
							//$('.rezgo-date-selector').html('<p class="lead">No tours available on this day.</p>').show();
						} else if (this_class == 'full') {
							//$('.rezgo-date-selector').html('<p class="lead">This day is fully booked.</p>').show();
							
						} else {
														
							$('.rezgo-date-options').html('<div class="rezgo-date-loading"></div>');
							
							if($('.rezgo-date-selector').css('display') == 'none') {
								$('.rezgo-date-selector').slideDown('fast');
							}
						
							$('.rezgo-date-selector').css('opacity', '0.4');
							
							$.ajax({
								url: '<?php echo $site->base?>/calendar_day.php?com=<?php echo $item->com?>&date=' + this_date + '&type=calendar&js_timestamp=' + js_timestamp,
								context: document.body,
								success: function(data) {
									$('.rezgo-date-selector').html(data).css('opacity', '1');
									$('.rezgo-date-options').fadeIn('slow');
									
									var opt_position = $('#rezgo-scrollto-options').offset();
									var opt_scroll = Math.round(opt_position.top);
									
									if ('parentIFrame' in window) {										
										setTimeout(function () {
												parentIFrame.scrollTo(0,opt_scroll);
										}, 100);										
										
									}								
									
								}
							});
							
						}
					
					},
										
					onActiveDayClick: function(events) { 
					
						$('.days .day').each(function () {
								$(this).removeClass('select');
						});
						
						$(this).parent().addClass('select');
					
					},
										
					/*onDayHover: function(events) { 
					
						var this_date, this_class;
						
						this_date = $(this).data('year')+'-'+ addLeadingZero($(this).data('month')) +'-'+ addLeadingZero($(this).data('day'));
						
						this_class = events[this_date].class;
						
						if (this_class == '') {
							$(this).parent().tooltip({'placement' : 'top', 'title': 'click to choose \n an option'});
						}					
						
					},*/
					
					onMonthChange: function(events) { 
					
						// first hide any options below ...
						$('.rezgo-date-selector').slideUp('slow');
						
						rezDate.setMonth(rezDate.getMonth() + 1);
						var rezNewMonth = rezDate.toYMD();
							
						$.ajax({
							url: '<?php echo $site->base?>/calendar_month.php?uid=<?php echo $item->uid?>&com=<?php echo $item->com?>&date=' + rezNewMonth,
							context: document.body,
							success: function(data) {
								$('#rezgo-date-script').html(data); 
							}
						});
					
					},
					
					events: {
						<?php echo $calendar_events?>				
					}
					
					
			});		
			
			<?php if ( ( $calendar_dates > 0 || $single_dates > 10 ) && $cal_day_set === TRUE ) { ?>
			// open the first available day			
			$('.rezgo-date-options').html('<div class="rezgo-date-loading"></div>');
			
			if($('.rezgo-date-selector').css('display') == 'none') {
				$('.rezgo-date-selector').slideDown('fast');
			}
			
			$.ajax({
				url: '<?php echo $site->base?>/calendar_day.php?com=<?php echo $item->com?>&date=<?php echo $open_cal_day?>&id=<?php echo $_REQUEST['option']?>&type=calendar&js_timestamp=' + js_timestamp,
				context: document.body,
				success: function(data) {
					$('.rezgo-date-selector').html(data).css('opacity', '1');
					$('.rezgo-date-options').fadeIn('slow');	
					$('.active [data-day="<?php echo $start_day?>"]').parent().addClass('select');						
				}
			});
			// end open first day
			<?php } ?>
	
			// handle short url popover
			$('*[data-ajaxload]').bind('click',function() {
				var e=$(this);
				e.unbind('click');
				$.get(e.data('ajaxload'),function(d){
						e.popover({
							html : true,
							title: false,
							placement: 'left',
							content: d,
							}).popover('show');
				});
			});
			
			$('body').on('click', function (e) {
					$('[data-toggle="popover"]').each(function () {
							if (!$(this).is(e.target) && e.target.id != 'rezgo-short-url' && $(this).has(e.target).length === 0) {
									$(this).popover('hide');
							}
					});
			});

			// prevent map float left
			$(window).resize(function() {
					var bodyWidth = $(document).width();
					var rightColumnHeight = $('.rezgo-right-wrp').height();
					if(bodyWidth > 760){
							$("#rezgo-details").css({'min-height' : rightColumnHeight + 'px'});
					} else {
							$("#rezgo-details").css({'min-height' : 0});
					}
			});

			// get reviews from panel click
			$('#reviews-load').click(function(e){ 
			
				e.preventDefault();
				
				$.ajax({
					url: '<?php echo $site->base?>/reviews_ajax.php?action=get&view=details&com=<?php echo $item->com?>&type=inventory&limit=5&total=<?php echo $item->rating_count?>',
					context: document.body,
					success: function(data) {
						
						$('#reviews-list').fadeOut(); 
						setTimeout(function () {
								$('#reviews-list').html(data); 
						}, 500);								
						$('#reviews-list').fadeIn('slow'); 
						
					}
				});
				
			});

			$('.rezgo-show-reviews').tooltip();
			
			// get reviews from item heading
			$('#rezgo-item-star-rating').click(function(e){ 
			
				e.preventDefault();
				
				$.ajax({
					url: '<?php echo $site->base?>/reviews_ajax.php?action=get&view=details&com=<?php echo $item->com?>&type=inventory&limit=5&total=<?php echo $item->rating_count?>',
					context: document.body,
					success: function(data) {
						
						$('#reviews-list').fadeOut(); 
						setTimeout(function () {
								$('#reviews-list').html(data); 
						}, 500);								
						$('#reviews-list').fadeIn('slow'); 
						
					}
				});
				
				var reviews_position = $('#scroll_reviews').position();
				var reviews_scroll = Math.round(reviews_position.top);
	
				if('parentIFrame' in window) {
					setTimeout(function () {
						parentIFrame.sendMessage(reviews_scroll);
					}, 100);
				}	    				
			
				$('#reviews').collapse('show'); 
				
			});			
			
			
		});
	</script>
<?php } ?>
</div>