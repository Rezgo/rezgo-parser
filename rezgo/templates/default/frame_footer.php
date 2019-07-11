<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-TK6F39" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

<script>
	datalayer = [{ 'cid':'<?php echo REZGO_CID?>' }];
	(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-TK6F39');
</script>

<div class="container-fluid">
	<?php if($_COOKIE['rezgo_refid_val']) { ?>
		<div id="rezgo-refid">
			RefID: <?php echo $_COOKIE['rezgo_refid_val']?>
		</div>
	<?php } ?>
	
	<?php 
	if ( $_SERVER['SCRIPT_NAME'] == '/page_book.php' || $_SERVER['SCRIPT_NAME'] == '/page_payment.php' || $_SERVER['SCRIPT_NAME'] == '/gift_card.php' ) { 
	
	$company = $site->getCompanyDetails();
	
	?>

		<div class="rezgo-content-row" id="rezgo-billing-footer">
    
    	<div class="col-xs-4 col-sm-2">
        <div id="rezgo-secure-seal">
          <div id="trustwave-seal"><script type="text/javascript" src="https://sealserver.trustwave.com/seal.js?style=invert"></script></div>
        </div>
      </div>
    
			<?php if ( (string) $company->gateway_id == 'rezgo') { ?>
      
			<div class="col-xs-8 col-sm-4" id="rezgo-billing-address">
				<address>
				<strong><?php echo $company->company_name?></strong><br />
				<?php echo $company->address_1?> <?php echo $company->address_2?><br />
				<?php echo $company->city?>, <?php if($site->exists($company->state_prov)) { ?><?php echo $company->state_prov?>, <?php } ?><?php echo $site->countryName($company->country)?><br />
				<?php echo $company->postal_code?><br />
				<?php if($site->exists($company->phone)) { ?>Phone: <?php echo $company->phone?><br /><?php } ?>
				Email: <a href="mailto:<?php echo $company->email?>"><?php echo $company->email?></a>
				<?php if($site->exists($company->tax_id)) { ?>
				<br />Tax ID: <?php echo $company->tax_id?>
				<?php } ?>
				</address>
			</div>
      
      <div class="col-xs-12 col-sm-6" id="rezgo-tmt-info">
        <div id="tmt-logo">
        <a href="https://www.trustmytravel.com/terms/" target="_blank" title="TMTProtects.Me"><img src="<?php echo $site->path;?>/img/logos/tmt-logo.png" class="img-responsive" alt="TMTProtects.Me" /></a>
        </div>
        <div id="tmt-address-container">
          <address>
          This merchant uses Trust My Travel Ltd to protect and process credit card payments. All credit card payments placed through this website are protected by <a href="https://www.trustmytravel.com/terms/" target="_blank" title="TMTProtects.Me">TMTProtects.Me</a> <br />
          <span id="tmt-address">
          For payment questions, please contact: <br />
          Tel: 44(0)1780 438828. <br />
          The Cedars, Ryhall PE9 4HL, United Kingdom.
          </span>
          </address>
        </div>
      </div>
      
      <?php } else { ?>
      <div class="col-xs-12 col-sm-10">&nbsp;</div>
      <?php } ?>
      
		</div>
  
	<?php } ?> 

	<?php if ($_SERVER['SCRIPT_NAME'] != '/modal.php' && !$_REQUEST['headless']) { ?>
		<div style="float:right;height:52px;margin:10px;display:table;">
			<div style="display:table-cell;vertical-align:bottom;">
				<div style="font-size:12px;">
					<a href="http://www.rezgo.com/features/online-booking/" title="Powering Tour and Activity Businesses Worldwide" style="color:#333;text-decoration:none;" target="_blank">
						<span style="display:inline-block;width:65px;text-indent:-9999px;margin-left:4px;background:url(<?php echo $site->path;?>/img/rezgo-logo.svg) no-repeat; background-size:contain;">Rezgo</span>
					</a>
				</div>
			</div>
		</div>
	<?php } ?>
</div>

</body>

<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	
	ga('create', 'UA-1943654-2', 'auto');
	
	// Set value for custom dimension at index 1.
	//ga('set', 'dimension5', '<?php echo REZGO_CID?>');
	
	// Send the custom dimension value with a pageview hit.
	ga('send', 'pageview', {
		'dimension1': '<?php echo REZGO_CID?>'
	});
	
	<?php if ($_SERVER['SCRIPT_NAME'] == '/page_order.php' || $_SERVER['SCRIPT_NAME'] == '/page_book.php' || $_SERVER['SCRIPT_NAME'] == '/booking_complete.php') {
		echo "ga('require', 'ec');"."\n";
		if ($_SERVER['SCRIPT_NAME'] == '/page_order.php') {
			echo "
			ga('ec:setAction','checkout', {
					'step': 1
			});
			";
		}
		if ($_SERVER['SCRIPT_NAME'] == '/page_book.php') {
			echo "
			ga('ec:setAction','checkout', {
					'step': 2
			});
			";
		}
		if ($_SERVER['SCRIPT_NAME'] == '/booking_complete.php' && $_SESSION['REZGO_CONVERSION_ANALYTICS']) {
			echo "
			ga('ec:setAction','checkout', {
					'step': 3
			});
			";
			
			echo "
			ga('require', 'ecommerce');
			".$ga_add_transacton."
			ga('ecommerce:send');
			";
		}
	} ?>
	
	var transcode = '<?php echo REZGO_CID ?>';
</script>

</html>