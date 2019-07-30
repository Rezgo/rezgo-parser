<div class="rezgo-ajax-container" id="rezgo-ajax-container-<?php echo $site->requestNum('pg')?>" style="display:none;">
	<?php if($site->requestNum('pg') == 1) { ?>
		<script>
			$(function() {
				$('a.rezgo-breadcrumb-link').tooltip();
			});
		</script>

		<?php if($site->requestStr('search_for') OR $site->requestStr('start_date') OR $site->requestStr('end_date') OR $site->requestStr('tags') OR $site->requestNum('cid')) { ?>
			<p class="rezgo-list-breadcrumb lead wp-hide">
				Results
				<?php if($site->requestStr('search_for')) { ?> for keyword <a class="rezgo-breadcrumb-link" data-toggle="tooltip" data-placement="top" title="Click to clear keywords" href="<?php echo $site->base?>/?start_date=<?php echo $site->requestStr('start_date')?>&end_date=<?php echo $site->requestStr('end_date')?>&tags=<?php echo $site->requestStr('tags')?>" target="_parent">&quot;<?php echo stripslashes($site->requestStr('search_for'))?>&quot;</a><?php } ?>
				<?php if($site->requestStr('tags')) { ?> tagged with <a class="rezgo-breadcrumb-link" data-toggle="tooltip" data-placement="top" title="Click to clear tags" href="<?php echo $site->base?>/?start_date=<?php echo $site->requestStr('start_date')?>&end_date=<?php echo $site->requestStr('end_date')?>&search_in=<?php echo $site->requestStr('search_in')?>&search_for=<?php echo $site->requestStr('search_for')?>" target="_parent">&quot;<?php echo $site->requestStr('tags')?>&quot;</a><?php } ?>
				<?php if($site->requestNum('cid')) { ?> supplied by <a class="rezgo-breadcrumb-link" data-toggle="tooltip" data-placement="top" title="Click to clear supplier"	href="<?php echo $site->base?>/?start_date=<?php echo $site->requestStr('start_date')?>&end_date=<?php echo $site->requestStr('end_date')?>" target="_parent">&quot;<?php echo $site->getCompanyName($site->requestNum('cid'))?>&quot;</a><?php } ?>
				<?php if($site->requestStr('start_date') AND $site->requestStr('end_date')) { ?>
				 between <a class="rezgo-breadcrumb-link" data-toggle="tooltip" data-placement="top" title="Click to clear date search" href="<?php echo $site->base?>/?search_in=<?php echo $site->requestStr('search_in')?>&search_for=<?php echo $site->requestStr('search_for')?>&tags=<?php echo $site->requestStr('tags')?>" target="_parent"><?php echo $site->requestStr('start_date')?> and <?php echo $site->requestStr('end_date')?></a>
				<?php } elseif($site->requestStr('start_date')) { ?>
				 for <a class="rezgo-breadcrumb-link" data-toggle="tooltip" data-placement="top" title="Click to clear date search" href="<?php echo $site->base?>/?search_in=<?php echo $site->requestStr('search_in')?>&search_for=<?php echo $site->requestStr('search_for')?>&tags=<?php echo $site->requestStr('tags')?>" target="_parent"><?php echo $site->requestStr('start_date')?></a>
				<?php } elseif($site->requestStr('end_date')) { ?>
				 for <a class="rezgo-breadcrumb-link" data-toggle="tooltip" data-placement="top" title="Click to clear date search" href="<?php echo $site->base?>/?search_in=<?php echo $site->requestStr('search_in')?>&search_for=<?php echo $site->requestStr('search_for')?>&tags=<?php echo $site->requestStr('tags')?>" target="_parent"><?php echo $site->requestStr('end_date')?></a>
				<?php } ?>
				<a href="<?php echo $site->base?>/" class="rezgo-list-clear pull-right hidden-xs" target="_parent">clear</a>
				<a href="<?php echo $site->base?>/" class="rezgo-list-clear-xs pull-right hidden-sm hidden-md hidden-lg" target="_parent">clear</a>
			</p>
		<?php } else { ?>
			<br />
		<?php } ?>
	<?php } ?>

	<?php if(!$site->getTours()) { ?>
		<p class="lead">Sorry, there were no results for your search.</p>
	<?php } ?>

	<?php
		$tourList = $site->getTours();

		if($tourList[REZGO_RESULTS_PER_PAGE]) {
			$moreButton = 1;
			unset($tourList[REZGO_RESULTS_PER_PAGE]);
		} else { 
			$moreButton = 0; 
		}
	?>

	<?php $available_items = 0; ?>

	<?php foreach($tourList as $item) { ?>
		<?php 
		$site->readItem($item);
		$item_unavailable = ($site->requestStr('start_date') AND count($site->getTourAvailability($item)) == 0) ? 1 : 0;
		if(!$item_unavailable) {
			$available_items++;
		}
		$tour_details_link = $site->base.'/details/'.$item->com.'/'.$site->seoEncode($item->item);
		
		// prepare average star rating
		$star_rating_display = '';
		
		if($item->rating_count >= 1) {
							
			$avg_rating = round(floatval($item->rating) * 2) / 2;	
			
			for($n=1; $n<=5; $n++) {
				if($avg_rating == ($n-0.5)) $star_rating_display .= '<i class="rezgo-star fa fa-star-half-o rezgo-star-half"></i>';
				elseif($avg_rating >= $n) $star_rating_display .= '<i class="rezgo-star fa fa-star rezgo-star-full"></i>';
				else $star_rating_display .= '<i class="rezgo-star fa fa-star-o rezgo-star-empty"></i>';
			}	
			
		}
		
		?>

		<div itemscope itemtype="http://schema.org/Product" class="rezgo-list-item<?php echo (($item_unavailable) ? ' rezgo-inventory-unavailable' : '')?>">
			<div class="row rezgo-tour">
				<div class="col-xs-12">
					<div class="row">
						<?php if($item->media->image[0]) { ?>
							<div class="col-xs-12 col-sm-5 col-md-4 rezgo-list-image pull-left">
								<a href="<?php echo $tour_details_link?>" itemprop="url" target="_parent">
									<img src="<?php echo $item->media->image[0]->path?>" border="0" />
								</a>
								<div class="visible-xs visible-sm rezgo-image-spacer"></div>
							</div>
							<div class="rezgo-tour-list col-xs-12 col-sm-7 col-md-8 pull-left">
						<?php } else { ?>
							<div class="rezgo-tour-list col-xs-12 pull-left">
						<?php } ?>
								<h2 itemprop="name"><a href="<?php echo $tour_details_link?>" itemprop="url" target="_parent"><?php echo $item->item?></a>&nbsp;
                  <span class="rezgo-list-star-rating"><?php echo $star_rating_display?></span>
                </h2>
                <?php if($item->rating_count >= 1) { ?>
                <span class="hidden" itemprop="aggregateRating" style="display:none;">
                  <span class="hidden" itemprop="ratingValue"><?php echo $avg_rating?></span>
                  <span class="hidden" itemprop="reviewCount"><?php echo $item->rating_count?></span>                
                </span>
                <?php } ?>
								<p>
									<?php
										$text = strip_tags($item->details->overview);
										$text = $text." ";
										$text = substr($text, 0, 200);
										$text = substr($text, 0, strrpos($text,' '));
										echo $text;

										if(strlen(strip_tags($item->details->overview)) > 200) {
											echo ' &hellip; <a href="'.$tour_details_link.'" itemprop="url" target="_parent">read more</a>';
										}
									?>
								</p>
							</div>
							<div class="col-sm-12 col-md-4 rezgo-info-left pull-left">
								<?php
									unset($location);
									if($site->exists($item->location_name)) $location['name'] = $item->location_name;
									if($site->exists($item->location_address)) $location['address'] = $item->location_address;
									if($site->exists($item->city)) $location['city'] = $item->city;
									if($site->exists($item->state)) $location['state'] = $item->state;
									if($site->exists($item->country)) $location['country'] = ucwords($site->countryName(strtolower($item->country)));
								?>

								<?php if(count($location) > 0) { ?>
									<p class="rezgo-list-location">
										<strong class="text-info" class="rezgo-location-label">Location</strong>
										<?php
											if($location['address'] != '') {
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
								<?php } ?>

								<?php if($site->exists($item->starting)) { ?>
									<p class="rezgo-list-price">
										<strong class="text-info rezgo-starting-label">Starting from </strong>
										<span class="rezgo-starting-price"><?php echo $site->formatCurrency($item->starting)?></span>
									</p>
								<?php } ?>
							</div>
							<div class="col-xs-12 col-sm-12 col-md-3 pull-right rezgo-more-spacer"></div>
							<div class="col-xs-12 col-sm-12 col-md-3 pull-right rezgo-detail">
								<a href="<?php echo $tour_details_link?>" itemprop="url" class="btn rezgo-btn-detail btn-lg btn-block" target="_parent"><span>More details</span></a>
							</div>
							<div class="clearfix"></div>
						</div>
				</div><!-- // .row -->
			</div><!-- // .rezgo-tour -->
		</div><!-- // .rezgo-list-item -->
	<?php } // end foreach( $tourList as $item ): ?>

	<?php //echo 'available_items: '.$available_items ?>
</div>
|||<?php echo $moreButton?>