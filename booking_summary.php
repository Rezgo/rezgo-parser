<?php 
	// any new page must start with the page_header, it will include the correct files
	// so that the rezgo parser classes and functions will be available to your templates
	
	require('rezgo/include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite();
	
	$company = $site->getCompanyDetails();
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		
	  <meta name="viewport" content="width=device-width, initial-scale=1.0">
	  <meta name="robots" content="noindex, nofollow">
	  <title>Booking Summary for <?=$_REQUEST[trans_num]?></title>
    
    <!-- Bootstrap CSS -->
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css" rel="stylesheet">
    
    <!-- Font awesome --> 
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    <!--[if IE 7]>
      <link href="<?=$site->path?>/css/font-awesome-ie7.css" rel="stylesheet">
    <![endif]-->  
	
	  <!-- Rezgo stylesheet -->
	  <link href="<?=$site->path?>/css/rezgo.css" rel="stylesheet">
    
		<? if($site->exists($site->getStyles())) { ?>
    <style>
      <?=$site->getStyles();?>
    </style>
    <? } ?>
	
	</head>
<body>

<? foreach( $site->getBookings($_REQUEST['trans_num']) as $booking ) { ?>

	<? $site->readItem($booking); ?>
	
	<div class="container" id="rezgo-booking-summary">	
		<h2>Booking details for <?=$site->getCompanyName($booking->cid)?></h2>
		<h3><?=$booking->tour_name?> - <?=$booking->option_name?>
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
    
    <table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
      <tr>
        <td class="rezgo-td-label">Trans<span class="hidden-xs">action</span>&nbsp;#:</td>
        <td class="rezgo-td-data"><?=$booking->trans_num?></td>
      </tr>
      <tr>
        <td class="rezgo-td-label">Booked<span class="hidden-xs">&nbsp;For</span>:</td>
        <td class="rezgo-td-data"><?=date((string) $company->date_format, (int)$booking->date)?></td>
      </tr>
      <tr>
        <td class="rezgo-td-label">Payment<span class="hidden-xs">&nbsp;Status</span>:</td>
        <td class="rezgo-td-data"><? if($booking->status == 1) { ?>RECEIVED<? } ?><? if($booking->status == 2) { ?>PENDING<? } ?><? if($booking->status == 3) { ?>CANCELLED<? } ?></td>
      </tr>
      <? if($site->exists($booking->trigger_code)) { ?>
      <tr>
        <td class="rezgo-td-label">Promo<span class="hidden-xs">tional Code</span>:</td>
        <td class="rezgo-td-data"><?=$booking->trigger_code?></td>
      </tr>
      <? } ?>
      <? if($site->exists($booking->refid)) { ?>
      <tr>
        <td class="rezgo-td-label">Ref<span class="hidden-xs">erral</span>&nbsp;ID:</td>
        <td class="rezgo-td-data"><?=$booking->refid?></td>
      </tr>
      <? } ?>
    </table>

    <div class="clearfix">&nbsp;</div>
		
		<h3>Billing Details</h3>
    
    <table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
      <tr>
        <td class="rezgo-td-label">Contact:</td>
        <td class="rezgo-td-data"><?=$booking->first_name?> <?=$booking->last_name?></td>
      </tr>
      <tr>
        <td class="rezgo-td-label">Address:</td>
        <td class="rezgo-td-data"><?=$booking->address_1?><? if($site->exists($booking->address_2)) { ?>, <?=$booking->address_2?><? } ?><? if($site->exists($booking->city)) { ?>, <?=$booking->city?><? } ?><? if($site->exists($booking->stateprov)) { ?>, <?=$booking->stateprov?><? } ?><? if($site->exists($booking->postal_code)) { ?>, <?=$booking->postal_code?><? } ?>, <?=$site->countryName($booking->country)?></td>
      </tr>
      <tr>
        <td class="rezgo-td-label">Tel<span class="hidden-xs">ephone</span>:</td>
        <td class="rezgo-td-data"><?=$booking->phone_number?></td>
      </tr>
      <tr>
        <td class="rezgo-td-label">Email:</td>
        <td class="rezgo-td-data"><?=$booking->email_address?></td>
      </tr>
    </table>    
    
    <div class="clearfix">&nbsp;</div>
    
		<? if($booking->overall_total > 0) { ?>
		<h3>Payment Details</h3>
    
    <table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
      <tr>
        <td class="rezgo-td-label">Payment<span class="hidden-xs">&nbsp;Method</span>:</td>
        <td class="rezgo-td-data"><?=$booking->payment_method?></td>
      </tr>
      <? if($booking->payment_method == 'Credit Cards') { ?>
      <tr>
        <td class="rezgo-td-label">Card&nbsp;Number:</td>
        <td class="rezgo-td-data"><?=$booking->card_number?></td>
      </tr>        
      <? } ?>
			<? if($site->exists($booking->payment_method_add->label)) { ?>
      <tr>
        <td class="rezgo-td-label"><?=$booking->payment_method_add->label?>:</td>
        <td class="rezgo-td-data"><?=$booking->payment_method_add->value?></td>
      </tr>        
      <? } ?>
    </table>
    
    <div class="clearfix">&nbsp;</div>
		<? } ?>
    
		<? if(count($site->getBookingForms()) > 0) { ?>
		
			<h3>Additional Information</h3>
      <table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
				<? foreach( $site->getBookingForms() as $form ) { ?>
          <? if($form->type == 'checkbox') { ?>
            <? if($site->exists($form->answer)) { $form->answer = 'yes'; } else { $form->answer = 'no'; } ?>
          <? } ?>
          <tr>
            <td class="rezgo-td-label"><?=$form->question?>:</td>
            <td class="rezgo-td-data"><?=$form->answer?></td>
          </tr>
        <? } ?>		
      </table>
      <div class="clearfix">&nbsp;</div>
    		
		<? } ?>
		
		<? if(count($site->getBookingPassengers()) > 0) { ?>
		
			<h3>Group Details</h3>
      <table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
				<? foreach( $site->getBookingPassengers() as $passenger ) { ?>
          <tr>
            <td class="rezgo-td-label"><?=$passenger->label?> <?=$passenger->num?>:</td>
            <td class="rezgo-td-data"><?=$passenger->first_name?> <?=$passenger->last_name?></td>
          </tr>
					<? foreach( $passenger->forms->form as $form ) { ?>
						<? if($form->type == 'checkbox') { ?>
							<? if($site->exists($form->answer)) { $form->answer = 'yes'; } else { $form->answer = 'no'; } ?>
	 					<? } ?>
            <tr>
              <td class="rezgo-td-label"><?=$form->question?>:</td>
              <td class="rezgo-td-data"><?=$form->answer?></td>
            </tr>            
					<? } ?>
				<? } ?>	
      </table>

      <div class="clearfix">&nbsp;</div>
	
		<? } ?>
	
	</div>
	
<? } ?>

</body>
</html>