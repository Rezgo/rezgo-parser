<?php 
$company = $site->getCompanyDetails();
// non-open date date_selection elements
$date_types = array('always', 'range', 'week', 'days', 'single'); // centralize this?

?>

<div id="rezgo-order-wrp" class="container-fluid rezgo-container">
	<div class="jumbotron rezgo-booking">
		<div id="rezgo-order-crumb" class="row hidden-xs">
			<ol class="breadcrumb rezgo-breadcrumb">
				<li id="rezgo-order-your-order" class="active">Your Order</li>
				<li id="rezgo-order-info">Guest Information</li>
				<li id="rezgo-order-billing">Billing Information</li>
				<li id="rezgo-order-confirmation">Confirmation</li>
			</ol>
		</div>

		<?php $cart = $site->getCart(); ?>

		<?php if(!$cart) { ?>
			<div class="rezgo-order-empty-cart-wrp">
				<div class="rezgo-form-group cart_empty">
					<p class="lead">
						<span class="hidden-xs">There are</span><span>&nbsp;<span class="hidden-xs">n</span><span class="visible-xs-inline">N</span>o items</span><span class="hidden-xs">&nbsp;in your order.</span>
					</p>
				</div>

				<div class="row" id="rezgo-booking-btn">
					<div class="col-md-4 col-xs-12 rezgo-btn-wrp">
						<a id="rezgo-order-book-more-btn" href="<?php echo $site->base?>/" class="btn rezgo-btn-default btn-lg btn-block">
							<span>Book More</span>
						</a>
					</div>
				</div>
			</div>
		<?php } else { ?>
			<?php $item_num = 0; ?>

			<?php foreach($cart as $item) { ?>
				<?php $site->readItem($item); ?>
				<div id="rezgo-order-item-<?php echo $item->uid?>">
					<div class="row rezgo-form-group rezgo-cart-title-wrp">
						<div class="col-lg-9 col-sm-8 col-xs-12 rezgo-cart-title">
							<h3>
								<a href="<?php echo $site->base?>/details/<?php echo $item->com?>/<?php echo $site->seoEncode($item->item)?>">
									<span><?php echo $item->item?> &mdash; <?php echo $item->option?></span>
								</a>
							</h3>

							<?php if(in_array((string) $item->date_selection, $date_types)) { ?>
								<?php $data_book_date = date("Y-m-d", (string)$item->booking_date); ?>

								<label>Date: <span class="lead"><?php echo date((string) $company->date_format, (string) $item->booking_date)?></span></label>
							<?php } else { ?>
								<?php $data_book_date = date('Y-m-d', strtotime('+1 day')); // open date ?>

								<label>Open Availability</label>
							<?php } ?>

							<?php if($item->discount_rules->rule) {
								echo '<br><label class="rezgo-booking-discount">
								<span class="rezgo-discount-span">Discount:</span> ';
								unset($discount_string);
								foreach($item->discount_rules->rule as $discount) {	
									$discount_string .= ($discount_string) ? ', '.$discount : $discount;
								}
								echo '<span class="rezgo-red">'.$discount_string.'</span>
								</label>';
							} ?>

							<div class="rezgo-order-memo rezgo-order-date-<?php echo date('Y-m-d', (string) $item->booking_date)?> rezgo-order-item-<?php echo $item->uid?>"></div>
						</div>

						<?php if($site->getCartState()) { ?>
							<div class="col-lg-3 col-sm-4 col-xs-12">
								<div class="col-sm-12 rezgo-btn-cart-wrp">
									<button type="button" data-toggle="collapse" class="btn rezgo-btn-default btn-block rezgo-pax-edit-btn" data-order-item="<?php echo $item->uid?>" data-order-com="<?php echo $item->com?>" data-cart-id="<?php echo $item->cartID?>" data-book-date="<?php echo $data_book_date;?>" data-target="#pax-edit-<?php echo $item->cartID?>">
										<span>Edit Guests</span>
									</button>
								</div>

								<div class="col-sm-12 rezgo-btn-cart-wrp">
									<button type="button" class="btn rezgo-btn-remove btn-block" onclick="top.location.href='<?php echo $site->base?>/order?edit[<?php echo $item->cartID?>][adult_num]=0';">
										<span>Remove<span class='hidden-xs'> from Order</span></span>
									</button>
								</div>
							</div>
						<?php } ?>
					</div>

					<div class="row rezgo-form-group rezgo-cart-table-wrp">
						<div class="col-xs-12">
							<table class="table table-bordered table-striped rezgo-billing-cart table-responsive">
								<tr class="rezgo-tr-head">
									<td class="text-right rezgo-billing-type"><label>Type</label></td>
									<td class="text-right rezgo-billing-qty"><label class="hidden-xs">Qty.</label></td>
									<td class="text-right rezgo-billing-cost"><label>Cost</label></td>
									<td class="text-right rezgo-billing-total"><label>Total</label></td>
								</tr>

								<?php foreach($site->getTourPrices($item) as $price) { ?>
									<?php if($item->{$price->name.'_num'}) { ?>
										<tr class="rezgo-tr-pax">
											<td class="text-right"><?php echo $price->label?></td>
											<td class="text-right"><?php echo $item->{$price->name.'_num'}?></td>
											<td class="text-right">
												<span>
													<?php if($site->exists($price->base)) { ?>
														<span class="discount"><?php echo $site->formatCurrency($price->base)?></span>
													<?php } ?>
													<?php echo $site->formatCurrency($price->price)?>
												</span>
											</td>
											<td class="text-right">
												<span>
													<?php if($site->exists($price->base)) { ?><span class="discount"></span><?php } ?>
													<?php echo $site->formatCurrency($price->total)?>
												</span>
											</td>
										</tr>
									<?php } ?>
								<?php } ?>

								<?php if((int) $item->availability < (int) $item->pax_count) { ?>
									<tr class="rezgo-tr-order-unavailable">
										<td colspan="4" class="rezgo-order-unavailable">
											<span data-toggle="tooltip" data-placement="top" title="This item has become unavailable after it was added to your order"><i class="fa fa-exclamation-triangle"></i> No Longer Available</span>
										</td>
									</tr>
								<?php } else { $cart_total += (float) $item->overall_total; } ?>

								<tr class="rezgo-tr-subtotal">
									<td colspan="3" class="text-right"><strong>Subtotal</strong></td>
									<td class="text-right"><?php echo $site->formatCurrency($item->sub_total)?></td>
								</tr>
                
                <?php $line_items = $site->getTourLineItems(); ?>
                
                <?php 	
									$pax_totals = array(
										'adult_num' => 'price_adult', 
										'child_num' => 'price_child', 
										'senior_num' => 'price_senior', 
										'price4_num' => 'price4', 
										'price5_num' => 'price5', 
										'price6_num' => 'price6', 
										'price7_num' => 'price7', 
										'price8_num' => 'price8', 
										'price9_num' => 'price9'
									);
                ?>
                
								<?php foreach($line_items as $line) { ?>
                  <?php unset($label_add); ?>

                  <?php if($site->exists($line->percent) || $site->exists($line->multi)) {
                    $label_add = ' (';

                    if($site->exists($line->percent)) {
                      $label_add .= $line->percent.'%';
                    }

                    if($site->exists($line->multi)) {
                      if(!$site->exists($line->percent)) {
                        $label_add .= $site->formatCurrency($line->multi);
                      }
											
											if($site->exists($line->meta)) {
												
												$line_pax = 0;
												foreach ($pax_totals as $p_num => $p_rate) {
													
													if ( (int) $item->{$p_num} > 0 && ((float) $item->date->{$p_rate} > (float) $line->meta)) {
														$line_pax += (int) $item->{$p_num};
													}
													
												}
												
												$label_add .= ' x '.$line_pax;
												
											} else {
												
												$label_add .= ' x '.$item->pax;
												
											}

                    }

                    $label_add .= ')';
                  } 
                    
                  ?>
                  
                  <tr class="rezgo-tr-subtotal">
                    <td colspan="3" class="text-right">
                    <?php if ($line->source == 'bundle') { ?>
                      <strong class="rezgo-line-bundle"><i class="fa fa-archive"></i>&nbsp;<?php echo $line->label?><?php echo $label_add?></strong>
                    <?php } else { ?>
                      <strong><?php echo $line->label?><?php echo $label_add?></strong>
                    <?php } ?>
                    </td>
                    <td class="text-right"><?php echo $site->formatCurrency($line->amount)?></td>
                  </tr>                  
                  
                <?php } ?>

								<tr class="rezgo-tr-subtotal">
									<td colspan="3" class="text-right"><strong>Total</strong></td>
									<td class="text-right"><?php echo $site->formatCurrency($item->overall_total)?></td>
								</tr>
                
							</table>

							<?php if($site->getTourRelated()) { ?>
								<div class="rezgo-related">
									<div class="rezgo-related-label"><span>Related products</span></div>

									<?php foreach($site->getTourRelated() as $related) { ?>
										<a href="<?php echo $site->base?>/details/<?php echo $related->com?>/<?php echo $site->seoEncode($related->name)?>" class="rezgo-related-link"><?php echo $related->name?></a><br>
									<?php } ?>
								</div>
							<?php } ?>

							<script>$(function(){$('.rezgo-order-unavailable span').tooltip()});</script>
						</div>
					</div>

					<div class="row rezgo-form-group-short">
						<div class="collapse rezgo-pax-edit-box" id="pax-edit-<?php echo $item->cartID?>"></div>
					</div>

					<div id="pax-edit-scroll-<?php echo $item->cartID?>" class="rezgo-cart-edit-wrp"></div>
				</div>

				<?php $item_num++; ?>
			<?php } ?>

			<?php if(!$site->isVendor()) { ?>
				<div id="rezgo-order-promo-code-wrp" class="row rezgo-form-group-short">
					<?php if(!$_SESSION['rezgo_promo']) { ?>
						<form class="form-inline" id="rezgo-promo-form" role="form" onsubmit="top.location.href = '<?php echo $site->base;?>/order?promo=' + $('#rezgo-promo-code').val(); return false;" target="rezgo_content_frame">
							<label for="rezgo-promo-code"><i class="fa fa-tags"></i>&nbsp;<span class="rezgo-promo-label"><span>Promo code</span></span></label>&nbsp;
							<div class="input-group">
							<input type="text" class="form-control" id="rezgo-promo-code" name="promo" placeholder="Enter Promo Code" value="<?php echo ($_SESSION['rezgo_promo'] ? $_SESSION['rezgo_promo'] : '')?>" />
							<div class="input-group-btn">
                <button class="btn rezgo-btn-default" type="submit">
                  <span>Apply</span>
                </button>
              </div>
							</div>
						</form>
					<?php } else { ?>
				            <div class="input-group">
				            	<label for="rezgo-promo-code"><i class="fa fa-tags"></i>&nbsp;<span class="rezgo-promo-label"><span>Promo code:</span></span></label>&nbsp;
						<span id="rezgo-promo-value"><?php echo $_SESSION['rezgo_promo']?></span>&nbsp;
						<button id="rezgo-promo-clear" class="btn rezgo-btn-default btn-sm" onclick="top.location.href='<?php echo $_SERVER['HTTP_REFERER']?>/?promo='" target="_parent">clear</button>
					        </div>        
				          
					<?php } ?>
				</div>
			<?php } ?>

			<div id="rezgo-order-grand-total-wrp" class="row rezgo-order-total-wrp">
				<div class="col-sm-12 col-sm-offset-0 col-md-6 col-md-offset-6 rezgo-order-total"><span class="hidden-xs">Current Order</span> Total <?php echo $site->formatCurrency($cart_total)?></div>
			</div>

			<div id="rezgo-booking-btn" class="row">
				<div class="col-md-4 col-sm-6 col-xs-12 rezgo-btn-wrp">
					<?php if($site->getCartState()) { ?>
						<a id="rezgo-order-book-more-btn" href="<?php echo $site->base?>/" class="btn rezgo-btn-default btn-lg btn-block">
							<span>Book More</span>
						</a>
					<?php } ?>
				</div>

				<div class="col-md-4 col-md-offset-4 col-sm-6 col-xs-12 rezgo-btn-wrp">
					<form role="form" action="<?php echo $site->base?>/book" target="rezgo_content_frame">
						<input type="hidden" name="order" value="clear">

						<?php $add_date_value = ''; ?>

						<?php foreach( $cart as $key => $item ) {
								echo '<input type="hidden" name="add['.$item->cartID.'][uid]" value="'.$item->uid.'">';
								if(in_array((string) $item->date_selection, $date_types)) {	
									$add_date_value = date("Y-m-d", (string)$item->booking_date);
								} else {
									$add_date_value = 'open'; // for open availability
								}

								echo '<input type="hidden" name="add['.$item->cartID.'][date]" value="'.$add_date_value.'">';

								foreach($site->getTourPrices($item) as $price) {
									if($item->{$price->name.'_num'}) {
										echo '<input type="hidden" name="add['.$item->cartID.']['.$price->name.'_num]" value="'.$item->{$price->name.'_num'}.'">';
									}
								}
							} ?>

						<?php /* if($_COOKIE['rezgo_promo']) { ?><input type="hidden" name="promo" value="<?php echo $_COOKIE['rezgo_promo']?>"><?php } */ ?>
						<?php /*if($_COOKIE['rezgo_refid_val']) { ?><input type="hidden" name="refid" value="<?php echo $_COOKIE['rezgo_refid_val']?>"><?php }*/ ?>
						<?php if($_COOKIE['show_standard']) { ?><input type="hidden" name="show_standard" value="<?php echo $_COOKIE['show_standard']?>"><?php } ?>

						<?php if(count($cart)) { ?>
							<button class="btn rezgo-btn-book btn-lg btn-block" type="submit">
                <span>Proceed to Check Out</span>
              </button>
						<?php } ?>
					</form>
				</div>
			</div>
		<?php } // end if(!$cart) ?>

		<?
			// build 'share this order' link
			$pax_nums = array ('adult_num', 'child_num', 'senior_num', 'price4_num', 'price5_num', 'price6_num', 'price7_num', 'price8_num', 'price9_num');

			$order_share_link = 'https://'.$_SERVER[HTTP_HOST].$site->base.'/order/?order=clear';	

			foreach($cart as $key => $item) {
				if(in_array((string) $item->date_selection, $date_types)) {	
					$order_share_date = date("Y-m-d", (string)$item->booking_date);
				} else {
					$order_share_date = 'open'; // for open availability
				}

				$order_share_link .= '&add['.$item->cartID.'][uid]='.$item->uid.'&add['.$item->cartID.'][date]='.$order_share_date;

				foreach($pax_nums as $pax) {	
					if($item->{$pax} != '') {
						$order_share_link .= '&add['.$item->cartID.']['.$pax.']='.$item->{$pax};
					}
				}
			}

			// finally, include promo if set
			if($_SESSION['rezgo_promo']) {
				$order_share_link .= '&promo='.$_SESSION['rezgo_promo'];
			}
		?>

		<?php if($site->getCartState() && count($cart)) { ?>
			
				<div id="rezgo-order-share-btn-wrp" class="clearfix">
					<a href="javascript:void(0);" id="rezgo-share-order" onclick="$('#rezgo-order-url').toggle('fade');">
						<span><i class="fa fa-share-alt-square"></i>&nbsp;share this order</span>
					</a>

					<input type="text" id="rezgo-order-url" style="display:none;" class="form-control" onclick="this.select();" value="<?php echo $order_share_link?>" />
				</div>
			
		<?php } ?>
	</div> <!-- // Jumbotron -->
</div><!-- // rezgo-container -->

<script>
	$(document).ready(function() {
		$('.rezgo-pax-edit-btn').each(function() {
			var order_com = $(this).attr('data-order-com'); 
			var order_item = $(this).attr('data-order-item');
			var cart_id = $(this).attr('data-cart-id'); 
			var book_date = $(this).attr('data-book-date'); 

			$.ajax({
				url: '<?php echo $site->base?>/edit_pax.php?com=' + order_com + '&id=' + order_item + '&order_id=' + cart_id + '&date=' + book_date,
				context: document.body,
				success: function(data) {
					$('#pax-edit-' + cart_id).html(data);
				}
			});
		});	

		$('.rezgo-pax-edit-btn').click(function() {
			var cart_id = $(this).attr('data-cart-id'); 
			var pax_edit_position = $('#pax-edit-scroll-' + cart_id).position();
			var pax_edit_scroll = Math.round(pax_edit_position.top);

			if('parentIFrame' in window) {
				setTimeout(function () {
					parentIFrame.scrollTo(0,pax_edit_scroll);
				}, 100);
			}
		});
	});
</script>