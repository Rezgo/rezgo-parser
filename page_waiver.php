<?php 
	// This is the waiver page
	require('rezgo/include/page_header.php');

	// start a new instance of RezgoSite
	$site = new RezgoSite(secure);

	// Page title
	$site->setPageTitle($_REQUEST['title'] ? $_REQUEST['title'] : 'Waiver');
?>

<?php echo $site->getTemplate('frame_header')?>

<?php echo $site->getTemplate('waiver')?>

<div class="container-fluid" id="waiver-footer">
	<?php if (!$_REQUEST['headless']) { ?>
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
</html>