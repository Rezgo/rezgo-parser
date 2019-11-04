<?php
	// handle old-style booking requests
	if($_REQUEST[uid] && $_REQUEST[date]) {
		$for_array = array('adult', 'child', 'senior', 'price4', 'price5', 'price6', 'price7', 'price8', 'price9');
		$new_header = '/book?order=clear&add[0][uid]='.$_REQUEST[uid].'&add[0][date]='.$_REQUEST[date];
		foreach($for_array as $v) {
			if($_REQUEST[$v.'_num']) $new_header .= '&add[0]['.$v.'_num]='.$_REQUEST[$v.'_num'];
		}
		$site->sendTo($new_header);
	}

	$company = $site->getCompanyDetails();
	$companyCountry = $site->getCompanyCountry();

	// non-open date date_selection elements
	$date_types = array('always', 'range', 'week', 'days', 'single'); // centralize this?

?>

<link rel="stylesheet" href="<?php echo $site->path;?>/css/intlTelInput.css" />
<link rel="stylesheet" href="<?php echo $site->path;?>/css/chosen.min.css" />

<style>
.chosen-container-single .chosen-single-with-deselect span {
	padding-top:3px;
}
.chosen-container-single .chosen-single div {
	padding-top:5px;
}	
.chosen-container-single .chosen-single,
.chosen-container-multi .chosen-choices {
	background: none;
	border-radius: 4px;
	border-color: #CCC;
	box-shadow: none;
	padding: 5px 0px 0 8px;
	height: 34px;
}
.chosen-container-multi .chosen-choices li.search-choice {
	margin: 2px 5px 3px -1px;
}
.chosen-container-single .chosen-single abbr {
	top: 13px;
}                           
</style>

<script type="text/javascript" src="<?php echo $site->path;?>/js/jquery.form.js"></script>
<script type="text/javascript" src="<?php echo $site->path;?>/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="<?php echo $site->path;?>/js/jquery.selectboxes.js"></script>
<script type="text/javascript" src="<?php echo $site->path;?>/js/intlTelInput/intlTelInput.js"></script>
<script type="text/javascript" src="<?php echo $site->path;?>/js/chosen.jquery.min.js"></script>

<script>
	var elements = new Array();
	var split_total = new Array();
	var overall_total = '0';
	var modified_total = '0';
	var form_symbol = '$';
	var form_decimals = '2';
	var form_separator = ',';
</script>

<script>
	$(document).ready(function(){
		// Start international phone input
		$("#tour_sms").intlTelInput({
			initialCountry: '<?php echo $companyCountry?>',
			formatOnInit: true,
			preferredCountries: ['us', 'ca', 'gb', 'au'],
			utilsScript: '<?php echo $site->path;?>/js/intlTelInput/utils.js'
		});
		$("#tour_sms").on("keyup change blur countrychange", function() {
			$('#sms').val($("#tour_sms").intlTelInput("getNumber"));
		});
		// End international phone input

		// Validation Setup
		$.validator.setDefaults({
			highlight: function(element) {
				if ($(element).attr("type") == "checkbox") {
					$(element).closest('.rezgo-form-checkbox').addClass('has-error');
				} else if ($(element).attr("name")=="waiver") {
					$(element).parent().find('.error').show();
				} else {
					$(element).closest('.rezgo-form-input').addClass('has-error');
				}

				$(element).closest('.form-group').addClass('has-error');
			},
			unhighlight: function(element) {
				if ( $(element).attr("type") == "checkbox" ) {
					$(element).closest('.rezgo-form-checkbox').removeClass('has-error');
				} else {
					$(element).closest('.rezgo-form-input').removeClass('has-error');
				}

				$(element).closest('.form-group').removeClass('has-error');
			},
			focusInvalid: false,
			errorElement: 'span',
			errorClass: 'help-block',
			ignore: ":hidden:not(.chosen-select)",
			errorPlacement: function(error, element) {
				if ($(element).attr("name") == "name" || $(element).attr("name") == "pan" || $(element).attr("name") == "cvv" || $(element).attr("name") == "waiver") {
					error.hide();
				} else if ($(element).attr("name") == "agree_terms") {
					error.insertAfter(element.parent().parent());
				} else if ($(element).attr("type") == "checkbox") {
					error.insertAfter(element.siblings('.rezgo-form-comment'));
				} else {
					error.insertAfter(element);
				}
			}
		});
		$('#rezgo-book-form').validate({
			messages: {
				tour_first_name: {
					required: "Enter your first name"
				},
				tour_last_name: {
					required: "Enter your last name"
				},
				tour_address_1: {
					required: "Enter your address"
				},
				tour_city: {
					required: "Enter your city"
				},
				tour_postal_code: {
					required: "Enter postal code"
				},
				tour_phone_number: {
					required: "Enter your phone number"
				},
				tour_email_address: {
					required: "Enter a valid email address"
				},
				agree_terms: {
					required: "You must agree to the terms"
				}
			}
		});
	});
</script>

<script type="text/javascript" src="<?php echo $site->base; ?>/js/ie8.polyfils.min.js"></script>

