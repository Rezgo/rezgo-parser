<div id="rezgo-gift-card-redeem" class="clearfix">
	<h3><span class="text-info">Use Gift Card</span></h3>
  <span id="rezgo-gift-card-memo"></span>

	<div class="input-group">
		 <input type="text" class="form-control" id="gift-card-number" name="gift_card" placeholder="Enter Gift Card Number" />
		 <span class="input-group-btn">
				<button id="gift-card-btn" class="btn btn-primary" type="button">Apply</button>
		 </span>
	</div>

	<div class="response">
		<div class="alert alert-info" style="display:none">
			<div class="row">
				<div class="col-xs-12 text-center">
					<span>You have <strong class="cur"><span class="gift-card-amount"></span></strong> available on this gift card. Do you want to use it to purchase this booking?</span>
				</div>
			</div>

			<div class="row alert-info-nav">
				<div class="col-xs-6">
					<button id="	-redeem-cancel-btn" type="button" class="btn btn-lg btn-block rezgo-btn-default">Cancel</button>
				</div>
				<div class="col-xs-6">
					<button id="	-redeem-confirm-btn" type="button" class="btn btn-lg btn-block rezgo-btn-book">Use</button>
				</div>
			</div>
		</div>
		<div class="alert alert-danger" style="display:none">
			<div class="row">
				<div class="col-xs-12">
					<span class="msg"></span>
				</div>
			</div>
		</div>
		<div class="alert alert-success" style="display:none">
			<div class="row">
				<div class="col-xs-12">
					<i class="fa fa-check" aria-hidden="true"></i>
					<span>Gift Card <strong class="gift-card-number"></strong> applied. <a class="rezgo-redeem-reset-btn">remove</a></span>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
var today = parseInt('<?php echo strtotime("today");?>');
var debug = 0; // turn this off
var $gcApp = $("#rezgo-gift-card-redeem");
var gcData;
var gcCur = "<?php echo $company->currency_symbol?>";

/*if (debug) {
	$('pre.copy').click(function(){
		var html = $(this).html();
		$('#gift-card-number').val(html);
	});
}*/

