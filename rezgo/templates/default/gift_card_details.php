<?php 
// send the user home if they shoulden't be here
if (!$_REQUEST['card']) {
	$site->sendTo($site->base."/gift-card");
}
$company = $site->getCompanyDetails();
$site->readItem($company);
$res = $site->getGiftCard($_REQUEST['card']); 
$card = $res->card;
if (!$card) {
	$site->sendTo($site->base."/gift-not-found");
}
$billing = $card->billing;
?>

<div class="container-fluid rezgo-container">
	<div class="row">
		<div class="col-xs-12">
			<div id="rezgo-gift-card-details" class="rezgo-gift-card-container">
				<div class="master-heading">
					<h3><span>Gift Card Details</span></h3>
					<p><span>Click the button below to print your gift card.</span></p>
					<center>
						<a class="btn btn-lg rezgo-btn-print" href="<?php echo $site->path;?>/gift-print/<?php echo $_REQUEST['card']?>" target="_blank">
							<i class="fa fa-print fa-lg"></i>
							<span>&nbsp;PRINT GIFT CARD</span>
						</a>
					</center>
				</div>

				<div class="rezgo-gift-card-group clearfix">
					<div class="row">
						<div class="col-xs-12 col-sm-6">
							<h5><span class="card-company"><?php echo ucfirst($company->company_name)?> Gift Card</span></h5>
							<h5><span class="card-company">https://<?php echo (($company->primary_domain) ? $company->primary_domain : $site->getDomain().'.rezgo.com')?></span></h5>
							<h3><span class="card-amount text-info"><?php echo $site->formatCurrency((float) $card->amount)?></span></h3>
							<h4><span class="card-number"><?php echo $site->cardFormat($card->number)?></span></h4>
							<?php if ((int) $card->expires !== 0) { ?>
							<h4><span class="card-expiry">
								<span>Expires: </span>
									<span><?php echo date((string) $company->date_format, (int) $card->expires)?></span>
								</span></h4>
							<?php } ?>
							<h4><span class="card-holder-name"><?php echo ucfirst($card->first_name)?> <?php echo ucfirst($card->last_name)?></span></h4>
						</div>
						<div class="col-xs-12 col-sm-6">
							<div class="rezgo-voucher-barcode clearfix">
								<img src="/barcode.php?barcode=<?php echo $card->number?>&width=370&height=150" alt="barcode" />
							</div>
						</div>
					</div>
				</div>

				<hr>

				<div class="rezgo-gift-card-group clearfix">

					<div class="rezgo-gift-card-head">
						<h3><span class="text-info">Transactions</span></h3>
					</div>


							<div class="table-responsive">
								<table class="table table-bordered table-striped">
									<tbody>
										<?php 
											foreach ($card->transactions->transaction as $trans) {
												$action = str_replace('[', '<span class="text-primary">', str_replace(']', '</span>', $trans->action));
												$change = (float) $trans->change;
												if($change > 0) { 
													$change = '<span class="text-success">+&nbsp;'.$site->formatCurrency(preg_replace("/[^0-9.]/", "", $change)).'</span>'; 
												}
												elseif($change < 0) { 
													$change = '<span class="text-danger">-&nbsp;'.$site->formatCurrency(preg_replace("/[^0-9.]/", "", $change)).'</span>'; 
												} else { 
													$change = '0'; 
												}
										?>
										<tr>
											<td><?php echo date((string) $company->date_format, (int) $trans->date)?></td>
											<td><?php echo $action?></td>
											<td align="right"><?php echo $change?></td>
											<td align="right"><?php echo $site->formatCurrency((float) $trans->balance)?></td>
										</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>


					<div class="rezgo-company-info">
						<p>
							<span>Only one gift card may be used per order.</span>

							<br/>

							<a href="javascript:void(0);" onclick="javascript:window.open('/terms',null,'width=800,height=600,status=no,toolbar=no,menubar=no,location=no,scrollbars=1');">Click here to view the terms and conditions.</a>
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
							<?php if($site->exists($company->state_prov)) { ?><?php echo $company->state_prov?>, <?php } ?>
							<?php echo $site->countryName($company->country)?><br />
							<?php echo $company->postal_code?><br />
							<?php echo $company->phone?><br />
							<?php echo $company->email?>
							<?php if($site->exists($company->tax_id)) { ?><br />Tax ID: <?php echo $company->tax_id?><?php } ?>
						</address>
					</div>
				</div>

				<?php if (DEBUG) { ?>
					<pre><?php var_dump($card); ?></pre>
				<?php } ?>
			</div>
		</div>
	</div>
</div>