<?php 
	// send the user home if they shouldn't be here
	if(!$trans_num) $site->sendTo($site->base."/order-not-found:empty");

	// unset promo session and cookie
	$site->resetPromoCode();

	// start a session so we can grab the analytics code
	session_start();

	$order_bookings = $site->getBookings('t=order_code&q='.$trans_num);

	if(!$order_bookings) $site->sendTo("/order-not-found:".$_REQUEST['trans_num']);

	// check and see if we want to be here or on the individual item
	// if we only have 1 item and the cart is off, forward them through
	if(!$site->getCartState() && count($order_bookings) == 1) {
		$site->sendTo($site->base.'/complete/'.$site->encode($order_bookings[0]->trans_num));
	}

	$company = $site->getCompanyDetails();
	$rzg_payment_method = 'None';
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

		<?php if($_SESSION['REZGO_CONVERSION_ANALYTICS']) { ?>
			<div class="row alert alert-success">
				<span id="rezgo-booking-added">YOUR BOOKING HAS BEEN ADDED</span>
			</div>
		<?php } ?>

		<!-- // breadcrumb-->

		<div class="row rezgo-confirmation-head">
			<h3>Your order <?php echo $trans_num?> contains <?php echo count($order_bookings)?> booking<?php echo ((count($order_bookings) != 1) ? 's' : '')?></h3>

			<div class="center-block">
				
				<button class="btn btn-lg rezgo-btn-print" onclick="window.open('<?php echo $site->base?>/complete/<?php echo $site->encode($trans_num)?>/print', '_blank'); return false;">
					<span><i class="fa fa-print fa-lg"></i>&nbsp;Print Order</span>
				</button>
				
			</div>
		</div>

		<?php $n = 1; ?>

		<?php foreach($order_bookings as $booking ) { ?>
			<?php 
			$item = $site->getTours('t=uid&q='.$booking->item_id, 0); 
			$share_url = urlencode('https://'.$_SERVER['HTTP_HOST'].$site->base.'/details/'.$item->com.'/'.$site->seoEncode($item->item));
			?>

			<?php $site->readItem($booking); ?>

			<div class="row rezgo-form-group rezgo-confirmation">
				<div class="rezgo-booking-status col-md-4 col-sm-12">
					<?php if($booking->status == 1 OR $booking->status == 4) { ?>
						<p class="rezgo-status-complete"><i class="fa fa-check fa-lg"></i>&nbsp;Booking Complete</p>
					<?php } ?>

					<?php if($booking->status == 2) { ?>
						<p class="rezgo-status-pending"><i class="fa fa-check fa-lg"></i>&nbsp;Booking Pending</p>
					<?php } ?>

					<?php if($booking->status == 3) { ?>
						<p class="rezgo-status-cancel"><i class="fa fa-times fa-lg"></i>&nbsp;Booking Cancelled</p>
					<?php } ?>

					<?php if($site->exists($booking->paypal_owed)) { ?>
						<?php $company_paypal = $site->getCompanyPaypal(); ?>

						<div>
							<form role="form" class="form-inline" method="post" action="<?php echo REZGO_DIR?>/php_paypal/process.php">
								<span id="paypal_owing">Total&nbsp;Owing&nbsp;:&nbsp;<?php echo $site->formatCurrency(((float)$booking->overall_total - (float)$booking->overall_paid))?></span>
								<input type="hidden" name="firstname" id="firstname" value="<?php echo $booking->first_name?>" />
								<input type="hidden" name="lastname" id="lastname" value="<?php echo $booking->last_name?>" />
								<input type="hidden" name="address1" id="address1" value="<?php echo $booking->address_1?>" /> 
								<input type="hidden" name="address2" id="address2" value="<?php echo $booking->address_2?>" />
								<input type="hidden" name="city" value="<?php echo $booking->city?>" />
								<input type="hidden" name="state" value="<?php echo $booking->stateprov?>" />
								<input type="hidden" name="country" value="<?php echo $site->countryName($booking->country)?>" />
								<input type="hidden" name="zip" value="<?php echo $booking->postal_code?>" />
								<input type="hidden" name="email" id="email" value="<?php echo $booking->email_address?>" />
								<input type="hidden" name="phone" id="phone" value="<?php echo $booking->phone_number?>" />
								<input type="hidden" name="item_name" id="item_name" value="<?php echo $booking->tour_name?> - <?php echo $booking->option_name?>" />
								<input type="hidden" name="encoded_transaction_id" id="encoded_transaction_id" value="<?php echo $site->encode($booking->trans_num)?>" />
								<input type="hidden" name="item_number" id="item_number" value="<?php echo $booking->trans_num?>" />
								<input type="hidden" name="amount" id="amount" value="<?php echo $booking->paypal_owed?>" />
								<input type="hidden" name="quantity" id="quantity" value="1" />	
								<input type="hidden" name="business" value="<?php echo $company_paypal?>" />
								<input type="hidden" name="currency_code" value="<?php echo $site->getBookingCurrency()?>" />
								<input type="hidden" name="domain" value="<?php echo $site->getDomain()?>.rezgo.com" />
								<input type="hidden" name="cid" value="<?php echo REZGO_CID?>" />
								<input type="hidden" name="paypal_signature" value="" />
								<input type="hidden" name="base_url" value="rezgo.com" />
								<input type="hidden" name="cancel_return" value="https://<?php echo $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']?>" />
								<input type="image"	class="paypal_button" name="submit_image" src="<?php echo $site->path;?>/img/logos/paypal_pay.png" />
							</form>
						</div>
					<?php } ?>
				</div><!-- // .rezgo-booking-status -->

				
					<div class="col-md-8 col-sm-12">
						<div class="rezgo-booking-share">
							<span id="rezgo-social-links">			 
								<a href="javascript:void(0);" title="Share this on Twitter" id="social_twitter" onclick="window.open('http://twitter.com/share?text=<?php echo urlencode('I found this great thing to do! "'.$item->item.'"')?>&url=<?php echo $share_url?><?php if($site->exists($site->getTwitterName())) { ?>&via=<?php echo $site->getTwitterName()?>'<?php } else {?>'<?php } ?>,'tweet','location=1,status=1,scrollbars=1,width=500,height=350');"><i class="fa fa-twitter-square" id="social_twitter_icon">&nbsp;</i></a>
								<a href="javascript:void(0);" title="Share this on Facebook" id="social_facebook" onclick="window.open('http://www.facebook.com/sharer.php?u=<?php echo $share_url?>&t=<?php echo urlencode($item->item)?>','facebook','location=1,status=1,scrollbars=1,width=600,height=400');"><i class="fa fa-facebook-square" id="social_facebook_icon">&nbsp;</i></a>
							</span>
						</div>
					</div>
				

				<div class="clearfix"></div>

				<h3><?php echo $booking->tour_name?>&nbsp;(<?php echo $booking->option_name?>)</h3>

				<div class="col-md-4 col-sm-12">
					<table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
						<tr>
							<td class="rezgo-td-label">Transaction&nbsp;#:</td>
							<td class="rezgo-td-data"><?php echo $booking->trans_num?></td>
						</tr>

						<?php if((string) $booking->date != 'open') { ?>
							<tr>
								<td class="rezgo-td-label">Date:</td>
								<td class="rezgo-td-data"><?php echo date((string) $company->date_format, (int) $booking->date)?>
								<?php if($booking->time != '') { ?> at <?php echo $booking->time?><?php } ?>
								</td>
							</tr>
							<?php } else { ?>
							<?php if ($booking->time) { ?>
								<tr id="rezgo-receipt-booked-for">
									<td class="rezgo-td-label"><span>Time:</span></td>
									<td class="rezgo-td-data"><span><?php echo $booking->time?></span></td>
								</tr>
							<?php } ?>
						<?php } ?>

						<?php if(isset($booking->expiry)) { ?>
							<tr>
								<td class="rezgo-td-label">Expires:</td>
								<?php if((int) $booking->expiry !== 0) { ?>
								<td class="rezgo-td-data"><?php echo date((string) $company->date_format, (int) $booking->expiry)?>
								<?php } else { ?>
								<td class="rezgo-td-data">Never
								<?php } ?>
								</td>
							</tr>
						<?php } ?>
	
						<?php if($site->exists($booking->trigger_code)) { ?>
							<tr id="rezgo-order-promo">
								<td class="rezgo-td-label"><span>Promotional&nbsp;Code:</span></td>
								<td class="rezgo-td-data"><?php echo $booking->trigger_code?></td>
							</tr>
						<?php } ?>
	
						<?php if($site->exists($booking->refid)) { ?>
							<tr id="rezgo-order-refid">
								<td class="rezgo-td-label">Ref<span class="hidden-xs">erral</span>&nbsp;ID</td>
								<td class="rezgo-td-data"><?php echo $booking->refid?></td>
							</tr>
						<?php } ?>
					</table>		

					<a href="<?php echo $site->base?>/complete/<?php echo $site->encode($booking->trans_num)?>" class="btn btn-lg rezgo-btn-default btn-block">View Details</a> 
          
					<?php $domain = "https://".$site->getDomain(); ?>
					<?php if( $booking->waiver == '2' ) {  ?>
              <?php 
							echo '<div class="rezgo-waiver-order">';
                $pax_signed = $pax_count = 0;
                foreach ($site->getBookingPassengers() as $passenger ) { 
                  if ($passenger->signed) $pax_signed++;
                  $pax_count++;
                }
                echo '<span>' . $pax_signed . ' of ' . $pax_count . ' passengers have signed waivers.</span>';
                if ($pax_signed != $pax_count) { // hide if all waivers signed
                  echo '&nbsp;<a href="'.$domain.'.rezgo.com/waiver/'.$site->waiver_encode($booking->trans_num).'" class="btn btn-lg rezgo-waiver-btn btn-block"><span><i class="fa fa-pencil-square-o"></i>&nbsp;Sign waivers</span></a>';
                }
							echo '</div>';
              ?>
          <?php } ?> 
          

					<div class="clearfix">&nbsp;</div>

					<?php if($booking->status == 1 OR $booking->status == 4) { ?>
						<?php $domain = $site->getDomain(); ?>

							<a href="https://<?php echo $domain?>.<?php echo $role?>rezgo.com/tickets/<?php echo $site->encode($booking->trans_num)?>" class="btn btn-lg rezgo-btn-print-voucher btn-block" target="_blank">Print <?php echo ((string) $booking->ticket_type == 'ticket') ? 'Tickets' : 'Voucher' ?></a>
					<?php } ?>
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

							<?php foreach($site->getBookingPrices() as $price) { ?>
								<tr>
									<td class="text-right"><?php echo $price->label?></td>
									<td class="text-right"><?php echo $price->number?></td>
									<td class="text-right">
									<?php if($site->exists($price->base)) { ?>
										<span class="discount"><?php echo $site->formatCurrency($price->base)?></span>
									<?php } ?>
									&nbsp;<?php echo $site->formatCurrency($price->price)?></td>
									<td class="text-right"><?php echo $site->formatCurrency($price->total)?></td>
								</tr>
							<?php } ?>

							<tr>
								<td colspan="3" class="text-right"><strong>Subtotal</strong></td>
								<td class="text-right"><?php echo $site->formatCurrency($booking->sub_total)?></td>
							</tr>

							<?php foreach($site->getBookingLineItems() as $line) { ?>
								<?php
									unset($label_add);
									if($site->exists($line->percent) || $site->exists($line->multi)) {
										$label_add = ' (';
											if($site->exists($line->percent)) $label_add .= $line->percent.'%';
											if($site->exists($line->multi)) {
												if(!$site->exists($line->percent)) $label_add .= $site->formatCurrency($line->multi);
		
												if($site->exists($line->meta)) {
													$pax_totals = array( 'adult_num' => 'price_adult', 'child_num' => 'price_child', 'senior_num' => 'price_senior', 'price4_num' => 'price4', 'price5_num' => 'price5', 'price6_num' => 'price6', 'price7_num' => 'price7', 'price8_num' => 'price8', 'price9_num' => 'price9');
													$line_pax = 0;
													foreach ($pax_totals as $p_num => $p_rate) {
														if ( (int) $booking->{$p_num} > 0 && ((float) $booking->price_range->date->{$p_rate} > (float) $line->meta)) {
															$line_pax += (int) $booking->{$p_num};
														}
													}
													$label_add .= ' x '.$line_pax;
												} else {
													$label_add .= ' x '.$booking->pax;
												}
			
											}
										$label_add .= ')';	
									}
								?>

								<?php if( $site->exists($line->amount) ) { ?>
  								<tr>
  									<td colspan="3" class="text-right"><strong><?php echo $line->label?><?php echo $label_add?></strong></td>
  									<td class="text-right"><?php echo $site->formatCurrency($line->amount)?></td>
  								</tr>
								<?php } ?>
							<?php } ?>

							<?php foreach($site->getBookingFees() as $fee){ ?>
								<?php if($site->exists($fee->total_amount)){ ?>
									<tr>
										<td colspan="3" class="text-right"><strong><?php echo $fee->label?></strong></td>
										<td class="text-right"><?php echo $site->formatCurrency($fee->total_amount)?></td>
									</tr>
								<?php } ?>
							<?php } ?>

							<tr>
								<td colspan="3" class="text-right"><strong>Total</strong></td>
								<td class="text-right"><strong><?php echo $site->formatCurrency($booking->overall_total)?></strong></td>
							</tr>

							<?php if($site->exists($booking->deposit)) { ?>
								<tr>
									<td colspan="3" class="text-right"><strong>Deposit</strong></td>
									<td class="text-right"><strong><?php echo $site->formatCurrency($booking->deposit)?></strong></td>
								</tr>
							<?php } ?>

							<?php if($site->exists($booking->overall_paid)) { ?>
								<tr>
									<td colspan="3" class="text-right"><strong>Total Paid</strong></td>
									<td class="text-right"><strong><?php echo $site->formatCurrency($booking->overall_paid)?></strong></td>
								</tr>
								<tr>
									<td colspan="3" class="text-right"><strong>Total&nbsp;Owing</strong></td>
									<td class="text-right"><strong><?php echo $site->formatCurrency(((float)$booking->overall_total - (float)$booking->overall_paid))?></strong></td>
								</tr>
							<?php } ?>
						</table>
					</table>
				</div>
			</div><!-- //  tour confirm --> 

			<?php 
			$cart_total += ((float)$booking->overall_total); 
			$cart_owing += ((float)$booking->overall_total - (float)$booking->overall_paid); 
			?>

			<?php if($booking->payment_method != 'None') {
				$rzg_payment_method = $booking->payment_method;
			} ?>
		<?php } ?>

		<div class="row rezgo-form-group rezgo-confirmation">
			<div class="col-md-6 col-xs-12 rezgo-billing-confirmation">
				<h3 class="text-info">Billing Information</h3>

				<table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
					<tr>
						<td class="rezgo-td-label">Name:</td>
						<td class="rezgo-td-data"><?php echo $booking->first_name?> <?php echo $booking->last_name?></td>
					</tr>
					<tr>
						<td class="rezgo-td-label">Address:</td>
						<td class="rezgo-td-data"><?php echo $booking->address_1?><?php if($site->exists($booking->address_2)) { ?>, <?php echo $booking->address_2?><?php } ?><?php if($site->exists($booking->city)) { ?>, <?php echo $booking->city?><?php } ?><?php if($site->exists($booking->stateprov)) { ?>, <?php echo $booking->stateprov?><?php } ?><?php if($site->exists($booking->postal_code)) { ?>, <?php echo $booking->postal_code?><?php } ?>, <?php echo $site->countryName($booking->country)?></td>
					</tr>
					<tr>
						<td class="rezgo-td-label">Phone&nbsp;No.:</td>
						<td class="rezgo-td-data"><?php echo $booking->phone_number?></td>
					</tr>
					<tr>
						<td class="rezgo-td-label">Email:</td>
						<td class="rezgo-td-data"><?php echo $booking->email_address?></td>
					</tr>
				</table>
			</div>

			<div class="col-md-6 col-xs-12 rezgo-payment-confirmation">
				<h3 class="text-info">Your Payment Information</h3>

				<table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
					<tr>
						<td class="rezgo-td-label">Total&nbsp;Order:</td>
						<td class="rezgo-td-data"><?php echo $site->formatCurrency($cart_total)?></td>
					</tr>
					<tr>
						<td class="rezgo-td-label">Total&nbsp;Owing:</td>
						<td class="rezgo-td-data"><?php echo $site->formatCurrency($cart_owing)?></td>
					</tr>
					<?php if($cart_total > 0) { ?>
						<tr>
							<td class="rezgo-td-label">Payment&nbsp;Method:</td>
							<td class="rezgo-td-data"><?php echo $rzg_payment_method?></td>
						</tr>
					<?php } ?>
				</table>
			</div>
		</div><!-- //  rezgo-confirmation --> 
	</div><!-- //  .jumbotron --> 
</div><!-- //  .rezgo-container --> 

<?php if (DEBUG) { ?><pre><?var_dump($booking);?></pre><?php } ?>

<?php if($_SESSION['REZGO_CONVERSION_ANALYTICS']) { 
	echo $_SESSION['REZGO_CONVERSION_ANALYTICS'];
	unset($_SESSION['REZGO_CONVERSION_ANALYTICS']);
} ?>
