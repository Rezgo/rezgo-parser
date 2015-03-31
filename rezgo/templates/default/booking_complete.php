<?
	$trans_num = $site->decode($_REQUEST['trans_num']);
	
	// send the user home if they shoulden't be here
	if(!$trans_num) $site->sendTo($site->base."/booking-not-found:empty");

	// start a session so we can grab the analytics code
	session_start();
	
	$company = $site->getCompanyDetails();
	
?>

<div class="container-fluid rezgo-container">
  <div class="jumbotron rezgo-booking">
    <? if(!$site->getBookings('q='.$trans_num)) { $site->sendTo("/booking-not-found:".$_REQUEST['trans_num']); } ?>
    <? foreach( $site->getBookings('q='.$trans_num) as $booking ): ?>
    <? $item = $site->getTours('t=uid&q='.$booking->item_id, 0); ?>
    <? $site->readItem($booking); ?>
        
    <div class="row">
      <ol class="breadcrumb rezgo-breadcrumb hidden-xs">
        <? if($site->exists($booking->order_code) && $site->getCartState()) { ?>
        <li><a href="<?=$site->base?>/complete/<?=$site->encode($booking->order_code)?>">Back to Order Summary</a></li>
        <? } ?>
        <li class="active">Booking Details</li>
      </ol>
      <? if($site->exists($booking->order_code) && $site->getCartState()) { ?>
      <h3 id="rezgo-back-to-summary-xs" class="hidden-sm hidden-md hidden-lg">&nbsp;<i class="fa fa-chevron-circle-left fa-lg"></i>&nbsp;<a href="<?=$site->base?>/complete/<?=$site->encode($booking->order_code)?>" class="">Back to Summary</a></h3>   
      <? } ?>
    </div>
    <!-- // breadcrumb-->
    <div class="row rezgo-confirmation-head">
      <? if($booking->status == 1 OR $booking->status == 4) { ?>
      <h3>BOOKING COMPLETE</h3>
      <p>Click on the button below for your printable voucher.</p>
      <? } ?>
      <? if($booking->status == 2) { ?>
      <h3>BOOKING NOT YET COMPLETE</h3>
      <p>
        <? if($site->exists($booking->paypal_owed)) { ?>
        To complete your booking, make your payment by clicking on the button below.
        <br />
        AMOUNT PAYABLE NOW:
        <?=$site->formatCurrency($booking->paypal_owed)?>
        <? } else { ?>
        Your booking will be complete once payment has been processed.
        <? } ?>
      </p>
      <? } ?>
      <? if($booking->status == 3) { ?>
      <h3>This booking has been CANCELLED</h3>
      <? } ?>
      <? if($site->exists($booking->paypal_owed)) { ?>
      <div class="row">
        <center>
          <? $company_paypal = $site->getCompanyPaypal(); ?>
          <form role="form" method="post" action="<?=REZGO_DIR?>/php_paypal/process.php">
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
            <input type="hidden" name="encoded_transaction_id" id="encoded_transaction_id" value="<?=$_REQUEST['trans_num']?>" />
            <input type="hidden" name="item_number" id="item_number" value="<?=$trans_num?>" />
            <input type="hidden" name="amount" id="amount" value="<?=$booking->paypal_owed?>" />
            <input type="hidden" name="quantity" id="quantity" value="1" />
            <input type="hidden" name="business" value="<?=$company_paypal?>" />
            <input type="hidden" name="currency_code" value="<?=$site->getBookingCurrency()?>" />
            <input type="hidden" name="domain" value="<?=$site->getDomain()?>.rezgo.com" />
            <input type="hidden" name="cid" value="<?=REZGO_CID?>" />
            <input type="hidden" name="paypal_signature" value="" />
            <input type="hidden" name="base_url" value="rezgo.com" />
            <input type="hidden" name="cancel_return" value="http://<?=$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']?>" />
            <input type="image" class="paypal_button" name="submit_image" src="<?=$site->path?>/img/logos/paypal_pay.png" />
          </form>
        </center>
      </div>
      <? } ?>
      
      <div class="center-block">
            
        <button class="btn btn-lg rezgo-btn-print" onclick="window.open('<?=$site->base?>/complete/<?=$site->encode($trans_num)?>/print', '_blank'); return false;"><i class="fa fa-print fa-lg"></i>&nbsp;Print Receipt</button>
      
      <? if($booking->status == 1 OR $booking->status == 4) { ?>
        <button class="btn btn-lg rezgo-btn-print-voucher" onclick="window.open('http://<?=$site->getDomain();?>.rezgo.com/voucher/<?=$site->encode($trans_num)?>', '_blank'); return false;"><i class="fa fa-print fa-lg"></i>&nbsp;Print Voucher</button>
      <? } ?>     
        
      </div>
      
    </div>
    
    <div class="row rezgo-form-group rezgo-confirmation-detail">
      <div class="col-sm-12">
        <h3 class="rezgo-book-name"><?=$booking->tour_name?> - <?=$booking->option_name?>
          <div class="rezgo-add-cal"><div class="rezgo-add-cal-cell"><a href="https://feed.rezgo.com/b/<?=$booking->trans_num?>"><i class="fa fa-calendar"></i>&nbsp;Add to my calendar</a></div></div>
        </h3>
        <small class="rezgo-booked-on">booked on <?=date((string) $company->date_format, (int) $booking->date_purchased_local)?> / local time</small>
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
              <td class="text-right">
                <?=$price->label?></td>
              <td class="text-right">
                <?=$price->number?></td>
              <td class="text-right">
								<? if($site->exists($price->base)) { ?>
                <span class="discount"><?=$site->formatCurrency($price->base)?></span>
                <? } ?>
                &nbsp;<?=$site->formatCurrency($price->price)?></td>
              <td class="text-right">
                <?=$site->formatCurrency($price->total)?></td>
            </tr>
            <? endforeach; ?>
            <tr>
              <td colspan="3" class="text-right"><strong>Sub-total</strong></td>
              <td class="text-right">
                <?=$site->formatCurrency($booking->sub_total)?></td>
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
              <td class="text-right"><strong>
                <?=$site->formatCurrency($booking->overall_total)?>
                </strong></td>
            </tr>
            <? if($site->exists($booking->deposit)) { ?>
            <tr>
              <td colspan="3" class="text-right"><strong>Deposit</strong></td>
              <td class="text-right"><strong>
                <?=$site->formatCurrency($booking->deposit)?>
                </strong></td>
            </tr>
            <? } ?>
            <? if($site->exists($booking->overall_paid)) { ?>
            <tr>
              <td colspan="3" class="text-right"><strong>Total Paid</strong></td>
              <td class="text-right"><strong>
                <?=$site->formatCurrency($booking->overall_paid)?>
                </strong></td>
            </tr>
            <tr>
              <td colspan="3" class="text-right"><strong>Total&nbsp;Owing</strong></td>
              <td class="text-right"><strong>
                <?=$site->formatCurrency(((float)$booking->overall_total - (float)$booking->overall_paid))?>
                </strong></td>
            </tr>
            <? } ?>
          </table>
        </table>
      </div>
      <div class="col-sm-12">
      	<table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
          <tr>
            <td class="rezgo-td-label">Transaction&nbsp;#:</td>
            <td class="rezgo-td-data"><?=$booking->trans_num?></td>
          </tr>
          <tr>
            <td class="rezgo-td-label">Date:</td>
            <td class="rezgo-td-data"><?=date((string) $company->date_format, (int) $booking->date)?></td>
          </tr>
          <tr>
            <td class="rezgo-td-label">Duration:</td>
            <td class="rezgo-td-data"><?=$item->duration?></td>
          </tr>
          <tr>
            <td class="rezgo-td-label">Location:</td>
            <td class="rezgo-td-data">
						<?
							unset($loc);
							if($site->exists($item->city)) $loc[] = $item->city;
							if($site->exists($item->state)) $loc[] = $item->state;
							if($site->exists($item->country)) $loc[] = $site->countryName($item->country);
							if($loc) echo implode(', ', $loc);
            ?>
            </td>
          </tr>
          <tr>
            <td class="rezgo-td-label">Departure/Pickup Location:</td>
            <td class="rezgo-td-data"><?=$item->details->pick_up?></td>
          </tr>
          <tr>
            <td class="rezgo-td-label">Dropoff/Return Information:</td>
            <td class="rezgo-td-data"><?=$item->details->drop_off?></td>
          </tr>
          <tr>
            <td class="rezgo-td-label">Things to bring:</td>
            <td class="rezgo-td-data"><?=$item->details->bring?></td>
          </tr>
          <tr>
            <td colspan="2"><strong>Itinerary:</strong></td>
          </tr>
          <tr>
            <td colspan="2" class="rezgo-td-data"><?=$item->details->itinerary?></td>
          </tr>
        </table>

      </div>
    </div>
    <!-- // tour confirmation--> 
    
    <div class="row rezgo-form-group rezgo-confirmation-additional-info">
      <div class="col-md-7 col-xs-12">
        <h3>Billing Information</h3>
      	<table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
          <tr>
            <td class="rezgo-td-label">Name</td>
            <td class="rezgo-td-data"><?=$booking->first_name?> <?=$booking->last_name?></td>
          </tr>
          <tr>
            <td class="rezgo-td-label">Address</td>
            <td class="rezgo-td-data">
							<?=$booking->address_1?><? if($site->exists($booking->address_2)) { ?>, <?=$booking->address_2?><? } ?><? if($site->exists($booking->city)) { ?>, <?=$booking->city?><? } ?><? if($site->exists($booking->stateprov)) { ?>, <?=$booking->stateprov?><? } ?><? if($site->exists($booking->postal_code)) { ?>, <?=$booking->postal_code?><? } ?>, <?=$site->countryName($booking->country)?>
            </td>
          </tr>
          <tr>
            <td class="rezgo-td-label">Phone Number</td>
            <td class="rezgo-td-data"><?=$booking->phone_number?></td>
          </tr>
          <tr>
            <td class="rezgo-td-label">Email Address</td>
            <td class="rezgo-td-data"><?=$booking->email_address?></td>
          </tr>
          <? if($booking->overall_total > 0) { ?>
            <tr>
              <td class="rezgo-td-label">Payment Method</td>
              <td class="rezgo-td-data"><?=$booking->payment_method?></td>
            </tr>
            <? if($booking->payment_method == 'Credit Cards') { ?>
            <tr>
              <td class="rezgo-td-label">Card Number</td>
              <td class="rezgo-td-data"><?=$booking->card_number?></td>
            </tr>
            <? } ?>
            <? if($site->exists($booking->payment_method_add->label)) { ?>
            <tr>
              <td class="rezgo-td-label"><?=$booking->payment_method_add->label?></td>
              <td class="rezgo-td-data"><?=$booking->payment_method_add->value?></td>
            </tr>
            <? } ?>
          <? } ?>
          <tr>
            <td class="rezgo-td-label">Payment Status</td>
            <td class="rezgo-td-data">
							<?=(($booking->status == 1) ? 'CONFIRMED' : '')?>
              <?=(($booking->status == 2) ? 'PENDING' : '')?>
              <?=(($booking->status == 3) ? 'CANCELLED' : '')?>
            </td>
          </tr>
          <? if($site->exists($booking->trigger_code)) { ?>
          <tr>
            <td class="rezgo-td-label">Promotional Code</td>
            <td class="rezgo-td-data"><?=$booking->trigger_code?></td>
          </tr>
          <? } ?>
        </table>

				<? if(count($site->getBookingForms()) > 0 OR count($site->getBookingPassengers()) > 0) { ?>
          
        <h4>Customer Information</h4>
        
          <table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
                  
            <? foreach( $site->getBookingForms() as $form ): ?>
							<? if($form->type == 'checkbox') { ?>
                <? if($site->exists($form->answer)) { $form->answer = 'yes'; } else { $form->answer = 'no'; } ?>
              <? } ?>
              <tr>
                <td class="rezgo-td-label"><?=$form->question?></td>
                <td class="rezgo-td-data"><?=$form->answer?>&nbsp;</td>
              </tr>
            <? endforeach; ?>
            
            <? foreach( $site->getBookingPassengers() as $passenger ): ?>
            
              <tr><td class="rezgo-td-label"><?=$passenger->label?></td><td class="rezgo-td-data">( <?=$passenger->num?> )&nbsp;</td></tr>
              <tr><td class="rezgo-td-label">Name</td><td class="rezgo-td-data"><?=$passenger->first_name?> <?=$passenger->last_name?>&nbsp;</td></tr>
              <tr><td class="rezgo-td-label">Phone Number</td><td class="rezgo-td-data"><?=$passenger->phone_number?>&nbsp;</td></tr>
              <tr><td class="rezgo-td-label">Email</td><td class="rezgo-td-data"><?=$passenger->email_address?>&nbsp;</td></tr>
              <? foreach( $passenger->forms->form as $form ): ?>
                <? if($form->type == 'checkbox') { ?>
                  <? if($site->exists($form->answer)) { $form->answer = 'yes'; } else { $form->answer = 'no'; } ?>
                <? } ?>
                <tr>
                  <td class="rezgo-td-label"><?=$form->question?></td>
                  <td class="rezgo-td-data"><?=$form->answer?>&nbsp;</td>
                </tr>
              <? endforeach; ?>
            
            <? endforeach; ?>
            
          </table>
            
        <? } ?>

        
      </div>
      
      <div class="col-md-5 col-xs-12 rezgo-company-info">
        <h3>Cancellation Policy</h3>
        <p>
        <? if($site->exists($booking->rezgo_gateway)) { ?>
        Canceling a booking with Rezgo can result in cancellation fees being
        applied by Rezgo, as outlined below. Additional fees may be levied by
        the individual supplier/operator (see your Rezgo Voucher for specific
        details). When canceling any booking you will be notified via email,
        facsimile or telephone of the total cancellation fees.<br />
        <br />
        1. Event, Attraction, Theater, Show or Coupon Ticket<br />
        These are non-refundable in all circumstances.<br />
        <br />
        2. Gift Certificate<br />
        These are non-refundable in all circumstances.<br />
        <br />
        3. Tour or Package Commencing During a Special Event Period<br />
        These are non-refundable in all circumstances. This includes,
        but is not limited to, Trade Fairs, Public or National Holidays,
        School Holidays, New Year's, Thanksgiving, Christmas, Easter, Ramadan.<br />
        <br />
        4. Other Tour Products & Services<br />
        If you cancel at least 7 calendar days in advance of the
        scheduled departure or commencement time, there is no cancellation
        fee.<br />
        If you cancel between 3 and 6 calendar days in advance of the
        scheduled departure or commencement time, you will be charged a 50%
        cancellation fee.<br />
        If you cancel within 2 calendar days of the scheduled departure
        or commencement time, you will be charged a 100% cancellation fee. <br />
        <br />
        <? } else { ?>
        <? if($site->exists($item->details->cancellation)) { ?>
        <?=$item->details->cancellation?>
        <br />
        <br />
        <? } ?>
        <? } ?>
        <a href="javascript:void(0);" onclick="javascript:window.open('/terms',null,'width=800,height=600,status=no,toolbar=no,menubar=no,location=no,scrollbars=1');">Click here to view terms and conditions.</a> 
        </p>
        
				<? if($site->exists($booking->rid)) { ?>
        <h3>Customer Service</h3>
        <p>
          <? if($site->exists($booking->rezgo_gateway)) { ?>
          Rezgo.com<br />
          Attn: Partner Bookings<br />
          333 Brooksbank Avenue<br />
          Suite 718<br />
          North Vancouver, BC<br />
          Canada V7J 3V8<br />
          (604) 983-0083<br />
          bookings@rezgo.com
          <? } else { ?>
          <? $company = $site->getCompanyDetails('p'.$booking->rid); ?>
          <?=$company->company_name?>
          <br />
          <?=$company->address_1?>
          <?=$company->address_2?>
          <br />
          <?=$company->city?>
          ,
          <? if($site->exists($company->state_prov)) { ?>
          <?=$company->state_prov?>
          ,
          <? } ?>
          <?=$site->countryName($company->country)?>
          <br />
          <?=$company->postal_code?>
          <br />
          <?=$company->phone?>
          <br />
          <?=$company->email?>
          <? if($site->exists($company->tax_id)) { ?>
          <br />
          <br />
          <?=$company->tax_id?>
          <? } ?>
          <? } ?>
        </p>
        <? } ?>
        
        <h3>Service Provided by</h3>
        <address>
					<? $company = $site->getCompanyDetails($booking->cid); ?>
          <strong><?=$company->company_name?></strong><br />
          <?=$company->address_1?><? if($site->exists($company->address_2)) { ?>, <?=$company->address_2?><? } ?>
          <br />
          <?=$company->city?>,
          <? if($site->exists($company->state_prov)) { ?><?=$company->state_prov?>, <? } ?>
          <?=$site->countryName($company->country)?><br />
          <?=$company->postal_code?><br />
          <?=$company->phone?><br />
          <?=$company->email?>
          <? if($site->exists($company->tax_id)) { ?><br />Tax ID: <?=$company->tax_id?><? } ?>
        </address>
        
      </div>
      <!-- // .rezgo-company-info --> 
    </div>
    <!-- // .rezgo-confirmation-additional-info --> 
  </div>
  <!-- // .rezgo-booking --> 
