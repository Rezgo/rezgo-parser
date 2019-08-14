<div class="container-fluid rezgo-container">
	<div class="row">
		<div class="col-xs-12">
			<div id="rezgo-gift-card-search" class="rezgo-gift-card-container clearfix">
				<div class="rezgo-gift-card-group search-section clearfix">
					<div class="rezgo-gift-card-head">
						<h3><span class="text-info">Gift Card Not Found..</span></h3>
						<h5>To check your balance, enter a gift card number.</h5>
					</div>

					<form id="search" role="form" method="post" target="rezgo_content_frame">
						<div class="input-group">
							<input type="text" class="form-control" id="search-card-number" placeholder="Gift Card Number" />

							<span class="input-group-btn">
									<button class="btn btn-primary" type="submit">Go!</button>
							</span>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script>	
/* FORM (#search) */
var $searchForm = $('#search');
var $searchText = $('#search-card-number');
$searchForm.submit(function(e){
	e.preventDefault();
	var search = $searchText.val();
	if (search) {
		top.location.href = '<?php echo $site->base?>/gift-card/'+search;
	}
});
</script>
