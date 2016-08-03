<?

	$company = $site->getCompanyDetails();

	if ($_REQUEST['option_num']) {
		$option_num = $_REQUEST['option_num'];
		$availability_title = '';	
	} else {
		$option_num = 1;	
		$availability_title = '<div class="rezgo-date-options" style="display:none;"><span class="rezgo-calendar-avail"><span>Availability&nbsp;for:</span></span> <strong>'.date((string) $company->date_format, strtotime($_REQUEST['date'])).'</strong></div>';	
	}
	
	$options = $site->getTours('t=com&q='.$_REQUEST['com'].'&d='.$_REQUEST['date']);
	
	if ($options) {
			
?>

  <?=$availability_title?>
  <div class="panel-group" id="rezgo-select-option-<?=$option_num?>"> 
  
  <? if (count($options) != 1) { //  && $option_num != 1?>
  <span class="rezgo-choose-options">Choose one of the options below <i class="fa fa-angle-double-down"></i></span>
  <? } ?>
  
  <? 
    //$option_total = count($site->getTours('t=com&q='.$_REQUEST['com'].'&d='.$_REQUEST['date']));
		
		$sub_option = 'a';
    
    foreach($options as $option) {
	    
	    $site->readItem($option);
	    
  ?>
  
  <div class="panel panel-default">
		<script>
      var fields_<?=$option_num.'_'.$sub_option?> = new Array();
			var required_num_<?=$option_num.'_'.$sub_option?> = 0;
				
			function isInt(n) {
				 return n % 1 === 0;
			} 
      
      // validate form data
      function check_<?=$option_num.'_'.$sub_option?>() {
        var err;
        var count_<?=$option_num.'_'.$sub_option?> = 0;
        var required_<?=$option_num.'_'.$sub_option?> = 0;
        
        for(v in fields_<?=$option_num.'_'.$sub_option?>) {
          // total number of spots
          count_<?=$option_num.'_'.$sub_option?> += $('#' + v).val() * 1;
          // has a required price point been used
          if(fields_<?=$option_num.'_'.$sub_option?>[v] && $('#' + v).val() >= 1) { required_<?=$option_num.'_'.$sub_option?> = 1; }
        }
        
        if(count_<?=$option_num.'_'.$sub_option?> == 0 || !count_<?=$option_num.'_'.$sub_option?>) {
          err = 'Please enter the number you would like to book.';
        } else if(required_num_<?=$option_num.'_'.$sub_option?> > 0 && required_<?=$option_num.'_'.$sub_option?> == 0) {
          err = 'At least one marked ( * ) price point is required to book.';
        } else if(!isInt(count_<?=$option_num.'_'.$sub_option?>)) {
					err = 'Please enter a whole number. No decimal places allowed.';
				} else if(count_<?=$option_num.'_'.$sub_option?> < <?=$option->per?>) {
          err = '<?=$option->per?> minimum required to book.';
        } else if(count_<?=$option_num.'_'.$sub_option?> > <?=$option->date->availability?>) {
          err = 'There is not enough availability to book ' + count_<?=$option_num.'_'.$sub_option?>;
        } else if(count_<?=$option_num.'_'.$sub_option?> > 150) {
          err = 'You can not book more than 150 spaces in a single booking.';
        }
				/*
NUM minimum required to book
There is not enough availability to book NUM.
You cannot book more than 150 in a single booking.
At least NUM are required.
				*/
        
        if(err) {
          $('#error_text_<?=$option_num.'_'.$sub_option?>').html(err);
          $('#error_text_<?=$option_num.'_'.$sub_option?>').slideDown().delay(2000).slideUp('slow'); //
          return false;
        }
      }
    </script>
  		
      <a data-toggle="collapse" data-parent="#rezgo-select-option-<?=$option_num.'_'.$sub_option?>" data-target="#option_<?=$option_num.'_'.$sub_option?>" class="panel-heading panel-title rezgo-panel-option-link">
        <div class="rezgo-panel-option"><?=utf8_decode($option->option)?>&nbsp;</div>
      </a>
			<div id="option_<?=$option_num.'_'.$sub_option?>" class="panel-collapse collapse<?=(((count($options) == 1 && $option_num == 1) || $_REQUEST['id'] == (int) $option->uid) ? ' in' : '')?>">
      <div class="panel-body">
				<? if ($option->date->availability != 0) { ?>
        
        <form class="rezgo-order-form" name="checkout_<?=$option_num.'_'.$sub_option?>" id="checkout_<?=$option_num.'_'.$sub_option?>" action="<?=$site->base?>/order">
          <input type="hidden" name="add[0][uid]" value="<?=$option->uid?>" />
          <input type="hidden" name="add[0][date]" value="<?=$_REQUEST['date']?>" />
					<? if(!$site->getCartState()) { // for no-cart requests, we want to make sure we clear the cart ?>
          <input type="hidden" name="order" value="clear" />
					<? } ?>      
          
					<? if($_COOKIE['rezgo_promo']) { ?><input type="hidden" name="promo" value="<?=$_COOKIE['rezgo_promo']?>"><? } ?>
          <? if($_COOKIE['rezgo_refid_val']) { ?><input type="hidden" name="refid" value="<?=$_COOKIE['rezgo_refid_val']?>"><? } ?>
        
          <div class="row"> 
            <div class="col-xs-12">
            
            	<? if (!$site->exists($option->date->hide_availability)) { ?>
              <span class="rezgo-memo rezgo-availability"><strong>Availability:</strong> <?=($option->date->availability == 0 ? 'full' : (string) $option->date->availability)?><br /></span>  
              <? } ?>
            
            	<? if ($option->duration != '') { ?>
              <span class="rezgo-memo rezgo-duration"><strong>Duration:</strong> <?=(string) $option->duration;?><br /></span>  
              <? } ?>
            
            	<? if ($option->time != '') { ?>
              <span class="rezgo-memo rezgo-time"><strong>Time:</strong> <?=(string) $option->time;?><br /></span>  
              <? } ?>
                    	
            	<? $prices = $site->getTourPrices($option);	?>
            	
              <? if($site->getTourRequired() == 1) { ?>
                <span class="rezgo-memo">At least one marked ( <em><i class="fa fa-asterisk"></i></em> ) price point is required.</span>
              <? } ?>
              
              <? if($option->per > 1) { ?>
                <span class="rezgo-memo">At least <?=$option->per?> are required to book.</span>
              <? } ?>
            	<div class="clearfix">&nbsp;</div>
              
              <div class="text-danger rezgo-option-error" id="error_text_<?=$option_num.'_'.$sub_option?>" style="display:none;"></div>
              
              <? 
							$total_required = 0;
							foreach( $prices as $price ) { 
							?>
                <script>fields_<?=$option_num.'_'.$sub_option?>['<?=$price->name?>_<?=$option_num.'_'.$sub_option?>'] = <?=(($price->required) ? 1 : 0)?>;</script>
                            
                <div class="form-group row">
                
                  <div class="col-md-3 col-xs-4 max-80">
                  	<input type="number" name="add[0][<?=$price->name?>_num]" value="<?=$_REQUEST[$price->name.'_num']?>" id="<?=$price->name?>_<?=$option_num.'_'.$sub_option?>" class="form-control input-sm" id="" placeholder="">
                  </div>
                  <label for="<?=$price->name?>_<?=$option_num.'_'.$sub_option?>" class="col-xs-8 control-label rezgo-label-margin rezgo-label-padding-left">
                  	x&nbsp;<?=utf8_decode($price->label)?><?=(($price->required && $site->getTourRequired()) ? ' <em><i class="fa fa-asterisk"></i></em>' : '')?> 
                  	<span class="rezgo-pax-price">(&nbsp;<? if($site->exists($price->base)) { ?><span class="discount"><?=$site->formatCurrency($price->base)?></span> <? } ?><?=$site->formatCurrency($price->price)?>&nbsp;)</span>
                  </label>
                </div> <!-- //  form-group -->
                             
              <? 
								if ($price->required) { $total_required++; }
							} // end foreach( $site->getTourPrices()  
							
							?>     
              <script>required_num_<?=$option_num.'_'.$sub_option?> = <?=$total_required?>;</script>
            
            </div> <!-- end col-sm-8-->
            <div class="col-lg-8 col-md-9 col-xs-12 pull-right">
	            <? $cart = $site->getCartState(); ?>
            	<button type="submit" class="btn btn-block rezgo-btn-book btn-lg" onclick="return check_<?=$option_num.'_'.$sub_option?>();"><?=(($cart) ? 'Add to Order' : 'Book Now')?></button>
            </div>
            
          </div>
        </form>
        
        <? } else { ?>
        
        	<div class="rezgo-order-unavailable"> Sorry, there is no availability left for this option</div>
        
        <? } // end if ($option->date->availability != 0) ?>
        
      </div>
    </div>
  </div>

  <? 
		$sub_option++; // increment sub option instead
		//$option_num++;
		} // end foreach($site->getTours)
		
	?>
  
  </div>
<?

	} else {
		echo 'rezgo-option-hide'; // no availability, hide this option
		//echo $_REQUEST['date'];
	}

?>