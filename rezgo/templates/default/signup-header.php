<?php if (!$site->config('REZGO_HIDE_HEADERS')) { ?>
	<?php
		header('Cache-Control: no-cache');
	  header('Pragma: no-cache');
	  
	  header('Content-Type: text/html; charset=utf-8');
	?>
	
	<?php echo $site->getHeader()?>	
<?php} ?>

<!--[if lte IE 6]><script src="<?php echo $this->path?>/js/ie6/warning.js"></script><script>window.onload=function(){e("<?php echo $this->path?>/js/ie6/")}</script><![endif]-->


<?php if($site->exists($site->getStyles())) { ?>
<style>

	<?php echo $site->getStyles()?>

</style>
<?php} ?>
