<?
	$company = $site->getCompanyDetails();
?>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex, nofollow">
	<title>Waiver</title>

	<!-- Bootstrap CSS -->
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css" rel="stylesheet">

	<!-- Font awesome --> 
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
	<!--[if IE 7]><link href="<?php echo $this->path?>/css/font-awesome-ie7.css" rel="stylesheet"><![endif]-->

	<!-- Rezgo stylesheet -->
	<link href="<?php echo $site->path;?>/css/rezgo.css?v=<?php echo REZGO_VERSION?>" rel="stylesheet">

	<?php if($site->exists($site->getStyles())) { ?><style><?php echo $site->getStyles();?></style><?php } ?>

</head>
<body>

<div class="container-fluid rezgo-container">
    
    <div class="row rezgo-form-group rezgo-waiver-main">
          
      <div id="rezgo-waiver-text" class="col-sm-12">

      <?php 
      
      $trans_num = '';
      
      if ($_REQUEST['trans_num'] && strlen($_REQUEST['trans_num']) >= 10) {
        
        $request_trans = $site->waiver_decode($_REQUEST['trans_num']);
        
        if (strlen($request_trans) === 10) { // booking
          
          $trans_num = $request_trans;
          
        } else { // booking pax
          
          $trans_part = explode('-', $request_trans);
          $trans_num = $trans_part[0];
          $pax_id = $trans_part[1];
          
        }
              
        foreach ($site->getBookings('q='.$trans_num) as $booking) { 
        
          $item = $site->getTours('t=uid&q='.$booking->item_id, 0);
          
          $site->readItem($booking);
          
          if ($booking->availability_type != 'product') {
            
            foreach ($site->getBookingPassengers() as $passenger ) { 
            
              if ($passenger->id == $pax_id) $pax_data = $passenger;
            
            }
        
          } // if ($booking->availability_type)
          
          ?>
          
          <h3>Waiver for <?php echo $booking->tour_name?>&nbsp;(<?php echo $booking->option_name?>)</h3>
          
          <table border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td class="rezgo-td-label">Transaction&nbsp;#:</td>
              <td class="rezgo-td-data"><?php echo $booking->trans_num?></td>
            </tr>
          
            <?php if((string) $booking->date != 'open') { ?>
              <tr>
                <td class="rezgo-td-label">Date:</td>
                <td class="rezgo-td-data"><?php echo date((string) $company->date_format, (int) $booking->date)?>
                <?php if($booking->time != '') { ?> at <?php echo $booking->time?><?php } ?>
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
          </table>			
          
          <hr />
          
        <?
      
        } // foreach $site->getBookings() 
        
        echo '<div id="pax_waiver_content">';
        echo $site->getWaiverContent($trans_num);
        echo '</div>';
            
      } else {
				
				if ($_REQUEST['trans_num'] && strlen($_REQUEST['trans_num']) < 10) {
					$item_id = $_REQUEST['trans_num'];
				} else {
					$item_id = '';
				}
              
        echo '<div id="pax_waiver_content">';
        echo $site->getWaiverContent($item_id);
        echo '</div>';
        
      } // if ($_REQUEST[trans_num])
      
      ?>
      
      </div>
      
      <div id="rezgo_waiver_wrap_print" class="col-sm-12">
      
      <hr />
        
        <div id="waiver_complete_print">
        
        	<?php if ($pax_data->signed) { ?>
          <div id="rezgo-waiver-success-print">
            <span>Thank you for signing. You entered the following information.</span>
          </div>
        	<?php } else { ?>
          <div id="rezgo-waiver-warn-print">
            <span><i class="fa fa-exclamation-triangle"></i> This waiver has not been signed. Please complete and sign waiver before printing.</span>
          </div>
        	<?php } ?>
          
          
          
          <table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-waiver-print">
            <tr>
              <td class="rezgo-td-label">Name</td>
              <td class="rezgo-td-data" id="rezgo-waiver-name"><span><?php echo $pax_data->first_name?>&nbsp;<?php echo $pax_data->last_name?></span></td>
            </tr>
            <tr>
              <td class="rezgo-td-label">Phone</td>
              <td class="rezgo-td-data" id="rezgo-waiver-phone"><span><?php echo $pax_data->phone_number?></span></td>
            </tr>
            <tr>
              <td class="rezgo-td-label">Email</td>
              <td class="rezgo-td-data" id="rezgo-waiver-email"><span><?php echo $pax_data->email_address?></span></td>
            </tr>
          </table>
          
          <p>&nbsp;</p>
        
        </div>
        
      </div>  
      
    </div><!-- // .rezgo-waiver-main --> 
      
</div><!-- //	.rezgo-container --> 
</body>
</html>