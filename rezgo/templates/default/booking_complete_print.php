<?
	// grab and decode the trans_num if it was set
	$trans_num = $site->decode($_REQUEST['trans_num']);

	// send the user home if they shoulden't be here
	if(!$trans_num) $site->sendTo("/".$current_wp_page."/booking-not-found");
	
	$company = $site->getCompanyDetails();
?>

<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex, nofollow">
</head>
<body>
    
<!-- Bootstrap CSS -->
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css" rel="stylesheet">

<!-- Font awesome --> 
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
<!--[if IE 7]>
  <link href="<?=$this->path?>/css/font-awesome-ie7.css" rel="stylesheet">
<![endif]-->  

<!-- Rezgo stylesheet -->
<link href="<?=$site->path?>/css/rezgo.css" rel="stylesheet">

<? if($site->exists($site->getStyles())) { ?>
<style>
	<?=$site->getStyles();?>
</style>
<? } ?>

<div class="container-fluid rezgo-container">	

	<? if(!$site->getBookings('q='.$trans_num)) { $site->sendTo("/tour"); } ?>
	
	<? foreach( $site->getBookings('q='.$trans_num) as $booking ): ?>
	
	<? $item = $site->getTours('t=uid&q='.$booking->item_id, 0); ?>
	
	<? $site->readItem($booking) ?>

  <div class="rezgo-content-row">
    <h2>Your Booking (booked on <?=date((string) $company->date_format, (int) $booking->date_purchased_local)?> / local time)</h2>
    
    <table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
      <tr>
        <td class="rezgo-td-label">Transaction #</td>
        <td class="rezgo-td-data"><?=$booking->trans_num?></td>
      </tr>
      <tr>
        <td class="rezgo-td-label">You have booked</td>
        <td class="rezgo-td-data"><?=$booking->tour_name?> &mdash; <?=$booking->option_name?></td>
      </tr>
      <tr>
        <td class="rezgo-td-label">Booked For</td>
        <td class="rezgo-td-data"><?=date((string) $company->date_format, (int) $booking->date)?></td>
      </tr>
      <tr>
        <td class="rezgo-td-label">Duration</td>
        <td class="rezgo-td-data"><?=$item->duration?></td>
      </tr>
      <tr>
        <td class="rezgo-td-label">Location</td>
        <td class="rezgo-td-data"><?=$item->city?><? if($site->exists($item->state)) { ?>, <?=$item->state?><? } ?><? if($site->exists($item->country)) { ?>, <?=$site->countryName($item->country)?><? } ?></td>
      </tr>
      <tr>
        <td class="rezgo-td-label">Pickup/Departure Information</td>
        <td class="rezgo-td-data"><?=$item->details->pick_up?></td>
      </tr>
      <tr>
        <td class="rezgo-td-label">Drop Off/Return Information</td>
        <td class="rezgo-td-data"><?=$item->details->drop_off?></td>
      </tr>
      <tr>
        <td class="rezgo-td-label">Things to bring</td>
        <td class="rezgo-td-data"><?=$item->details->bring?></td>
      </tr>
      <tr>
        <td class="rezgo-td-label">Itinerary</td>
        <td class="rezgo-td-data"><?=$item->details->itinerary?></td>
      </tr>
    </table>        
 
  </div> <!-- end of cart section -->

	<div style="page-break-after:always;"></div>

  <div class="rezgo-content-row">
    <h2>Payment Information</h2>
    
    <table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
      <tr>
        <td class="rezgo-td-label">Name</td>
        <td class="rezgo-td-data"><?=$booking->first_name?> <?=$booking->last_name?></td>
      </tr>
      <tr>
        <td class="rezgo-td-label">Address</td>
        <td class="rezgo-td-data"><?=$booking->address_1?><? if($site->exists($booking->address_2)) { ?>, <?=$booking->address_2?><? } ?><? if($site->exists($booking->city)) { ?>, <?=$booking->city?><? } ?><? if($site->exists($booking->stateprov)) { ?>, <?=$booking->stateprov?><? } ?><? if($site->exists($booking->postal_code)) { ?>, <?=$booking->postal_code?><? } ?>, <?=$site->countryName($booking->country)?></td>
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
          <td class="rezgo-td-label">Card Number</td><td class="rezgo-td-data"><?=$booking->card_number?></td>
        </tr>
        <? } ?>
        <? if($site->exists($booking->payment_method_add->label)) { ?>
        <tr>
          <td class="rezgo-td-label"><?=$booking->payment_method_add->label?></td><td class="rezgo-td-data"><?=$booking->payment_method_add->value?></td>
        </tr>
        <? } ?>
      <? } ?>
      <tr>
        <td class="rezgo-td-label">Payment Status</td>
        <td class="rezgo-td-data"><?=(($booking->status == 1) ? 'CONFIRMED' : '')?><?=(($booking->status == 2) ? 'PENDING' : '')?><?=(($booking->status == 3) ? 'CANCELLED' : '')?></td>
      </tr>
      <? if($site->exists($booking->trigger_code)) { ?>
      <tr>
        <td class="rezgo-td-label">Promotional Code</td>
        <td class="rezgo-td-data"><?=$booking->trigger_code?></td>
      </tr>
      <? } ?>
      <tr>
        <td class="rezgo-td-label">Charges</td>
        <td class="rezgo-td-data">
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
            
              <tr>
                <td colspan="3" class="text-right"><strong><?=$line->label?><?=$label_add?></strong></td>
                <td class="text-right"><?=$site->formatCurrency($line->amount)?></td>
              </tr>
            
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
        
        </td>
      </tr>
    </table>    

	</div>

	<? if(count($site->getBookingForms()) > 0 OR count($site->getBookingPassengers()) > 0) { ?>
  <div class="rezgo-content-row">
    <h2>Guest Information</h2>
    
    <table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
  		<? foreach( $site->getBookingForms() as $form ): ?>
  			<? if($form->type == 'checkbox') { ?>
					<? if($site->exists($form->answer)) { $form->answer = 'yes'; } else { $form->answer = 'no'; } ?>
				<? } ?>
        <tr>
          <td class="rezgo-td-label"><?=$form->question?>:</td>
          <td class="rezgo-td-data"><?=$form->answer?></td>
        </tr>
  		<? endforeach; ?>    
      
	  	<? foreach( $site->getBookingPassengers() as $passenger ): ?>
        <tr>
          <td class="rezgo-td-label"><?=$passenger->label?> <?=$passenger->num?>:</td>
          <td class="rezgo-td-data"><?=$passenger->first_name?> <?=$passenger->last_name?></td>
        </tr>
        <tr>
          <td class="rezgo-td-label">Phone Number:</td>
          <td class="rezgo-td-data"><?=$passenger->phone_number?></td>
        </tr>
        <tr>
          <td class="rezgo-td-label">Email:</td>
          <td class="rezgo-td-data"><?=$passenger->email_address?></td>
        </tr>
				<? foreach( $passenger->forms->form as $form ): ?>
					<? if($form->type == 'checkbox') { ?>
						<? if($site->exists($form->answer)) { $form->answer = 'yes'; } else { $form->answer = 'no'; } ?>
 					<? } ?>
          <tr>
            <td class="rezgo-td-label"><?=$form->question?>:</td>
            <td class="rezgo-td-data"><?=$form->answer?></td>
          </tr>
				<? endforeach; ?>
        <tr>
          <td class="rezgo-td-label">&nbsp;</td>
          <td class="rezgo-td-data">&nbsp;</td>
        </tr>        
			<? endforeach; ?>
    </table>    
    

  </div>
  <? } ?>
  
  <div class="rezgo-content-row">
  	<h2>Customer Service</h2>
    
    <table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
      <tr>
        <td class="rezgo-td-label">Cancellation Policy</td>
        <td class="rezgo-td-data">
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
          or commencement time, you will be charged a 100% cancellation fee.
          <br />
        <? } else { ?>
          <? if($site->exists($item->details->cancellation)) { ?>
            <?=$item->details->cancellation?>
            <br />
          <? } ?>
        <? } ?>
        
        View terms and conditions: <strong>http://<?=$site->getDomain()?>.rezgo.com/terms</strong>
        </td>
      </tr>
      
			<? if($site->exists($booking->rid)) { ?>
      <tr>
        <td class="rezgo-td-label">Customer Service</td>
        <td class="rezgo-td-data">
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
          <?=$company->company_name?><br />
          <?=$company->address_1?> <?=$company->address_2?><br />
          <?=$company->city?>, <? if($site->exists($company->state_prov)) { ?><?=$company->state_prov?>, <? } ?><?=$site->countryName($company->country)?><br />
          <?=$company->postal_code?><br />
          <?=$company->phone?><br />
          <?=$company->email?>
          <? if($site->exists($company->tax_id)) { ?>
          <br />
          <br />
          <?=$company->tax_id?>
          <? } ?>

        <? } ?>

        </td>
      </tr>
      
      <? } ?>

      <tr>
        <td class="rezgo-td-label">Service Provided By</td>
        <td class="rezgo-td-data">
				<? $company = $site->getCompanyDetails($booking->cid); ?>
        <?=$company->company_name?><br />
        <?=$company->address_1?> <?=$company->address_2?><br />
        <?=$company->city?>, <? if($site->exists($company->state_prov)) { ?><?=$company->state_prov?>, <? } ?><?=$site->countryName($company->country)?><br />
        <?=$company->postal_code?><br />
        <?=$company->phone?><br />
        <?=$company->email?>
        <? if($site->exists($company->tax_id)) { ?>
        <br />
        Tax ID: <?=$company->tax_id?>
        <? } ?>
        </td>
      </tr>
    </table>    
  	 
  </div>
	
	<? endforeach; ?>

</div><!-- .container -->

</body>
</html>