<?
	$cart = $site->getCart();
	
	if(!$cart) {
		
		echo '
		<div class="col-xs-12">
			<div id="rezgo-cart-list">
				<h4><i class="fa fa-shopping-cart"></i>&nbsp;<span class="hidden-xs">Your Order</span> &ndash;
				<span>There are no items in your order.</span></h4>
			</div>
		</div>';
		
	} else {
		
		foreach($cart as $order) {
			$site->readItem($order);
			$this_order_total += (float) $order->sub_total;
		}
				
?>
    
  <div class="col-xs-12">
  	<div id="rezgo-cart-list">
      <h4><i class="fa fa-shopping-cart"></i>&nbsp;<span class="hidden-xs">Your Order</span> &ndash;
      <span><a href="<?=$site->base?>/order"><?=count($cart).' item'.((count($cart) == 1) ? '' : 's')?> in your order. &nbsp;
      Total:&nbsp;<span><?=$site->formatCurrency($this_order_total)?></span></a></span></h4>
    </div>
  </div><!-- //  mini cart -->

<?
  } // end if(!$cart) 
?>