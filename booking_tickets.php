<?php 
	// This is the booking receipt page
	
	require('rezgo/include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite(secure);

	$site->setMetaTags('<meta name="robots" content="noindex, nofollow">');
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex, nofollow">
		<title>Booking Tickets</title>

		<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
		<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css" rel="stylesheet">
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
		<!--[if IE 7]><link href="<?php echo $site->path;?>/css/font-awesome-ie7.css" rel="stylesheet"><![endif]-->
		<link href="<?php echo $site->path;?>/css/rezgo.css?v=<?php echo REZGO_VERSION?>" rel="stylesheet">
		<?php if($site->exists($site->getStyles())) { echo '<style>'.$site->getStyles().'</style>'; } ?>
	</head>

	<body>
    <?php if ($_REQUEST['print']) { ?>
    <div class="col-xs-12 rezgo-print-hide">
    	<div class="clearfix"></div>
      <a class="btn rezgo-btn-print pull-right" style="border-radius:0" onclick="window.print();"><i class="fa fa-print" style="padding-right:2px;"></i> Print Tickets</a>
    </div>
    <?php } ?>
		<?php echo $site->getTemplate('booking_tickets')?>
	</body>
</html>