<div id="rezgo-book-wrp" class="container-fluid rezgo-container rezgo-book-wrp">
	<div class="row">
		<div class="col-xs-12">
			<div class="jumbotron rezgo-book-form">
				<ul id="rezgo-book-tabs" class="nav nav-tabs" style="display:none">
					<li class="active">
						<a id="rezgo-book-step-one-btn" href="#rezgo-book-step-one" data-toggle="tab">Step 1</a>
					</li>
					<li>
						<a id="rezgo-book-step-two-btn" href="#rezgo-book-step-two" data-toggle="tab">Step 2</a>
					</li>
				</ul>

				<form id="rezgo-book-form" role="form" method="post" target="rezgo_content_frame">
					<div class="tab-content">
						<div id="rezgo-book-step-one" class="tab-pane active">
							<div class="row rezgo-breadcrumb-wrp">
								<ol class="breadcrumb rezgo-breadcrumb hidden-xs">
									<li id="rezgo-book-step-one-order"><a href="/order"><span>Your Order</span></a></li>
									<li id="rezgo-book-step-one-info" class="active"><span>Guest Information</span></li>
									<li id="rezgo-book-step-one-billing"><span>Billing Information</span></li>
									<li id="rezgo-book-step-one-confirmation"><span>Confirmation</span></li>
								</ol>
							</div>

							<?php
							$c = 0;
							$cart = $site->getCart(1); // get the cart, remove any dead entries
							
							if(!count($cart)) {
								$site->sendTo('/'.$site->base);
							}
							$cart_count = count($cart);
							?>
							<?php // start cart loop for each tour in the order ?>
							<?php foreach($cart as $item) { ?>
								<?php
								$required_fields = 0;
								$site->readItem($item);
								?>

								<?php if((int) $item->availability >= (int) $item->pax_count) { ?>
									<?php $c++; // only increment if it's still available ?>

									<div id="rezgo-book-step-one-item-<?php echo $item->uid?>" class="clearfix">

										<?php 
										if(in_array((string) $item->date_selection, $date_types)) {
											$booking_date = date("Y-m-d", (string)$item->booking_date);
										} else {
											$booking_date = 'open'; // for open availability
										} 
										?>

										<input type="hidden" name="booking[<?php echo $c?>][book]" value="<?php echo $item->uid?>" />
										<input type="hidden" name="booking[<?php echo $c?>][date]" value="<?php echo $booking_date?>" />
										<input type="hidden" name="booking[<?php echo $c?>][adult_num]" value="<?php echo $item->adult_num?>" />
										<input type="hidden" name="booking[<?php echo $c?>][child_num]" value="<?php echo $item->child_num?>" />
										<input type="hidden" name="booking[<?php echo $c?>][senior_num]" value="<?php echo $item->senior_num?>" />
										<input type="hidden" name="booking[<?php echo $c?>][price4_num]" value="<?php echo $item->price4_num?>" />
										<input type="hidden" name="booking[<?php echo $c?>][price5_num]" value="<?php echo $item->price5_num?>" />
										<input type="hidden" name="booking[<?php echo $c?>][price6_num]" value="<?php echo $item->price6_num?>" />
										<input type="hidden" name="booking[<?php echo $c?>][price7_num]" value="<?php echo $item->price7_num?>" />
										<input type="hidden" name="booking[<?php echo $c?>][price8_num]" value="<?php echo $item->price8_num?>" />
										<input type="hidden" name="booking[<?php echo $c?>][price9_num]" value="<?php echo $item->price9_num?>" />

										<div class="rezgo-booking-title-wrp">
											<h3 class="rezgo-booking-title">
												<span class="text-info">
													<span>Booking <?php echo $c?> of </span>
													<span class="rezgo-cart-count"></span>
													<span>&nbsp;</span>
												</span>
												<br />
												<span><?php echo $item->item?> &mdash; <?php echo $item->option?></span>
											</h3>

											<?php if(in_array((string) $item->date_selection, $date_types)) { ?>
												<h4 class="rezgo-booking-subtitle">
													<span><?php echo date((string) $company->date_format, (string)$item->booking_date)?></span>
												</h4>
											<?php } ?>

											<?php if($item->discount_rules->rule) { ?>
												<h4 class="rezgo-booking-discount rezgo-booking-subtitle-step-1">
													<span class="rezgo-discount-span">Discount:</span>

													<?php unset($discount_string); ?>

													<?php foreach($item->discount_rules->rule as $discount) {
														$discount_string .= ($discount_string) ? ', '.$discount : $discount;
													} ?>

													<span class="rezgo-red"><?php echo $discount_string?></span>
												</h4>
											<?php } ?>
										</div>

										<?php if($item->group != 'hide') { ?>
											<div class="row rezgo-booking-instructions">
												<span>To complete this booking, please fill out the following form. </span>
												<span id="required_note-<?php echo $c?>" <?php if($item->group == 'require' || $item->group == 'require_name') { echo ' style="display:inline;"'; } else { echo ' style="display:none;"'; } ?>>Please note that fields marked with <em class="fa fa-asterisk"></em> are required.</span>
											</div>

											<?php foreach($site->getTourPrices($item) as $price) { ?>
												<?php foreach($site->getTourPriceNum($price, $item) as $num) { ?>
													<div class="row rezgo-form-group rezgo-additional-info">
														<div class="col-sm-12 rezgo-sub-title">
															<span><?php echo $price->label?> (<?php echo $num?>)</span>
														</div>

														<div class="rezgo-form-row rezgo-form-one form-group rezgo-pax-first-last rezgo-first-last-<?php echo $item->uid?>">
															<label for="frm_<?php echo $c?>_<?php echo $price->name?>_<?php echo $num?>_first_name" class="col-sm-2 control-label rezgo-label-right">
																<span>First&nbsp;Name<?php if($item->group == 'require' || $item->group == 'require_name') { ?>&nbsp;<em class="fa fa-asterisk"></em><?php } ?></span>
															</label>

															<div class="col-sm-4 rezgo-form-input">
																<input type="text" class="form-control<?php echo ($item->group == 'require' || $item->group == 'require_name') ? ' required' : ''; ?>" id="frm_<?php echo $c?>_<?php echo $price->name?>_<?php echo $num?>_first_name" name="booking[<?php echo $c?>][tour_group][<?php echo $price->name?>][<?php echo $num?>][first_name]" />
															</div>

															<label for="frm_<?php echo $c?>_<?php echo $price->name?>_<?php echo $num?>_last_name" class="col-sm-2 control-label rezgo-label-right">
																<span>Last&nbsp;Name<?php if($item->group == 'require' || $item->group == 'require_name') { ?>&nbsp;<em class="fa fa-asterisk"></em><?php } ?></span>
															</label>

															<div class="col-sm-4 rezgo-form-input">
																<input type="text" class="form-control<?php echo ($item->group == 'require' || $item->group == 'require_name') ? ' required' : ''; ?>" id="frm_<?php echo $c?>_<?php echo $price->name?>_<?php echo $num?>_last_name" name="booking[<?php echo $c?>][tour_group][<?php echo $price->name?>][<?php echo $num?>][last_name]" />
															</div>
														</div>

														<?php if($item->group != 'request_name') { ?>
															<div class="rezgo-form-row rezgo-form-one form-group rezgo-pax-phone-email rezgo-phone-email-<?php echo $item->uid?>">
																<label for="frm_<?php echo $c?>_<?php echo $price->name?>_<?php echo $num?>_phone" class="col-sm-2 control-label rezgo-label-right">Phone<?php if($item->group == 'require') { ?>&nbsp;<em class="fa fa-asterisk"></em><?php } ?></label>

																<div class="col-sm-4 rezgo-form-input">
																	<input type="text" class="form-control<?php echo ($item->group == 'require') ? ' required' : ''; ?>" id="frm_<?php echo $c?>_<?php echo $price->name?>_<?php echo $num?>_phone" name="booking[<?php echo $c?>][tour_group][<?php echo $price->name?>][<?php echo $num?>][phone]" />
																</div>

																<label for="frm_<?php echo $c?>_<?php echo $price->name?>_<?php echo $num?>_email" class="col-sm-2 control-label rezgo-label-right">Email<?php if($item->group == 'require') { ?>&nbsp;<em class="fa fa-asterisk"></em><?php } ?></label>
																<div class="col-sm-4 rezgo-form-input">
																<input type="email" class="form-control<?php echo ($item->group == 'require') ? ' required' : ''; ?>" id="frm_<?php echo $c?>_<?php echo $price->name?>_<?php echo $num?>_email" name="booking[<?php echo $c?>][tour_group][<?php echo $price->name?>][<?php echo $num?>][email]" />
																</div>
															</div>
														<?php } ?>

														<?php $form_counter = 1; // form counter to create unique IDs ?>

														<?php foreach( $site->getTourForms('group') as $form ) { ?>
															<?php if($form->require) $required_fields++; ?>

															<?php if($form->type == 'text') { ?>
																<div class="form-group rezgo-custom-form rezgo-form-input">
																	<label><?php echo $form->title?><?php if($form->require) { ?> <em class="fa fa-asterisk"></em><?php } ?></label>

																	<input type="text" class="form-control<?php echo ($form->require) ? ' required' : ''; ?> " name="booking[<?php echo $c?>][tour_group][<?php echo $price->name?>][<?php echo $num?>][forms][<?php echo $form->id?>]" />

																	<p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>
																</div>
															<?php } ?>

															<?php if($form->type == 'select') { ?>
																<div class="form-group rezgo-custom-form rezgo-form-input">
																	<label><span><?php echo $form->title?><?php if($form->require) { ?> <em class="fa fa-asterisk"></em><?php } ?></span></label>

																	<select class="chosen-select form-control<?php echo ($form->require) ? ' required' : ''; ?> rezgo-custom-select" name="booking[<?php echo $c?>][tour_group][<?php echo $price->name?>][<?php echo $num?>][forms][<?php echo $form->id?>]">
																		<option value=""></option>
																		<?php foreach($form->options as $option) { ?>
																			<option><?php echo $option?></option>
																		<?php } ?>
																	</select>

																	<p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>

                                  <?php
																	if ($form->options_instructions) {
																		$optex_count = 1;
																		foreach($form->options_instructions as $opt_extra) {
																			echo '<span class="opt_extra" id="optex_'.$optex_count.'" style="display:none">'.$opt_extra.'</span>';
																			$optex_count++;
																		}
																	}
																	?>
																</div>
															<?php } ?>

															<?php if($form->type == 'multiselect') { ?>
																<div class="form-group rezgo-custom-form rezgo-form-input">
																	<label><span><?php echo $form->title?><?php if($form->require) { ?> <em class="fa fa-asterisk"></em><?php } ?></span></label>

																	<select class="chosen-select form-control<?php echo ($form->require) ? ' required' : ''; ?> rezgo-custom-select" multiple="multiple" name="booking[<?php echo $c?>][tour_group][<?php echo $price->name?>][<?php echo $num?>][forms][<?php echo $form->id?>][]">
																		<option value=""></option>
																		<?php foreach($form->options as $option) { ?>
																			<option><?php echo $option?></option>
																		<?php } ?>
																	</select>

																	<p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>

                                  <?php
																	if ($form->options_instructions) {
																		$optex_count = 1;
																		foreach($form->options_instructions as $opt_extra) {
																			echo '<span class="opt_extra" id="optex_'.$optex_count.'" style="display:none">'.$opt_extra.'</span>';
																			$optex_count++;
																		}
																	}
																	?>
																</div>
															<?php } ?>

															<?php if($form->type == 'textarea') { ?>
																<div class="form-group rezgo-custom-form rezgo-form-input">
																	<label><span><?php echo $form->title?><?php if($form->require) { ?> <em class="fa fa-asterisk"></em><?php } ?></span></label>

																	<textarea class="form-control<?php echo ($form->require) ? ' required' : ''; ?>" name="booking[<?php echo $c?>][tour_group][<?php echo $price->name?>][<?php echo $num?>][forms][<?php echo $form->id?>]" cols="40" rows="4"></textarea>

																	<p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>
																</div>
															<?php } ?>

															<?php if($form->type == 'checkbox') { ?>
																<div class="form-group rezgo-custom-form rezgo-form-input">
																	<div class="checkbox rezgo-form-checkbox">
																		<label for="<?php echo $form->id."|".base64_encode($form->title)."|".$form->price."|".$c."|".$price->name."|".$num; ?>">
																			<input type="checkbox"<?php echo ($form->require) ? ' class="required"' : ''; ?> id="<?php echo $form->id?>|<?php echo base64_encode($form->title)?>|<?php echo $form->price?>|<?php echo $c?>|<?php echo $price->name?>|<?php echo $num?>" name="booking[<?php echo $c?>][tour_group][<?php echo $price->name?>][<?php echo $num?>][forms][<?php echo $form->id?>]" <?php if ($form->price) { ?>onclick="if (this.checked) { add_element('<?php echo $form_counter?>', '<?php echo base64_encode($form->title)?>', '<?php if ($form->price_mod == '-') { ?><?php echo $form->price_mod?><?php } ?><?php echo $form->price?>', '<?php echo $c?>'); } else { sub_element('<?php echo $form_counter?>', '<?php if ($form->price_mod == '-') { ?><?php echo $form->price_mod?><?php } ?><?php echo $form->price?>', '<?php echo $c?>'); }"<?php } ?> />
																			<span><?php echo $form->title?></span>
																			<?php if ($form->require) { ?> <em class="fa fa-asterisk"></em><?php } ?>
																			<?php if ($form->price) { ?> <em><?php echo $form->price_mod?> <?php echo $site->formatCurrency($form->price)?></em><?php } ?>
																			<p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>
																		</label>
																	</div>
																</div>
															<?php } ?>

															<?php if($form->type == 'checkbox_price') { ?>
																<div class="form-group rezgo-custom-form rezgo-form-input">
																	<div class="checkbox rezgo-form-checkbox">
																		<label for="<?php echo $form->id?>|<?php echo base64_encode($form->title)?>|<?php echo $form->price?>|<?php echo $c?>|<?php echo $price->name?>|<?php echo $num?>">
																			<input type="checkbox"<?php echo ($form->require) ? ' class="required"' : ''; ?> id="<?php echo $form->id?>|<?php echo base64_encode($form->title)?>|<?php echo $form->price?>|<?php echo $c?>|<?php echo $price->name?>|<?php echo $num?>" name="booking[<?php echo $c?>][tour_group][<?php echo $price->name?>][<?php echo $num?>][forms][<?php echo $form->id?>]" <?php if ($form->price) { ?>onclick="if (this.checked) { add_element('<?php echo $form_counter?>', '<?php echo base64_encode($form->title)?>', '<?php if ($form->price_mod == '-') { ?><?php echo $form->price_mod?><?php } ?><?php echo $form->price?>', '<?php echo $c?>'); } else { sub_element('<?php echo $form_counter?>', '<?php if ($form->price_mod == '-') { ?><?php echo $form->price_mod?><?php } ?><?php echo $form->price?>', '<?php echo $c?>'); }"<?php } ?> />
																			<span><?php echo $form->title?></span>
																			<?php if ($form->require) { ?> <em class="fa fa-asterisk"></em><?php } ?>
																			<?php if ($form->price) { ?> <em><?php echo $form->price_mod?> <?php echo $site->formatCurrency($form->price)?></em><?php } ?>
																			<p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>
																		</label>
																	</div>
																</div>
															<?php } ?>
															<?php $form_counter++; ?>
														<?php } // end foreach($site->getTourForms ?>
													</div>
												<?php } // end foreach($site->getTourPriceNum ?>
											<?php } ?>
										<?php } ?>

										<?php if($site->getTourForms('primary')) { ?>
											<?php if($item->group == 'hide') { ?>
												<div class="row rezgo-booking-instructions">
													<span>To complete this booking, please fill out the following form.</span>
												</div>
											<?php } ?>

											<div class="row rezgo-form-group rezgo-additional-info">
												<div class="col-sm-12 rezgo-sub-title">
													<span>Additional Information</span>
												</div>

												<div class="clearfix rezgo-short-clearfix">&nbsp;</div>

												<?php foreach($site->getTourForms('primary') as $form) { ?>
													<?php if($form->require) $required_fields++; ?>

													<?php if($form->type == 'text') { ?>
														<div class="form-group rezgo-custom-form rezgo-form-input">
															<label><?php echo $form->title?><?php if($form->require) { ?> <em class="fa fa-asterisk"></em><?php } ?></label>
															<input type="text" class="form-control<?php echo ($form->require) ? ' required' : ''; ?>" name="booking[<?php echo $c?>][tour_forms][<?php echo $form->id?>]" />
															<p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>
														</div>
													<?php } ?>

													<?php if($form->type == 'select') { ?>
														<div class="form-group rezgo-custom-form rezgo-form-input">
															<label><?php echo $form->title?><?php if($form->require) { ?> <em class="fa fa-asterisk"></em><?php } ?></label>
															<select class="chosen-select form-control<?php echo ($form->require) ? ' required' : ''; ?> rezgo-custom-select" name="booking[<?php echo $c?>][tour_forms][<?php echo $form->id?>]">
																<option value=""></option>
																<?php foreach($form->options as $option) { ?>
																	<option><?php echo $option?></option>
																<?php } ?>
															</select>
															<p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>

															<?php
                              if ($form->options_instructions) {
																$optex_count = 1;
                                foreach($form->options_instructions as $opt_extra) {
                                  echo '<span class="opt_extra" id="optex_'.$optex_count.'" style="display:none">'.$opt_extra.'</span>';
																	$optex_count++;
                                }
                              }
                              ?>
														</div>
													<?php } ?>

													<?php if($form->type == 'multiselect') { ?>
														<div class="form-group rezgo-custom-form rezgo-form-input">
															<label><?php echo $form->title?><?php if($form->require) { ?> <em class="fa fa-asterisk"></em><?php } ?></label>
															<select class="chosen-select form-control<?php echo ($form->require) ? ' required' : ''; ?> rezgo-custom-select" multiple="multiple" name="booking[<?php echo $c?>][tour_forms][<?php echo $form->id?>][]">
																<option value=""></option>
																<?php foreach($form->options as $option) { ?>
																	<option><?php echo $option?></option>
																<?php } ?>
															</select>
															<p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>

															<?php
                              if ($form->options_instructions) {
																$optex_count = 1;
                                foreach($form->options_instructions as $opt_extra) {
                                  echo '<span class="opt_extra" id="optex_'.$optex_count.'" style="display:none">'.$opt_extra.'</span>';
																	$optex_count++;
                                }
                              }
                              ?>
														</div>
													<?php } ?>

													<?php if($form->type == 'textarea') { ?>
														<div class="form-group rezgo-custom-form rezgo-form-input">
															<label><?php echo $form->title?><?php if($form->require) { ?> <em class="fa fa-asterisk"></em><?php } ?></label>
															<textarea class="form-control<?php echo ($form->require) ? ' required' : ''; ?>" name="booking[<?php echo $c?>][tour_forms][<?php echo $form->id?>]" cols="40" rows="4"></textarea>
															<p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>
														</div>
													<?php } ?>

													<?php if($form->type == 'checkbox') { ?>
														<div class="form-group rezgo-custom-form rezgo-form-input">
															<div class="checkbox rezgo-form-checkbox">
																<label for="<?php echo $form->id?>|<?php echo base64_encode($form->title)?>|<?php echo $form->price?>|<?php echo $c?>|<?php echo $price->name?>|<?php echo $num?>">
																	<input type="checkbox"<?php echo ($form->require) ? ' class="required"' : ''; ?> id="<?php echo $form->id?>|<?php echo base64_encode($form->title)?>|<?php echo $form->price?>|<?php echo $c?>|<?php echo $price->name?>|<?php echo $num?>" name="booking[<?php echo $c?>][tour_forms][<?php echo $form->id?>]" <?php if ($form->price) { ?>onclick="if (this.checked) { add_element('<?php echo $form_counter?>', '<?php echo base64_encode($form->title)?>', '<?php if ($form->price_mod == '-') { ?><?php echo $form->price_mod?><?php } ?><?php echo $form->price?>', '<?php echo $c?>'); } else { sub_element('<?php echo $form_counter?>', '<?php if ($form->price_mod == '-') { ?><?php echo $form->price_mod?><?php } ?><?php echo $form->price?>', '<?php echo $c?>'); }"<?php } ?> />
																	<span><?php echo $form->title?></span>
																	<?php if ($form->require) { ?> <em class="fa fa-asterisk"></em><?php } ?>
																	<?php if ($form->price) { ?> <em><?php echo $form->price_mod?> <?php echo $site->formatCurrency($form->price)?></em><?php } ?>
																	<p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>
																</label>
															</div>
														</div>
													<?php } ?>

													<?php if($form->type == 'checkbox_price') { ?>
														<div class="form-group rezgo-custom-form rezgo-form-input">
															<div class="checkbox rezgo-form-checkbox">
																<label for="<?php echo $form->id?>|<?php echo base64_encode($form->title)?>|<?php echo $form->price?>|<?php echo $c?>|<?php echo $price->name?>|<?php echo $num?>">
																	<input type="checkbox"<?php echo ($form->require) ? ' class="required"' : ''; ?> id="<?php echo $form->id?>|<?php echo base64_encode($form->title)?>|<?php echo $form->price?>|<?php echo $c?>|<?php echo $price->name?>|<?php echo $num?>" name="booking[<?php echo $c?>][tour_forms][<?php echo $form->id?>]" <?php if ($form->price) { ?>onclick="if (this.checked) { add_element('<?php echo $form_counter?>', '<?php echo base64_encode($form->title)?>', '<?php if ($form->price_mod == '-') { ?><?php echo $form->price_mod?><?php } ?><?php echo $form->price?>', '<?php echo $c?>'); } else { sub_element('<?php echo $form_counter?>', '<?php if ($form->price_mod == '-') { ?><?php echo $form->price_mod?><?php } ?><?php echo $form->price?>', '<?php echo $c?>'); }"<?php } ?> />
																	<span><?php echo $form->title?></span>
																	<?php if ($form->require) { ?> <em class="fa fa-asterisk"></em><?php } ?>
																	<?php if ($form->price) { ?> <em><?php echo $form->price_mod?> <?php echo $site->formatCurrency($form->price)?></em><?php } ?>
																	<p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>
																</label>
															</div>
														</div>
													<?php } ?>
													<?php $form_counter++; ?>
												<?php } // end foreach($site->getTourForms('primary') ?>
											</div>
										<?php } ?>

										<?php if($item->group == 'hide' && count ($site->getTourForms('primary')) == 0) { ?>
											<div class='rezgo-guest-info-not-required'>
												<span>Guest information is not required for booking #<?php echo $c?></span>
											</div>
										<?php } ?>

										<?php if($required_fields > 0) { ?>
											<script>$(document).ready(function(){$('#required_note-<?php echo $c?>').fadeIn();});</script>
										<?php } ?>
                    
										<?php if ($item->pick_up_locations) { ?>
                    <div class="row rezgo-form-group rezgo-additional-info">
                      <div class="col-sm-12 rezgo-sub-title">
                        <span>Transportation</span>
                      </div>

                      <div class="clearfix rezgo-short-clearfix">&nbsp;</div>
                      
                      <?php $pickup_locations = $site->getPickupList((int) $item->uid); ?>
                      
                      <div class="form-group rezgo-custom-form rezgo-form-input">
                        <label>Choose your pickup location</label>
                        <select class="chosen-select form-control rezgo-pickup-select" name="booking[<?php echo $c?>][pickup]" data-target="rezgo-pickup-detail-<?php echo $c?>" data-id="<?php echo $c?>" data-counter="<?php echo $form_counter?>" data-option="<?php echo $item->uid?>" data-pax="<?php echo $item->pax?>">
                          <option value=""></option>
													<?php
													
														foreach($pickup_locations->pickup as $pickup) { 
															
															$cost = ((int) $pickup->cost > 0) ? ' ('.$site->formatCurrency($pickup->cost).')' : ''; 
													
															if($pickup->sources) { 
															
																echo '<optgroup label="Pickup At: '.$pickup->name.' - '.$pickup->location_address.$cost.'">'."\n";
																	
																$s=0;
																foreach($pickup->sources->source as $source) {
																	echo '<option value="'.$pickup->id.'-'.$s.'">'.$source->name.'</option>'."\n";
																	$s++;
																}
																
																echo '</optgroup>'."\n";
																
															} else { 
																echo '<option value="'.$pickup->id.'">'.$pickup->name.' - '.$pickup->location_address.$cost.'</option>'."\n";
															} 
															
														}
													
													?>
                          
                        </select>
                        <input type="hidden" id="rezgo-pickup-price-<?php echo $c?>" value="0" />
                        <?php $form_counter++; ?>
                      </div>
                      
                      <div id="rezgo-pickup-detail-<?php echo $c?>" class="rezgo-pickup-detail"></div>
                      
                    </div>   
                    
                    <?php } ?>                                  
                    
										<span class="rezgo-booking-memo rezgo-booking-memo-<?php echo $item->uid?>"></span>
                  
                  </div><!-- // rezgo-book-step-one-item -->
								<?php } else { $cart_count--; } ?>
							<?php } ?>
							<?php // end cart loop for each tour in the order ?>

							<div class="clearfix rezgo-booking-cta">
								<div class="row" id="rezgo-booking-btn">
									<div class="col-sm-6 col-xs-3 rezgo-btn-wrp rezgo-chevron-left">
										<?php if($site->getCartState()) { ?>
											<button id="rezgo-book-step-one-btn-back" class="btn rezgo-btn-default btn-lg center-block" type="button" onclick="window.top.location.href='/order'; return false;">
												<span class="hidden-xs">Back to order</span>
												<span class="visible-xs-inline">
													<span class="glyphicon glyphicon-chevron-left"></span>
												</span>
											</button>
										<?php } ?>
									</div>

									<div class="col-sm-6 col-xs-9 rezgo-btn-wrp">
										<button id="rezgo-book-step-one-btn-continue" class="btn rezgo-btn-book btn-lg center-block" type="button" onClick="stepForward();">
											<span>Continue</span>
										</button>
									</div>
								</div>
							</div>
						</div><!-- // #book_step_one -->

						<script>$(document).ready(function(){
							$('.rezgo-cart-count').text('<?php echo $cart_count?>');

							// copy info from first pax to billing fields
							$( "#rezgo-copy-pax" ).click(function() {
								if($(this).prop("checked") == true) {
									$('#tour_first_name').val($('#frm_1_adult_1_first_name').val());
									$('#tour_last_name').val($('#frm_1_adult_1_last_name').val());
									$('#tour_email_address').val($('#frm_1_adult_1_email').val());
									$('#tour_phone_number').val($('#frm_1_adult_1_phone').val());
								}
								else if($(this).prop("checked") == false) {
									$('#tour_first_name').val('');
									$('#tour_last_name').val('');
									$('#tour_email_address').val('');
									$('#tour_phone_number').val('');
								}
							});
						});</script>

						<div id="rezgo-book-step-two" class="tab-pane">
							<div id="rezgo-book-step-two-anchor"></div>

							<div id="rezgo-book-step-two-crumb" class="row">
								<ol class="breadcrumb rezgo-breadcrumb hidden-xs">
									<?php // if($site->getCartState()) { ?>
										<li id="rezgo-book-step-two-order"><a href="/order"><span>Your Order</span></a></li>
									<?php // } ?>
									<li id="rezgo-book-step-two-info">
										<a href="#" onClick="$('#rezgo-book-tabs a:first').tab('show'); return false;">
											<span>Guest Information</span>
										</a>
									</li>
									<li id="rezgo-book-step-two-billing" class="active"><span>Billing Information</span></li>
									<li id="rezgo-book-step-two-confirmation"><span>Confirmation</span></li>
								</ol>
							</div>

							<?php $c = 0; // start cart loop for each booking in the order ?>

							<?php foreach($cart as $item) { ?>
								<?php $site->readItem($item); ?>

								<?php if(DEBUG) { ?>
									<div class="row">
										<pre style="max-height:100px; overflow-y:auto; margin:15px 0"><?php var_dump($item); ?></pre>
									</div>
								<?php } ?>

								<?php if((int) $item->availability >= (int) $item->pax_count) { ?>
									<?php $c++; // only increment if it's still available ?>

									<div id="rezgo-book-step-two-item-<?php echo $item->uid?>" class="row rezgo-form-group rezgo-booking-info">
										<script>split_total[<?php echo $c?>] = <?php echo $item->overall_total?>;</script>
                    
										<div class="rezgo-booking-title-wrp">
											<h3 class="rezgo-booking-of rezgo-booking-title">
												<span class="text-info">
													<span>Booking <?php echo $c?> of </span>
													<span class="rezgo-cart-count"></span>
													<span>&nbsp;</span>
												</span>
												<br />
												<span><?php echo $item->item?> &mdash; <?php echo $item->option?></span>
											</h3>
										</div>

										<div class="col-md-5 col-sm-12 col-xs-12 rezgo-table-container">
											<table class="rezgo-table-list" border="0" cellspacing="0" cellpadding="2">
												<?php if(in_array((string) $item->date_selection, $date_types)) {	?>
													<tr class="rezgo-tr-date">
														<td class="rezgo-td-label"><span>Date:</span></td>
														<td class="rezgo-td-data"><span><?php echo date((string) $company->date_format, (string) $item->booking_date)?></span></td>
													</tr>
												<?php } ?>

												<?php if($item->duration != '') { ?>
													<tr class="rezgo-tr-duration">
														<td class="rezgo-td-label"><span>Duration:</span></td>
														<td class="rezgo-td-data"><span><?php echo $item->duration?></span></td>
													</tr>
												<?php } ?>

												<?php if($item->discount_rules->rule) { ?>
													<tr class="rezgo-tr-discount">
														<td class="rezgo-td-label rezgo-booking-discount">
															<span class="rezgo-discount-span">Discount:</span>
														</td>
														<td class="rezgo-td-data">
															<span>
																<?php
																unset($discount_string);
																foreach($item->discount_rules->rule as $discount) {
																	$discount_string .= ($discount_string) ? ', '.$discount : $discount;
																}
																?>
																<span class="rezgo-red"><?php echo $discount_string?></span>
															</span>
														</td>
													</tr>
												<?php } ?>
											</table>
										</div>

										<div class="col-md-7 col-sm-12 col-xs-12 rezgo-table-container">
											<table class="table table-bordered table-striped table-responsive rezgo-billing-cart" id="<?php echo $item->uid?>">
												<tr class="rezgo-tr-head">
													<td class="text-right"><label>Type</label></td>
													<td class="text-right"><label class="hidden-xs">Qty.</label></td>
													<td class="text-right"><label>Cost</label></td>
													<td class="text-right"><label>Total</label></td>
												</tr>

												<?php foreach($site->getTourPrices($item) as $price ) { ?>
													<?php if($item->{$price->name.'_num'}) { ?>
														<tr class="rezgo-tr-pax">
															<td class="text-right"><span><?php echo $price->label?></span></td>
															<td class="text-right"><span><?php echo $item->{$price->name.'_num'}?></span></td>
															<td class="text-right">
																<span>
																	<?php if($site->exists($price->base)) { ?>
																		<span class="discount"><?php echo $site->formatCurrency($price->base)?></span>
																	<?php } ?>
																	<?php echo $site->formatCurrency($price->price)?>
																</span>
															</td>
															<td class="text-right">
																<span>
																	<?php if($site->exists($price->base)) { ?>
																		<span class="discount"></span>
																	<?php } ?>
																	<?php echo $site->formatCurrency($price->total)?>
																</span>
															</td>
														</tr>
													<?php } ?>
												<?php } ?>

												<tr class="rezgo-tr-subtotal">
													<td colspan="3" class="text-right"><strong>Subtotal</strong></td>
													<td class="text-right"><span><?php echo $site->formatCurrency($item->sub_total)?></span></td>
												</tr>

												<tbody id="line_item_box_<?php echo $c?>" class="rezgo-line-item-box" data-line-uid="<?php echo $item->uid?>" data-book-id="<?php echo $c?>">
												</tbody><!-- line items -->
                        <input type="hidden" id="rezgo-pickup-line-<?php echo $c?>" value="" />

												<tbody id="fee_box_<?php echo $c?>" class="rezgo-fee-box">
												</tbody><!-- extra fees -->

												<tr class="rezgo-tr-total">
													<td colspan="3" class="text-right">
														<strong>Total</strong>
													</td>
													<td class="text-right">
														<span class="rezgo-item-total" id="total_value_<?php echo $c?>" rel="<?php echo $item->overall_total?>">
															<?php echo $site->formatCurrency($item->overall_total)?>
                            </span>
                            <input type="hidden" id="total_extras_<?php echo $c?>" value="" />
													</td>
												</tr>

												<tbody class="rezgo-gc-box" style="display:none">
													<tr class="rezgo-tr-gift-card">
														<td colspan="3" class="text-right alert-info">
															<strong>Gift Card</strong>
														</td>
														<td class="text-right alert-info">
															<strong><span>-</span> <span class="cur"></span><span class="rezgo-gc-min"></span></strong>
														</td>
													</tr>
												</tbody>

												<?php if($site->exists($item->deposit)) { ?>
													<tr class="rezgo-tr-deposit">
														<td colspan="3" class="text-right">
															<strong>Deposit to Pay Now</strong>
														</td>
														<td class="text-right">
															<strong class="rezgo-item-deposit" id="deposit_value_<?php echo $c?>" rel="<?php echo $item->deposit_value?>">
																<?php echo $site->formatCurrency($item->deposit_value)?>
                              </strong>
														</td>
													</tr>

													<?php $complete_booking_total += (float) $item->deposit_value; ?>
                          
												<?php } else { ?>
                        
													<?php $complete_booking_total += (float) $item->overall_total; ?>
                          
												<?php } ?>
											</table>
										</div>
									</div>
								<?php } // end if((int) $item->availability >= (int) $item->pax_count) ?>
							<?php } // end foreach($cart as $item ) ?>

							<script>
								overall_total = '<?php echo $complete_booking_total?>';
								form_decimals = '<?php echo $item->currency_decimals?>';
								form_symbol = '<?php echo $item->currency_symbol?>';
								form_separator = '<?php echo $item->currency_separator?>';
							</script>

							<!-- BOOKING TOTAL -->
							<div class="rezgo-total-payable-wrp">
								<div class="row">
									<div class="col-sm-7 col-xs-12 col-sm-offset-5 rezgo-total-payable">
										<span>Total<span class="hidden-xs"> to Pay Now</span>:</span>

										<span id="total_value" rel="<?php echo $complete_booking_total?>"><?php echo $site->formatCurrency($complete_booking_total)?></span>

										<input type="hidden" id="expected" name="expected" value="<?php echo $complete_booking_total?>"/>
									</div>

									<div class="clearfix visible-xs"></div>
								</div>
							</div>
							<?php
								$site_base_currency = strtoupper($site->getBookingCurrency());
							?>
							<!-- BILLING INFO -->
							<div class="rezgo-billing-wrp">
								<div class="row rezgo-form-group rezgo-booking">
									<div class="col-xs-12">
										<h3 class="text-info">
											<span>Billing Information &nbsp;</span> 
											<span id="rezgo-copy-pax-span" style="display:none">
												<br class="visible-xs-inline"/>
												<input type="checkbox" name="copy_pax" id="rezgo-copy-pax" />
												<span id="rezgo-copy-pax-desc" class="rezgo-memo">Use first passenger information</span>
											</span>
										</h3>

										<div class="form-group">
											<label for="tour_first_name" class="control-label">Name</label>

											<div class="rezgo-form-row">
												<div class="col-sm-6 rezgo-form-input">
													<input type="text" class="form-control" id="tour_first_name" name="tour_first_name" value="<?php echo $site->requestStr('tour_first_name')?>" placeholder="First Name" />
												</div>

												<div class="col-sm-6 rezgo-form-input">
													<input type="text" class="form-control" id="tour_last_name" name="tour_last_name" value="<?php echo $site->requestStr('tour_last_name')?>" placeholder="Last Name" />
												</div>
											</div>
										</div>

										<div class="form-group">
											<label for="tour_address_1" class="control-label">Address</label>

											<div class="rezgo-form-input col-xs-12">
												<input type="text" class="form-control" id="tour_address_1" name="tour_address_1" placeholder="Address 1" />
											</div>
										</div>

										<div class="form-group clearfix">
											<div class="rezgo-form-input col-xs-12">
												<input type="text" class="form-control" id="tour_address_2" name="tour_address_2" placeholder="Address 2 (optional)" />
											</div>
										</div>

										<div class="form-group">
											<div class="rezgo-form-row">
												<label for="tour_city" class="control-label col-sm-8 col-xs-12 rezgo-form-label">City</label>
												<label for="tour_postal_code" class="control-label col-sm-4 hidden-xs rezgo-form-label">Zip/Postal</label>
											</div>

											<div class="rezgo-form-row">
												<div class="col-sm-8 col-xs-12 rezgo-form-input">
													<input type="text" class="form-control" id="tour_city" name="tour_city" placeholder="City" />
												</div>

												<label for="tour_postal_code" class="control-label col-xs-12 hidden-lg hidden-md hidden-sm rezgo-form-label">Zip/Postal</label>
												<div class="col-sm-4 col-xs-12 rezgo-form-input">
													<input type="text" class="form-control" id="tour_postal_code" name="tour_postal_code" placeholder="Zip/Postal Code" />
												</div>
											</div>
										</div>

										<div class="form-group">
											<div class="rezgo-form-row">
												<label for="tour_country" class="control-label col-sm-8 rezgo-form-label">Country</label>
												<label for="tour_stateprov" class="control-label col-sm-4 hidden-xs rezgo-form-label">State/Prov</label>
											</div>

											<div class="rezgo-form-row">
												<div class="col-sm-8 col-xs-12 rezgo-form-input">
												<select class="form-control" name="tour_country" id="tour_country">
													<option value=""></option>
													<?php foreach($site->getRegionList() as $iso => $name ) { ?>
														<option value="<?php echo $iso?>" <?php echo (($iso == $companyCountry) ? 'selected' : '')?>><?php echo ucwords($name)?></option>
													<?php } ?>
												</select>
												</div>

												<div class="col-sm-4 col-xs-12 rezgo-form-input">
													<div class="rezgo-form-row hidden-lg hidden-md hidden-sm">
														<label for="tour_stateprov" class="control-label col-xs-12 rezgo-form-label">State/Prov</label>
													</div>
													<select class="form-control" id="tour_stateprov" style="display:<?php echo (($companyCountry != 'ca' && $companyCountry != 'us' && $companyCountry != 'au') ? 'none' : '')?>;"></select>
													<input id="tour_stateprov_txt" class="form-control" name="tour_stateprov" type="text" value="" style="display:<?php echo (($companyCountry != 'ca' && $companyCountry != 'us' && $companyCountry != 'au') ? '' : 'none')?>;" />
												</div>
											</div>
										</div>

										<div class="form-group">
											<div class="rezgo-form-row">
												<label for="tour_email_address" class="control-label col-sm-6 rezgo-form-label">Email</label>
												<label for="tour_phone_number" class="control-label col-sm-6 hidden-xs rezgo-form-label">Phone</label>
											</div>

											<div class="rezgo-form-row">
												<div class="col-sm-6 col-xs-12 rezgo-form-input">
													<input type="email" class="form-control" id="tour_email_address" name="tour_email_address" placeholder="Email" />
												</div>

												<label for="tour_phone_number" class="control-label col-sm-6 col-xs-12 hidden-lg hidden-md hidden-sm rezgo-form-label">Phone</label>

												<div class="col-sm-6 col-xs-12 rezgo-form-input">
													<input type="text" class="form-control" id="tour_phone_number" name="tour_phone_number" placeholder="Phone" />
												</div>
											</div>
										</div>

										<div class="form-group rezgo-sms">
											<div class="rezgo-form-row">
													<span>Would you like to receive SMS messages regarding your booking? If so, please enter your mobile number in the space provided. Please note that your provider may charge additional fees.</span>
											</div>
										</div>

										<div class="form-group rezgo-sms-input">
											<div class="rezgo-form-row">
												<label for="tour_sms" class="control-label col-sm-12 rezgo-form-label">SMS</label>
											</div>
											<div class="rezgo-form-row">
												<div class="col-sm-12 rezgo-form-input">
													<input type="text" name="tour_sms" id="tour_sms" class="form-control col-xs-12" value="" />
													<input type="hidden" name="sms" id="sms" value="" />
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<hr>

							<!-- GIFT CARD -->
							<?php if(!$site->isVendor() ) { ?>
								<div id="rezgo-gift-card-use" style="<?php echo (($complete_booking_total > 0) ? '' : 'display:none;')?>">
									<div class="row rezgo-form-group rezgo-booking">
										<div class="col-xs-12">
											<?php require 'gift_card_redeem.php'; ?>
										</div>
									</div>
								</div>

								<hr id="rezgo-gift-card-use-hr" style="<?php echo (($complete_booking_total > 0) ? '' : 'display:none;')?>">
							<?php } ?>

							<!-- WAIVER -->
							<?php if(!$site->isVendor()) { ?>
								<?php
								$waiver = 0;
								$waiver_ids = '';
								foreach($cart as $item) {
									if((int) $item->waiver === 1 && (int) $item->waiver['type'] === 0) {
										$waiver++;
										$waiver_ids .= $item->uid.',';
									}
								}
								?>

								<?php if($waiver >= 1) { ?>
									<div id="rezgo-waiver-use">
										<div id="rezgo-waiver" class="row rezgo-form-group rezgo-booking">
											<div class="col-xs-12">
												<h3 class="text-info"><span>Waiver</span></h3>

												<div class="row">
													<div id="rezgo-waiver-info" class="col-xs-12">
														<div class="msg intro">
															<span>You must read and sign the liability waiver to complete this order.</span>
														</div>

														<div class="msg success" style="display:none">
															<i class="fa fa-check" aria-hidden="true"></i>
															<span>Thank you for signing the waiver.</span>
														</div>

														<div class="msg error" style="display:none">
															<i class="fa fa-times" aria-hidden="true"></i>
															<span>Waiver signature is required.</span>
														</div>
													</div>

													<div class="col-md-6">
														<button id="rezgo-waiver-show" class="btn rezgo-btn-default btn-lg btn-block" type="button" data-ids="<?php echo rtrim($waiver_ids,',')?>">
															<span><i class="fa fa-pencil-square-o"></i>&nbsp;<span>read and sign waiver</span></span>
														</button>
													</div>

													<div id="rezgo-waiver-signature" class="col-md-6">
														<div class="row">
															<div class="col-xs-12">
																<img class="signature" style="display:none">
															</div>
														</div>
													</div>

													<input id="rezgo-waiver-input" name="waiver" type="text" value="" required />
												</div>
											</div>
										</div>
									</div>

									<hr>
								<?php } ?>
							<?php } ?>

							<!-- PAYMENT INFO -->
							<div class="rezgo-payment-wrp">
								<div class="row rezgo-form-group rezgo-booking">
									<div class="col-xs-12">
										<h3 class="text-info" id="payment_info_head" style="<?php echo (($complete_booking_total > 0) ? '' : 'display:none;')?>">
											<span>Payment Information</span>
										</h3>

										<div class="rezgo-payment-frame" id="payment_info" style="<?php echo (($complete_booking_total > 0) ? '' : 'display:none;')?>">
											<div class="form-group" id="payment_methods">
												<?php
													$card_fa_logos = array(
														'visa' => 'fa-cc-visa',
														'mastercard' => 'fa-cc-mastercard',
														'american express' => 'fa-cc-amex',
														'discover' => 'fa-cc-discover'
													);
													$pmc = 1; // payment method counter 1
												?>

												<?php foreach($site->getPaymentMethods() as $pay ) { ?>
													<?php if($pay[name] == 'Credit Cards') { ?>
														<div class="rezgo-input-radio">
															<input type="radio" name="payment_method" id="payment_method_credit" class="rezgo-payment-method" value="Credit Cards" checked onclick="toggleCard();" />

															<span>&nbsp;&nbsp;</span>

															<label for="payment_method_credit">
																<span class="hidden-xs">Credit </span>
																<span>Card</span>
																<span>&nbsp;&nbsp;</span>
																<?php foreach($site->getPaymentCards() as $card ) { ?>
																	<img src="<?php echo $site->path;?>/img/logos/<?php echo $card?>.png" class="hidden-xs" />
																	<span class="visible-xs-inline">
																		<i class="fa <?php echo $card_fa_logos[$card]?>"></i>
																	</span>
																<?php } ?>
															</label>

															<input type="hidden" name="tour_card_token" id="tour_card_token" value="" />

															<script>
																$(document).ready(function() {
																	$('#tour_card_token').val('');
																	setTimeout(function() {
																		$('#payment_method_credit').attr('checked', true);
																	}, 600);
																});
															</script>
														</div>
													<?php } else { ?>
														<?php if ($pay[name] == 'PayPal') { ?>
															<?php $set_name = '
															<img src="'.$site->path.'/img/logos/paypal.png" style="height:30px; width:auto;" class="hidden-xs" />
															<span class="visible-xs-inline">PayPal <i class="fa fa-cc-paypal"></i></span>
															'; ?>
														<?php } else { ?>
															<?php $set_name = $pay[name]; ?>
														<?php } ?>

														<div class="rezgo-input-radio">
															<input type="radio" name="payment_method" id="payment_method_<?php echo $pmc?>" class="rezgo-payment-method" value="<?php echo $pay[name]?>" onclick="toggleCard();" />

															<span>&nbsp;&nbsp;</span>

															<label for="payment_method_<?php echo $pmc?>"><?php echo $set_name?></label>
														</div>

														<?php $pmc++; ?>
													<?php } ?>
												<?php } // end foreach($site->getPaymentMethods() ?>
											</div><!-- // #payment_methods -->

											<div id="payment_data">
												<?php $pmdc = 1; // payment method counter 1 ?>

												<?php foreach($site->getPaymentMethods() as $pay ) { ?>
													<?php if($pay[name] == 'Credit Cards') { ?>
														<div id="payment_cards">
															<iframe scrolling="no" frameborder="0" name="tour_payment" id="tour_payment" src="<?php echo $site->base?>/booking_payment.php"></iframe>

															<script type="text/javascript">
																iFrameResize ({
																	enablePublicMethods: true,
																	scrolling: false
																}, '#tour_payment');
															</script>
														</div>
													<?php } else { ?>
														<div id="payment_method_<?php echo $pmdc?>_box" class="payment_method_box" style="display:none;">
															<?php if($pay[add]) { ?>
																<div id="payment_method_<?php echo $pmdc?>_container" class="payment_method_container">
																	<label><?php echo $pay[add]?></label>
																	<br/>
																	<input type="text" id="payment_method_<?php echo $pmdc?>_field" class="form-control payment_method_field" name="payment_method_add" value="" disabled="disabled" />
                                  <span id="payment_method_<?php echo $pmdc?>_error" class="payment_method_error">Please enter a value</span>
																</div>
															<?php } ?>
														</div>

														<?php $pmdc++; ?>
													<?php } ?>
												<?php } // end ?>
											</div><!-- // #payment_data -->
										</div><!-- // #payment_info -->

										<div class="rezgo-form-row rezgo-terms-container">
											<div class="col-sm-12 rezgo-payment-terms">
												<div class="rezgo-form-input">
													<div class="checkbox">
                            <label id="rezgo-terms-check">
                              <input type="checkbox" id="agree_terms" name="agree_terms" value="1" />
                              <span>I agree to the </span>
                            </label>
														<label id="rezgo-terms-label">
															<a data-toggle="collapse" class="collapsed rezgo-terms-link" data-target="#rezgo-terms-panel">
																<span>Terms and Conditions</span>
															</a>
                              and
															<a data-toggle="collapse" class="collapsed rezgo-terms-link" id="rezgo-privacy-link" data-target="#rezgo-privacy-panel">
																<span>Privacy Policy</span>
															</a>
														</label>
													</div>

													<div id="rezgo-terms-panel" class="collapse">
														<?php echo $site->getPageContent('terms')?>
                            <?php if ( (string) $company->gateway_id == 'rezgo') { ?>
                            <p>We have partnered with Trust My Travel Ltd. to provide global payment services for our business.  By using our services, you must read and agree to the following <a href="https://www.trustmytravel.com/terms/" target="_blank" title="TMTProtects.Me">Payment Services Agreement</a>.</p>
                            <?php } ?>
													</div>

													<div id="rezgo-privacy-panel" class="collapse">
														<?php echo $site->getPageContent('privacy')?>
													</div>
												</div>

											</div>
										</div>

										<?php if($company->review_express == 1 || $company->marketing_consent == 1) { ?>

                    <div id="rezgo-optional-terms">
                      <h4 id="rezgo-optional-head">Optional Terms</h4>

											<?php if($company->marketing_consent == 1) { ?>
                      <div class="rezgo-form-row">
                        <div class="col-sm-12 rezgo-marketing-terms">
                          <div class="rezgo-form-input">
                            <div class="checkbox">
                              <label id="rezgo-marketing-terms-label">
                                <input type="checkbox" id="marketing_consent" name="marketing_consent" value="1" />
                                <span>I consent to receiving marketing emails and/or text messages from <?php echo $company->company_name?> in accordance with the privacy policy.</span>
                              </label>
                            </div>
                          </div>
                        </div>
                      </div>
                      <?php } ?>

											<?php if($company->review_express == 1) { ?>
                      <div class="rezgo-form-row">
                        <div class="col-sm-12 rezgo-tripadvisor-terms">
                          <div class="rezgo-form-input">
                            <div class="checkbox">
                              <label id="rezgo-tripadvisor-terms-label">
                                <input type="checkbox" id="review_sent" name="review_sent" value="1" />
                                <span>I consent to allow my private and personal information including name, email and booking information to be sent to TripAdvisor for the purposes of allowing TripAdvisor to email me a request for review on behalf of this business as well as other marketing. I further consent and agree to be bound by the TripAdvisor <a href="https://www.tripadvisor.com/pages/privacy_pre_060407.html" target="_blank">privacy policy</a> and <a href="https://tripadvisor.mediaroom.com/us-terms-of-use" target="_blank">terms of use</a>.</span>
                              </label>
                            </div>
                          </div>
                        </div>
                      </div>
                      <?php } ?>

                    </div>

                    <?php } ?>

										<div class="rezgo-form-row">
											<div class="col-sm-12 rezgo-payment-terms">

												<div id="rezgo-book-terms">
													<div class="help-block" id="terms_credit_card" style="display:<?php if(!$site->getPaymentMethods('Credit Cards')) { ?>none<?php } ?> ;">
														<?php if($site->getGateway() OR $site->isVendor()) { ?>
															<?php if($complete_booking_total > 0) { ?>
																<span class='terms_credit_card_over_zero'>Please note that your credit card will be charged.</span>
																<br>
															<?php } ?>
															<span>If you are satisfied with your entries, please confirm by clicking the &quot;Complete Booking&quot; button.</span>
														<?php } else { ?>
															<?php if($complete_booking_total > 0) { ?>
																<span class='terms_credit_card_over_zero'>Please note that your credit card will not be charged now. Your transaction information will be stored until your payment is processed. Please see the Terms and Conditions for more information.</span>
																<br>
															<?php } ?>
															<span>If you are satisfied with your entries, please click the &quot;Complete Booking&quot; button.</span>
														<?php } ?>
													</div>

													<div class="help-block" id="terms_other" style="display:<?php if($site->getPaymentMethods('Credit Cards')) { ?>none<?php } ?>;">
														<span>If you are satisfied with your entries, please confirm by clicking the &quot;Complete Booking&quot; button.</span>
													</div>
												</div>

												<div id="rezgo-book-message" class="row" style="display:none;">
													<div id="rezgo-book-message-body" class="col-sm-8 col-sm-offset-2"></div>
                          <div id="rezgo-book-message-wait" class="col-sm-2"><i class="fa fa-refresh fa-spin fa-3x fa-fw"></i></div>
												</div>
											</div>
                                          
										</div>

									</div>
								</div>
							</div>

							<div id="rezgo-book-step-two-cta-wrp" class="rezgo-booking-cta">
								<div class="row">
									<div class="col-sm-6 col-xs-3 rezgo-btn-wrp rezgo-chevron-left">
										<button type="button" class="btn rezgo-btn-default btn-lg btn-block" onClick="stepBack(); return false;">
											<span class="hidden-xs">Previous Step</span>
											<span class="visible-xs-inline">
												<span class="glyphicon glyphicon-chevron-left"></span>
											</span>
										</button>
									</div>

									<div class="col-sm-6 col-xs-9 rezgo-btn-wrp rezgo-complete-btn-wrp">
                    <button type="submit" class="btn rezgo-btn-book btn-lg btn-block" id="rezgo-complete-booking">
                    <i class="fa fa-lock fa-lg" aria-hidden="true"></i>&nbsp; Complete Booking</button>
									</div>
								</div>
							</div>
						</div><!-- // #book_step_two -->
					</div>
				</form>

				<div id="rezgo-book-errors-wrp" class="row">
					<div class="col-sm-12 col-md-6">
						<p>&nbsp;</p>
						<br />
					</div>

					<div class="col-sm-12 col-md-6">
						<div id="rezgo-book-errors" class="alert alert-danger">
							<span>Some required fields are missing. Please complete the highlighted fields.</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>#debug_response {width:100%; height:200px;}</style>

