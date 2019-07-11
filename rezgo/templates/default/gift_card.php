<?php 
$company = $site->getCompanyDetails();
$companyCountry = $site->getCompanyCountry();
$site->readItem($company);
$card_fa_logos = array(
	'visa' => 'fa-cc-visa',
	'mastercard' => 'fa-cc-mastercard',
	'american express' => 'fa-cc-amex',
	'discover' => 'fa-cc-discover'
);
?>

<script>var debug = <?php echo DEBUG?>;</script>

<div class="container-fluid rezgo-container">
	<div class="row">
		<div class="col-xs-12">
			<div id="rezgo-gift-card-search" class="rezgo-gift-card-container clearfix">
				<div class="rezgo-gift-card-group search-section clearfix">
					<h3><span class="text-info">Check Your Balance</span></h3>

					<form id="search" role="form" method="post" target="rezgo_content_frame">
						<div class="input-group">
							<input type="text" class="form-control" id="search-card-number" placeholder="Gift Card Number" />

							<span class="input-group-btn">
									<button class="btn btn-primary" type="submit">Go!</button>
							</span>
						</div>
					</form>

					<div class='alert alert-info' style='display:none'>
						<span class='msg'></span>
					</div>
				</div>
			</div>

			<?php if (!$site->isVendor() && $site->getGateway()) { ?>
				<div class="rezgo-gift-card-container clearfix">
					<form id="purchase" class="gift-card-purchase" role="form" method="post" target="rezgo_content_frame">
						<div class="rezgo-gift-card-group clearfix">
							<div class="rezgo-gift-card-head">
								<h3 class="rezgo-gift-card-heading text-info"><span>Buy a Gift Card</span></h3>
								<p class="rezgo-gift-card-desc"><span>Select the card value or enter your own amount.</span></p>
							</div>

							<div class="row">
								<div class="col-xs-12">
									<div class="form-group">
										<label for="billing_amount" class="control-label">
											<span>Gift Card Value in <?php echo $company->currency_symbol?></span>
										</label>

										<select id="rezgo-billing-amount" class="form-control" name="billing_amount">
											<option value="50" selected><?php echo $site->formatCurrency(50)?></option>
											<option value="100"><?php echo $site->formatCurrency(100)?></option>
											<option value="150"><?php echo $site->formatCurrency(150)?></option>
											<option value="250"><?php echo $site->formatCurrency(250)?></option>
											<option value="custom">Other Amount...</option>
										</select>

										<div id="rezgo-custom-billing-amount-wrp" style="display:none;">
											<input type="number" min="1" name="custom_billing_amount" id="rezgo-custom-billing-amount" class="form-control" placeholder="Enter a custom gift card amount here" />
										</div>
									</div>
								</div>
							</div>
						</div>

						<hr>

						<div class="rezgo-gift-card-group clearfix">
							<div class="rezgo-gift-card-head">
								<h3><span class="text-info">Send it to...</span></h3>
							</div>

							<div class="row">
								<div class="col-xs-12 col-sm-6">
									<div class="form-group">
										<label for="recipient_name" class="control-label">
											<span>Name</span>
										</label>

										<input class="form-control required" name="recipient_name" type="text" placeholder="Full Name" />
									</div>
								</div>

								<div class="col-xs-12 col-sm-6">
									<div class="form-group">
										<label for="recipient_email" class="control-label">
											<span>Email Address</span>
										</label>

										<input class="form-control required" name="recipient_email" type="email" placeholder="Email Address" />
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-xs-12">
									<div class="form-group">
										<label for="recipient_message" class="control-label">Your Message (optional)</label>

										<textarea class="form-control" name="recipient_message" rows="5" style="resize:none" placeholder="Your Message"></textarea>
									</div>
								</div>
							</div>
						</div>

						<hr>

						<div class="rezgo-gift-card-group clearfix">
							<div class="rezgo-gift-card-head">
								<h3><span class="text-info">Billing Information</span></h3>
							</div>

							<div class="row">
								<div class="form-group clearfix">
									<div class="col-xs-12">
										<label for="billing_first_name" class="control-label">
											<span>Name</span>
										</label>
									</div>

									<div class="col-xs-12 col-sm-6">
										<input class="form-control required" name="billing_first_name" type="text" placeholder="First Name" />
									</div>

									<div class="col-xs-12 col-sm-6">
										<input class="form-control required" name="billing_last_name" type="text" placeholder="Last Name" />
									</div>
								</div>
							</div>
						
							<div class="row">
								<div class="col-xs-12">
									<div class="form-group">
										<label for="billing_address_1" class="control-label">
											<span>Address</span>
										</label>

										<input class="form-control required" name="billing_address_1" type="text" placeholder="Address 1" />

										<input class="form-control" name="billing_address_2" type="text" placeholder="Address 2 (optional)" />
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-xs-12 col-sm-8">
									<div class="form-group">
										<label for="billing_city" class="control-label">
											<span>City</span>
										</label>

										<input class="form-control required" name="billing_city" type="text" placeholder="City" />
									</div>
								</div>

								<div class="col-xs-12 col-sm-4">
									<div class="form-group">
										<label for="billing_postal_code" class="control-label">
											<span>Zip/Postal</span>
										</label>

										<input 
										class="form-control required" 
										name="billing_postal_code" 
										type="text" 
										placeholder="Zip/Postal Code"
										/>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-xs-12 col-sm-8">
									<div class="form-group">
										<label for="billing_country" class="control-label">
											<span>Country</span>
										</label>

										<select id="billing_country" name="billing_country" class="form-control">
											<?php foreach ($site->getRegionList() as $iso => $name) { ?>
												<option value="<?php echo $iso?>" <?php echo (($iso == $companyCountry) ? 'selected' : '')?> ><?php echo ucwords($name)?></option>
											<?php } ?>
										</select>
									</div>
								</div>

								<div class="col-xs-12 col-sm-4">
									<div class="form-group">
										<label for="billing_stateprov" class="control-label">
											<span>State/Prov</span>
										</label>

										<select id="billing_stateprov" class="form-control" style="<?php echo (($companyCountry != 'ca' && $companyCountry != 'us' && $companyCountry != 'au') ? 'display:none' : '')?>" ></select>

										<input id="billing_stateprov_txt" class="form-control" name="billing_stateprov" type="text" value="" placeholder="State/Province" style="<?php echo (($companyCountry != 'ca' && $companyCountry != 'us' && $companyCountry != 'au') ? '' : 'display:none')?>" />
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-xs-12 col-sm-6">
									<div class="form-group">
										<label for="billing_email" class="control-label">
											<span>Email</span>
										</label>

										<input class="form-control required" name="billing_email" type="email" placeholder="Email Address" />
									</div>
								</div>

								<div class="col-xs-12 col-sm-6">
									<div class="form-group">
										<label for="billing_phone" class="control-label">
											<span>Phone</span>
										</label>

										<input class="form-control required" name="billing_phone" type="text" placeholder="Phone Number" />
									</div>
								</div>
							</div>
						</div>

						<hr>

						<div class="rezgo-gift-card-group clearfix">
							<div class="rezgo-gift-card-head">
								<h3><span class="text-info">Credit Card Information</span></h3>
							</div>

							<div class="row">
								<div class="col-xs-12">
									<div id="rezgo-credit-card-container">
										<div id="payment_methods" class="thb-cards">
											<?php foreach( $site->getPaymentCards() as $card ) { ?>
												<img src="<?php echo $site->path;?>/img/logos/<?php echo $card?>.png" class="hidden-xs" />
												<span class="visible-xs-inline">
													<i class="fa <?php echo $card_fa_logos[$card]?>"></i>
												</span>
											<?php } ?>
										</div>

										<input type="hidden" name="gift_card_token" id="gift_card_token" value="" />

										<script>$('#gift_card_token').val("");</script>

										<iframe scrolling="no" frameborder="0" name="gift_payment" id="rezgo-gift-payment" src="<?php echo $site->base?>/booking_payment.php"></iframe>

										<script type="text/javascript">iFrameResize ({
												enablePublicMethods: true,
												scrolling: false,
												checkOrigin: false
										});</script>

										<div id='rezgo-credit-card-success' style='display:none;'></div>
									</div>
								</div>
							</div>
						
							<div class="row">
								<div class="col-sm-12">
									<div class="form-group">
										<input class="required" id="rezgo-terms-agree" name="terms_agree" type="checkbox" />

										<label>
											<span>I agree to the </span>
											<a data-toggle="collapse" data-target="#rezgo-terms-panel" class="collapsed"><span>Terms &amp; Conditions</span></a>
                      &nbsp;and&nbsp;
											<a data-toggle="collapse" data-target="#rezgo-privacy-panel" class="collapsed"><span>Privacy Policy</span></a>
										</label>

										<div id="rezgo-terms-panel" class="collapse">
											<div class="collapse-wrapper">
												<?php echo $site->getPageContent('terms')?>
												<?php if ( (string) $company->gateway_id == 'rezgo') { ?>
                        <p>We have partnered with Trust My Travel Ltd. to provide global payment services for our business.  By using our services, you must read and agree to the following <a href="https://www.trustmytravel.com/terms/" target="_blank" title="TMTProtects.Me">Payment Services Agreement</a>.</p>
                        <?php } ?>
											</div>
										</div>

										<div id="rezgo-privacy-panel" class="collapse">
											<div class="collapse-wrapper">
												<?php echo $site->getPageContent('privacy')?>
											</div>
										</div>
									
										<hr>

										<p>
											<span>Please note that your credit card will be charged.</span>
											<br>
											<span>If you are satisfied with your entries, please click the "Complete Purchase" button.</span>
										</p>
									</div>
								</div>
							</div>
						</div>

						<div id="rezgo-gift-message" style="display:none;">
							<div id="rezgo-gift-message-body"></div>
						</div>

						<div id="rezgo-gift-errors" style="display:none;">
							<div class="alert alert-danger">Some required fields are missing or incorrect. Please review the highlighted fields.</div>
						</div>

						<div class="cta">
							<div class="row">
								<div class="col-xs-12 col-sm-6 pull-right">
									<input type="submit" id="purchase-submit" class="btn rezgo-btn-book btn-lg btn-block" value="Complete Purchase" />
								</div>
							</div>
						</div>
					</form>
				</div>
			<?php } ?>
		</div>
	</div>
