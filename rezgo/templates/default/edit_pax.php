<?php
	/* url: '<?=$site->base?>/edit_pax.php?id=' + order_item + '&order_id=' + cart_id + '&date=' + book_date, */
	$option = $site->getTours('t=uid&q='.$_REQUEST['id'].'&d='.$_REQUEST['date']);
	
	$site->readItem($option[0]);
	
	$order_id = $_REQUEST['order_id'];
	
	$cart = $site->getCart();
	
?>

  <div class="rezgo-edit-pax-content">
    
      <script>
        var fields_<?=$order_id?> = new Array();
				var required_num_<?=$order_id?> = 0;
				
				function isInt(n) {
					 return n % 1 === 0;
				}        
				
        // validate form data
        function check_pax_<?=$order_id?>() {
          var err;
          var count = 0;
          var required = 0;
          
          for(v in fields_<?=$order_id?>) {
            // total number of spots
            count += $('#' + v).val() * 1;
            
            // has a required price point been used
            if(fields_<?=$order_id?>[v] && $('#' + v).val() >= 1) { required = 1; }
          }
          					
          if(count == 0 || !count) {
            err = 'Please enter the number you would like to book.';
          } else if(required_num_<?=$order_id?> > 0 && required == 0) {
            err = 'At least one marked ( * ) price point is required to book.';
          } else if(!isInt(count)) {
            err = 'Please enter a whole number. No decimal places allowed.';
          } else if(count < <?=$option[0]->per?>) {
            err = '<?=$option[0]->per?> minimum required to book.';
          } else if(count > <?=$option[0]->date->availability?>) {
            err = 'There is not enough availability to book ' + count;
          } else if(count > 150) {
            err = 'You can not book more than 150 spaces in a single booking.';
          }
          
          if(err) {
            $('#error_text_<?=$order_id?>').html(err);
            $('#error_text_<?=$order_id?>').slideDown().delay(2000).slideUp('slow');
            return false;
          }
        }
      </script>
          
			<? if ($option[0]->date->availability != 0) { ?>
      
      <form class="rezgo-order-form" name="rezgo-edit-pax-<?=$order_id?>" id="rezgo-edit-pax-<?=$order_id?>" action="<?=$site->base?>/order">
        <input type="hidden" name="edit[<?=$order_id?>][uid]" value="<?=$option[0]->uid?>" />
        <input type="hidden" name="edit[<?=$order_id?>][date]" value="<?=$_REQUEST['date']?>" />
        <? if(!$site->getCartState()) { // for no-cart requests, we want to make sure we clear the cart ?>
        <input type="hidden" name="order" value="clear" />
        <? } ?>      
        
        <? if($_COOKIE['rezgo_promo']) { ?><input type="hidden" name="promo" value="<?=$_COOKIE['rezgo_promo']?>"><? } ?>
        <? if($_COOKIE['rezgo_refid_val']) { ?><input type="hidden" name="refid" value="<?=$_COOKIE['rezgo_refid_val']?>"><? } ?>
        
					<? $prices = $site->getTourPrices($option[0]);	?>
        
					<? if($site->getTourRequired()) { ?>
            <span class="rezgo-memo">At least one marked ( <em><i class="fa fa-asterisk"></i></em> ) price point is required to book.</span>
          <? } ?>
          
          <? if($option[0]->per > 1) { ?>
            <span class="rezgo-memo">At least <?=$option[0]->per?> are required to book.</span>
          <? } ?>
          <div class="clearfix">&nbsp;</div>
    			
          <? 
					$total_required = 0;
					foreach( $prices as $price ) {
					?>
          
            <script>fields_<?=$order_id?>['<?=$price->name?>_<?=$order_id?>'] = <?=(($price->required) ? 1 : 0)?>;</script>
                        
            <div class="form-group row">
            
              <div class="col-md-2 col-xs-4"><input type="number" name="edit[<?=$order_id?>][<?=$price->name?>_num]" value="<?=(string) $cart[$order_id]->{$price->name.'_num'}?>" id="<?=$price->name?>_<?=$order_id?>" size="3" class="form-control input-sm" /></div>
              <label for="<?=$price->name?>" class="col-xs-8 control-label rezgo-label-margin">x&nbsp;&nbsp;<?=$price->label?><?=(($price->required && $site->getTourRequired()) ? ' <em><i class="fa fa-asterisk"></i></em>' : '')?>&nbsp;(
              <? if($site->exists($cart[$order_id]->date->base_prices->{'price_'.$price->name})) { ?><span class="discount"><?=$site->formatCurrency($cart[$order_id]->date->base_prices->{'price_'.$price->name})?></span><? } ?>
              <?=$site->formatCurrency($cart[$order_id]->date->{'price_'.$price->name})?> )</label>
            </div> <!-- //  form-group -->
                         
          <? 
						if ($price->required) { $total_required++; }
					} // end foreach( $site->getTourPrices()  
					?>     
					<script>required_num_<?=$order_id?> = <?=$total_required?>;</script>
          
          <div class="text-danger rezgo-option-error" id="error_text_<?=$order_id?>" style="display:none;"></div>
        
          <div class="form-group pull-right">
            <a data-toggle="collapse" href="#pax-edit-<?=$order_id?>">Cancel</a>&nbsp;
            <button type="submit" class="btn rezgo-btn-book" onclick="return check_pax_<?=$order_id?>();">Update Booking</button>
          </div>
          <br />
        
      </form>
        
			<? } else { ?>
      
        <div class="rezgo-order-unavailable"> Sorry, there is no availability left for this option</div>
      
      <? } // end if ($option->date->availability != 0) ?>
      
    
  
  </div>
  
