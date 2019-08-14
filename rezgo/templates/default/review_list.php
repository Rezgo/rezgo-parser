<?php 

$company = $site->getCompanyDetails(); 

if($site->isVendor()) { 
	$items = $site->getTours('t=com&q='.$_REQUEST['com']);
	foreach($items as $item) {
		$site->readItem($item);
	}
	$supplier = $site->getCompanyDetails($item->cid);
	$show_reviews = $supplier->reviews;
} else {
	$show_reviews = $company->reviews;
}

?>

<?php if ($_REQUEST['trans_num'] == 'all') $_REQUEST['com'] = 'all'; ?>

<script type="text/javascript" src="<?php echo $this->path?>/js/jquery.readmore.min.js"></script>

<div class="container-fluid rezgo-container">
  <div class="rezgo-content-row" id="rezgo-list-content">
		<?php if (!$_REQUEST['com'] || $_REQUEST['com'] == '') { 
			$site->sendTo($site->base."/reviews/all")
		?>
    <p class="lead" style="margin-top:60px;">You have not specified an item to review. Please check back later.</p>
    <?php } elseif ($show_reviews != 1) { ?>
    <p class="lead" style="margin-top:60px;">Reviews are not available at this time. Please check back later.</p>
    <?php } else { ?>
    
    <?php
    
      if ($_REQUEST['com'] != 'all') {
      
        $items = $site->getTours('t=com&q='.$_REQUEST['com']);
      
        if (count($items) >= 1) {
          
          foreach($items as $item) {
            $site->readItem($item);
          }
					
					$com_search = (int) $item->com;
            
          // prepare average star rating
          $star_rating_display = '';
          
          if($item->rating_count >= 1) {
                    
            $avg_rating = round(floatval($item->rating) * 2) / 2;	
            
            for($n=1; $n<=5; $n++) {
              if($avg_rating == ($n-0.5)) $star_rating_display .= '<i class="rezgo-star fa fa-star-half-o rezgo-star-half"></i>';
              elseif($avg_rating >= $n) $star_rating_display .= '<i class="rezgo-star fa fa-star rezgo-star-full"></i>';
              else $star_rating_display .= '<i class="rezgo-star fa fa-star-o rezgo-star-empty"></i>';
            }	
            
          ?>
          
          <h1 id="rezgo-review-head"><span>Verified Guest Reviews for <?php echo $item->item?></span></h1>
          <div id="rezgo-item-rating">
            <span>Average rating of <?php echo $avg_rating?>&nbsp;</span>
            <span id="rezgo-item-star-rating"><?php echo $star_rating_display?></span>
          </div>
          
          <div id="rezgo-review-list"></div>
          <div id="rezgo-more-reviews"></div>
            
          <?php } else { ?>
            
          <p class="lead" style="margin-top:60px;">There are no reviews for <strong><?php echo $item->item?></strong> at this time. Please check back later.</p>	
                      
          <?php } // if($item->rating_count)
					
				} // if (count($item)
				
				$review_total = $item->rating_count;
        
      } else {
				
				$com_search = 'all';
				$review_total = 100; // set upper limit
        
			?>
			
        <h1 id="rezgo-review-head"><span>Verified Guest Reviews for <?php echo $company->company_name?></span></h1>
        
        <div id="rezgo-review-list"></div>
        <div id="rezgo-more-reviews"></div>      
			
			<?php } // if ($_REQUEST['com'] != 'all') ?>
      
    <?php } // if (!$_REQUEST[com]) ?>
  </div>
</div>

<script>

  $(document).ready(function() {
		
		var limit = 10;
		
    // load the first set
    $.ajax({
      url: '<?php echo $site->base?>/reviews_ajax.php?action=get&view=list&com=<?php echo $com_search?>&type=inventory&limit=' + limit + '&total=<?php echo $review_total?>',
      context: document.body,
      success: function(data) {				
        $('#rezgo-review-list').html(data); 	
      }
    });	
		
		// load each following set
		$('#rezgo-list-content').on('click', '#rezgo-load-more-reviews', function() { 
		
			var limit_plus = limit + ',10';
			limit = limit + 10;
		
			$.ajax({
				url: '<?php echo $site->base?>/reviews_ajax.php?action=get&view=list&com=<?php echo $com_search?>&type=inventory&limit=' + limit_plus + '&total=<?php echo $review_total?>',
				context: document.body,
				success: function(data) {				
					$('#rezgo-more-reviews-btn').remove(); 
					$('#rezgo-more-reviews').append(data); 	
				}
			});	
		});
    
  });

</script>