<script>
	var toComplete = 0;
	var response; // needs to be global to work in timeout
	var paypalAccount = 0;

	var ca_states = <?php echo  json_encode( $site->getRegionList('ca') ); ?>;
	var us_states = <?php echo  json_encode( $site->getRegionList('us') ); ?>;
	var au_states = <?php echo  json_encode( $site->getRegionList('au') ); ?>;

	// Money Formatting
	// Add/sub elements
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

	// Catch form submissions
	$('#rezgo-book-form').submit(function(evt) {
		evt.preventDefault();

		submit_booking();
	});

	$('#tour_country').change(function() {
		var country = $(this).val();

		// set SMS country
		$("#tour_sms").intlTelInput("setCountry", $(this).val());

		$('#tour_stateprov').removeOption(/.*/);

		switch (country) {
			case 'ca':
				$('#tour_stateprov_txt').hide();
				$('#tour_stateprov').addOption(ca_states, false).show();
				$('#tour_stateprov_txt').val($('#tour_stateprov').val());
				break;
			case 'us':
				$('#tour_stateprov_txt').hide();
				$('#tour_stateprov').addOption(us_states, false).show();
				$('#tour_stateprov_txt').val($('#tour_stateprov').val());
				break;
			case 'au':
				$('#tour_stateprov_txt').hide();
				$('#tour_stateprov').addOption(au_states, false).show();
				$('#tour_stateprov_txt').val($('#tour_stateprov').val());
				break;
			default:
				$('#tour_stateprov').hide();
				$('#tour_stateprov_txt').val('');
				$('#tour_stateprov_txt').show();
				break;
		}
	});

	$('#tour_stateprov').change(function() {
		var state = $(this).val();
		$('#tour_stateprov_txt').val(state);
	});

	<?php if(in_array($site->getCompanyCountry(), array('ca', 'us', 'au'))) { ?>
		$('#tour_stateprov').addOption(<?php echo $site->getCompanyCountry();?>_states, false);

		$('#tour_stateprov_txt').val($('#tour_stateprov').val());
	<?php } ?>

	if(typeof String.prototype.trim != 'function') {
		// detect native implementation
		String.prototype.trim = function () {
			return this.replace(/^\s+/, '').replace(/\s+$/, '');
		};
	}

	// change the modal dialog box or pass the user to the receipt depending on the response
	function show_response() {
		response = response.trim();

		if(response != '1') {
			$('#rezgo-complete-booking').val('Complete Booking');
			$('#rezgo-complete-booking').removeAttr('disabled');
		}
		if(response == '2') {
			var title = 'No Availability Left';
			var body = 'Sorry, there is not enough availability left for this item on this date.<br />';
		}
		else if(response == '3') {
			var title = 'Payment Error';
			var body = 'Sorry, your payment could not be completed. Please verify your card details and try again.<br /';
		}
		else if(response == '4') {
			var title = 'Booking Error';
			var body = 'Sorry, there has been an error with your booking and it can not be completed at this time.<br />';
		}
		else if(response == '5') {
			// this error should only come up in preview mode without a valid payment method set
			var title = 'Booking Error';
			var body = 'Sorry, you must have a credit card attached to your Rezgo Account in order to complete a booking.<br><br>Please go to "Settings &gt; Rezgo Account" to attach a credit card.<br />';
		}
		else if(response == '6') {
			// this error is returned when expected total does not match actual total
			var title = 'Booking Error';
			var body = 'Sorry, a price on an item you are booking has changed. Please return to the shopping cart and try again.<br />';
		}
		else {
			
			if(response.indexOf('STOP::') != -1) { // debug handling
				
				var split = response.split('<br><br>');

				if(split[1] == '2' || split[1] == '3' || split[1] == '4' || split[1] == '5' || split[1] == '6') {
					split[1] = '<br /><br />Error Code: ' + split[1] + '<br />';
				} else {
					split[1] = '<div class="clearfix">&nbsp;</div>BOOKING COMPLETED WITHOUT ERRORS<div class="clearfix">&nbsp;</div><button type="button" class="btn btn-default" onclick="window.top.location.replace(\'<?php echo $site->base?>/complete/' + split[1].replace('TXID|*|', '') + '\');">Continue to Receipt</button><div class="clearfix">&nbsp;</div>';
				}

				var body = 'DEBUG-STOP ENCOUNTERED<br /><br />' + '<textarea id="debug_response">' + split[0] + '</textarea>' + split[1];
				
			} else {
				
				if(response.indexOf('TXID') != -1) {
					
					var trans = response.split('|*|');
					
					// send the user to the receipt page
					//top.location.replace("<?php echo $site->base?>/complete/" + trans[1]);
					window.top.location.replace("<?php echo $site->base?>/complete/" + trans[1]);
					return true; // stop the html replace
					
				} else {
					
					var title = 'Booking Error';
					var body = 'Sorry, an unknown error has occurred.<br />';
					
					console.log(response);
					
				}
				
			}
		}

		booking_wait(false);
		$('#rezgo-book-message-body').html(body);
		$('#rezgo-book-message-body').addClass('alert alert-warning');
	}

	// this function delays the output so we see the loading graphic
	function delay_response(responseText) {
		response = responseText;

		if(response.debug) {
			console.log(response);
		} else {
			setTimeout(function () {
				show_response();
			}, 800);
		}
	}

	function validate_form() {
		var valid = $('#rezgo-book-form').valid();

		return valid;
	}

	function error_booking() {
		$('#rezgo-book-errors').fadeIn();

		setTimeout(function () {
				$('#rezgo-book-errors').fadeOut();
		}, 5000);
		return false;
	}
	
	// booking wait time
  var seconds = 0;
  
  function booking_wait (wait) {
    
    if (wait) {
			
			$('#rezgo-book-message-wait').show();
      
      timex = setTimeout(function(){
        seconds++;
        
        if (seconds == 10) {
          
          $("#rezgo-book-message-body").fadeOut(function() {
            $(this).html('We are still working on your request. <br class="hidden-md hidden-lg" />Thank you for your patience.').fadeIn();
          });																
          $("#rezgo-book-message-body").effect("highlight", {color: '#FCF6B0'}, 1500);
          
        } else if (seconds == 25) {
          
          $("#rezgo-book-message-body").fadeOut(function() {
            $(this).html('Your request is taking longer than expected. <br class="hidden-md hidden-lg" />Please hold on ...').fadeIn();
          });	
          $('#rezgo-book-message-body').effect("highlight", {color: '#F9F6AF'}, 2000);
          
        } else if (seconds == 40) {
          
          $("#rezgo-book-message-body").fadeOut(function() {
            $(this).html('Working on payment processing. <br class="hidden-md hidden-lg" />Your order should be completed soon.').fadeIn();
          });	
          $('#rezgo-book-message-body').effect("highlight", {color: '#F6F5AE'}, 2500);
          
        } else if (seconds == 55) {
          
          $("#rezgo-book-message-body").fadeOut(function() {
            $(this).html('So &hellip; do you have any plans for the weekend?').fadeIn();
          });	
          $('#rezgo-book-message-body').effect("highlight", {color: '#ECF2AB'}, 2500);
          
        } else if (seconds == 70) {
          
          $("#rezgo-book-message-body").fadeOut(function() {
            $(this).html('We really had hoped to be done by now. <br class="hidden-md hidden-lg" />It shouldn\'t take much longer.').fadeIn();
          });	
          $('#rezgo-book-message-body').effect("highlight", {color: '#E2EFA7'}, 2500);
          
        }
        
        // console.log(seconds);
        booking_wait(true);
        
      }, 1000);
      
    } else {
      
      clearTimeout(timex);
      $('#rezgo-book-message-body').html('');
      $('#rezgo-book-message-wait').hide();
      
    }
    
  }

	function submit_booking() {
		// do nothing if we are on step 1
		if(toComplete == 0) return false;

		var validate_check = validate_form();

		$('#rezgo-complete-booking').val('Please wait ...');
		$('#rezgo-complete-booking').attr('disabled','disabled');
		$('#rezgo-book-message-body').removeClass('alert alert-warning');
		$('#rezgo-book-message-body').html('');
		$('#rezgo-book-message').fadeOut();
		$('#rezgo-book-terms').fadeIn();

		// only activate on actual form submission, check payment info
		if(toComplete == 1 && overall_total > 0) {

			var force_error = 0;
			var payment_method = $('input:radio[name=payment_method]:checked').val();

			if(payment_method == 'Credit Cards') {
				if(!$('#tour_payment').contents().find('#payment').valid()) {
					force_error = 1;
				}
			} else {
				// other payment methods need their additional fields filled
				var id = $('input:radio[name=payment_method]:checked').attr('id');
				if($('#' + id + '_field').length != 0 && !$('#' + id + '_field').val()) {
					// this payment method has additional data that is empty
					force_error = 1;
					$('#' + id + '_field').css('border-color', '#a94442');
					$('#' + id + '_error').show();
				}
			}
		}

		if(force_error || !validate_check) {
			$('#rezgo-complete-booking').val('Complete Booking');
			$('#rezgo-complete-booking').removeAttr('disabled');

			return error_booking();
		} else {
			if(toComplete == 1) {
				
				booking_wait(true);
				
				$('#rezgo-book-message-body').html('Please wait one moment ...');

				$('#rezgo-book-terms').fadeOut().promise().done(function(){
					 $('#rezgo-book-message').fadeIn();
				});

				var payment_method = $('input:radio[name=payment_method]:checked').val();

				if(payment_method == 'Credit Cards' && overall_total > 0) {

					// clear the existing credit card token, just in case one has been set from a previous attempt
					$('#tour_card_token').val('');

					// submit the card token request and wait for a response
					$('#tour_payment').contents().find('#payment').submit();

					// wait until the card token is set before continuing (with throttling)
					function check_card_token() {
						var card_token = $('#tour_card_token').val();
						if(card_token == '') {
							// card token has not been set yet, wait and try again
							setTimeout(function() {
								check_card_token();
							}, 200);
						} else {
							// the field is present? submit normally
							$('#rezgo-book-form').ajaxSubmit({
								url: '<?php echo $site->base?>/book_ajax.php',
								data: { rezgoAction: 'book' },
								success: delay_response,
								error: function() {
									var body = 'Sorry, the system has suffered an error that it can not recover from.<br />Please try again later.<br />';
									$('#rezgo-book-message-body').html(body);
									$('#rezgo-book-message-body').addClass('alert alert-warning');
								}
							});
						}
					}

					check_card_token();
				} else {
					// not a credit card payment (or $0) and everything checked out, submit via ajaxSubmit (jquery.form.js)
					$('#rezgo-book-form').ajaxSubmit({
						url: '<?php echo $site->base?>/book_ajax.php',
						data: {
							rezgoAction: 'book',
						},
						success: delay_response,
						error: function() {
							var body = 'Sorry, the system has suffered an error that it can not recover from.<br><br>Please try again later.<br />';
							$('#rezgo-book-message-body').html(body);
							$('#rezgo-book-message-body').addClass('alert alert-warning');
						}
					});
				}

				// return false to prevent normal browser submit and page navigation
				return false;
			}
		}

	}

	function stepForward() {
		if(!validate_form()) return error_booking();

		toComplete = 1;

		var step_two_position = $('#rezgo-book-step-two-anchor').position();
		var step_two_scroll = Math.round(step_two_position.top);

		if('parentIFrame' in window) {
			setTimeout(function () {
					parentIFrame.scrollTo(0,0);
			}, 100);
		}

		// show copy pax checkbox if we have pax info
		if($('#frm_1_adult_1_first_name').val()) {
			$('#rezgo-copy-pax-span').show();
		}

		$('#rezgo-book-errors').fadeOut();
		$("#tour_first_name").addClass("required");
		$("#tour_last_name").addClass("required");
		$("#tour_address_1").addClass("required");
		$("#tour_city").addClass("required");
		$("#tour_country").addClass("required");
		$("#tour_postal_code").addClass("required");
		$("#tour_phone_number").addClass("required");
		$("#tour_email_address").addClass("required");
		$("#agree_terms").addClass("required");
		$('#rezgo-book-tabs a:last').tab('show');
		
		// get line items
		$('.rezgo-line-item-box').each(function() {
			
			var item_id = $(this).data('line-uid'); 
			var book_id = $(this).data('book-id'); 
			
			var pickup_id = $('#rezgo-pickup-line-' + book_id).val();
			
			var total_extras = $("#total_extras_" + book_id).val();
			
			// show loading msg before fetching line items
			$('tbody#line_item_box_' + book_id).html('<tr><td colspan="4" class="rezgo-line-wait"><div class="rezgo-wait-div"></div></td></tr>');
			$('#line_item_box_' + book_id).show();
			
			// reset
			modified_total = 0;

			$.ajax({
				url: '<?php echo $site->base?>/cart_ajax.php?action=cart&item_id=' + item_id + '&book_id=' + book_id + '&pickup_id=' + pickup_id + '',
				context: document.body,
				success: function(data) {
					
					$('tbody#line_item_box_' + book_id).html(data); 
						
					var source = $('' + data + '');
					
					var pickup_total = source.find('#pickup_total_' + book_id).val();
					var pickup_deposit = source.find('#pickup_deposit_' + book_id).val();
					
					if (pickup_deposit > 0) { 
						
						modified_total = clean_money_string(modified_total) + (pickup_deposit * 1);
						
					} else {
						
						modified_total = clean_money_string(modified_total) + (pickup_total * 1);
						
						if (total_extras != 0) {
							modified_total = clean_money_string(modified_total) + (total_extras * 1);
						}
						
					}	
					
					var relative_total;
					relative_total = (modified_total < 0) ? 0 : modified_total;
					
					if (total_extras != 0) {
						pickup_total = (pickup_total * 1) + (total_extras * 1);
					}		
					
					if (pickup_total < 0) {
						pickup_total = 0;
					}
					
					split_total[book_id] = pickup_total;
					
					pickup_total = parseFloat(pickup_total).toLocaleString('en', {minimumFractionDigits: form_decimals});
					
					relative_total = parseFloat(relative_total).toLocaleString('en', {minimumFractionDigits: form_decimals});
					
					$('#total_value_' + book_id).html(form_symbol + pickup_total); 
					$('#total_value_' + book_id).attr('rel', clean_money_string(pickup_total));
					
					$("#total_value").html(form_symbol + relative_total);
					$("#total_value").attr('rel', clean_money_string(modified_total));
					$("#expected").val(clean_money_string(modified_total));
					
				}
				
			});
			
		});	// .rezgo-line-item-box .each
		
	}

	function stepBack() {
		toComplete = 0;

		$('#rezgo-book-tabs a:first').tab('show');
		$("#tour_first_name").removeClass("required");
		$("#tour_last_name").removeClass("required");
		$("#tour_address_1").removeClass("required");
		$("#tour_city").removeClass("required");
		$("#tour_country").removeClass("required");
		$("#tour_postal_code").removeClass("required");
		$("#tour_phone_number").removeClass("required");
		$("#tour_email_address").removeClass("required");
		$("#agree_terms").removeClass("required");

		if('parentIFrame' in window) {
			setTimeout(function(){
				parentIFrame.scrollTo(0,0);
			},100);
		}
	}

	function toggleCard() {

		if($('input[name=payment_method]:checked').val() == 'Credit Cards') {

			<?php $pmn = 0; ?>
			<?php foreach($site->getPaymentMethods() as $pay ) { ?>
				<?php if($pay[name] == 'Credit Cards') { ?>
				<?php } else { ?>
					<?php $pmn++; ?>
					$('#payment_method_<?php echo $pmn?>_box').hide();
					$('#payment_method_<?php echo $pmn?>_field').attr('disabled', 'disabled');
				<?php } ?>
			<?php } ?>

			setTimeout(function() {
				$('#payment_cards').fadeIn();
			}, 450);

			document.getElementById("terms_other").style.display = 'none';
			document.getElementById("terms_credit_card").style.display = '';
			

		} else if($('input[name=payment_method]:checked').val() == 'PayPal') {
			<?php $pmn = 0; ?>

			<?php foreach($site->getPaymentMethods() as $pay ) { ?>
				<?php if($pay[name] == 'Credit Cards') { ?>
					$('#payment_cards').hide();
				<?php } else { ?>
					<?php $pmn++; ?>
					$('#payment_method_<?php echo $pmn?>_box').hide();
					$('#payment_method_<?php echo $pmn?>_field').attr('disabled', 'disabled');
				<?php } ?>
			<?php } ?>

			document.getElementById("terms_credit_card").style.display = 'none';
			document.getElementById("terms_other").style.display = '';
			

		} else {
			<?php $pmn = 0; ?>
			<?php foreach($site->getPaymentMethods() as $pay ) { ?>
				<?php if($pay[name] == 'Credit Cards') { ?>
					$('#payment_cards').hide();
				<?php } else { ?>
					<?php $pmn++; ?>
					$('#payment_method_<?php echo $pmn?>_box').hide();
					$('#payment_method_<?php echo $pmn?>_field').attr('disabled', 'disabled');
				<?php } ?>
			<?php } ?>

			setTimeout(function() {
				var id = $('input[name=payment_method]:checked').attr('id');
				$('#' + id + '_box').fadeIn();
				$('#' + id + '_field').attr('disabled', false);
			}, 450);

			document.getElementById("terms_credit_card").style.display = 'none';
			document.getElementById("terms_other").style.display = '';
						
		}
	}

	// these functions do a soft-commit when you click on the paypal option so they
	// can get an express payment token from the paypal API via the XML gateway
	function getPaypalToken(force) {
		// if we aren't forcing it, don't load if we already have an id
		if(!force && paypalAccount == 1) {
			// an account is set, don't re-open the box
			return false;
		}

		$('#rezgo-book-form').ajaxSubmit({
			url: '<?php echo $site->base?>/book_ajax.php',
			data: { rezgoAction: 'get_paypal_token' },
			success: function(token) {
				// this section is mostly for debug handling
				if(token.indexOf('STOP::') != -1) {
					var split = token.split('<br><br>');

					if(split[1] == '0') {
						alert('The system encountered an error with PayPal. Please try again in a few minutes or select another payment method.');
						return false;
					}

					token = split[1];
				}

				dg.startFlow("https://www.paypal.com/incontext?token=" + token.trim());
			}
		});
	}

	function paypalCancel() {
		// the paypal transaction was cancelled, uncheck the radio and close the box
		dg.closeFlow();
		$('#payment_method_paypal').attr('checked', false);
	}

	function creditConfirm(token) {
		// the credit card transaction was completed, give us the token
		$('#tour_card_token').val(token);
	}

	// this function checks through each element on the form, if that element is
	// a checkbox and has a price value and is checked (thanks to browser form retention)
	// then we go ahead and add that to the total like it was clicked
	function saveForm(form) {
		$(':input', form).each(function() {
			if(this.type == 'checkbox' && this.checked == true) {
				var split = this.id.split("|");
				// if the ID contains a price value then add it
				if(split[2]) add_element(split[0], split[1], split[2], split[3]);
			}
		 });
	};

	saveForm('#rezgo-book-form');

	function clean_money_string(str) {
		// convert to str in case it has strange characters (like a ,)
		str += '';
		// clean (except . and -) and convert back to float
		return parseFloat(str.replace(/[^0-9.-]/, ""));
	}

	function b64DecodeUnicode(str) {
	    // Going backwards: from bytestream, to percent-encoding, to original string.
	    return decodeURIComponent(atob(str).split('').map(function(c) {
	        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
	    }).join(''));
	}

	function add_element(id, name, price, order_num) {
		// GIFT CARD RESET
    if (typeof gcReset === 'function') { gcReset(); }

		// ensure our array has an array for the actual elements
		if(!elements[order_num]) elements[order_num] = new Array();

		var num = add_price = clean_money_string(price);

		if (elements[order_num][id]) {
			num = num + clean_money_string(elements[order_num][id]);
		}

		var price = num.formatMoney();

		var display_price = form_symbol + price;

		name = window.b64DecodeUnicode(name).replace("\\'", "'");

		if(!elements[order_num][id]) {
			var content = '<tr class="rezgo-tr-fee"><td colspan="3" class="text-right rezgo-line-item" id="element_'+order_num+'_'+id+'"><span id="text_'+order_num+'_'+id+'">'+name+'</span></td><td class="text-right" id="val_'+order_num+'_'+id+'"><span class="rezgo-item-opt" rel="'+price+'">'+display_price+'</span></td></tr>';

			$("#fee_box_" + order_num).html( $("#fee_box_" + order_num).html() + content );

			$("#fee_box_" + order_num).show();
		} else {
			if (document.getElementById('element_' + order_num + '_' + id).style.display == 'none') {
				document.getElementById('element_' + order_num + '_' + id).style.display = '';
			}

			if (document.getElementById('val_' + order_num + '_' + id).style.display == 'none') {
				document.getElementById('val_' + order_num + '_' + id).style.display = '';
			}

			$("#val_" + order_num + "_" + id).html(display_price);
			$("#text_" + order_num + "_" + id).html(name);
		}

		elements[order_num][id] = price;

		// add to total amount
		var total = split_total[order_num];
		total = clean_money_string(total) + add_price;
		total = total.formatMoney();
		split_total[order_num] = total;
				
		// set running total of extras
		var total_extras = $("#total_extras_" + order_num).val();
		total_extras = (total_extras * 1) + (add_price * 1); // multiply by 1 to force number
		$("#total_extras_" + order_num).val(total_extras);

		// set the total for this item
		$("#total_value_" + order_num).html(form_symbol + total);
		$("#total_value_" + order_num).attr('rel', clean_money_string(total));

		// set the order total if this item doesn't have a deposit set
		if(!$("#deposit_value_" + order_num).html()) {
			
			var relative_total;
			var display_overall_total;

			overall_total = clean_money_string(overall_total) + add_price;
			modified_total = clean_money_string(modified_total) + add_price;
			
			relative_total = (overall_total < 0) ? 0 : overall_total;

			display_overall_total = overall_total.formatMoney();
			relative_total = relative_total.formatMoney();

			$("#total_value").html(form_symbol + relative_total);
			$("#total_value").attr('rel', clean_money_string(display_overall_total));
			$("#expected").val(clean_money_string(display_overall_total));
			
		}

		// if total is greater than 0 then show payment section
		showPaymentSection(overall_total);

	}

	function sub_element(id, price, order_num) {
		// gift card reset
		if (typeof gcReset === 'function') { gcReset(); }

		// ensure our array has an array for the actual elements
		if(!elements[order_num]) elements[order_num] = new Array();

		if(!elements[order_num][id] || elements[order_num][id] == 0) return false;

		var num = sub_price = clean_money_string(price);
		num = clean_money_string(elements[order_num][id]) - num;

		var price = num.formatMoney();

		var display_price = form_symbol + price;

		if(price == 0) {
			document.getElementById('element_' + order_num + '_' + id).style.display = 'none';
			document.getElementById('val_' + order_num + '_' + id).style.display = 'none';
		} else {
			document.getElementById('val_' + order_num + '_' + id).innerHTML = display_price;
		}
		elements[order_num][id] = price;

		// sub from total amount
		var total = split_total[order_num];
		total = clean_money_string(total) - sub_price;
		total = total.formatMoney();
		split_total[order_num] = total;
				
		// set running total of extras
		var total_extras = $("#total_extras_" + order_num).val();
		total_extras = (total_extras * 1) - (sub_price * 1); // multiply by 1 to force number
		$("#total_extras_" + order_num).val(total_extras);

		// set the total for this item
		$("#total_value_" + order_num).html(form_symbol + total);
		$("#total_value_" + order_num).attr('rel', clean_money_string(total));

		// set the order total if this item doesn't have a deposit set
		if(!$("#deposit_value_" + order_num).html()) {
			var relative_total;
			var display_overall_total;

			overall_total = clean_money_string(overall_total) - sub_price;
			modified_total = clean_money_string(modified_total) - sub_price;
			
			relative_total = (overall_total < 0) ? 0 : overall_total;

			display_overall_total = overall_total.formatMoney();
			relative_total = relative_total.formatMoney();

			$("#total_value").html(form_symbol + relative_total);
			$("#total_value").attr('rel', clean_money_string(display_overall_total));
			$("#expected").val(clean_money_string(display_overall_total));
		}

		// if total is 0 then hide payment section
		showPaymentSection(overall_total);
	}

	function showPaymentSection(overall_total) {
		if(overall_total <= 0) {
			$('.rezgo-payment-frame, #payment_info_head, #rezgo-gift-card-use, #rezgo-gift-card-use-hr').hide();
		} else {
			$('.rezgo-payment-frame, #payment_info_head, #rezgo-gift-card-use, #rezgo-gift-card-use-hr').show();
		}
	}

	// WAIVER
	function receiveMessage(e) {
		// Update the div element to display the message.
		if(e.data.type && e.data.type=='modal' && e.data.mode=='order_waiver') {

			var
			waiverInfo = document.getElementById('rezgo-waiver-info'),
			waiverSignature = document.getElementById('rezgo-waiver-signature'),
			waiverInput = document.getElementById('rezgo-waiver-input'),
			waiverIntro = waiverInfo.getElementsByClassName('intro')[0],
			waiverSuccess = waiverInfo.getElementsByClassName('success')[0],
			waiverError = waiverInfo.getElementsByClassName('error')[0],
			signature = waiverSignature.getElementsByClassName('signature')[0];

			signature.src = e.data.sig;

			signature.style.display = 'inline-block';
			waiverIntro.style.display = 'none';
			waiverSuccess.style.display = 'inline-block';
			waiverError.style.display = 'none';
			waiverInput.value = e.data.sig;

			window.top.$('#rezgo-modal').modal('toggle');

		}
	}

	$('#rezgo-waiver-show').click(function(){
		var
		rezgoModalTitle = 'Sign Waiver',
		ids = $(this).data('ids'),
		query = '/modal?mode=waiver&type=order&sec=1&ids=' + ids + '&title=' + rezgoModalTitle;

		window.top.jQuery('#rezgo-modal-loader').css({'display':'block'});
		window.top.jQuery('#rezgo-modal-iframe').attr('src', query).attr('height', '500px');
		window.top.jQuery('#rezgo-modal-title').html(rezgoModalTitle);
		window.top.jQuery('#rezgo-modal').modal();
		
			// var x = $("#rezgo-waiver-use").position(); 
			// window.top.$('#rezgo-modal').css({'top':x.top});
	    
	});

	window.onload = function() {
		window.addEventListener('message', receiveMessage);
	}

	$(".chosen-select").chosen( { width: "98%", allow_single_deselect: true } );

	$('.chosen-select').change(function () {
		$(this).valid();
	});

	$('.rezgo-custom-select').chosen().change( function() {

		var parent = $(this).parent();
		var chosen_options = this && this.options;
		var opt;

		for (var i=0, len=chosen_options.length; i<len; i++) {

			opt = chosen_options[i];

			if (opt.selected) {
				parent.find( '#optex_' + i + '.opt_extra' ).show();
			} else {
				parent.find( '#optex_' + i + '.opt_extra' ).hide();
			}

		}

	});

	$('.rezgo-pickup-select').change(function () {
		
		// gift card reset
		if (typeof gcReset === 'function') { gcReset(); }
		
		var pickup_id = $(this).val();
		var pickup_target = $(this).data('target');
		var count = $(this).data('counter');
		var book_id = $(this).data('id');
		var option_id = $(this).data('option');
		var pax_num = $(this).data('pax');
		
		$('#rezgo-pickup-line-' + book_id).val('');
		
		if (pickup_id) {
					
			$('#rezgo-pickup-line-' + book_id).val(pickup_id);
		
			// wait animation
			$('#' + pickup_target).html('<div class="rezgo-pickup-loading"></div>');
		
			$.ajax({
				url: '<?php echo $site->base?>/pickup_ajax.php?action=item&pickup_id=' + pickup_id + '&option_id=' + option_id + '&book_id=' + book_id + '&pax_num=' + pax_num + '', 
				context: document.body,
				success: function(data) {			

					$('#' + pickup_target).fadeOut().html(data).fadeIn('fast'); 
					
				}
			});	
		
		} else {
			$('#' + pickup_target).html('');
		}
		
	});

</script>
