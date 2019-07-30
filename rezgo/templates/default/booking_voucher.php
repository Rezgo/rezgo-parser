<?php
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
      <link href="<?php echo $this->path?>/css/font-awesome-ie7.css" rel="stylesheet">
    <![endif]-->  
			
    <!-- Rezgo stylesheet -->
    <link href="<?php echo $site->path;?>/css/rezgo.css?v=<?php echo REZGO_VERSION?>" rel="stylesheet">
    
		<?php if($site->exists($site->getStyles())) { ?>
    <style>
      <?php echo $site->getStyles();?>
    </style>
    <?php } ?>
			
			<div id="rezgo-voucher-body">
		    <div class="container-fluid">
		    			
				<?php $item = $site->getTours('t=uid&q='.$booking->item_id, 0); ?>
		    
		    <?php $site->readItem($booking) ?>
		    
		    <?php $company = $site->getCompanyDetails(); ?>
          	      
		      <div class="max-800">
		      	
			      <div class="row">
			      	
			        <div class="col-xs-12 col-sm-4 text-center pull-right">
			          <div id="rezgo-voucher-qr">
                <img src="https://chart.googleapis.com/chart?cht=qr&chs=200x200&chld=M|1&chl=http://checkin.rezgo.com/<?php echo $checkin?>" /></div>
			          <div id="rezgo-voucher-barcode">
                <img src="/barcode.php?barcode=<?php echo $checkin?>&width=250&height=70" alt="barcode" /></div>
			          <h4 id="rezgo-voucher-num"><?php echo $checkin?></h4>
			        </div>
			        
			        <div class="col-xs-12 col-sm-8 pull-left">
			          <h3 id="rezgo-voucher-company">Booking Voucher for <?php echo $company->company_name?>	</h3>
			          <h1 id="rezgo-voucher-tour"><?php echo $booking->tour_name?></h1>
			          <h3 id="rezgo-voucher-option"><?php echo $booking->option_name?><span class="small">&nbsp;&nbsp;(SKU: <?php echo $item->uid?>)</span></h3>
                <?php if ((string) $booking->date != 'open') { ?>
			          <h4 id="rezgo-voucher-booking-date"><label>Booked for Date:</label>&nbsp;<?php echo date((string) $company->date_format, (int) $booking->date)?></h4>
                <?php } ?>
			          <?php if ($booking->time != '') { ?>
                <h4 id="rezgo-voucher-booking-time"><label>Time:</label>&nbsp;<?php echo $booking->time?></h4>
                <?php } ?>
			          <h4 id="rezgo-voucher-created-date"><label>Issued Date:</label>&nbsp;<?php echo date((string) $company->date_format, (int) $booking->date_purchased_local)?> (local time)</h4>
                <?php if (isset($booking->expiry)) { ?>
			          <h4 id="rezgo-voucher-expiry"><label>Expires:</label>&nbsp;
									<?php 
                  if ((int) $booking->expiry !== 0) {
                    echo date((string) $company->date_format, (int) $booking->expiry);
                  } else {
                    echo 'Never';
                  }
                  ?>
                </h4>
                <?php } ?>
                
			          <h4 id="rezgo-voucher-transnum"><label>Booking Reference:</label>&nbsp;<?php echo $booking->trans_num?></h4>
			          <h4 id="rezgo-voucher-contact"><label>Booking Contact:</label>&nbsp;<?php echo $booking->first_name?> <?php echo $booking->last_name?></h4>
			          <h4 id="rezgo-voucher-paxcount"><label>Booking Pax:</label>&nbsp;
								<?php foreach( $site->getBookingCounts() as $count ) { ?>
			          <?php if($n) { echo ', '; } else { $n = 1; } ?><?php echo $count->num?> x <?php echo $count->label?>
			          <?php } ?>
			          </h4>
								<?php if($site->exists($booking->trigger_code)) { ?>
			            <h4 id="rezgo-voucher-promocode"><label class="rezgo-promo-label"><span>Promotional Code:</span></label>&nbsp;<?php echo $booking->trigger_code?></h4>
			          <?php } ?>
			          <p id="rezgo-voucher-paxlist" class="rezgo-voucher-para">
                
								<?php foreach( $site->getBookingPassengers() as $passenger ) { ?>
			            <label><?php echo $passenger->label?> <?php echo $passenger->num?>:</label> <?php echo $passenger->first_name?> <?php echo $passenger->last_name?><br />
			          <?php } ?>
			          &nbsp;</p>
                <div id="rezgo-voucher-pickup" class="rezgo-voucher-para">
                  <label>Pickup/Departure:</label> <br />
                  <?php echo $item->details->pick_up?>
                </div>
                <div id="rezgo-voucher-dropoff" class="rezgo-voucher-para">
                  <label>Dropoff:</label> <br />
                  <?php echo $item->details->drop_off?>
                </div>
                <div id="rezgo-voucher-cancel" class="rezgo-voucher-para">
                  <label>Cancellation Policy:</label> <br />
                  <p>
                  <?php if($site->exists($booking->rezgo_gateway)) { ?>
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
                  <?php } else { ?>
                    <?php if($site->exists($item->details->cancellation)) { ?>
                      <?php echo htmlspecialchars_decode($item->details->cancellation)?>
                      <br />
                    <?php } ?>
                  <?php } ?>
                  View terms and conditions: <strong>https://<?php echo $site->getDomain()?>.rezgo.com/terms</strong>
                  <br /><br />
                  </p>
                </div>
			        </div>
			        
			        <div class="col-xs-12 col-sm-4 text-center pull-right">
			           
			          <br />
			           
								<?php if($site->exists($booking->rid)) { ?>
		            <div id="rezgo-voucher-customer-service">
		              <p><label>Customer Service Contact:</label><br />
		            
		              <?php if($site->exists($booking->rezgo_gateway)) { ?>
		                
		                <strong>Rezgo.com</strong><br />
		                Attn: Partner Bookings<br />
		                333 Brooksbank Avenue<br />
		                Suite 718<br />
		                North Vancouver, BC<br />
		                Canada V7J 3V8<br />
		                (604) 983-0083<br />
		                bookings@rezgo.com
		                
		              <?php } else { ?>
		                      
		                <?php $company = $site->getCompanyDetails('p'.$booking->rid); ?>
		                <strong><?php echo $company->company_name?></strong><br />
		                <?php echo $company->address_1?> <?php echo $company->address_2?><br />
		                <?php echo $company->city?>, <?php if($site->exists($company->state_prov)) { ?><?php echo $company->state_prov?>, <?php } ?><?php echo $site->countryName($company->country)?><br />
		                <?php echo $company->postal_code?><br />
		                <?php echo $company->phone?><br />
		                <?php echo $company->email?>
		    
		              <?php } // end if($site->exists($booking->rezgo_gateway)) ?>
		        
		              </p>
		            </div>  
		            <?php } // end if($site->exists($booking->rid))  ?>
		            <div id="rezgo-voucher-service">
                  <p><label>Service Provided By:</label><br />
                  <?php $company = $site->getCompanyDetails($booking->cid); ?>
                  <strong><?php echo $company->company_name?></strong><br />
                  <?php echo $company->address_1?> <?php echo $company->address_2?><br />
                  <?php echo $company->city?>, <?php if($site->exists($company->state_prov)) { ?><?php echo $company->state_prov?>, <?php } ?><?php echo $site->countryName($company->country)?><br />
                  <?php echo $company->postal_code?><br />
                  <?php echo $company->phone?><br />
                  <?php echo $company->email?>            
                  </p>
		            </div>
		          </div>
			        
			        
			      </div> <!-- end:1st row-->
		      
		      </div>
					
		    </div><!-- end .container -->
		  </div><!-- end #rezgo-voucher-body -->
			
		<?php
		
			echo $site->getVoucherFooter();
		
		} else {
			
			if ($booking->status == 3) {
				echo '<div class="col-xs-12"><br />Booking '.$trans_num.' has been cancelled, voucher is not available.<br /><br /></div>';
			} else {
				echo '<div class="col-xs-12"><br />Voucher for Booking '.$trans_num.' is not available until the booking has been confirmed.<br /><br /></div>';
			}
			
		}
		
?>
<div class="h6 pull-right"><a href="http://www.rezgo.com/features/online-booking/" title="Powering Tour and Activity Businesses Worldwide" style="color:#333; text-decoration:none;"><span style="display: inline-block; width: 65px; text-indent: -9999px; margin-left: 4px; background:url(<?php echo $site->path;?>/img/rezgo-logo.svg) no-repeat; background-size: contain;">Rezgo</span></a></div>
<div class="clearfix"></div>

<?		
		if(count($split) > 1) {
			echo '<div class="col-xs-12" style="border-top:1px solid #CCC; page-break-after: always;"></div>';
		}

	}
		
?>

