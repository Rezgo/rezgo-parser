<?php 
	require('rezgo/include/page_header.php');

	// new instance of RezgoSite
	$site = new RezgoSite();
	
	$company = $site->getCompanyDetails();
	
	$response = '';
	
	// get reviews
	if ($_REQUEST['action'] == 'get') {
		
		$item_reviews = $site->getReview($_REQUEST['com'], $_REQUEST['type'], $_REQUEST['limit']);
		
		if (strpos($_REQUEST['limit'], ',') !== false) {
			$l = explode(',', $_REQUEST['limit']);
			$lower_limit = $l[0];
			$upper_limit = $l[1];
		}	else {
			$lower_limit = 0;
			$upper_limit = $_REQUEST['limit'];
		}
			
		$counted = 0;
		
		$running_count = $lower_limit + 1;
		
		if($item_reviews->total >= 1) {
			
			foreach ($item_reviews->review as $review) {
				
				if ($review->item != '') {
					$item_link = $site->base.'/details/'.$review->com.'/'.$site->seoEncode($review->item);
				}
				
				$response .= '<div class="rezgo-review-container" data-num="'.$running_count.'" data-trans="'.$review->booking.'">';
																
				for($n=1; $n<=5; $n++) {
					$response .= '<i class="rezgo-star fa fa-star'.(($review->rating >= $n) ? ' rezgo-star-full' : '-o rezgo-star-empty').'"></i>';
				}
				
				$review_date = (int) $review->date;
				
				if (strpos($company->time_format, '-') === false) {
					$review_date += (int) $company->time_format * 3600;
				} else {
					$review_date -= (int) $company->time_format * 3600;
				}					
				
				$response .= '
				&nbsp;<strong>'. $review->title .'</strong>
				'. ($_REQUEST['com'] == 'all' ? ' <span class="rezgo-review-item-name">( reviewing <a href="'.$item_link.'">'.$review->item.'</a> )</span>' : '') . '
				<br />
				<span class="rezgo-memo">
				'. ($review->name != '' ? ' by '. $review->name : '') . '
				'. ($review->country != '' ? ' from '. $site->countryName($review->country) : '') . '
				 on '. date((string) $company->date_format, $review_date) . '
				</span><br />
				<div class="rezgo-review-body" style="max-height:'.($_REQUEST['view'] == 'list' ? '320' : '110').'px; overflow:hidden;">'. nl2br($review->body); // 
				
				if ($review->response && !$site->isVendor()) {
					
					$response_date = (int) $review->response->date;
					
					if (strpos($company->time_format, '-') === false) {
						$response_date += (int) $company->time_format * 3600;
					} else {
						$response_date -= (int) $company->time_format * 3600;
					}					
				
					$response .= '
					<div class="clearfix">&nbsp;</div>
					<span class="rezgo-memo">Response by '.$company->company_name.' on '. date((string) $company->date_format, $response_date) .'</span><br />
					<blockquote>'. nl2br($review->response->body) .'</blockquote>'; // 
				
				}
				
				$response .= '
				</div>
				</div>
				<div class="clearfix rezgo-review-break">&nbsp;</div>
				';
				
				$counted++;
				$running_count++;
				
			}
		
		}
		
		if ( $counted > 0 ) {
			
			$response .= '
				<script>
					$(\'.rezgo-review-body\').readmore({
						speed: 500,
						collapsedHeight: '.($_REQUEST['view'] == 'list' ? '180' : '110').',
						moreLink: \'<a href="#" class="btn btn-xs rezgo-review-readmore"><i class="fa fa-chevron-down"></i> Read More</a>\',
						lessLink: \'<a href="#" class="btn btn-xs rezgo-review-readmore"><i class="fa fa-chevron-up"></i> Read Less</a>\'	
					});          
				</script>		
			';
			
			// link to full review list
			if ($_REQUEST['view'] == 'details' && $_REQUEST['total'] > 5) {
				$response .= '
				<span id="rezgo-view-all-reviews">
					<a href="'.$site->base.'/reviews/item/'.$_REQUEST['com'].'" target="_parent" class="btn btn-primary">View '.($_REQUEST['total'] - $_REQUEST['limit']).' more reviews for this item</a>
				</span>
				';
			}		
			
			// show the next page 
			if ($_REQUEST['view'] == 'list' && ($counted == $upper_limit) && $running_count <= $_REQUEST['total']) {
				$response .= '
				<div id="rezgo-more-reviews-btn">
					<span class="rezgo-load-more-wrap">
						<button class="btn btn-block rezgo-review-load-more" id="rezgo-load-more-reviews"><i class="fa fa-chevron-down"></i> Load More Reviews</button>
					</span>
				</div>
				';
			}
			
		} else {
			
			if ($lower_limit == 0) {
				$response .= '<p class="lead">There are no reviews to show at this time. Please check back later.</p>';		
			} else {
				$response .= '<p class="rezgo-review-container">There are no more reviews available. Please check back later.</p>';	
			}
			
		} // end if (counted)
		
	}

	if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		// ajax response if we requested this page correctly
		echo $response;		
	} else {
		// if, for some reason, the ajax form submit failed, then we want to handle the user anyway
		die ('Something went wrong getting reviews.');
	}
	
?>