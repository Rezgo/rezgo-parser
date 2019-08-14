<?php $cart = $site->getCart(); ?>
<div class="col-xs-12 top-bar-order">
	<?php if($site->getCartState()) { ?>
		<?php if (!$cart) { ?>
			<div id="rezgo-cart-list" class="order-spacer">
				<h4>
					<i class="fa fa-shopping-cart"></i>
					<span class="hidden-xs">&nbsp;Your Order&nbsp;&ndash;</span>
					<span class="hidden-xs">There are</span>
					<span><span class="hidden-xs">n</span><span class="visible-xs-inline">&nbsp;N</span>o items in your order</span>
				</h4>
			</div>
		<?php } else { ?>
			<?php foreach ($cart as $order) {
				$site->readItem($order);
				$this_order_total += (float) $order->overall_total;
			}	?>

			<div id="rezgo-cart-list" class="order-spacer">
				<h4>
					<i class="fa fa-shopping-cart"></i>
					<span class="hidden-xs"> Your Order </span>
					<span>
						<a href="<?php echo $site->base?>/order" target="_parent">
							<span><?php echo count($cart).' item'.((count($cart) == 1) ? '' : 's')?></span>
							<span class="hidden-xs">in your order. </span>
							<span class="hidden-xs">Total:</span>
							<span><?php echo $site->formatCurrency($this_order_total)?></span>
						</a>
					</span>
				</h4>
			</div>
		<?php } ?>
	<?php } ?>

	<?php if (!$site->isVendor() && $site->getGateway()) { ?>
		<div id="rezgo-gift-link-use">
			<a class="rezgo-gift-link" href="<?php echo $site->base?>/gift-card" target="_parent">
				<i class="fa fa-gift"></i><span>&nbsp;Buy a gift card</span>
			</a>
		</div>
	<?php } ?>
</div>