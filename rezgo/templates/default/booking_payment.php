<?
	if($_REQUEST['rezgoAction'] == 'return') {
		echo '<script>parent.creditConfirm("'.$_REQUEST['token'].'");</script>';
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>

<!-- Bootstrap CSS -->
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css" rel="stylesheet">

<!-- Font awesome --> 
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
<!--[if IE 7]>
  <link href="<?=$this->path?>/css/font-awesome-ie7.css" rel="stylesheet">
<![endif]-->  

<!-- Rezgo stylesheet -->
<link href="<?=$site->path?>/css/rezgo.css" rel="stylesheet">

<? if($site->exists($site->getStyles())) { ?>
<style>
	<?=$site->getStyles();?>
</style>
<? } ?>

<!-- jQuery & Bootstrap JS -->
<script src="/js/iframeResizer.contentWindow.min.js"></script>
<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="<?=$site->path?>/js/bootstrap.min.js"></script>

</head>
<body class="rezgo-booking-payment-body">
	
	<script>
		function check_valid() {
		
			var valid = $("#payment").valid();
			
			return valid;
		}
	</script>
	
	<form method="post" id="payment" action="https://process.rezgo.com/form">
	
	<input type="hidden" name="return" value="https://<?=$_SERVER['HTTP_HOST'].REZGO_URL_BASE?>/booking_payment.php?rezgoAction=return&">
	
	<div id="payment_card_info" class="container-fluid">
    
        <!-- iframe : payment info -->
        <div class="form-group col-sm-6 col-xs-12 rezgo-form-input">
          <label for="name" class="control-label">Cardholder Name</label>
          <input type="text" class="form-control" id="name" name="name" value="<?=$site->requestStr('name')?>" required />
        </div>
        <div class="clearfix"></div>
        <div class="form-group col-sm-6 col-xs-12 rezgo-form-input">
          <label for="pan" class="control-label">Card Number</label>
          <input type="text" class="form-control" id="pan" name="pan" value="<?=$site->requestStr('pan')?>" required />
        </div>
        <div class="clearfix"></div>
        <div class="form-group rezgo-form-input col-sm-3 col-xs-6">
          <label for="exp_month" class="control-label">Card Exp<span class="hidden-xs">iration</span></label>
          <select name="exp_month" id="exp_month" class="form-control">
            <option value="01">01</option>
            <option value="02">02</option>
            <option value="03">03</option>
            <option value="04">04</option>
            <option value="05">05</option>
            <option value="06">06</option>
            <option value="07">07</option>
            <option value="08">08</option>
            <option value="09">09</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
          </select>
        </div>
        <div class="form-group rezgo-form-input col-sm-4 col-xs-6">
          <label for="exp_year" class="control-label">&nbsp;</label>
          <select name="exp_year" id="exp_year" class="form-control">
            <? for($d=date("Y"); $d <= date("Y")+12; $d++) { ?>
              <option value="<?=substr($d, -2)?>"><?=$d?></option>
            <? } ?>
          </select>	
        </div>
        <? if($site->getCVV()) { ?>
        <div class="clearfix"></div>
        <div class="form-group rezgo-form-input col-sm-5 col-xs-12">
          <label for="rezgo-cvv" class="control-label" id="rezgo-cvv-label">CVV&nbsp;<a href="javascript:void(0);" onclick="javascript:window.open('<?=$site->path?>/img/cvv_cards.png',null,'width=600,height=300,status=no,toolbar=no,menubar=no,location=no');">what is this?</a></label><br />
          <input type="text" class="form-control col-xs-5" name="cvv" id="rezgo-cvv" required />
        </div>
        <? } ?>
        
        <!-- iframe end : payment info -->			
			
		</div>
	
	</form>

</body>
</html>