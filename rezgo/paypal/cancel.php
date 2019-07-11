<?
	// the paypal process was cancelled. Show this to the user in the modal window.
?>

<script>
	try {
  	window.opener.paypalCancel();	
  }
  catch(err) {
  	parent.paypalCancel();
  }
</script>