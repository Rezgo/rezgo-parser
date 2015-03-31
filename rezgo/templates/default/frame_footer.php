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
  
  <div style="float:right;height:52px;margin:10px;display:table;"><div style="display:table-cell;vertical-align:bottom;"><div style="font-size:12px;"><a href="http://www.rezgo.com/features/online-booking/" title="Powering Tour and Activity Businesses Worldwide" style="color:#333;text-decoration:none;">Online bookings powered by <span style="display:inline-block;width:65px;text-indent:-9999px;margin-left:4px;background:url(<?=$site->path?>/img/rezgo-logo.svg) no-repeat; background-size:contain;">Rezgo</span></a></div></div></div>
</div>

</body>
</html>