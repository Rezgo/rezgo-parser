<div class="rezgo-ajax-container" id="rezgo-ajax-container-<?=$site->requestNum('pg')?>" style="display:none;">
	
	<?  if($site->requestNum('pg') == 1) { ?>
  
    <script>
			$(function() {
				$('a.rezgo-breadcrumb-link').tooltip();
			});
		</script>
		
			
		<? if($site->requestStr('search_for') OR $site->requestStr('start_date') OR $site->requestStr('end_date') OR $site->requestStr('tags') OR $site->requestNum('cid')) { ?>
      <p class="rezgo-list-breadcrumb lead">
				Results
				<? if($site->requestStr('search_for')) { ?> for keyword <a class="rezgo-breadcrumb-link" data-toggle="tooltip" data-placement="top" title="Click to clear keywords" href="<?=$site->base?>/?start_date=<?=$site->requestStr('start_date')?>&end_date=<?=$site->requestStr('end_date')?>&tags=<?=$site->requestStr('tags')?>" target="_top">&quot;<?=stripslashes($site->requestStr('search_for'))?>&quot;</a><? } ?>
				<? if($site->requestStr('tags')) { ?> tagged with <a class="rezgo-breadcrumb-link" data-toggle="tooltip" data-placement="top" title="Click to clear tags" href="<?=$site->base?>/?start_date=<?=$site->requestStr('start_date')?>&end_date=<?=$site->requestStr('end_date')?>&search_in=<?=$site->requestStr('search_in')?>&search_for=<?=$site->requestStr('search_for')?>" target="_top">&quot;<?=$site->requestStr('tags')?>&quot;</a><? } ?>
				<? if($site->requestNum('cid')) { ?> supplied by <a class="rezgo-breadcrumb-link" data-toggle="tooltip" data-placement="top" title="Click to clear supplier"  href="<?=$site->base?>/?start_date=<?=$site->requestStr('start_date')?>&end_date=<?=$site->requestStr('end_date')?>" target="_top">&quot;<?=$site->getCompanyName($site->requestNum('cid'))?>&quot;</a><? } ?>
				<? if($site->requestStr('start_date') AND $site->requestStr('end_date')) { ?>
				 between <a class="rezgo-breadcrumb-link" data-toggle="tooltip" data-placement="top" title="Click to clear date search" href="<?=$site->base?>/?search_in=<?=$site->requestStr('search_in')?>&search_for=<?=$site->requestStr('search_for')?>&tags=<?=$site->requestStr('tags')?>" target="_top"><?=$site->requestStr('start_date')?> and <?=$site->requestStr('end_date')?></a>
				<? } elseif($site->requestStr('start_date')) { ?>
				 for <a class="rezgo-breadcrumb-link" data-toggle="tooltip" data-placement="top" title="Click to clear date search" href="<?=$site->base?>/?search_in=<?=$site->requestStr('search_in')?>&search_for=<?=$site->requestStr('search_for')?>&tags=<?=$site->requestStr('tags')?>" target="_top"><?=$site->requestStr('start_date')?></a>
				<? } elseif($site->requestStr('end_date')) { ?>
				 for <a class="rezgo-breadcrumb-link" data-toggle="tooltip" data-placement="top" title="Click to clear date search" href="<?=$site->base?>/?search_in=<?=$site->requestStr('search_in')?>&search_for=<?=$site->requestStr('search_for')?>&tags=<?=$site->requestStr('tags')?>" target="_top"><?=$site->requestStr('end_date')?></a>
				<? } ?>
				<a href="<?=$site->base?>/" class="rezgo-list-clear pull-right hidden-xs" target="_top">clear</a>
        <a href="<?=$site->base?>/" class="rezgo-list-clear-xs hidden-sm hidden-md hidden-lg" target="_top">clear</a>
      </p>
		<? } else { ?>
			<br />
		<? } ?>
		
		
	<? }  ?>
	
	<? if(!$site->getTours()) { ?>
		<p class="lead">Sorry, there were no results for your search.</p>
	<? } ?>
	
	<?		
		$tourList = $site->getTours();
		if($tourList[REZGO_RESULTS_PER_PAGE]) {
			$moreButton = 1;	
			unset($tourList[REZGO_RESULTS_PER_PAGE]);
		} else { $moreButton = 0; }
	?>
  
  <? $available_items = 0; ?>

	<? foreach( $tourList as $item ): ?>
	
	<? 
	$site->readItem($item) ;
	
	$item_unavailable = ($site->requestStr('start_date') AND count($site->getTourAvailability($item)) == 0) ? 1 : 0;
	
	if (!$item_unavailable) {
		$available_items++;
	}
	
	$tour_details_link = $site->base.'/details/'.$item->com.'/'.$site->seoEncode($item->item);
	?>
  
    <div itemscope itemtype="http://schema.org/Product" class="rezgo-list-item<?=(($item_unavailable) ? ' rezgo-inventory-unavailable' : '')?>">
    
      <div class="row rezgo-tour">
				
				<div class="col-xs-12">
        
					<div class="row">
				
		    		<? if ($item->media->image[0]) { ?>
		        	<div class="col-xs-12 col-sm-5 col-md-4 rezgo-list-image pull-left"> 
		        	  <a href="<?=$tour_details_link?>" itemprop="url" target="_top">
			            <img src="<?=$item->media->image[0]->path?>" border="0" />
			          </a>
			          <div class="visible-xs visible-sm rezgo-image-spacer"></div>
		        	</div>
		        	<div class="rezgo-tour-list col-xs-12 col-sm-7 col-md-8 pull-left">
		        <? } else { ?>
							<div class="rezgo-tour-list col-xs-12 pull-left">
		        <? } ?>
        
	            <h2 itemprop="name"><a href="<?=$tour_details_link?>" itemprop="url" target="_top"><?=$item->item?></a></h2>		
		          <p>
			          <?
									$text = strip_tags($item->details->overview);
									$text = $text." ";
									$text = substr($text, 0, 200);
									$text = substr($text, 0, strrpos($text,' '));
									echo $text;
									
									if(strlen(strip_tags($item->details->overview)) > 200) {
										echo ' &hellip; <a href="'.$tour_details_link.'" itemprop="url" target="_top">read more</a>';
									}
								?>
							</p>					
	          </div>
						
            <div class="col-sm-12 col-md-4 rezgo-info-left pull-left">
            
							<?
                unset($location);
								
                if($site->exists($item->location_name)) $location['name'] = $item->location_name;
                if($site->exists($item->location_address)) $location['address'] = $item->location_address;
                if($site->exists($item->city)) $location['city'] = $item->city;
                if($site->exists($item->state)) $location['state'] = $item->state;
                if($site->exists($item->country)) $location['country'] = ucwords($site->countryName(strtolower($item->country)));
              
                if(count($location) > 0) {
              ?>
              <p class="rezgo-list-location">
              	<strong class="text-info" class="rezgo-location-label">Location</strong>
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
              </p>
              <? } ?>
              
              <? if($site->exists($item->starting)) { ?>
                <p class="rezgo-list-price">
                	<strong class="text-info rezgo-starting-label">Starting from </strong>
									<span class="rezgo-starting-price"><?=$site->formatCurrency($item->starting)?></span>
                </p>
              <? } ?>            
            </div>
	            
            <div class="col-xs-12 col-sm-12 col-md-3 pull-right rezgo-more-spacer"></div>
	            
            <div class="col-xs-12 col-sm-12 col-md-3 pull-right rezgo-detail">
              <a href="<?=$tour_details_link?>" itemprop="url" class="btn rezgo-btn-detail btn-lg btn-block" target="_top">More details</a>
            </div>
	            
            <div class="clearfix"></div>
	            
					</div>
      
				</div>   
    	
    </div>
    	
 	</div><!-- // .rezgo-list-item -->
 	
 	<? endforeach; // end foreach( $tourList as $item ): ?>
  
  <? //echo 'available_items: '.$available_items ?>

</div>
|||<?=$moreButton?>