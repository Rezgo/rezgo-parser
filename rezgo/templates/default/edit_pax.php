<?php
	/* edit_pax.php?id=' + order_item + '&order_id=' + cart_id + '&date=' + book_date, */
	$option = $site->getTours('t=uid&q='.$_REQUEST['id'].'&d='.$_REQUEST['date'].'&file=edit_pax');
	$site->readItem($option[0]);
	$order_id = $_REQUEST['order_id'];
	$cart = $site->getCart();
	// non-open date date_selection elements
	$date_types = array('always', 'range', 'week', 'days', 'single');
?>

<div class="rezgo-edit-pax-content">
	<script>
		var fields_<?php echo $order_id?> = new Array();
		var required_num_<?php echo $order_id?> = 0;

		function isInt(n) {
			 return n % 1 === 0;
		}

		// validate form data
		function check_pax_<?php echo $order_id?>() {
			var err;
			var count = 0;
			var required = 0;

			for(v in fields_<?php echo $order_id?>) {
				// total number of spots
				count += $('#' + v).val() * 1;

				// has a required price point been used
				if(fields_<?php echo $order_id?>[v] && $('#' + v).val() >= 1) { required = 1; }
			}

			if(count == 0 || !count) {
				err = 'Please enter the number you would like to book.';
			} else if(required_num_<?php echo $order_id?> > 0 && required == 0) {
				err = 'At least one marked ( * ) price point is required to book.';
			} else if(!isInt(count)) {
				err = 'Please enter a whole number. No decimal places allowed.';
			} else if(count < <?php echo $option[0]->per?>) {
				err = '<?php echo $option[0]->per?> minimum required to book.';
			} else if(count > <?php echo $option[0]->date->availability?>) {
				err = 'There is not enough availability to book ' + count;
			} else if(count > 250) {
				err = 'You can not book more than 250 spaces in a single booking.';
			}

			if(err) {
				$('#error_text_<?php echo $order_id?>').html(err);
				$('#error_text_<?php echo $order_id?>').slideDown().delay(2000).slideUp('slow');
				return false;
			}
		}
	</script>

	<?php if ($option[0]->date->availability != 0) { ?>

	<form class="rezgo-order-form clearfix" name="rezgo-edit-pax-<?php echo $order_id?>" id="rezgo-edit-pax-<?php echo $order_id?>" action="<?php echo $site->base?>/order" target="rezgo_content_frame">
		<input type="hidden" name="edit[<?php echo $order_id?>][uid]" value="<?php echo $option[0]->uid?>" />
		<input type="hidden" name="edit[<?php echo $order_id?>][date]" value="<?php echo $_REQUEST['date']?>" />

		<?php if(!$site->getCartState()) { // for no-cart requests, we want to make sure we clear the cart ?>
			<input type="hidden" name="order" value="clear" />
		<?php } ?>

		<?php /*if($_COOKIE['rezgo_promo']) { ?><input type="hidden" name="promo" value="<?php echo $_COOKIE['rezgo_promo']?>"><?php } */ ?>
		<?php /*if($_COOKIE['rezgo_refid_val']) { ?><input type="hidden" name="refid" value="<?php echo $_COOKIE['rezgo_refid_val']?>"><?php } */ ?>

		<?php $prices = $site->getTourPrices($option[0]); ?>

		<?php if($site->getTourRequired()) { ?>
			<span class="rezgo-memo">At least one marked ( <em><i class="fa fa-asterisk"></i></em> ) price point is required to book.</span>
		<?php } ?>

		<?php if($option[0]->per > 1) { ?>
			<span class="rezgo-memo">At least <?php echo $option[0]->per?> are required to book.</span>
		<?php } ?>

		<div class="clearfix">
			<span>&nbsp;</span>
		</div>

		<?php 
		$total_required = 0;

		foreach( $prices as $price ) {
			/* 
			There is a mismatch between labels between cart and getTourPrices()
			Consider fixing in the class instead
			*/

			if (in_array( $price->name, array('adult', 'child', 'senior'))) {
				$price_name = 'price_'.$price->name;
			} else {
				$price_name = $price->name;
			}
		?>

		<script>fields_<?php echo $order_id?>['<?php echo $price->name?>_<?php echo $order_id?>'] = <?php echo (($price->required) ? 1 : 0)?>;</script>

		<div class="form-group row">
			<div class="col-md-2 col-xs-4">
				<input type="number" min="0" name="edit[<?php echo $order_id?>][<?php echo $price->name?>_num]" value="<?php echo (string) $cart[$order_id]->{$price->name.'_num'}?>" id="<?php echo $price->name?>_<?php echo $order_id?>" size="3" class="form-control input-sm" />
			</div>

			<label for="<?php echo $price->name?>" class="col-xs-10 control-label rezgo-label-margin">
				<span>x&nbsp;&nbsp;<?php echo $price->label?><?php echo (($price->required && $site->getTourRequired()) ? ' <em><i class="fa fa-asterisk"></i></em>' : '')?><span class="rezgo-pax-price">&nbsp;(
			<?php if($site->exists($cart[$order_id]->date->base_prices->{$price_name})) { ?><span class="discount"><?php echo $site->formatCurrency($cart[$order_id]->date->base_prices->{$price_name})?></span><?php } ?>
			<?php echo $site->formatCurrency($cart[$order_id]->date->{$price_name})?> )</span></span>
			</label>
		</div><!-- // form-group -->

		<?php 
			if ($price->required) { $total_required++; }
		} // end foreach( $site->getTourPrices()	
		?>

		<script>required_num_<?php echo $order_id?> = <?php echo $total_required?>;</script>

		<div class="text-danger rezgo-option-error" id="error_text_<?php echo $order_id?>" style="display:none;"></div>

		<div class="form-group pull-right">
			<a data-toggle="collapse" data-target="#pax-edit-<?php echo $order_id?>">Cancel</a>
			<span>&nbsp;</span>
			<button type="submit" class="btn rezgo-btn-book" onclick="return check_pax_<?php echo $order_id?>();">
				<span>Update Booking</span>
			</button>
		</div>

		<br />
	</form>
	<?php } else { ?>
		<div class="rezgo-order-unavailable">
			<span>Sorry, there is no availability left for this option</span>
		</div>
	<?php } // end if ($option->date->availability != 0) ?>
</div>