</div>
<!-- //  rezgo-container --> 

<? endforeach; ?>


<? if($_SESSION['REZGO_CONVERSION_ANALYTICS']) { ?>
<?=$_SESSION['REZGO_CONVERSION_ANALYTICS']?>

<!-- HitsLink.com tracking script --> 
<script type="text/javascript" id="wa_u" defer></script> 
<script type="text/javascript" async>//<![CDATA[
	var wa_pageName=location.pathname;    // customize the page name here;
	wa_account="C6CFCDCACDCFC7CBCDCB"; wa_location=93;
	wa_MultivariateKey = '';    //  Set this variable to perform multivariate testing
	ec_Orders_orderID='<?=$trans_num?>';      //  Enter the Orders unique ID
	ec_Orders_orderAmt='<?=$booking->overall_total?>';  //  Enter the amount of the Orders
	var wa_c=new RegExp('__wa_v=([^;]+)').exec(document.cookie),wa_tz=new Date(),
	wa_rf=document.referrer,wa_sr=location.search,wa_hp='http'+(location.protocol=='https:'?'s':'');
	if(top!==self){wa_rf=top.document.referrer;wa_sr=top.location.search}
	if(wa_c!=null){wa_c=wa_c[1]}else{wa_c=wa_tz.getTime();
	document.cookie='__wa_v='+wa_c+';path=/;expires=1/1/'+(wa_tz.getUTCFullYear()+2);}wa_img=new Image();
	wa_img.src=wa_hp+'://counter.hitslink.com/statistics.asp?v=1&s=93&eacct='+wa_account+'&an='+
	escape(navigator.appName)+'&sr='+escape(wa_sr)+'&rf='+escape(wa_rf)+'&mvk='+escape(wa_MultivariateKey)+
	'&sl='+escape(navigator.systemLanguage)+'&l='+escape(navigator.language)+
	'&pf='+escape(navigator.platform)+'&pg='+escape(wa_pageName)+'&cd='+screen.colorDepth+'&rs='+escape(screen.width+
	' x '+screen.height)+'&je='+navigator.javaEnabled()+'&c='+wa_c+'&tks='+wa_tz.getTime()
	+'&ec_type=111133&ec_uniqueId='+ec_Orders_orderID+'&ec_orderAmount='+ec_Orders_orderAmt
	;document.getElementById('wa_u').src=wa_hp+'://counter.hitslink.com/track.js';//]]>
	</script>
<? unset($_SESSION['REZGO_CONVERSION_ANALYTICS']); ?>
<? } ?>
