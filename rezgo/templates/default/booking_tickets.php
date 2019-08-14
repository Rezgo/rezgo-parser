<?php $split = explode(",", $_REQUEST['trans_num']); ?>

<?php foreach((array) $split as $v) { ?>
	<?php
	$trans_num = $site->decode($v);
	if(!$trans_num) $site->sendTo("/");
	$booking = $site->getBookings($trans_num, 0);
	$checkin = (string) $booking->checkin;
	$availability_type = (string) $booking->availability_type;
	$checkin_state = $booking->checkin_state;
	$type = ((string) $booking->ticket_type != '' ? $booking->ticket_type : 'voucher'); 
	?>

	<?php if($checkin && $availability_type != 'product') { ?>
		<?php $ticket_content = $site->getTicketContent($trans_num, 0); ?>

		<?php foreach($ticket_content->tickets as $ticket_list) { ?>
			<?php foreach ($ticket_list as $ticket) { ?>
				<?php 
					if($ticket == 'Already checked in') {
						$ticket = '<div style="padding-left:15px;">'.$ticket.'</div>';
					} 
				?>
				<?php echo $ticket?><br />
				<div class="h6 pull-right">
					<span class="rezgo-ticket-logo">Rezgo</span>
				</div>
				<div class="clearfix"></div>
				<hr class="rezgo-ticket-bottom" />
			<?php } ?>
		<?php } ?>
	<?php } elseif(!$checkin && $availability_type != 'product') { ?>
		<?php if ($booking->status == 3) { ?>
			<div class="col-xs-12 rezgo-print-hide"><br />Booking <strong><?php echo $trans_num?></strong> has been cancelled, ticket is not available.<br /><br /></div>
		<?php } else { ?>
			<div class="col-xs-12 rezgo-print-hide"><br /><?php echo ucwords($type)?> for Booking <strong><?php echo $trans_num?></strong> is not available until the booking has been confirmed.<br /><br /></div>
		<?php } ?>
    
		<div class="h6 pull-right"><span class="rezgo-ticket-logo">Rezgo</span></div>
	
	<?php } else { ?>
  
    <div class="col-xs-12 rezgo-print-hide"><br /><?php echo ucwords($type)?> is not available for product purchase <strong><?php echo $trans_num?></strong>.<br /><br /></div>
		<div class="h6 pull-right"><span class="rezgo-ticket-logo">Rezgo</span></div>
    
	<?php } ?>

	<div class="clearfix"></div>

	<?php if(count($split) > 1) { ?>
		<div class="col-xs-12" style="border-top:1px solid #CCC; page-break-after: always;"></div>
	<?php } ?>
<?php } ?>