function gcReq(req) {
	if (!req) {
		return;
	}

	// UPDATE EACH ITEM SECTION
	$('.rezgo-gc-box').hide();

	// UPDATE GC SECTION
	$gcApp.find('.alert').hide();

	// XML REQUEST
	$.ajax({
		url: '<?php echo $site->base?>/gift_card_ajax.php',
		type: 'POST',
		data: {
			rezgoAction: 'getGiftCard',
			gcNum: req
		},
		success: function (data) {
			var json, success, error, msg;

			json = data.split("|||");
			json = json.slice(-1)[0];
			gcData = JSON.parse(json);
			success = parseFloat(gcData.status);
			error = 0;

			/*if (debug) {
				console.log(gcData);
			}*/

			// GIFT CARD BALANCE IS 0
			if (success && !parseFloat(gcData.amount) > 0) {
				error = 1;
				msg = 'This gift card has no fund available.';
			}

			// GIFT CARD NOT FOUND
			if (!success) {
				error = 1;
				msg = 'Gift card not found. Please, make sure you entered a correct card number.';
			}

			// RESULT
			if (error) {
				$gcApp.find('.alert-danger .msg').html(msg);
				$gcApp.find('.alert-danger').show();
			}
			else {
				gcRedeem(gcData);
			}
		},
		error: function () {
			var msg = 'Connection error. Please try again or contact Rezgo for customer support.';
			$gcApp.find('.alert-danger .msg').html(msg);
			$gcApp.find('.alert-danger').show();
		}
	});
}
function gcRedeem(req) {
	var exp = parseInt(req.expires);

	/*if (debug) {
		console.log('GC expiry: ' + exp);
		console.log('today: ' + today);
	}*/

	// If GC is not expired
	if (!exp || (exp && exp >= today)) {
		var max = parseInt (req.max_uses);
		var use = parseInt (req.uses);

		/*if (debug) {
			console.log('GC max_uses: ' + max);
			console.log('GC uses: ' + use);
		}*/

		// If GC max is set to never OR is set but not reached
		if (!max || (max && max > use)) {
			var total = $('#total_value').attr('rel');
			var t = parseFloat(total);
			var b = parseFloat(req.amount); // GC balance
			var c = 0; // GC charge
			var r = 0;

			// RESTRICTED CARD INFO
			if (typeof req.items === 'string') {
				r = req.items.replace(/ /g,"").split(',');

				if (debug) {
					console.log('Restricted card: ');
					console.log(r);
					console.log(req.items);
				}
			}

			// UPDATE EACH ITEM SECTION
			$('.rezgo-billing-cart').each(function(){
				var $t = $(this);
				var id = $t.attr('id');
				var $r = $t.find('.rezgo-gc-box');
				var $i = $t.find('.rezgo-item-total');
				var $d = $t.find('.rezgo-item-deposit');
				var i = parseFloat($i.attr('rel')); // item value
				if ($d.length) {
					var	d = parseFloat($d.attr('rel')); // deposit value
				}

				if (debug) {
					console.log('-------------------------------');
					console.log('balance: '+b+' '+typeof b);
					console.log('charge: '+c+' '+typeof c);
					console.log('item TOTAL: '+i+' '+typeof i);
					if ($d.length) {
						console.log('item DEPOSIT: '+d+' '+typeof d);
					}
					console.log('over TOTAL: '+t+' '+typeof b);
				}

				function recalculate(mode) {
					if (mode === 'd') {
						if (debug) {
							console.log('gc deposit mode..');
						}

						var real_overall_total = t + i - d;
					}

					if (b > 0 && t > 0 && i > 0) {
						if (debug) {
							console.log('GC uses::'+use);
						}

						// CHECK USE < MAX IF MAX
						if (!max || (max && max > use)) {
							if (b >= i) {
								if (mode == 'd') {
									t = real_overall_total;

									$d.parents('tr').hide();

									if (debug) {
										console.log('over TOTAL = ' + t)
										console.log('gc balance >= item value');
										console.log('ignore deposit..');
									}
								}

								i = parseFloat(i.toFixed(2));
								$t.find('.rezgo-gc-min').html(i.formatMoney());

								b = b-i;
								t = t-i;
								c = c+i;
								i = 0;
							}

							else {
								b = parseFloat(b.toFixed(2));
								$t.find('.rezgo-gc-min').html(b.formatMoney());

								i = i-b;
								c = c+b;

								// item value is 80
								// if gc is $50, the remaining be 30, and order total be 25 for deposit to pay. (and show deposit line)
								// if gc is $70, the remaining be 10, and order total be 10 to pay. (and hide deposit line)

								if (mode === 'd') {
									if ((i >= d) == 0) {
										t = real_overall_total;

										t = t-b;
										$d.parents('tr').hide();
									}
								}

								else {
									t = t-b;
								}

								b = 0;
							}

							if (t < 0) {
								t = 0;
							}

							t = parseFloat (t.toFixed(2));
							i = parseFloat (i.toFixed(2));
							b = parseFloat (b.toFixed(2));
							c = parseFloat (c.toFixed(2));

							// $i.html(gcCur+i.formatMoney());
							$r.find('.cur').html(gcCur);
							$r.show();

							// INCREMENT USE COUNT
							use++;
						}
					}
				}

				if (debug) {
					console.log('r: '+r);
					console.log('id: '+id);
				}

				// If no restriction OR item is a restricted item
				if (!r || (r && $.inArray(id,r) >= 0)) {
					if (debug) {
						console.log('-- RECALCULATE --');
					}

					// DEPOSIT
					if ($d.length) {
						recalculate('d');
					}

					// NO DEPOSIT
					else {
						recalculate('i');
					}
				}

				if (debug) {
					console.log('--RESULT--');
					console.log('balance: '+b+' '+typeof b);
					console.log('charge: '+c+' '+typeof c);
					console.log('item TOTAL: '+i+' '+typeof i);
					if ($d.length) {
						console.log('item DEPOSIT: '+d+' '+typeof d);
					}
					console.log('over TOTAL: '+t+' '+typeof b);
					console.log('-------------------------------');
				}
			});

			// UPDATE OVERALL TOTAL
			$('#total_value').html(gcCur + t.formatMoney());
			$('input[name="expected"]').val(t);
			overall_total = t;

			// SHOW/HIDE PAYMENT INFO
			gcUpdatePaymentSection(parseFloat(t));

			// UPDATE GC SECTION
			$gcApp.find('.input-group').hide();
			$gcApp.find('.alert').hide();
			$gcApp.find('.gift-card-number').html(gcData.number);
			$gcApp.find('.alert-success').show();

			// SCROLL TOP
			top.window.scrollTo(0,0);
		}
		else {
			// UPDATE GC SECTION
			// $gcApp.find('.input-group').hide();
			$gcApp.find('.alert').hide();
			var msg = "Gift card can't be applied because it does not have enough uses remaining.";
			$gcApp.find('.alert-danger .msg').html(msg);
			$gcApp.find('.alert-danger').show();
		}
	}
	else {
		// UPDATE GC SECTION
		// $gcApp.find('.input-group').hide();
		$gcApp.find('.alert').hide();
		var msg = "Gift card can't be applied because the card has expired.";
		$gcApp.find('.alert-danger .msg').html(msg);
		$gcApp.find('.alert-danger').show();
	}
}
function gcUpdatePaymentSection(t) {
	//TG change

	if (t > 0) {
		$('#payment_info_head').show();
		$('#payment_info').show();
		$('.terms_credit_card_over_zero').show();
		
	}
	else {
		$('#payment_info_head').hide();
		$('#payment_info').hide();
		$('.terms_credit_card_over_zero').hide();
		
	}
}
gcReset = function() {
	// TOTAL RESET
	var $t = $('#total_value');
	var t	= parseFloat($t.attr('rel'));
	$t.html(gcCur + t.formatMoney());
	overall_total = t;

	// ITEM RESET
	$('.rezgo-gc-box').hide();
	$('.rezgo-billing-cart').each(function(){
		var $i = $(this).find('.rezgo-item-total');
		var $d = $(this).find('.rezgo-item-deposit');
		var	i = parseFloat($i.attr('rel')); // item value
		if ($d.length) {
			var	d = parseFloat($d.attr('rel')); // deposit value
		}

		$i.html(gcCur + i.formatMoney());
		if ($d.length) {
			$d.html(gcCur + d.formatMoney()).parents('tr').show();
		}
	});

	// HIDDEN INPUT
	$('input[name="expected"]').val(overall_total);
	$('input[name="gift_card"]').val('');

	// GIFTCARD SECTION
	$gcApp.find('.alert').hide();
	$gcApp.find('.input-group').show();

	// SHOW/HIDE PAYMENT INFO
	gcUpdatePaymentSection(t);
}

$("#gift-card-btn").click(function(){
	var req = $('#gift-card-number').val()

	gcReq(req);
});
$(document).on('click','.rezgo-redeem-reset-btn',function(){
	gcReset();

	top.window.scrollTo(0,0);
});
</script>
