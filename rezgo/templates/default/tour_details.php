<? 
$map_key = 'AIzaSyCqFNdI5b319sgzE3WH3Bw97fBl4kRVzWw'; 
$ta_key = '2E2B919141464E31B384DE1026A2DE7B'; 
?>
<!-- fonts -->
<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Lato:300,400,700">
<!-- calendar.css -->
<link href="<?=$this->path?>/css/responsive-calendar.css" rel="stylesheet">
<link href="<?=$this->path?>/css/responsive-calendar.rezgo.css" rel="stylesheet">

<script type="text/javascript" src="<?=$this->path?>/js/responsive-calendar.min.js"></script>  
	
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDkCWu6MoROFlsRGoqFj-AXPEApsVjyTiA&libraries=places"></script>		
	
<div class="container-fluid rezgo-container">

<?
	$items = $site->getTours('t=com&q='.$_REQUEST['com'].'&f[uid]='.$_REQUEST['option'].'&d='.$_REQUEST['date']);
	
	if(!$items) { ?>
  
  <div class="jumbotron"> 
    <h3><i class="fa fa-exclamation-triangle"></i> Item not found</h3>
    <p class="lead">Sorry, the item you are looking for is not available or has no available options.</p>
    <p><a class="btn btn-lg btn-info" href="<?=$site->base?>/" role="button">Return to home</a></p>
  </div>
  
<? } else { ?>
		
	<? 
	
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
	
	$calendar_options = array();
	$single_dates = 0;
	$non_single_dates = 0;
	
	$item_count = 1;
	
	foreach( $items as $item ) { 
		
		$site->readItem($item);
		
		// check if single dates or calendar
		$option_start_date = (int) $item->start_date;
		if (recursive_array_search($option_start_date, $calendar_options) === FALSE) {
			$calendar_options[(int) $item->uid]['start_date'] = $option_start_date;
		}
		
		if ((string) $item->date_selection == 'single') { $single_dates++; } else { $non_single_dates++; }
		
		// prepare media gallery
		if ($item_count == 1) { // we only need to grab it for the first item
			
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
	usort($calendar_options, 'date_sort'); 
	
	// setup calendar start days
	$company = $site->getCompanyDetails();
	// set defaults for start of availability
	$start_day = date('j', strtotime('+'.$item_cutoff.' days '.$company->time_format.' hours'));
	$open_cal_day = date('Y-m-d', strtotime('+'.$item_cutoff.' days '.$company->time_format.' hours'));
	
	// get the available dates
	$site->getCalendar($item->uid, $_REQUEST['date']); 
	
	$cal_day_set = FALSE;
	
	foreach ( $site->getCalendarDays() as $day ) {
		
		if ($day->cond == 'a') { $class = ''; } // available
		elseif ($day->cond == 'p') { $class = 'passed'; }
		elseif ($day->cond == 'f') { $class = 'full'; }
		elseif ($day->cond == 'i' || $day->cond == 'u') { $class = 'unavailable'; }
		elseif ($day->cond == 'c') { $class = 'cutoff'; }
		
		if ($day->date) { // && (int)$day->lead != 1
			$calendar_events .= '"'.date('Y-m-d', $day->date).'":{"class": "'.$class.'"},'."\n"; 
		}
		// ,"dayEvents": [{"name": "Important meeting", "hour": "17:30"}]
		
		if ($_REQUEST['date']) {
			
			$request_date = strtotime($_REQUEST['date']);
			$calendar_start = date('Y-m', $request_date);
			$start_day =  date('j', $request_date);
			$open_cal_day =  date('Y-m-d', $request_date);
			$cal_day_set = TRUE;
			
		} else {
			
			if ($day->date) {
				$calendar_start = date('Y-m', (int) $day->date);
			}
			// redefine start days
			if ($day->cond == 'a' && !$cal_day_set) { 
				$start_day =  date('j', $day->date);
				$open_cal_day =  date('Y-m-d', $day->date);
				$cal_day_set = TRUE;
			} 
		
		}
		
	}
		
	$calendar_events = trim($calendar_events, ','."\n");
  
  ?>
  
      	
  <div class="row" itemscope itemtype="http://schema.org/Product">
	  
	  <div class="col-md-8 col-sm-7 col-xs-12">
	    <h1 itemprop="name" id="rezgo-item-name"><span><?=utf8_decode($item->item)?></span></h1>
	  </div>
  
	  <div class="col-md-4 col-sm-5 col-xs-12">
	    <div class="row">
	      <div class="col-xs-5 col-sm-6">&nbsp;
					<?
					if($site->getCartState()) {
	          $cart = $site->getCart();
	          if($cart) {
							echo '<a class="rezgo-cart-link badge pull-left" href="'.$site->base.'/order"><i class="fa fa-shopping-cart"></i>&nbsp;<span class="hidden-xs">'.count($cart).' item'.((count($cart) > 1) ? 's' : '').' in </span>order<span class="visible-xs-inline"> ('.count($cart).')</span></a>';
	        	}
					}
					?>      
	      </div>
	      
	      <div class="col-xs-7 col-sm-6">
		      <div class="rezgo-social-box">
			      <span id="rezgo-social-links">
              <a href="javascript:void(0);" title="Pin this on Pinterest" id="social_pinterest" onclick="window.open('http://www.pinterest.com/pin/create/button/?url=<?=urlencode('http://'.$_SERVER['HTTP_HOST'].$site->base.'/details/'.$item->com.'/'.$site->seoEncode($item->item))?>&media=<?=$pinterest_img_path?>&description=<?=urlencode($item->item).'%0A'.urlencode(strip_tags($item->details->overview))?>','pinterest','location=0,status=0,scrollbars=1,width=750,height=320');"><i class="fa fa-pinterest-square" id="pinterest_icon">&nbsp;</i></a>            
              <a href="javascript:void(0);" title="Share this on Twitter" id="social_twitter" onclick="window.open('http://twitter.com/share?text=<?=urlencode('I found this great thing to do! "'.$item->item.'"')?>&url=' + escape(top.location.href)<? if($site->exists($site->getTwitterName())) { ?> + '&via=<?=$site->getTwitterName()?>'<? } ?>,'tweet','location=1,status=1,scrollbars=1,width=500,height=350');"><i class="fa fa-twitter-square" id="social_twitter_icon">&nbsp;</i></a>
              <a href="javascript:void(0);" title="Share this on Facebook" id="social_facebook" onclick="window.open('http://www.facebook.com/sharer.php?u=' + escape(top.location.href) + '&t=<?=urlencode($item->item)?>','facebook','location=1,status=1,scrollbars=1,width=600,height=400');"><i class="fa fa-facebook-square" id="social_facebook_icon">&nbsp;</i></a>
              <a href="javascript:void(0);" id="social_url" data-toggle="popover" data-ajaxload="<?=$site->base?>/shorturl_ajax.php?url=<?= urlencode('http://'.$_SERVER['HTTP_HOST'].$site->base.'/details/'.$item->com.'/'.$site->seoEncode($item->item)) ?>"><i class="fa fa-share-alt-square" id="social_url_icon">&nbsp;</i></a>
			      </span>		      
		      </div>
	      </div>
	      
	    </div>
	  </div><!-- //  promocode/cart -->
		
  </div> <!-- //  row title / mini cart -->
  
  <div class="row">
  
  	<div class="col-md-8 col-sm-7 col-xs-12 rezgo-left-wrp">
	
			<?php if($media_count > 0) { ?>
	    
	    <div id="rezgo-img-carousel" class="carousel slide" data-ride="carousel">    
	      <ol class="carousel-indicators">
	      	<?=$indicators?>
	      </ol>
	      <div class="carousel-inner">
	      	<?=$media_items?>
	      </div>
	      <a class="left carousel-control" data-target="#rezgo-img-carousel" data-slide="prev">
	        <span class="glyphicon glyphicon-chevron-left"></span>
	      </a>
	      <a class="right carousel-control" data-target="#rezgo-img-carousel" data-slide="next">
	        <span class="glyphicon glyphicon-chevron-right"></span>
	      </a>
	    </div><!-- // #rezgo-img-carousel -->
	    
	    <? } ?>
	   
  	</div>
  	
  	<div class="col-md-4 col-sm-5 col-xs-12 rezgo-right-wrp pull-right">
      
		  <? if ( $non_single_dates > 0 || $single_dates > 10 ) { ?>
		  
		  <div class="hidden visible-xs">&nbsp;</div>
		  
		  <div class="rezgo-calendar-wrp">
		    <div class="rezgo-calendar-header">
		      <span>Choose a Date</span>
		    </div>
		    <div class="rezgo-calendar">
		      <div class="responsive-calendar" id="rezgo-calendar">
		        <div class="controls">
		          <a class="pull-left" data-go="prev"><div class="glyphicon glyphicon-chevron-left"></div></a>
		          <h4><span data-head-year></span> <span data-head-month></span></h4>
		          <a class="pull-right" data-go="next"><div class="glyphicon glyphicon-chevron-right"></div></a>
		        </div>
		        <? if ($company->start_week == 'mon') { ?>
            <div class="day-headers">
		          <div class="day header">Mon</div>
		          <div class="day header">Tue</div>
		          <div class="day header">Wed</div>
		          <div class="day header">Thu</div>
		          <div class="day header">Fri</div>
		          <div class="day header">Sat</div>
		          <div class="day header">Sun</div>
		        </div>
            <? } else { ?>
		        <div class="day-headers">
		          <div class="day header">Sun</div>
		          <div class="day header">Mon</div>
		          <div class="day header">Tue</div>
		          <div class="day header">Wed</div>
		          <div class="day header">Thu</div>
		          <div class="day header">Fri</div>
		          <div class="day header">Sat</div>
		        </div>
            <? } ?>
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
        
				<? if(!$site->isVendor()) { ?>
        <div id="rezgo-details-promo"><!-- hidden by default -->
          <div class="rezgo-form-group-short">
          <? if (!$_SESSION['rezgo_promo']) { ?>
            <form class="form-inline" id="rezgo-promo-form" role="form" onsubmit="top.location.href = '<?=$_SERVER['HTTP_REFERER']?>/?promo=' + $('#rezgo-promo-code').val(); return false;">
              <label for="rezgo-promo-code"><i class="fa fa-tags"></i>&nbsp;<span class="rezgo-promo-label"><span>Promo code</span></span></label>&nbsp;
              <div class="input-group">
              <input type="text" class="form-control" id="rezgo-promo-code" name="promo" placeholder="Enter Promo Code" value="<?=($_SESSION['rezgo_promo'] ? $_SESSION['rezgo_promo'] : '')?>" />
              <span class="input-group-btn"><button class="btn rezgo-btn-default" type="submit">Apply</button></span>
              </div>
            </form>
          <? } else { ?>
            <label for="rezgo-promo-code"><i class="fa fa-tags"></i>&nbsp;<span class="rezgo-promo-label"><span>Promo code</span></span></label>&nbsp;
            <span id="rezgo-promo-value"><?=$_SESSION['rezgo_promo']?></span>&nbsp;
            <a id="rezgo-promo-clear" class="btn rezgo-btn-default btn-sm" href="<?=$_SERVER['HTTP_REFERER']?>/?promo=" target="_top">clear</a>
          <? } ?>
          </div>
        </div>
        <? } ?>
        
		  </div>
		  <!-- //  calendar -->
      
		  <? } else { ?>
      
		  <div class="rezgo-calendar-wrp">        
					<? 
          $opt = 1; // pass an option counter to calendar day
          foreach ($calendar_options as $option) { 
          
          ?>
          <div class="rezgo-calendar-single" id="rezgo-calendar-single-<?=$opt?>" style="display:none;">
          <div class="rezgo-calendar-single-head"><span class="rezgo-calendar-avail"><span>Availability&nbsp;for:</span></span> <strong><?=date((string) $company->date_format, $option['start_date'])?></strong></div>
            <div class="rezgo-date-selector" id="rezgo-single-date-<?=$opt?>"></div>
            
            <script type="text/javascript">
              
              $(document).ready(function () {
                
                $.ajax({
                  url: '<?=$site->base?>/calendar_day.php?com=<?=$item->com?>&date=<?=date('Y-m-d', $option['start_date'])?>&option_num=<?=$opt?>',
                  context: document.body,
                  success: function(data) {
										if (data.indexOf('rezgo-option-hide') == -1) {
											$('#rezgo-single-date-<?=$opt?>').html(data).slideDown('fast');
											$('#rezgo-calendar-single-<?=$opt?>').fadeIn('fast');
										}
                  }
                });
                
              });
              
            </script> 
          </div>
          <?
            $opt++;
           } // end foreach ($calendar_options) 
          ?> 
          
        <div id="rezgo-calendar-memo"></div>
          
				<? if(!$site->isVendor()) { ?>
        <div class="clear">&nbsp;</div>
        <div id="rezgo-details-promo"><!-- hidden by default -->
          <div class="rezgo-form-group-short">
          <? if (!$_SESSION['rezgo_promo']) { ?>
            <form class="form-inline" id="rezgo-promo-form" role="form" onsubmit="top.location.href = '<?=$_SERVER['HTTP_REFERER']?>/?promo=' + $('#rezgo-promo-code').val(); return false;">
              <label for="rezgo-promo-code"><i class="fa fa-tags"></i>&nbsp;<span class="rezgo-promo-label"><span>Promo code</span></span></label>&nbsp;
              <div class="input-group">
              <input type="text" class="form-control" id="rezgo-promo-code" name="promo" placeholder="Enter Promo Code" value="<?=($_SESSION['rezgo_promo'] ? $_SESSION['rezgo_promo'] : '')?>" />
              <span class="input-group-btn"><button class="btn rezgo-btn-default" type="submit">Apply</button></span>
              </div>
            </form>
          <? } else { ?>
            <label for="rezgo-promo-code"><i class="fa fa-tags"></i>&nbsp;<span class="rezgo-promo-label"><span>Promo code</span></span></label>&nbsp;
            <span id="rezgo-promo-value"><?=$_SESSION['rezgo_promo']?></span>&nbsp;
            <a id="rezgo-promo-clear" class="btn rezgo-btn-default btn-sm" href="<?=$_SERVER['HTTP_REFERER']?>/?promo=" target="_top">clear</a>
          <? } ?>
          </div>
        </div>
        <? } ?>
		      
		  </div><!-- // .rezgo-calendar-wrp -->
		  <!-- // single day booking -->
		  <? } // end if ( $non_single_dates > 0 ) ?>
		  
  	</div>
  	
		<div class="col-md-8 col-sm-7 col-xs-12 rezgo-left-wrp pull-left" id="rezgo-details">
		  
      <? if($site->exists($item->details->highlights)) { ?> 
        <div class="rezgo-tour-highlights"><?=$item->details->highlights?></div>
      <? } ?>
      
		  <div class="rezgo-tour-description">
		  
		  	<? if($site->exists($item->details->overview)) { ?> 
		    	<div class="lead" id="rezgo-tour-overview"><?=$item->details->overview?></div>
		    <? } ?>  
		      
	      <?
					unset($location);
					
					if($site->exists($item->location_name)) $location['name'] = $item->location_name;
					if($site->exists($item->location_address)) $location['address'] = $item->location_address;
					if($site->exists($item->city)) $location['city'] = $item->city;
					if($site->exists($item->state)) $location['state'] = $item->state;
					if($site->exists($item->country)) $location['country'] = ucwords($site->countryName(strtolower($item->country)));
			  ?>
			  
			  <? if (count($location) > 0) { ?>
				  <div id="rezgo-tour-location">
				    <label id="rezgo-tour-location-label"><span>Location:&nbsp;</span></label>
						<?
              if ($location['address'] != '') {
                echo '
                '.($location['name'] != '' ? '<span class="rezgo-location-name">'.$location['name'].' - </span>' : '').'
                <span class="rezgo-location-address">'.$location['address'].'</span>';
              } else {
                echo '
                '.($location['city'] != '' ? '<span class="rezgo-location-city">'.$location['city'].', </span>' : '').'
                '.($location['state'] != '' ? '<span class="rezgo-location-state">'.$location['state'].', </span>' : '').'
                '.($location['country'] != '' ? '<span class="rezgo-location-country">'.$location['country'].'</span>' : '');
                //echo implode(', ', $location);
              }
            ?>
				  </div>
			  <? } ?>
			          
				<? if($site->isVendor()) { ?>
          <div id="rezgo-provided-by">
            <label id="rezgo-provided-by-label"><span>Provided by:&nbsp;</span></label>
            <a href="<?=$site->base?>/supplier/<?=$item->cid?>"><?=$site->getCompanyName($item->cid)?></a>
          </div>
        <? } ?>
            
			</div>
		  
		  <?
				if(!$site->config('REZGO_MOBILE_XML')) {
					// add 'in' class to expand collapsible for non-mobile devices
					$mclass = ' in';
				}	
			?>
	  
	    <div class="panel-group rezgo-desc-panel" id="rezgo-tour-panels">      
	  	
				<? if($site->exists($item->details->itinerary)) { ?> 
					<div class="panel panel-default rezgo-panel" id="rezgo-panel-itinerary">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section" data-target="#itinerary">
									<div class="rezgo-section-icon"><i class="fa fa-bars fa-lg"></i></div>
									<div class="rezgo-section-text"><span>Itinerary</span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="itinerary" class="panel-collapse collapse<?=$mclass?>">
						<div class="panel-body rezgo-panel-body"><?=$item->details->itinerary?></div>
						</div>
					</div>
				<? } ?>
				
				<? if($site->exists($item->details->pick_up)) { ?> 
		    	<div class="panel panel-default rezgo-panel" id="rezgo-panel-pickup">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section" data-target="#pickup">
									<div class="rezgo-section-icon"><i class="fa fa-map-marker fa-lg"></i></div>
									<div class="rezgo-section-text"><span>Pickup</span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="pickup" class="panel-collapse collapse<?=$mclass?>">
						<div class="panel-body rezgo-panel-body"><?=$item->details->pick_up?></div>
						</div>
					</div> 
				<? } ?>
				
				<? if($site->exists($item->details->drop_off)) { ?> 
		    	<div class="panel panel-default rezgo-panel" id="rezgo-panel-dropoff">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section" data-target="#dropoff">
									<div class="rezgo-section-icon"><i class="fa fa-location-arrow fa-lg"></i></div>
									<div class="rezgo-section-text"><span>Drop Off</span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="dropoff" class="panel-collapse collapse<?=$mclass?>">
						<div class="panel-body rezgo-panel-body"><?=$item->details->drop_off?></div>
						</div>
					</div> 
				<? } ?>
				
				<? if($site->exists($item->details->bring)) { ?> 
		    	<div class="panel panel-default rezgo-panel" id="rezgo-panel-thingstobring">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section" data-target="#thingstobring">
									<div class="rezgo-section-icon"><i class="fa fa-suitcase fa-lg"></i></div>
									<div class="rezgo-section-text"><span>Things To Bring</span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="thingstobring" class="panel-collapse collapse<?=$mclass?>">
						<div class="panel-body rezgo-panel-body"><?=$item->details->bring?></div>
						</div>
					</div> 
				<? } ?>
				
				<? if($site->exists($item->details->inclusions)) { ?> 
		    	<div class="panel panel-default rezgo-panel" id="rezgo-panel-inclusion">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section" data-target="#inclusion">
									<div class="rezgo-section-icon"><i class="fa fa-plus-square fa-lg"></i></div>
									<div class="rezgo-section-text"><span>Inclusions</span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="inclusion" class="panel-collapse collapse<?=$mclass?>">
						<div class="panel-body rezgo-panel-body"><?=$item->details->inclusions?></div>
						</div>
					</div> 
				<? } ?>
				
				<? if($site->exists($item->details->exclusions)) { ?> 
		    	<div class="panel panel-default rezgo-panel" id="rezgo-panel-exclusion">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section" data-target="#exclusion">
									<div class="rezgo-section-icon"><i class="fa fa-minus-square fa-lg"></i></div>
									<div class="rezgo-section-text"><span>Exclusions</span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="exclusion" class="panel-collapse collapse<?=$mclass?>">
						<div class="panel-body rezgo-panel-body"><?=$item->details->exclusions?></div>
						</div>
					</div> 
				<? } ?>
				
				<? if($site->exists($item->details->description)) { ?> 
		    	<div class="panel panel-default rezgo-panel" id="rezgo-panel-addinfo">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section" data-target="#addinfo">
									<div class="rezgo-section-icon"><i class="fa fa-info-circle fa-lg"></i></div>
									<div class="rezgo-section-text"><span><?=$item->details->description_name?></span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="addinfo" class="panel-collapse collapse<?=$mclass?>">
						<div class="panel-body rezgo-panel-body"><?=$item->details->description?></div>
						</div>
					</div> 
				<? } ?>
				  
				<? if($site->exists($item->details->cancellation)) { ?> 
		    	<div class="panel panel-default rezgo-panel" id="rezgo-panel-cancellation">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section" data-target="#cancellation">
									<div class="rezgo-section-icon"><i class="fa fa-exclamation-circle fa-lg"></i></div>
									<div class="rezgo-section-text"><span>Cancellation Policy</span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="cancellation" class="panel-collapse collapse<?=$mclass?>">
						<div class="panel-body rezgo-panel-body"><?=$item->details->cancellation?></div>
						</div>
					</div> 
				<? } ?>
        
        <?
				
				if (count($item->details->specifications->specification) >= 1) {
					$s=1;
					foreach ($item->details->specifications->specification as $spec) { 
					
					$spec_id = $site->seoEncode($spec->name);
				?>
            
		    	<div class="panel panel-default rezgo-panel rezgo-spec-panel" id="rezgo-spec-<?=$spec_id?>">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section" data-target="#spec-<?=$s?>">
									<div class="rezgo-section-icon"><i class="fa fa-circle-o fa-lg"></i></div>
									<div class="rezgo-section-text"><span><?=$spec->name?></span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="spec-<?=$s?>" class="panel-collapse collapse<?=$mclass?>">
						<div class="panel-body rezgo-panel-body"><?=$spec->value?></div>
						</div>
					</div>             
						
				<?	
						$s++;
					} // end specifications
				} // end if (count)
				?>
        
      
        <?php
          // $ta_key
          // ta-ratings/s1.0-MCID-0.gif
          // tripadvisor.png
          // http://api.tripadvisor.com/api/partner/2.0/location/258705?key=$ta_key
        ?>  
	  	
				<? if($company->tripadvisor_url != '') { 
				
							$ta_id = (string) $company->tripadvisor_url;
							
							$ta_api_url = 'http://api.tripadvisor.com/api/partner/2.0/location/'.$ta_id.'?key='.$ta_key;
							
							$ta_contents = $site->getFile($ta_api_url);
							
							$ta_json = json_decode($ta_contents);
							
							//echo '<pre>'.print_r($ta_json, 1).'</pre>';
				
				?> 
					<div class="panel panel-default rezgo-panel" id="rezgo-panel-reviews">
						<div class="panel-heading rezgo-section">
							<h4 class="panel-title">
								<a data-toggle="collapse" class="rezgo-section" data-target="#reviews">
									<div class="rezgo-section-icon"><i class="fa fa-tripadvisor fa-lg"></i></div>
									<div class="rezgo-section-text"><span>Reviews</span></div>
									<div class="clearfix"></div>
								</a>
							</h4>
						</div>
						<div id="reviews" class="panel-collapse collapse<?=$mclass?>">
						<div class="panel-body rezgo-panel-body review-panel-body">
              <div id="TA_selfserveprop753" class="TA_selfserveprop"></div>
              <script src="http://www.jscache.com/wejs?wtype=selfserveprop&amp;uniq=753&amp;locationId=<?=$ta_id?>&amp;lang=en_US&amp;rating=true&amp;nreviews=4&amp;writereviewlink=true&amp;popIdx=true&amp;iswide=true&amp;border=true&amp;display_version=2"></script>
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
          
				<? } ?>
			
	    </div><!-- //  #rezgo-tour-panels -->
      
          
			<? if($site->getTourRelated()) { ?>
        <div class="rezgo-related rezgo-related-details">
        	
          <div class="rezgo-related-label"><span>Related products</span></div>
        
        <? foreach($site->getTourRelated() as $related) { ?>
          
          <a href="<?=$site->base?>/details/<?=$related->com?>/<?=$site->seoEncode($related->name)?>" class="rezgo-related-link"><?=$related->name?></a><br>
          
        <? } ?>
        
        </div>
      <? } ?>
      
	  
    </div><!-- // .rezgo-left-wrp -->
        
		<div class="col-md-4 col-sm-5 col-xs-12 rezgo-right-wrp pull-right">
		
			<? if($site->exists($item->lat)) { ?>
      
				<? if (!$site->exists($item->zoom)) { $map_zoom = 8; } else { $map_zoom = $item->zoom; } ?>        
        
				<script>
          var tour_markers = [];
          var tour_map;
          
          var tour_map_lat = <?=$item->lat?>;
          var tour_map_lon = <?=$item->lon?>;
          var tour_map_zoom = <?=$map_zoom?>;
          
          var tour_map_center=new google.maps.LatLng(tour_map_lat, tour_map_lon);
					
          function tour_map_init() {
			
						var tour_map_styles =[{
							featureType: "poi",
							elementType: "labels",
							stylers: [{ 
								visibility: "off" 
							}]
						}];
						
            var tour_map_prop = {
              center: tour_map_center,
              zoom: tour_map_zoom,
              scrollwheel: false,
							<? if ($site->config('REZGO_MOBILE_XML')) { ?>
							draggable: false,
							<? } ?>
              mapTypeControl: false,
							streetViewControl: false,
							zoomControl: true,
							zoomControlOptions: {
									position: google.maps.ControlPosition.LEFT_BOTTOM
							},
							styles: tour_map_styles, 
              mapTypeId: google.maps.MapTypeId.<?=$item->map_type?>
            };
						            
            tour_map = new google.maps.Map(document.getElementById("rezgo-tour-map"), tour_map_prop);
            
            google.maps.event.addListener(tour_map, 'zoom_changed', function() {
              document.getElementById("zoom").value = tour_map.getZoom();
            });
            
						var tour_map_marker = new google.maps.Marker({
							position: new google.maps.LatLng(<?=$item->lat?>, <?=$item->lon?>),
							map: tour_map
						});
						
						tour_markers.push(tour_map_marker);
						              
          }
           
          google.maps.event.addDomListener(window, 'load', tour_map_init);
					          
        </script>    
      
      	<div style="position:relative;">	
          <div class="rezgo-map" id="rezgo-tour-map"><!-- tour map here --></div>	
            
          <div class="rezgo-map-labels">
            <? if($item->location_name != '') { ?>
              <div class="rezgo-map-marker pull-left"><i class="fa fa-map-marker"></i></div> <?=$item->location_name?>
              <div class="rezgo-map-hr"></div>
            <? } ?>
            
            <? if($item->location_address != '') { ?>
              <div class="rezgo-map-marker pull-left"><i class="fa fa-location-arrow"></i></div> <?=$item->location_address?>
              <div class="rezgo-map-hr"></div>
            <? } else { ?>
              <div class="rezgo-map-marker pull-left"><i class="fa fa-location-arrow"></i></div> 
              <?php
								echo '
								'.($item->city != '' ? $item->city.', ' : '').'
								'.($item->state != '' ? $item->state.', ' : '').'
								'.($item->country != '' ? ucwords($site->countryName(strtolower($item->country))) : '');
							?>
              <div class="rezgo-map-hr"></div>
            <? } ?>
            
          </div>
        
        </div>
        
			<? } ?>
		  
		  <? // include ('sidebar_reviews.php'); ?>
      
			<? if(count($site->getTourTags()) > 0) { ?>  
        <div id="rezgo-tour-tags">
          <label id="rezgo-tour-tags-label"><span>Tags:&nbsp;</span></label>
          <? 
            foreach($site->getTourTags() as $tag) { 
              if ($tag != '') {
                $taglist .= '<a href="'.$site->base.'/tag/'.urlencode($tag).'">'.$tag.'</a>, ';
              }
            }
            $taglist = trim($taglist, ', ');
            echo $taglist;
          ?>
        </div>
      <? } ?>
		    
		</div><!-- // .rezgo-right-wrp 
    <div class="clearfix">&nbsp;</div>-->
		
  </div><!-- // main row -->
	  
	<script type="text/javascript">
	
    $(document).ready(function () {
			
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
			var rezDate = new Date('<?=$calendar_start?>-15');			
      
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
          time: '<?=$calendar_start?>', 
          startFromSunday: <?=(($company->start_week == 'mon') ? 'false' : 'true') ?>,
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
								url: '<?=$site->base?>/calendar_day.php?com=<?=$item->com?>&date=' + this_date,
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
							url: '<?=$site->base?>/calendar_month.php?uid=<?=$item->uid?>&com=<?=$item->com?>&date=' + rezNewMonth,
							context: document.body,
							success: function(data) {
								$('#rezgo-date-script').html(data); 
							}
						});
          
          },
					
          events: {
						<?=$calendar_events?>				
					}
					
          
      });		
			
		  <? if ( ($non_single_dates > 0 || $single_dates > 10) && $cal_day_set === TRUE ) { ?>
			// open the first available day			
			$('.rezgo-date-options').html('<div class="rezgo-date-loading"></div>');
			
			if($('.rezgo-date-selector').css('display') == 'none') {
				$('.rezgo-date-selector').slideDown('fast');
			}
			
			$.ajax({
				url: '<?=$site->base?>/calendar_day.php?com=<?=$item->com?>&date=<?=$open_cal_day?>&id=<?=$_REQUEST['option']?>',
				context: document.body,
				success: function(data) {
					$('.rezgo-date-selector').html(data).css('opacity', '1');
					$('.rezgo-date-options').fadeIn('slow');	
					$('.active [data-day="<?=$start_day?>"]').parent().addClass('select');						
				}
			});
			// end open first day
			<? } ?>
	
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
      
    });
		
  </script>

<? } ?>
</div><!-- // .container -->
<!--<pre>
<?
echo print_r($_COOKIE,1);
?>
</pre>-->