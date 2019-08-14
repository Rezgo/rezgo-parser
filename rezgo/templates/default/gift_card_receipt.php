<?php 
$res = $site->getGiftCard($_SESSION['GIFT_CARD_KEY']); 
$card = $res->card;
$billing = $card->billing;
$company = $site->getCompanyDetails();
$site->readItem($company);
$debug = 0;
?>

<div class="container-fluid rezgo-container">
	<div class="row">
		<div class="col-xs-12">
			<div class="rezgo-gift-card-container gift-card-receipt">
				<div class="master-heading">
					<h3 style="margin-bottom:0px;"><span>PURCHASE COMPLETE</span></h3>
				</div>

				<div class="rezgo-gift-card-group balance-section clearfix">
					<div class="rezgo-gift-card-head">
						<h3><span class="text-info">Gift Card Receipt</span></h3>
						<p>Thank you for your gift card purchase.	The gift card has been sent to <span><?php echo $card->email?>.</span></p>
					</div>
					
					<div class="clearfix">
					<table class="table table-bordered table-striped">
						<tr>
							<td>Date</td>
							<td><?php echo date((string) $company->date_format, (int) $card->created)?></td>
						</tr>

						<tr>
							<td>Sent To</td>
							<td><?php echo $card->first_name?> <?php echo $card->last_name?> <?php echo (($card->sent->to) ? '('.$card->sent->to.')' : '')?></td>
						</tr>

						<tr>
							<td>Value</td>
							<td><?php echo $site->formatCurrency((float) $card->amount)?></td>
						</tr>
						<?php if((string) $card->sent->message) { ?>
						<tr>
							<td>Message</td>
							<td><?php echo nl2br((string)$card->sent->message)?></td>
						</tr>
						<?php } ?>
					</table>
					</div>
				</div>

				<hr>

				<div class="rezgo-gift-card-group balance-section clearfix">
					<div class="rezgo-gift-card-head">
						<h3><span class="text-info">Billing Information</span></h3>
					</div>

					<div class="clearfix">
					<table class="table table-bordered table-striped">
						<?php if ($billing->first_name) { ?>
							<tr>
								<td>FirstName</td>
								<td><?php echo $billing->first_name?></td>
							</tr>
						<?php } ?>

						<?php if ($billing->last_name) { ?>
							<tr>
								<td>Last Name</td>
								<td><?php echo $billing->last_name?></td>
							</tr>
						<?php } ?>

						<?php if ($billing->address_1) { ?>
							<tr>
								<td>Address</td>
								<td><?php echo $billing->address_1?></td>
							</tr>
						<?php } ?>

						<?php if ($billing->address_2) { ?>
							<tr>
								<td>Address 2</td>
								<td><?php echo $billing->address_2?></td>
							</tr>
						<?php } ?>

						<?php if ($billing->city) { ?>
							<tr>
								<td>City</td>
								<td><?php echo $billing->city?></td>
							</tr>
						<?php } ?>

						<?php if ($billing->state) { ?>
							<tr>
								<td>Prov/State</td>
								<td><?php echo $billing->state?></td>
							</tr>
						<?php } ?>

						<?php if ($billing->country) { ?>
							<tr>
								<td>Country</td>
								<td>
									<?php foreach ($site->getRegionList() as $iso => $name) { ?>
										<?php if ($iso == $billing->country) { ?>
											<?php echo ucwords($name)?>
										<?php } ?>
									<?php } ?>
								</td>
							</tr>
						<?php } ?>

						<?php if ($billing->postal) { ?>
							<tr>
								<td>Postal Code/ZIP</td>
								<td><?php echo $billing->postal?></td>
							</tr>
						<?php } ?>

						<?php if ($billing->email) { ?>
							<tr>
								<td>Email</td>
								<td><?php echo $billing->email?></td>
							</tr>
						<?php } ?>

						<?php if ($billing->phone) { ?>
							<tr>
								<td>Phone</td>
								<td><?php echo $billing->phone?></td>
							</tr>
						<?php } ?>
					</table>
					</div>

					<div class="rezgo-company-info">
						<p>
							<span>Only one gift card may be used per order.</span>

							<br/>

							<a 
							href="javascript:void(0);"
							onclick="javascript:window.open('/terms',null,'width=800,height=600,status=no,toolbar=no,menubar=no,location=no,scrollbars=1');"
							>Click here to view the terms and conditions.</a>
						</p>

						<br/>

						<h3 id="rezgo-receipt-head-provided-by">
							<span>Valid At</span>
						</h3>

						<address>
							<?php $company = $site->getCompanyDetails($booking->cid); ?>
							<strong><?php echo $company->company_name?></strong><br />
							<?php echo $company->address_1?><?php if($site->exists($company->address_2)) { ?>, <?php echo $company->address_2?><?php } ?>
							<br />
							<?php echo $company->city?>,
							<?php if ($site->exists($company->state_prov)) { ?><?php echo $company->state_prov?>, <?php } ?>
							<?php echo $site->countryName($company->country)?><br />
							<?php echo $company->postal_code?><br />
							<?php echo $company->phone?><br />
							<?php echo $company->email?>
							<?php if ($site->exists($company->tax_id)) { ?><br />Tax ID: <?php echo $company->tax_id?><?php } ?>
						</address>
					</div>
				</div>
			</div>
		</div>

		<?php if ($debug) { ?>
			<div class="col-xs-12">
				<pre><?php var_dump($card); ?></pre>
			</div>
		<?php } ?>
	</div>	
</div>

<?php unset($_SESSION['GIFT_CARD_KEY']); ?>