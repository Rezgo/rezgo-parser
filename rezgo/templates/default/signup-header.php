<? if(!$site->config('REZGO_HIDE_HEADERS')) { ?>
	<?
		header('Cache-Control: no-cache');
	  header('Pragma: no-cache');
	  
	  header('Content-Type: text/html; charset=utf-8');
	?>
	
	<?=$site->getHeader()?>	
<? } ?>

<!--[if lte IE 6]><script src="<?=$this->path?>/js/ie6/warning.js"></script><script>window.onload=function(){e("<?=$this->path?>/js/ie6/")}</script><![endif]-->


<? if($site->exists($site->getStyles())) { ?>
<style>
<!--

	<?=$site->getStyles()?>

-->
</style>
<? } ?>