</div>

<script type="text/javascript" src="<?php echo $site->path;?>/js/jquery.form.js"></script>
<script type="text/javascript" src="<?php echo $site->path;?>/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="<?php echo $site->path;?>/js/jquery.selectboxes.js"></script>

<?php if (!$site->isVendor() && $site->getGateway()) { ?>
	<script>
	/* FORM (#purchase) */

	// STATES VAR
	var ca_states = <?php echo  json_encode( $site->getRegionList('ca') ); ?>;
	var us_states = <?php echo  json_encode( $site->getRegionList('us') ); ?>;
	var au_states = <?php echo  json_encode( $site->getRegionList('au') ); ?>;

	// FORM ELEM
	var $purchaseForm = $('#purchase');
	var $purchaseBtn = $('#purchase-submit');
	var $formMessage = $('#rezgo-gift-message');
	var $formMsgBody = $('#rezgo-gift-message-body');
	var $amtSelect = $('#rezgo-billing-amount');
	var $amtCustom = $('#rezgo-custom-billing-amount');

	// CUSTOM AMOUNT
	$amtSelect.change(function(){
		if ($(this).val() == 'custom') {
			$amtCustom.parent().show();
			$amtCustom.addClass('required');
			$amtCustom.focus();
		}
		else {
			$amtCustom.parent().hide();
			$amtCustom.removeClass('required')
		}
	});

	// FORM VALIDATE
	$purchaseForm.validate({
		messages: {
			recipient_first_name: {
				required: "Enter recipient first name"
			},
			recipient_last_name: {
				required: "Enter recipient last name"
			},
			recipient_address_1: {
				required: "Enter recipient address"
			},
			recipient_city: {
				required: "Enter recipient city"
			},
			recipient_postal_code: {
				required: "Enter recipient postal code"
			},
			recipient_phone_number: {
				required: "Enter recipient phone number"
			},
			recipient_email: {
				required: "Enter a valid email address"
			},
			billing_amount: {
				required: 'A Numeric value is required for this field'
			},
			billing_first_name: {
				required: "Enter billing first name"
			},
			billing_last_name: {
				required: "Enter billing last name"
			},
			billing_address_1: {
				required: "Enter billing address"
			},
			billing_city: {
				required: "Enter billing city"
			},
			billing_state: {
				required: "Enter billing state"
			},
			billing_country: {
				required: "Enter billing country"
			},
			billing_postal_code: {
				required: "Enter billing postal code"
			},
			billing_email: {
				required: "Enter a valid email address"
			},
			billing_phone: {
				required: "Enter billing phone number"
			},
			terms_agree: {
				required: "You must agree to our terms &amp; conditions"
			}
		},
		errorPlacement: function(error, element) {
			if (element.attr("name") == "terms_agree" ) {
				error.insertAfter("#rezgo-terms-panel");
			} else {
				error.insertAfter(element);
			}
		},
		highlight: function(element) {
			$(element).closest('.form-group').addClass('has-error');			
		},
		unhighlight: function(element) {
			$(element).closest('.form-group').removeClass('has-error');
		},
		errorClass: 'help-block',
		focusInvalid: false,
		errorElement: 'span'
	});

	// FORM COUNTRY & STATES OPTIONS SWITCH
	$('#billing_country').change(function() {
		var country = $(this).val();

		$('#billing_stateprov').removeOption(/.*/);
		switch (country) {
			case 'ca':
				$('#billing_stateprov_txt').hide();
				$('#billing_stateprov').addOption(ca_states, false).show();
				$('#billing_stateprov_txt').val($('#billing_stateprov').val());
				break;
			case 'us':
				$('#billing_stateprov_txt').hide();
				$('#billing_stateprov').addOption(us_states, false).show();
				$('#billing_stateprov_txt').val($('#billing_stateprov').val());
				break;
			case 'au':
				$('#billing_stateprov_txt').hide();
				$('#billing_stateprov').addOption(au_states, false).show();
				$('#billing_stateprov_txt').val($('#billing_stateprov').val());
				break;		
			default:
				$('#billing_stateprov').hide();
				$('#billing_stateprov_txt').val('');
				$('#billing_stateprov_txt').show();
				break;
		}
	});
	$('#billing_stateprov').change(function() {
		var state = $(this).val();

		$('#billing_stateprov_txt').val(state);
	});
	<?php if (in_array($site->getCompanyCountry(), array('ca', 'us', 'au'))) { ?>
		$('#billing_stateprov').addOption(<?php echo $site->getCompanyCountry();?>_states, false);
		$('#billing_stateprov_txt').val($('#billing_stateprov').val());
	<?php } ?>

	// FORM SUBMIT
	function creditConfirm(token) {
		// the credit card transaction was completed, give us the token
		$('#gift_card_token').val(token);
	}
	function error_booking() {
		$('#rezgo-gift-errors').show();

		setTimeout(function(){
			$('#rezgo-gift-errors').hide();
		}, 8000);
	}
	function check_card_token() {
		var card_token = $('#gift_card_token').val();

		if (card_token == '') {
			// card token has not been set yet, wait and try again
			setTimeout(function() {
				check_card_token();
			}, 200);
		} else {
			// TOKEN SUCCESS ANIM
			showSuccessIcon($('#rezgo-credit-card-success'));
			// the field is present? submit normally
			$purchaseForm.ajaxSubmit({
				url: '<?php echo $site->base?>/gift_card_ajax.php', 
				data: {
					rezgoAction:'addGiftCard'
				},
				success: function(data){
					var strArray, json;

					strArray = data.split("|||");
					strArray = strArray.slice(-1)[0];
					json = JSON.parse(strArray);
					response = json.response;

					if (response == 1) {
						top.location.replace('/gift-receipt');
					} 
					else {
						if (response == 2)			{
							var body = 'Sorry, there is not enough availability left for this item on this date.';
						} 
						else if (response == 3) {
							var body = 'Sorry, your payment could not be completed. Please verify your card details and try again.';
						} 
						else if (response == 4) {
							var body = 'Sorry, there has been an error with your transaction and it can not be completed at this time.';
						} 
						else if (response == 5) {
							var body = 'Sorry, you must have a credit card attached to your Rezgo Account in order to complete a booking.<br><br>Please go to "Settings &gt; Rezgo Account" to attach a credit card.';
						}
						else {
							var body = 'Sorry, an unknown error has occurred. If this keep happening, please contact Rezgo.';
						}

						$('#rezgo-credit-card-success').hide().empty();
						$formMsgBody.addClass('alert alert-danger').html(body);
						$formMessage.show();
						$purchaseBtn.removeAttr('disabled');

						setTimeout(function(){
							$formMessage.hide();
						}, 8000);
					}
				}, 
				error: function() {
					var body = 'Sorry, the system has suffered an error that it can not recover from.<br />Please try again later.<br />';
					$formMsgBody.addClass('alert alert-danger').html(body);
				}
			});
		}
	}
	function submit_purchase() {
		var $cardForm = $('#rezgo-gift-payment').contents().find('#payment');
		var payment_method = 'Credit Cards';
		var validationCheck;

		// MESSAGE TO CLIENT
		$purchaseBtn.attr('disabled','disabled');
		$formMsgBody.removeClass().html('');
		$formMessage.hide();

		// FORM VALIDATION
		validationCheck = $purchaseForm.valid();
		$cardForm.validate({
			messages: {
				name: {
					required: ""
				},
				pan: {
					required: ""
				},
				cvv: {
					required: ""
				}
			},
			highlight: function(element) {
				$(element).closest('.form-group').addClass('has-error');
			},
			unhighlight: function(element) {
				$(element).closest('.form-group').removeClass('has-error');
			},
			errorClass: 'help-block',
			focusInvalid: false,
			errorElement: 'span'
		});
		if (!$cardForm.valid()) {
			validationCheck = false; 
		}

		// UNVALID FORM
		// error_booking()
		if (!validationCheck) {
			$purchaseBtn.removeAttr('disabled');

			error_booking();
		}

		// VALID FORM
		// 1) check_card_token()
		// 2) creditConfirm()
		// 3) $purchaseForm.ajaxSubmit()
		else {
			$formMsgBody
			.html('<center>Please wait a moment... <i class="fa fa-circle-o-notch fa-spin"></i></center>')
			.addClass('alert alert-info')

			$formMessage.show();

			if (payment_method == 'Credit Cards') {
				// Clear the existing credit card token, just in case one has been set from a previous attempt
				$('#gift_card_token').val('');

				// Submit the card token request and wait for a response
				$cardForm.submit();

				// Wait until the card token is set before continuing (with throttling)
				check_card_token();
			}
		}
	}
	function showSuccessIcon(parent) {
		parent.append('<div class="icon icon--order-success svg"><svg xmlns="http://www.w3.org/2000/svg" width="72px" height="72px"><g fill="none" stroke="#8EC343" stroke-width="2"><circle cx="36" cy="36" r="35" style="stroke-dasharray:240px, 240px; stroke-dashoffset: 480px;"></circle><path d="M17.417,37.778l9.93,9.909l25.444-25.393" style="stroke-dasharray:50px, 50px; stroke-dashoffset: 0px;"></path></g></svg></div>').show();
	}
	$purchaseForm.submit(function(e) {
		e.preventDefault();
		submit_purchase();
	});
	</script>
<?php } ?>

