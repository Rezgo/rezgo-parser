<?php
	require('../include/page_header.php');
	
	// start a new instance of RezgoSite
	$site = new RezgoSite(secure);

	//set GET var's to local vars: 
	$token = $_GET['token']; 
	$payerid = $_GET['PayerID'];
	
	$result = $site->sendPartialCommit('a=get_paypal_transaction&paypal_token='.$token.'&paypal_payer_id='.$payerid);
	
	if($result->status == 1) {
		echo '<script>
			try {
		  	window.opener.paypalConfirm("'.$token.'", "'.$payerid.'", "'.$result->paypal_name.'", "'.$result->paypal_email.'");
		  }
		  catch(err) {
		  	parent.paypalConfirm("'.$token.'", "'.$payerid.'", "'.$result->paypal_name.'", "'.$result->paypal_email.'");
		  }
		</script>';	
	} else {
		echo '<script>
			try {
		  	window.opener.paypalConfirm("0");
		  }
		  catch(err) {
		  	parent.paypalConfirm("0");
		  }	
		</script>';		
	}
?>


