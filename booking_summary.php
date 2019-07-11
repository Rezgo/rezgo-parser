<?php 
	// any new page must start with the page_header, it will include the correct files
	// so that the rezgo parser classes and functions will be available to your templates
	
	require('rezgo/include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite();

	if(!$site->getBookings('q='.$_REQUEST['trans_num'])) { $site->sendTo("/booking-not-found:".$_REQUEST['trans_num']); } 
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />

	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex, nofollow">
	<title>Booking Summary for <?php echo $_REQUEST['trans_num']?></title>

	<!-- Bootstrap CSS -->
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css" rel="stylesheet">

	<!-- Font awesome --> 
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
	<!--[if IE 7]>
		<link href="<?php echo $site->path;?>/css/font-awesome-ie7.css" rel="stylesheet">
	<![endif]-->	

	<!-- Rezgo stylesheet -->
	<link href="<?php echo $site->path;?>/css/rezgo.css?v=<?php echo REZGO_VERSION?>" rel="stylesheet">

	<?php if($site->exists($site->getStyles())) { ?>
	<style>
		<?php echo $site->getStyles();?>
	</style>
	<?php } ?>

	<?php $company = $site->getCompanyDetails(); ?>
</head>
<body>
<?php foreach( $site->getBookings($_REQUEST['trans_num']) as $booking ) { ?>
	<?php $site->readItem($booking); ?>

	<div class="container" id="rezgo-booking-summary">
		<h2>Booking details for <?php echo $site->getCompanyName($booking->cid)?></h2>

		<h3>
			<span><?php echo $booking->tour_name?> - <?php echo $booking->option_name?></span>
			<span><?php if ((string) $booking->date != 'open') { ?>
				<div class="rezgo-add-cal">
					<div class="rezgo-add-cal-cell">
						<a href="https://feed.rezgo.com/b/<?php echo $booking->trans_num?>"><i class="fa fa-calendar"></i>&nbsp;Add to my calendar</a>
					</div>
				</div>
			<?php } ?></span>
		</h3>

		<small class="rezgo-booked-on">booked on <?php echo date((string) $company->date_format, (int) $booking->date_purchased_local)?> / local time</small>

		<table class="table-responsive">
			<table class="table table-bordered table-striped rezgo-billing-cart">
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
					<?
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

					<tr>
						<td colspan="3" class="text-right"><strong><?php echo $line->label?><?php echo $label_add?></strong></td>
						<td class="text-right"><?php echo $site->formatCurrency($line->amount)?></td>
					</tr>
				<?php } ?>

				<?php foreach( $site->getBookingFees() as $fee ): ?>
					<?php if( $site->exists($fee->total_amount) ): ?>
						<tr>
							<td colspan="3" class="text-right"><strong><?php echo $fee->label?></strong></td>
							<td class="text-right"><?php echo $site->formatCurrency($fee->total_amount)?></td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>

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
		
		<table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
			<tr>
				<td class="rezgo-td-label">Transaction</td>
				<td class="rezgo-td-data"><?php echo $booking->trans_num?></td>
			</tr>
			<?php if ((string) $booking->date != 'open') { ?>
			<tr>
				<td class="rezgo-td-label">Booked<span class="hidden-xs">&nbsp;For</span></td>
				<td class="rezgo-td-data"><?php echo date((string) $company->date_format, (int)$booking->date)?>
				<?php if ($booking->time != '') { ?> at <?php echo $booking->time?><?php } ?>
				</td>
			</tr>
				<?php } else { ?>
				<?php if ($booking->time) { ?>
					<tr>
						<td class="rezgo-td-label">Time</td>
						<td class="rezgo-td-data"><?php echo $booking->time?></td>
					</tr>
			<?php } ?>
			<?php } ?>
			<?php if (isset($booking->expiry)) { ?>
			<tr>
				<td class="rezgo-td-label">Expires</td>
					<td class="rezgo-td-data">
				<?php if ((int) $booking->expiry !== 0) { ?>
							<span><?php echo date((string) $company->date_format, (int) $booking->expiry)?></span>
				<?php } else { ?>
							<span>Never</span>
				<?php } ?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td class="rezgo-td-label">Payment<span class="hidden-xs">&nbsp;Status</span></td>
				<td class="rezgo-td-data"><?php if($booking->status == 1) { ?>RECEIVED<?php } ?><?php if($booking->status == 2) { ?>PENDING<?php } ?><?php if($booking->status == 3) { ?>CANCELLED<?php } ?></td>
			</tr>
			<?php if($site->exists($booking->trigger_code)) { ?>
			<tr>
				<td class="rezgo-td-label">Promo<span class="hidden-xs">tional Code</span></td>
				<td class="rezgo-td-data"><?php echo $booking->trigger_code?></td>
			</tr>
			<?php } ?>
			<?php if($site->exists($booking->refid)) { ?>
			<tr>
				<td class="rezgo-td-label">Ref<span class="hidden-xs">erral</span>&nbsp;ID</td>
				<td class="rezgo-td-data"><?php echo $booking->refid?></td>
			</tr>
			<?php } ?>
		</table>

		<div class="clearfix">&nbsp;</div>
		
		<fieldset>
			<legend>Owner</legend>
	
			<table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
				<?php if($booking->user_name) { ?>
				<tr>
					<td class="rezgo-td-label">User</td>
					<td class="rezgo-td-data"><?php echo $booking->user_name?></td>
				</tr>
				<?php } ?>
				
				<?php if($booking->desk_name) { ?>
				<tr>
					<td class="rezgo-td-label">Desk</td>
					<td class="rezgo-td-data"><?php echo $booking->desk_name?></td>
				</tr>
				<?php } ?>
				
				<?php if($booking->location_name) { ?>
				<tr>
					<td class="rezgo-td-label">Location</td>
					<td class="rezgo-td-data"><?php echo $booking->location_name?></td>
				</tr>
				<?php } ?>
				
			</table>
			
		</fieldset>

		<div class="clearfix">&nbsp;</div>
		
		<fieldset>
			<legend>Billing Details</legend>
	
			<table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
				<tr>
					<td class="rezgo-td-label">Contact</td>
					<td class="rezgo-td-data"><?php echo $booking->first_name?> <?php echo $booking->last_name?></td>
				</tr>
				<tr>
					<td class="rezgo-td-label">Address</td>
					<td class="rezgo-td-data"><?php echo $booking->address_1?><?php if($site->exists($booking->address_2)) { ?>, <?php echo $booking->address_2?><?php } ?><?php if($site->exists($booking->city)) { ?>, <?php echo $booking->city?><?php } ?><?php if($site->exists($booking->stateprov)) { ?>, <?php echo $booking->stateprov?><?php } ?><?php if($site->exists($booking->postal_code)) { ?>, <?php echo $booking->postal_code?><?php } ?> <?php echo $site->countryName($booking->country)?></td>
				</tr>
				<tr>
					<td class="rezgo-td-label">Tel<span class="hidden-xs">ephone</span></td>
					<td class="rezgo-td-data"><?php echo $booking->phone_number?></td>
				</tr>
				<tr>
					<td class="rezgo-td-label">Email</td>
					<td class="rezgo-td-data"><?php echo $booking->email_address?></td>
				</tr>
			</table>
			
		</fieldset>

		<div class="clearfix">&nbsp;</div>

		<?php if($booking->overall_total > 0) { ?>
		
			<fieldset>
		
				<legend>Payment Details</legend>
	
				<table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
					<tr>
						<td class="rezgo-td-label">Payment<span class="hidden-xs">&nbsp;Method</span></td>
						<td class="rezgo-td-data"><?php echo $booking->payment_method?></td>
					</tr>
					<?php if($booking->payment_method == 'Credit Cards') { ?>
					<tr>
						<td class="rezgo-td-label">Card&nbsp;Number</td>
						<td class="rezgo-td-data"><?php echo $booking->card_number?></td>
					</tr>
					<?php } ?>
					<?php if($site->exists($booking->payment_method_add->label)) { ?>
					<tr>
						<td class="rezgo-td-label"><?php echo $booking->payment_method_add->label?></td>
						<td class="rezgo-td-data"><?php echo $booking->payment_method_add->value?></td>
					</tr>
					<?php } ?>
				</table>
				
			</fieldset>

			<div class="clearfix">&nbsp;</div>
		<?php } ?>

		<?php if(count($site->getBookingForms()) > 0) { ?>
		
			<fieldset>
				<legend>Additional Information</legend>
	
				<table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
					<?php foreach( $site->getBookingForms() as $form ) { ?>
						<?php if(in_array($form->type, array('checkbox','checkbox_price'))) { ?>
							<?php if($site->exists($form->answer)) { $form->answer = 'yes'; } else { $form->answer = 'no'; } ?>
						<?php } ?>
						<tr>
							<td class="rezgo-td-label"><?php echo $form->question?></td>
							<td class="rezgo-td-data"><?php echo $form->answer?></td>
						</tr>
					<?php } ?>
				</table>
			</fieldset>

			<div class="clearfix">&nbsp;</div>
		<?php } ?>

		<?php if(count($site->getBookingPassengers()) > 0) { ?>
			<fieldset>
				<legend>Group Details</legend>
	
				<table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
					<?php foreach( $site->getBookingPassengers() as $passenger ) { ?>
						<tr>
							<td class="rezgo-td-label"><?php echo $passenger->label?> <?php echo $passenger->num?></td>
							<td class="rezgo-td-data"><?php echo $passenger->first_name?> <?php echo $passenger->last_name?></td>
						</tr>
						<?php if ((string) $passenger->phone_number != '') { ?>
						<tr>
							<td class="rezgo-td-label">Phone Number</td>
							<td class="rezgo-td-data"><?php echo $passenger->phone_number?></td>
						</tr>
						<?php } ?>
						<?php if ((string) $passenger->email_address != '') { ?>
						<tr>
							<td class="rezgo-td-label">Email</td>
							<td class="rezgo-td-data"><?php echo $passenger->email_address?></td>
						</tr>
						<?php } ?>
						<?php foreach( $passenger->forms->form as $form ) { ?>
							<?php if(in_array($form->type, array('checkbox','checkbox_price'))) { ?>
								<?php if($site->exists($form->answer)) { $form->answer = 'yes'; } else { $form->answer = 'no'; } ?>
							<?php } ?>
							<tr>
								<td class="rezgo-td-label"><?php echo $form->question?></td>
								<td class="rezgo-td-data"><?php echo $form->answer?></td>
							</tr>
						<?php } ?>
					<?php } ?>	
				</table>
			</fieldset>

			<div class="clearfix">&nbsp;</div>
		<?php } ?>
	</div>
<?php } ?>
</body>
</html>