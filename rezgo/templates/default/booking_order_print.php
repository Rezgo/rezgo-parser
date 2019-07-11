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
		$site->sendTo($site->base.'/complete/'.$site->encode($order_bookings[0]->trans_num).'/print');
	}
	
	$company = $site->getCompanyDetails();

	$rzg_payment_method = 'None';
?>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex, nofollow">
	<title>Booking - <?php echo $trans_num?></title>
	
	<!-- Bootstrap CSS -->
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css" rel="stylesheet">
	
	<!-- Font awesome --> 
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
	<!--[if IE 7]><link href="<?php echo $this->path?>/css/font-awesome-ie7.css" rel="stylesheet"><![endif]-->

	
	<!-- Rezgo stylesheet -->
	<link href="<?php echo $site->path;?>/css/rezgo.css?v=<?php echo REZGO_VERSION?>" rel="stylesheet">
	
	<?php if($site->exists($site->getStyles())) { ?>
		<style><?php echo $site->getStyles();?></style>
	<?php } ?>
	
</head>
<body>
	<div class="container-fluid rezgo-container">
		<h2 id="rezgo-order-head">Your order <?php echo $trans_num?> contains <?php echo count($order_bookings)?> booking<?php echo ((count($order_bookings) != 1) ? 's' : '')?></h2>

		<?php $n = 1; ?>

		<?php foreach( $order_bookings as $booking ) { ?>

			<?php 
			$item = $site->getTours('t=uid&q='.$booking->item_id, 0); 
			$share_url = urlencode('https://'.$_SERVER['HTTP_HOST'].$site->base.'/details/'.$item->com.'/'.$site->seoEncode($item->item));
			?>

			<?php $site->readItem($booking); ?>

			<div class="row rezgo-form-group rezgo-confirmation"> 
				<div class="clearfix"></div>

				<h3><?php echo $booking->tour_name?>&nbsp;(<?php echo $booking->option_name?>)</h3>

				<div class="col-md-4 col-sm-12">
					<table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
						<tr>
							<td class="rezgo-td-label">Transaction&nbsp;#:</td>
							<td class="rezgo-td-data"><?php echo $booking->trans_num?></td>
						</tr>

						<?php if ((string) $booking->date != 'open') { ?>
							<tr>
								<td class="rezgo-td-label">Date:</td>
								<td class="rezgo-td-data"><?php echo date((string) $company->date_format, (int) $booking->date)?>
								<?php if ($booking->time != '') { ?> at <?php echo $booking->time?><?php } ?>
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

						<?php if (isset($booking->expiry)) { ?>
							<tr>
								<td class="rezgo-td-label">Expires:</td>
								<?php if ((int) $booking->expiry !== 0) { ?>
								<td class="rezgo-td-data"><?php echo date((string) $company->date_format, (int) $booking->expiry)?>
								<?php } else { ?>
								<td class="rezgo-td-data">Never
								<?php } ?>
								</td>
							</tr>
						<?php } ?>

						<tr>
							<td class="rezgo-td-label">Booking Status:</td>
							<td class="rezgo-td-data"><?php echo (($booking->status == 1) ? 'CONFIRMED' : '')?><?php echo (($booking->status == 2) ? 'PENDING' : '')?><?php echo (($booking->status == 3) ? 'CANCELLED' : '')?></td>
						</tr>

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
				</div>

				<div class="col-md-8 col-sm-12">
					<table class="table table-responsive table-bordered table-striped rezgo-billing-cart">
						<tr>
							<td class="text-right"><label>Type</label></td>
							<td class="text-right"><label class="hidden-xs">Qty.</label></td>
							<td class="text-right"><label>Cost</label></td>
							<td class="text-right"><label>Total</label></td>
						</tr>

						<?php foreach( $site->getBookingPrices() as $price ): ?>
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
						<?php endforeach; ?>

						<tr>
							<td colspan="3" class="text-right"><strong>Subtotal</strong></td>
							<td class="text-right"><?php echo $site->formatCurrency($booking->sub_total)?></td>
						</tr>

						<?php foreach( $site->getBookingLineItems() as $line ) { ?>
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
				</div>
			</div>

			<!-- //	tour confirm --> 
	
			<div style="page-break-after:always;"></div>

			<?php 
			$cart_total += ((float)$booking->overall_total); 
			$cart_owing += ((float)$booking->overall_total - (float)$booking->overall_paid); 

			if($booking->payment_method != 'None') {
				$rzg_payment_method = $booking->payment_method;
			} 
			?>
		<?php } ?>

		<div class="rezgo-content-row" id="rezgo-order-billing-info">
			<h2 id="rezgo-order-billing-info">Billing Information</h2>

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

		<div class="rezgo-content-row" id="rezgo-order-payment-info">
			<h2 id="rezgo-order-payment-head">Your Payment Information</h2>
	
			<table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
				<tr>
					<td class="rezgo-td-label">Total&nbsp;Order:</td>
					<td class="rezgo-td-data"><?php echo $site->formatCurrency($cart_total)?></td>
				</tr>
				<tr>
					<td class="rezgo-td-label">Total&nbsp;Owing:</td>
					<td class="rezgo-td-data"><?php echo $site->formatCurrency($cart_owing)?></td>
				</tr>
				<?php if ($cart_total > 0) { ?>
					<tr>
						<td class="rezgo-td-label">Payment&nbsp;Method:</td>
						<td class="rezgo-td-data"><?php echo $rzg_payment_method?></td>
					</tr>
				<?php } ?>
			</table>		
	
		</div>

		<div class="rezgo-content-row" id="rezgo-order-company-info">
			<h2 id="rezgo-order-company-head">Service Provided By</h2>
	
			<table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
				<tr>
					<td class="rezgo-td-label">Company:</td>
					<td class="rezgo-td-data"><?php echo $company->company_name?></td>
				</tr>
				<tr>
					<td class="rezgo-td-label">Address:</td>
					<td class="rezgo-td-data"><?php echo $company->address_1?> <?php echo $company->address_2?><br />
					<?php echo $company->city?>, <?php if($site->exists($company->state_prov)) { ?><?php echo $company->state_prov?>, <?php } ?><?php echo $site->countryName($company->country)?><br />
					<?php echo $company->postal_code?>
					</td>
				</tr>
				<tr>
					<td class="rezgo-td-label">Phone:</td>
					<td class="rezgo-td-data"><?php echo $company->phone?></td>
				</tr>
				<tr>
					<td class="rezgo-td-label">Email:</td>
					<td class="rezgo-td-data"><?php echo $company->email?></td>
				</tr>
				<?php if($site->exists($company->tax_id)) { ?>
					<tr>
						<td class="rezgo-td-label">Tax ID:</td>
						<td class="rezgo-td-data"><?php echo $company->tax_id?></td>
					</tr>
				<?php } ?>
			</table>
		</div>
	</div>
</body>
</html>