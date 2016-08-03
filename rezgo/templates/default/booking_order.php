<?

	// send the user home if they shouldn't be here
	if(!$trans_num) $site->sendTo($site->base."/order-not-found:empty");
	
	// start a session so we can grab the analytics code
	session_start();
	
	$order_bookings = $site->getBookings('t=order_code&q='.$trans_num);

	if(!$order_bookings) { $site->sendTo("/order-not-found:".$_REQUEST['trans_num']); }
	
	// check and see if we want to be here or on the individual item
	// if we only have 1 item and the cart is off, forward them through
	if(!$site->getCartState() && count($order_bookings) == 1) {
		$site->sendTo($site->base.'/complete/'.$site->encode($order_bookings[0]->trans_num));
	}
	
	$company = $site->getCompanyDetails();
	

	// unset promo session and cookie
	unset($_REQUEST['promo']);
	unset($_SESSION['rezgo_promo']);
	// set no value, expire an hour ago
	setcookie("rezgo_promo", " ", time() - 3600, '/', $_SERVER['SERVER_NAME']); 
?>


<div class="container-fluid rezgo-container">
  <div class="jumbotron rezgo-booking"> 
    
    <div class="row">
      <ol class="breadcrumb rezgo-breadcrumb hidden-xs">
        <li>Your Order</li>
        <li>Guest Information</li>
        <li>Billing Information</li>
        <li class="active">Confirmation</li>
      </ol>
    </div>
    
		<? if($_SESSION['REZGO_CONVERSION_ANALYTICS']) { ?>
    <div class="row alert alert-success">
      <span id="rezgo-booking-added">YOUR BOOKING HAS BEEN ADDED</span>
    </div>
    <? } ?>    
    
    <!-- // breadcrumb-->
    <div class="row rezgo-confirmation-head">
      <h3>Your order <?=$trans_num?> contains <?=count($order_bookings)?> booking<?=((count($order_bookings) != 1) ? 's' : '')?></h3>
    </div>
	
	<? $n = 1; ?>
	
	<? foreach( $order_bookings as $booking ) { ?>
  
		<? 
		$item = $site->getTours('t=uid&q='.$booking->item_id, 0); 
		$share_url = urlencode('http://'.$_SERVER['HTTP_HOST'].$site->base.'/details/'.$item->com.'/'.$site->seoEncode($item->item));
		?>
		
		<? $site->readItem($booking); ?>
	  
    <div class="row rezgo-form-group rezgo-confirmation"> 
      
      <div class="rezgo-booking-status col-md-4 col-sm-12">
      <!-- Booking <?=$n++?> of <?=count($order_bookings)?> (booked on <?=date((string) $company->date_format, (int) $booking->date_purchased_local)?> / local time) -->
			<? if($booking->status == 1 OR $booking->status == 4) { ?>
        <p class="rezgo-status-complete"><i class="fa fa-check fa-lg"></i>&nbsp;Booking Complete</p>
			<? } ?>
			<? if($booking->status == 2) { ?>
        <p class="rezgo-status-pending"><i class="fa fa-check fa-lg"></i>&nbsp;Booking Pending</p>
			<? } ?>
			<? if($booking->status == 3) { ?>
        <p class="rezgo-status-cancel"><i class="fa fa-times fa-lg"></i>&nbsp;Booking Cancelled</p>
			<? } ?>
      
			<? if($site->exists($booking->paypal_owed)) { ?>
      
				<? $company_paypal = $site->getCompanyPaypal(); ?>
        <div>
            
          <form role="form" class="form-inline" method="post" action="<?=REZGO_DIR?>/php_paypal/process.php">	
          
            <span id="paypal_owing">Total&nbsp;Owing&nbsp;:&nbsp;<?=$site->formatCurrency(((float)$booking->overall_total - (float)$booking->overall_paid))?></span>
          	
            <input type="hidden" name="firstname" id="firstname" value="<?=$booking->first_name?>" />
            <input type="hidden" name="lastname" id="lastname" value="<?=$booking->last_name?>" />
            <input type="hidden" name="address1" id="address1" value="<?=$booking->address_1?>" /> 
            <input type="hidden" name="address2" id="address2" value="<?=$booking->address_2?>" />
            <input type="hidden" name="city" value="<?=$booking->city?>" />
            <input type="hidden" name="state" value="<?=$booking->stateprov?>" />
            <input type="hidden" name="country" value="<?=$site->countryName($booking->country)?>" />
            <input type="hidden" name="zip" value="<?=$booking->postal_code?>" />
            <input type="hidden" name="email" id="email" value="<?=$booking->email_address?>" />
            <input type="hidden" name="phone" id="phone" value="<?=$booking->phone_number?>" />
            <input type="hidden" name="item_name" id="item_name" value="<?=$booking->tour_name?> - <?=$booking->option_name?>" />
            <input type="hidden" name="encoded_transaction_id" id="encoded_transaction_id" value="<?=$site->encode($booking->trans_num)?>" />
            <input type="hidden" name="item_number" id="item_number" value="<?=$booking->trans_num?>" />
            <input type="hidden" name="amount" id="amount" value="<?=$booking->paypal_owed?>" />
            <input type="hidden" name="quantity" id="quantity" value="1" />	
            <input type="hidden" name="business" value="<?=$company_paypal?>" />
            <input type="hidden" name="currency_code" value="<?=$site->getBookingCurrency()?>" />
            <input type="hidden" name="domain" value="<?=$site->getDomain()?>.rezgo.com" />
            <input type="hidden" name="cid" value="<?=REZGO_CID?>" />
            <input type="hidden" name="paypal_signature" value="" />
            <input type="hidden" name="base_url" value="rezgo.com" />
            <input type="hidden" name="cancel_return" value="http://<?=$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']?>" />
            <input type="image"  class="paypal_button" name="submit_image" src="<?=$site->path?>/img/logos/paypal_pay.png" />
          </form>
          
        </div>
      
      <? } ?>
      
      </div><!-- // .rezgo-booking-status -->
      
      <div class="col-md-8 col-sm-12">
        
        <div class="rezgo-booking-share">
          <span id="rezgo-social-links">       
            <a href="javascript:void(0);" title="Share this on Twitter" id="social_twitter" onclick="window.open('http://twitter.com/share?text=<?=urlencode('I found this great thing to do! "'.$item->item.'"')?>&url=<?=$share_url?><? if($site->exists($site->getTwitterName())) { ?>&via=<?=$site->getTwitterName()?>'<? } else {?>'<? } ?>,'tweet','location=1,status=1,scrollbars=1,width=500,height=350');"><i class="fa fa-twitter-square" id="social_twitter_icon">&nbsp;</i></a>
            <a href="javascript:void(0);" title="Share this on Facebook" id="social_facebook" onclick="window.open('http://www.facebook.com/sharer.php?u=<?=$share_url?>&t=<?=urlencode($item->item)?>','facebook','location=1,status=1,scrollbars=1,width=600,height=400');"><i class="fa fa-facebook-square" id="social_facebook_icon">&nbsp;</i></a>
          </span>		      
        </div>
        
      </div>
      
      <div class="clearfix"></div>
            
      <h3><?=$booking->tour_name?>&nbsp;(<?=$booking->option_name?>)</h3>
      <div class="col-md-4 col-sm-12">
      
        <table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
          <tr>
            <td class="rezgo-td-label">Transaction&nbsp;#:</td>
            <td class="rezgo-td-data"><?=$booking->trans_num?></td>
          </tr>
          <tr>
            <td class="rezgo-td-label">Date:</td>
            <td class="rezgo-td-data"><?=date((string) $company->date_format, (int) $booking->date)?>
            <? if ($booking->time != '') { ?> at <?=$booking->time?><? } ?>
            </td>
          </tr>
          <? if($site->exists($booking->trigger_code)) { ?>
          <tr>
            <td class="rezgo-td-label" class="rezgo-promo-label"><span>Promotional&nbsp;Code:</span></td>
            <td class="rezgo-td-data"><?=$booking->trigger_code?></td>
          </tr>
          <? } ?>
        </table>    
          
        <a href="<?=$site->base?>/complete/<?=$site->encode($booking->trans_num)?>" class="btn btn-lg rezgo-btn-default btn-block">View Details</a> 
        <div class="clearfix">&nbsp;</div>
				<? if($booking->status == 1 OR $booking->status == 4) { ?>
        <? $domain = $site->getDomain(); ?>
          <a href="http://<?=$domain?>.rezgo.com/voucher/<?=$site->encode($booking->trans_num)?>" class="btn btn-lg rezgo-btn-print-voucher btn-block" target="_blank">Print Voucher</a>
        <? } ?>
        </div>
      <div class="col-md-8 col-sm-12">
        <table class="table-responsive">
          <table class="table table-bordered table-striped rezgo-billing-cart">
            <tr>
              <td class="text-right"><label>Type</label></td>
              <td class="text-right"><label class="hidden-xs">Qty.</label></td>
              <td class="text-right"><label>Cost</label></td>
              <td class="text-right"><label>Total</label></td>
            </tr>
            
						<? foreach( $site->getBookingPrices() as $price ): ?>
            
            <tr>
              <td class="text-right"><?=$price->label?></td>
              <td class="text-right"><?=$price->number?></td>
              <td class="text-right">
							<? if($site->exists($price->base)) { ?>
                <span class="discount"><?=$site->formatCurrency($price->base)?></span>
							<? } ?>
							&nbsp;<?=$site->formatCurrency($price->price)?></td>
              <td class="text-right"><?=$site->formatCurrency($price->total)?></td>
              
            </tr>
              
            <? endforeach; ?>
            
            <tr>
              <td colspan="3" class="text-right"><strong>Sub-total</strong></td>
              <td class="text-right"><?=$site->formatCurrency($booking->sub_total)?></td>
            </tr>
            
						<? foreach( $site->getBookingLineItems() as $line ) { ?>
              <?
                unset($label_add);
                if($site->exists($line->percent) || $site->exists($line->multi)) {
                  $label_add = ' (';
                    
                    if($site->exists($line->percent)) $label_add .= $line->percent.'%';
                    if($site->exists($line->multi)) {
                      if(!$site->exists($line->percent)) $label_add .= $site->formatCurrency($line->multi);
                      $label_add .= ' x '.$booking->pax;
                    }
                    
                  $label_add .= ')';	
                }
              ?>
            
            	<? if( $site->exists($line->amount) ) { ?>
              <tr>
                <td colspan="3" class="text-right"><strong><?=$line->label?><?=$label_add?></strong></td>
                <td class="text-right"><?=$site->formatCurrency($line->amount)?></td>
              </tr>
              <? } ?>
            
            <? } ?>
            
						<? foreach( $site->getBookingFees() as $fee ): ?>
              <? if( $site->exists($fee->total_amount) ): ?>
                <tr>
                  <td colspan="3" class="text-right"><strong><?=$fee->label?></strong></td>
                  <td class="text-right"><?=$site->formatCurrency($fee->total_amount)?></td>
                </tr>
              <? endif; ?>
            <? endforeach; ?>
            
            <tr>
              <td colspan="3" class="text-right"><strong>Total</strong></td>
              <td class="text-right"><strong><?=$site->formatCurrency($booking->overall_total)?></strong></td>
            </tr>
            
						<? if($site->exists($booking->deposit)) { ?>
              <tr>
                <td colspan="3" class="text-right"><strong>Deposit</strong></td>
                <td class="text-right"><strong><?=$site->formatCurrency($booking->deposit)?></strong></td>
              </tr>
            <? } ?>
            
            <? if($site->exists($booking->overall_paid)) { ?>
              <tr>
                <td colspan="3" class="text-right"><strong>Total Paid</strong></td>
                <td class="text-right"><strong><?=$site->formatCurrency($booking->overall_paid)?></strong></td>
              </tr>
              <tr>
                <td colspan="3" class="text-right"><strong>Total&nbsp;Owing</strong></td>
                <td class="text-right"><strong><?=$site->formatCurrency(((float)$booking->overall_total - (float)$booking->overall_paid))?></strong></td>
              </tr>
            <? } ?>
            
          </table>
        </table>
      </div>

    </div>
    <!-- //  tour confirm --> 
	          
	  <? 
		$cart_total += ((float)$booking->overall_total); 
		$cart_owing += ((float)$booking->overall_total - (float)$booking->overall_paid); 
		
		if ($booking->payment_method != 'None') {
			$final_payment_method = (string) $booking->payment_method;
		}
		?>
	  	
	<? } ?>
	
    <div class="row rezgo-form-group rezgo-confirmation">
      <div class="col-md-6 col-sm-12 rezgo-billing-confirmation">
        <h3 class="text-info">Billing Information</h3>
        
        <table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
          <tr>
            <td class="rezgo-td-label">Name:</td>
            <td class="rezgo-td-data"><?=$booking->first_name?> <?=$booking->last_name?></td>
          </tr>
          <tr>
            <td class="rezgo-td-label">Address:</td>
            <td class="rezgo-td-data"><?=$booking->address_1?><? if($site->exists($booking->address_2)) { ?>, <?=$booking->address_2?><? } ?><? if($site->exists($booking->city)) { ?>, <?=$booking->city?><? } ?><? if($site->exists($booking->stateprov)) { ?>, <?=$booking->stateprov?><? } ?><? if($site->exists($booking->postal_code)) { ?>, <?=$booking->postal_code?><? } ?>, <?=$site->countryName($booking->country)?>
            </td>
          </tr>
          <tr>
            <td class="rezgo-td-label">Phone&nbsp;No.:</td>
            <td class="rezgo-td-data"><?=$booking->phone_number?></td>
          </tr>
          <tr>
            <td class="rezgo-td-label">Email:</td>
            <td class="rezgo-td-data"><?=$booking->email_address?></td>
          </tr>
        </table>    
          
      </div>
      <div class="col-md-6 col-sm-12 rezgo-payment-confirmation">
        <h3 class="text-info">Your Payment Information</h3>
        
        <table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
          <tr>
            <td class="rezgo-td-label">Total&nbsp;Order:</td>
            <td class="rezgo-td-data"><strong><?=$site->formatCurrency($cart_total)?></strong></td>
          </tr>
          <tr>
            <td class="rezgo-td-label">Total&nbsp;Owing:</td>
            <td class="rezgo-td-data"><strong><?=$site->formatCurrency($cart_owing)?></strong></td>
          </tr>
          <? if ($cart_total > 0) { ?>
          <tr>
            <td class="rezgo-td-label">Payment Made&nbsp;by:</td>
            <td class="rezgo-td-data"><?=$final_payment_method?></td>
          </tr>
          <? } ?>
        </table>    
        
      </div>
    </div>
    <!-- //  payment confirmation --> 
  </div><!-- //  .jumbotron --> 
</div>

<? 
	if($_SESSION['REZGO_CONVERSION_ANALYTICS']) { 
		echo $_SESSION['REZGO_CONVERSION_ANALYTICS'];
		unset($_SESSION['REZGO_CONVERSION_ANALYTICS']);
	} 
?>