<script>	
// MONEY FORMATTING
var form_symbol = '$';
var form_decimals = '2';
var form_separator = ',';
Number.prototype.formatMoney = function(decPlaces, thouSeparator, decSeparator) {
	var n = this,
	decPlaces = isNaN(decPlaces = Math.abs(decPlaces)) ? form_decimals : decPlaces,
	decSeparator = decSeparator == undefined ? "." : decSeparator,
	thouSeparator = thouSeparator == undefined ? form_separator : thouSeparator,
	sign = n < 0 ? "-" : "",
	i = parseInt(n = Math.abs(+n || 0).toFixed(decPlaces)) + "",
	j = (j = i.length) > 3 ? j % 3 : 0;

	var dec;
	var out = sign + (j ? i.substr(0, j) + thouSeparator : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thouSeparator);
	if(decPlaces) dec = Math.abs(n - i).toFixed(decPlaces).slice(2);
	if(dec) out += decSeparator + dec;
	return out;
};

/* FORM (#search) */
var $search = $('.search-section');
var $searchForm = $('#search');
var $searchText = $('#search-card-number');
var gcCur = "<?php echo $company->currency_symbol?>";
var today = parseInt('<?php echo strtotime("today");?>');

$searchForm.submit(function(e){
	e.preventDefault();

	var search = $searchText.val();

	if (search) {
		$search.find('.alert').removeClass('alert-danger alert-info').hide();

		$.ajax({
			url: '<?php echo $site->base?>/gift_card_ajax.php', 
			type: 'POST',
			data: {
				rezgoAction: 'getGiftCard',
				gcNum: search
			},
			success: function (data) {
				var json, success, err, msg, amt, exp, max, use;

				err = 0;
				json = data.split("|||");
				json = json.slice(-1)[0];
				gcData = JSON.parse(json);

				s = parseFloat(gcData.status);

				if (debug) console.log(gcData);

				if (s) {
					amt = parseFloat(gcData.amount);
					exp = parseInt(gcData.expires);
					max = parseInt(gcData.max_uses);
					use = parseInt(gcData.uses);
					msg = 'Gift Card Balance: ' + gcCur + amt.formatMoney();

					if (max && use >= max) {
						err = "Gift card max use reached.";
					}

					if (exp && today >= exp) {
						err = "Gift card expired.";
					}
				} else {
					err = 'Gift card not found. Please, make sure you entered a correct card number.';
				}

				// RESULT
				if (err) {
					$search.find('.alert .msg').html(err);
					$search.find('.alert').addClass('alert-danger').show();
				} else {
					$search.find('.alert .msg').html(msg);
					$search.find('.alert').addClass('alert-info').show();
				}
			},
			error: function () {
				var msg = 'Connection error. Please try again or contact Rezgo for customer support.';
				$search.find('.alert .msg').html(msg);
				$search.find('.alert').addClass('alert-danger').show();
			}
		});
	}
});
</script>