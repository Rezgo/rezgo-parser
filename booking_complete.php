<?php 
	// This is the booking receipt page
	
	require('rezgo/include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite();
		
	// grab and decode the trans_num if it was set
	$trans_num = $site->decode($_REQUEST['trans_num']);
	
	// send the user home if they shoulden't be here
	if(!$trans_num) $site->sendTo($site->base."/booking-not-found");
	// start a session so we can grab the analytics code
	session_start();
	
	// empty the cart
	$site->clearCart();
	
	$site->setMetaTags('<meta name="robots" content="noindex, nofollow">');
	
?>

<?=$site->getTemplate('frame_header')?>


<? if(strlen($trans_num) == 16) { ?>
	
	<?=$site->getTemplate('booking_order')?>
  
  <?
		$ga_add_transacton = "
			ga('ecommerce:addTransaction', {
				'id': '$trans_num',
				'affiliation': '$c',
				'revenue': '$cart_total',
				'currency': '".$site->getBookingCurrency()."'
			});
		";
	?>
	
<? } else { ?>

	<?=$site->getTemplate('booking_complete')?>

<? } ?>

<?=$site->getTemplate('frame_footer')?>