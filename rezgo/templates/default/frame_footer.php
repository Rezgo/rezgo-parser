<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-TK6F39" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>
	datalayer = [{ 'cid':'<?=REZGO_CID?>' }];
	(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-TK6F39');
</script>

<div class="container-fluid">
	<? if($_COOKIE['rezgo_refid_val']) { ?>
    <div id="rezgo-refid">
      RefID: <?=$_COOKIE['rezgo_refid_val']?>
    </div>
  <? } ?>
  
  <? if($base_url == 'dev.rezgo.com') { /* Help with Bootstrap Grid */?>
		<div style="float:left; padding-left:5px;">
      <div class="hidden-xs hidden-sm hidden-md hidden-lg">Size XXS</div>
      <div class="hidden-xxs hidden-sm hidden-md hidden-lg">Size XS</div>
      <div class="hidden-xxs hidden-xs hidden-md hidden-lg">Size SM</div>
      <div class="hidden-xxs hidden-xs hidden-sm hidden-lg">Size MD</div>
      <div class="hidden-xxs hidden-xs hidden-sm hidden-md">Size LG</div>
    </div>
    <div class="clearfix"></div>
  <? } ?>
  
  <? if ($_SERVER['SCRIPT_NAME'] == '/page_book.php') { ?>
  <div id="rezgo-secure-seal">
    <div id="verisign-seal"><script type="text/javascript" src="https://seal.verisign.com/getseal?host_name=rezgo.com&size=S&use_flash=NO&use_transparent=NO&lang=en"></script></div>
		<div id="trustwave-seal"><script type="text/javascript" src="https://sealserver.trustwave.com/seal.js?style=invert"></script></div>
  </div>
  <? } ?> 
  
  <div style="float:right;height:52px;margin:10px;display:table;"><div style="display:table-cell;vertical-align:bottom;"><div style="font-size:12px;"><a href="http://www.rezgo.com/features/online-booking/" title="Powering Tour and Activity Businesses Worldwide" style="color:#333;text-decoration:none;"><span style="display:inline-block;width:65px;text-indent:-9999px;margin-left:4px;background:url(<?=$site->path?>/img/rezgo-logo.svg) no-repeat; background-size:contain;">Rezgo</span></a></div></div></div>
</div>

</body>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-1943654-2', 'auto');
  ga('send', 'pageview');
	
	<?
	if ($_SERVER['SCRIPT_NAME'] == '/page_order.php' || $_SERVER['SCRIPT_NAME'] == '/page_book.php' || $_SERVER['SCRIPT_NAME'] == '/booking_complete.php') {
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
	}
	
	?>
	
	var transcode = '<?php echo REZGO_CID ?>';
</script>

<!--<script type="text/javascript" src="/analytics/glance.js"></script>-->

</html>