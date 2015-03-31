<?
	$split = explode(",", $_REQUEST[trans_num]);

	foreach((array) $split as $v) {

		$trans_num = $site->decode($v);
		
		if(!$trans_num) $site->sendTo("/");
		
		$booking = $site->getBookings($trans_num, 0);
		
		$checkin = (string) $booking->checkin;
		$checkin_state = $booking->checkin_state;
		
		if($checkin) {	
		
			echo $site->getVoucherHeader();
		
		?>
    
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
			
			<div id="rezgo-voucher-body">
		    <div class="container-fluid">
		    			
				<? $item = $site->getTours('t=uid&q='.$booking->item_id, 0); ?>
		    
		    <? $site->readItem($booking) ?>
		    
		    <? $company = $site->getCompanyDetails(); ?>
          	      
		      <div class="max-800">
		      	
			      <div class="row">
			      	
			        <div class="col-xs-12 col-sm-4 text-center pull-right">
			          <div id="rezgo-voucher-qr">
                <img src="https://chart.googleapis.com/chart?cht=qr&chs=200x200&chld=M|1&chl=http://checkin.rezgo.com/<?=$checkin?>" /></div>
			          <div id="rezgo-voucher-barcode">
                <img src="/barcode.php?barcode=<?=$checkin?>&width=250&height=70" alt="barcode" /></div>
			          <h4 id="rezgo-voucher-num"><?=$checkin?></h4>
			        </div>
			        
			        <div class="col-xs-12 col-sm-8 pull-left">
			          <h3 id="rezgo-voucher-company">Booking Voucher for <?=$company->company_name?>	</h3>
			          <h1 id="rezgo-voucher-tour"><?=$booking->tour_name?></h1>
			          <h3 id="rezgo-voucher-option"><?=$booking->option_name?><span class="small">&nbsp;&nbsp;(SKU: <?=$item->uid?>)</span></h3>
			          <h4 id="rezgo-voucher-booking-date"><label>Booked for Date:</label>&nbsp;<?=date((string) $company->date_format, (int) $booking->date)?></h4>
			          <h4 id="rezgo-voucher-created-date"><label>Issued Date:</label>&nbsp;<?=date((string) $company->date_format, (int) $booking->date_purchased_local)?> (local time)</h4>
			          <h4 id="rezgo-voucher-transnum"><label>Booking Reference:</label>&nbsp;<?=$booking->trans_num?></h4>
			          <h4 id="rezgo-voucher-contact"><label>Booking Contact:</label>&nbsp;<?=$booking->first_name?> <?=$booking->last_name?></h4>
			          <h4 id="rezgo-voucher-paxcount"><label>Booking Pax:</label>&nbsp;      
								<? foreach( $site->getBookingCounts() as $count ) { ?>
			          <? if($n) { echo ', '; } else { $n = 1; } ?><?=$count->num?> x <?=$count->label?>
			          <? } ?>
			          </h4>
								<? if($site->exists($booking->trigger_code)) { ?>
			            <h4 id="rezgo-voucher-promocode"><label>Promotional Code:</label>&nbsp;<?=$booking->trigger_code?></h4>
			          <? } ?>
			          <p id="rezgo-voucher-paxlist">
								<? foreach( $site->getBookingPassengers() as $passenger ) { ?>
			            <label><?=$passenger->label?> <?=$passenger->num?>:</label> <?=$passenger->first_name?> <?=$passenger->last_name?><br />
			          <? } ?>
			          &nbsp;</p>
                <div id="rezgo-voucher-pickup">
                  <p><label>Pickup/Departure:</label></p>
                  <p><?=htmlspecialchars_decode($item->details->pick_up)?></p>
                </div>
                <div id="rezgo-voucher-dropoff">
                  <p><label>Dropoff:</label></p>
                  <p><?=htmlspecialchars_decode($item->details->drop_off)?></p>
                </div>
                <div id="rezgo-voucher-cancel">
                  <p><label>Cancellation Policy:</label></p>
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
                    or commencement time, you will be charged a 100% cancellation fee.
                    <br /><br />
                  <? } else { ?>
                    <? if($site->exists($item->details->cancellation)) { ?>
                      <?=htmlspecialchars_decode($item->details->cancellation)?>
                      <br />
                    <? } ?>
                  <? } ?>
                  View terms and conditions: <strong>http://<?=$site->getDomain()?>.rezgo.com/terms</strong>
                  <br /><br />
                  </p>
                </div>
			        </div>
			        
			        <div class="col-xs-12 col-sm-4 text-center pull-right">
			           
			          <br />
			           
								<? if($site->exists($booking->rid)) { ?>
		            <div id="rezgo-voucher-customer-service">
		              <p><label>Customer Service Contact:</label><br />
		            
		              <? if($site->exists($booking->rezgo_gateway)) { ?>
		                
		                <strong>Rezgo.com</strong><br />
		                Attn: Partner Bookings<br />
		                333 Brooksbank Avenue<br />
		                Suite 718<br />
		                North Vancouver, BC<br />
		                Canada V7J 3V8<br />
		                (604) 983-0083<br />
		                bookings@rezgo.com
		                
		              <? } else { ?>
		                      
		                <? $company = $site->getCompanyDetails('p'.$booking->rid); ?>
		                <strong><?=$company->company_name?></strong><br />
		                <?=$company->address_1?> <?=$company->address_2?><br />
		                <?=$company->city?>, <? if($site->exists($company->state_prov)) { ?><?=$company->state_prov?>, <? } ?><?=$site->countryName($company->country)?><br />
		                <?=$company->postal_code?><br />
		                <?=$company->phone?><br />
		                <?=$company->email?>
		    
		              <? } // end if($site->exists($booking->rezgo_gateway)) ?>
		        
		              </p>
		            </div>  
		            <? } // end if($site->exists($booking->rid))  ?>
		            <div id="rezgo-voucher-service">
                  <p><label>Service Provided By:</label><br />
                  <? $company = $site->getCompanyDetails($booking->cid); ?>
                  <strong><?=$company->company_name?></strong><br />
                  <?=$company->address_1?> <?=$company->address_2?><br />
                  <?=$company->city?>, <? if($site->exists($company->state_prov)) { ?><?=$company->state_prov?>, <? } ?><?=$site->countryName($company->country)?><br />
                  <?=$company->postal_code?><br />
                  <?=$company->phone?><br />
                  <?=$company->email?>            
                  </p>
		            </div>
		          </div>
			        
			        
			      </div> <!-- end:1st row-->
		      
		      </div>
					
		    </div><!-- end .container -->
		  </div><!-- end #rezgo-voucher-body -->
			
		<?
		
			echo $site->getVoucherFooter();
		
		} else {
			
			if ($booking->status == 3) {
				echo '<div class="col-xs-12"><br />Booking '.$trans_num.' has been cancelled. This action cannot be undone. No voucher will be available for this booking.<br /><br /></div>';
			} else {
				echo '<div class="col-xs-12"><br />Voucher for Booking '.$trans_num.' is not available until the booking has been confirmed.<br /><br /></div>';
			}
			
		}
		
?>
<div class="h6 pull-right"><a href="http://www.rezgo.com/features/online-booking/" title="Powering Tour and Activity Businesses Worldwide" style="color:#333; text-decoration:none;">Online bookings powered by <span style="display: inline-block; width: 65px; text-indent: -9999px; margin-left: 4px; background:url(<?=$site->path?>/img/rezgo-logo.svg) no-repeat; background-size: contain;">Rezgo</span></a></div>
<div class="clearfix"></div>

<?		
		if(count($split) > 1) {
			echo '<div class="col-xs-12" style="border-top:1px solid #CCC; page-break-after: always;"></div>';
		}

	}
		
?>

