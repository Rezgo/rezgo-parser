<?php
	$company = $site->getCompanyDetails();
	$availability_title = '';	

	if ($_REQUEST['option_num']) {
		$option_num = $_REQUEST['option_num'];
	} else {
		$option_num = 1;	
		
		if ($_REQUEST['type'] != 'open') {
			
			if ($_REQUEST['js_timestamp']) {
				$now = $_REQUEST['js_timestamp'];
			} else {
				$now = time();
			}
			
      $today = date('Y-m-d', $now);
			$selected_date = date('Y-m-d', strtotime($_REQUEST['date'] . ' ' . $company->time_format . ' hours'));
			$selected_date = date('Y-m-d', strtotime($_REQUEST['date']));
			$available_day = date('D', strtotime($_REQUEST['date']));
      $available_date = date((string) $company->date_format, strtotime($_REQUEST['date'])); 

			$availability_title = '<div class="rezgo-date-options" style="display:none;"><span class="rezgo-calendar-avail"><span>Availability&nbsp;for: </span></span> <strong><span class="rezgo-avail-day">'.$available_day.',&nbsp;</span><span class="rezgo-avail-date">'.$available_date.'</span></strong>';
      
      if($today !== $selected_date) {
        $date_diff = $site->getCalendarDiff($today, $selected_date);
				$date_diff = ($date_diff=='1 day') ? 'Tomorrow' : $date_diff . ' from today';
        $availability_title .= '<small class="rezgo-calendar-diff"><span>('.$date_diff.')</span></small>';
      } else {
      	$availability_title .= '<small class="rezgo-calendar-diff"><span>(Today)</span></small>';
      }
      
      $availability_title .= '</div>';
		}
	}

	if ($_REQUEST['date'] != 'open') {
		$date_request = '&d='.$_REQUEST['date'];
	} else {
		$date_request = '';
	}

	$options = $site->getTours('t=com&q='.$_REQUEST['com'].$date_request.'&file=calendar_day');
	
?>

<?php if ($options) { ?>
	<?php echo $availability_title?>
  
  <script>
		//console.log('JS: <?php echo $_REQUEST['js_timestamp']?>');
		//console.log('PHP: <?php echo $now?>');
  </script>

	<span class="rezgo-date-memo rezgo-calendar-date-<?php echo $_REQUEST['date']?>"></span>

	<div class="panel-group" id="rezgo-select-option-<?php echo $option_num?>">
		<?php if (count($options) != 1) { // && $option_num != 1 ?>
			<span class="rezgo-choose-options">Choose one of the options below <i class="fa fa-angle-double-down"></i></span>
		<?php }

		if ($_REQUEST['type'] == 'open') {
			$sub_option = 'o1';
		} else {
			$sub_option = 'a';
		}

		foreach($options as $option) { ?>
    
			<?php $site->readItem($option);
			
			if ( (int) $option->date->availability >= (int) $option->block_size || !$option->block_size) {

				// how does this apply to open?
				if ($option->date->availability == 0) {
					$panel_unclass = ' panel-unavailable';
				} else {
					$panel_unclass = '';
				}
	
				// don't mix open options with calendar options
				// only return options that match the request type
				if ((($_REQUEST['type'] == 'calendar' || $_REQUEST['type'] == 'single') && (string) $option->date['value'] != 'open') 
					|| ($_REQUEST['type'] == 'open' && (string) $option->date['value'] == 'open' )
				) { ?>
					<div class="panel panel-default<?php echo $panel_unclass?>">
						<script>
							var fields_<?php echo $option_num.'_'.$sub_option?> = new Array();
							var required_num_<?php echo $option_num.'_'.$sub_option?> = 0;
	
							function isInt(n) {
								 return n % 1 === 0;
							}
	
							// validate form data
							function check_<?php echo $option_num.'_'.$sub_option?>() {
								var err;
								var count_<?php echo $option_num.'_'.$sub_option?> = 0;
								var required_<?php echo $option_num.'_'.$sub_option?> = 0;
	
								for(v in fields_<?php echo $option_num.'_'.$sub_option?>) {
									
									if ($('#' + v).attr('rel') == 'bundle' && $('#' + v).val() >= 1) {
										
										$('.' + v).each(function() {
											var multiple = $(this).data('multiple');
											var val = $('#' + v).val();
											var newval = multiple * val;
											var rel = $(this).attr('rel');
											
											count_<?php echo $option_num.'_'.$sub_option?> += newval; // increment total
											
											if(fields_<?php echo $option_num.'_'.$sub_option?>[rel]) { required_<?php echo $option_num.'_'.$sub_option?> = 1; }
											
											if((count_<?php echo $option_num.'_'.$sub_option?> <= <?php echo $option->date->availability?>) && (count_<?php echo $option_num.'_'.$sub_option?> <= 150)) {
												$(this).attr('disabled', false).val(newval);
											}
											
										});									
									
									} else {
										
										count_<?php echo $option_num.'_'.$sub_option?> += $('#' + v).val() * 1; // increment total
										
									}
									
									// has a required price point been used
									if(fields_<?php echo $option_num.'_'.$sub_option?>[v] && $('#' + v).val() >= 1) { required_<?php echo $option_num.'_'.$sub_option?> = 1; }
								}
	
								if(count_<?php echo $option_num.'_'.$sub_option?> == 0 || !count_<?php echo $option_num.'_'.$sub_option?>) {
									err = 'Please enter the number you would like to book.';
								} else if(required_num_<?php echo $option_num.'_'.$sub_option?> > 0 && required_<?php echo $option_num.'_'.$sub_option?> == 0) {
									err = 'At least one marked ( * ) price point is required to book.';
								} else if(!isInt(count_<?php echo $option_num.'_'.$sub_option?>)) {
									err = 'Please enter a whole number. No decimal places allowed.';
								} else if(count_<?php echo $option_num.'_'.$sub_option?> < <?php echo $option->per?>) {
									err = '<?php echo $option->per?> minimum required to book.';
								} else if(count_<?php echo $option_num.'_'.$sub_option?> > <?php echo $option->date->availability?>) {
									err = 'There is not enough availability to book ' + count_<?php echo $option_num.'_'.$sub_option?>;
								} else if(count_<?php echo $option_num.'_'.$sub_option?> > 250) {
									err = 'You can not book more than 250 spaces in a single booking.';
								}
	
								if(err) {
									
									<?php if(!$site->config('REZGO_MOBILE_XML')) { ?>
										$('#error_text_<?php echo $option_num.'_'.$sub_option?>').html(err);
										$('#error_text_<?php echo $option_num.'_'.$sub_option?>').slideDown().delay(2000).slideUp('slow');
									<?php } else { ?>
										$('#error_mobile_text_<?php echo $option_num.'_'.$sub_option?>').html(err);
										$('#error_mobile_text_<?php echo $option_num.'_'.$sub_option?>').slideDown().delay(2000).slideUp('slow');
									<?php } ?>
									return false;
									
								} else {
									
									// prepare inputs before submitting (*bundles)							
									var inputs = new Object(); // create new object
									
									$("#checkout_<?php echo $option_num.'_'.$sub_option?> input").each(function() {
										
										if (this.name != '') {
											
											var index = this.name; // set variable prop as input name
											var val;
											
											if (this.value == '') { val = 0; } else { val = parseInt(this.value); }
											
											if ( inputs.hasOwnProperty(index) == true ) { // check if prop exists 
												$(this).val(val + parseInt(inputs[index])); // update value of current input, adding current prop val 
												inputs[index] += val; // update this prop
											} else {
												inputs[index] = val; // set first val of this prop
											}									
											
										}
										
									});		
									
								}
								
								// return false;
								
							}
						</script>
	
							<a data-toggle="collapse" data-parent="#rezgo-select-option-<?php echo $option_num.'_'.$sub_option?>" data-target="#option_<?php echo $option_num.'_'.$sub_option?>" class="panel-heading panel-title rezgo-panel-option-link">
								<div class="rezgo-panel-option"><?php echo $option->option?>
								
								<?php if (!$site->exists($option->date->hide_availability)) { ?>
								
									<span class="rezgo-show-count">
									
									<?php if ($option->date->availability == 0) { ?>
									
									<span class="fa rezgo-full-dash"><span>&nbsp;&ndash;&nbsp;</span></span>
									<span class="rezgo-option-full"><span>full</span></span>
									
									<?php } else { ?>
									
									<span class="fa rezgo-option-dash"><span>&nbsp;&ndash;&nbsp;</span></span>
									<span class="rezgo-option-count"><?php echo (string) $option->date->availability?></span>
									<span class="rezgo-option-pax"><span>&nbsp;spots</span></span>
									
									<?php } ?>
									
									</span>	
									
								<?php } ?>
								
								</div>
							</a>
							<div id="option_<?php echo $option_num.'_'.$sub_option?>" class="panel-collapse collapse<?php echo (((count($options) == 1 && $option_num == 1) || $_REQUEST['id'] == (int) $option->uid) ? ' in' : '')?>">
							<div class="panel-body">
								<?php if ($option->date->availability != 0) { ?>
									<span class="rezgo-option-memo rezgo-option-<?php echo $option->uid?> rezgo-option-date-<?php echo $_REQUEST['date']?>"></span>
	
									<form class="rezgo-order-form" name="checkout_<?php echo $option_num.'_'.$sub_option?>" id="checkout_<?php echo $option_num.'_'.$sub_option?>" action="<?php echo $site->base?>/order" target="rezgo_content_frame">
										<input type="hidden" name="add[0][uid]" value="<?php echo $option->uid?>" />
										<input type="hidden" name="add[0][date]" value="<?php echo $_REQUEST['date']?>" />
										<?php if(!$site->getCartState()) { // for no-cart requests, we want to make sure we clear the cart ?>
										<input type="hidden" name="order" value="clear" />
										<?php } ?>
	
										<?php /* if($_COOKIE['rezgo_promo']) { ?><input type="hidden" name="promo" value="<?php echo $_COOKIE['rezgo_promo']?>"><?php } */ ?>
	
										<?php /* if($_COOKIE['rezgo_refid_val']) { ?><input type="hidden" name="refid" value="<?php echo $_COOKIE['rezgo_refid_val']?>"><?php } */?>
	
										<div class="row"> 
											<div class="col-xs-12 rezgo-order-fields">
												<?php if (!$site->exists($option->date->hide_availability)) { ?>
												<span class="rezgo-memo rezgo-availability"><strong>Availability:</strong> <?php echo ($option->date->availability == 0 ? 'full' : (string) $option->date->availability)?><br /></span>	
												<?php } ?>
								
												<?php if ($option->duration != '') { ?>
													<span class="rezgo-memo rezgo-duration"><strong>Duration:</strong> <?php echo (string) $option->duration;?><br /></span>	
												<?php } ?>
								
												<?php if ($option->time != '') { ?>
													<span class="rezgo-memo rezgo-time"><strong>Time:</strong> <?php echo (string) $option->time;?><br /></span>	
												<?php } ?>
	
												<?php $prices = $site->getTourPrices($option);	?>
	
												<?php if($site->getTourRequired() == 1) { ?>
													<span class="rezgo-memo">At least one marked ( <em><i class="fa fa-asterisk"></i></em> ) price point is required.</span>
												<?php } ?>
	
												<?php if($option->per > 1) { ?>
													<span class="rezgo-memo">At least <?php echo $option->per?> are required to book.</span>
												<?php } ?>
	
												<div class="clearfix">&nbsp;</div>
	
												<div class="text-danger rezgo-option-error" id="error_text_<?php echo $option_num.'_'.$sub_option?>" style="display:none;"></div>
	
												<?php $total_required = 0; ?>
	
												<?php foreach( $prices as $price ) { ?>
													<script>fields_<?php echo $option_num.'_'.$sub_option?>['<?php echo $price->name?>_<?php echo $option_num.'_'.$sub_option?>'] = <?php echo (($price->required) ? 1 : 0)?>;</script>
	
													<div class="form-group row">
														<div class="col-md-3 col-xs-4 max-80">
															<input type="number" min="0" name="add[0][<?php echo $price->name?>_num]" value="<?php echo $_REQUEST[$price->name.'_num']?>" id="<?php echo $price->name?>_<?php echo $option_num.'_'.$sub_option?>" class="form-control input-sm" />
														</div>
														<label for="<?php echo $price->name?>_<?php echo $option_num.'_'.$sub_option?>" class="col-xs-8 control-label rezgo-label-margin rezgo-label-padding-left">
															x&nbsp;<?php echo $price->label?><?php echo (($price->required && $site->getTourRequired()) ? ' <em><i class="fa fa-asterisk"></i></em>' : '')?> 
															<span class="rezgo-pax-price">(&nbsp;<?php if($site->exists($price->base)) { ?><span class="discount"><?php echo $site->formatCurrency($price->base)?></span> <?php } ?><?php echo $site->formatCurrency($price->price)?>&nbsp;)</span>
														</label>
                            <?php if(!$site->isVendor() && $site->exists($price->strike)) { ?>
                            <div class="col-xs-offset-2 col-xs-8 col-md-offset-3 col-md-9 rezgo-strike">
                              <span class="rezgo-strike-price">&nbsp;<?php echo $site->formatCurrency($price->strike)?>&nbsp;</span>
                              <span class="rezgo-strike-extra"><span></span></span>
                            </div>
                            <?php } ?>
													</div>
	
													<?php if ($price->required) $total_required++; ?>
												<?php } // end foreach( $site->getTourPrices() ?>
	
												<script>required_num_<?php echo $option_num.'_'.$sub_option?> = <?php echo $total_required?>;</script>
												
												<?php
												
													$bundles = $site->getTourBundles($option);	
													
													//echo '<pre>'.print_r($bundles, 1).'</pre>';
													
													if (count($bundles) > 0) {
														
														echo '
														<div class="form-group row rezgo-bundles-row rezgo-bundle-hidden">
															<hr />
															<div class="col-md-3 col-xs-4 max-80 rezgo-bundles-offset">
															<input type="number" class="form-control input-sm" style="visibility:hidden" disabled /></div>
															<div class="col-xs-8 control-label rezgo-label-padding-left rezgo-bundles-offset">
																<span class="rezgo-option-bundles"><i class="fa fa-archive"></i>&nbsp;Bundles</span>
															</div>
														</div>
														';
														
														$b = 0;
														
														foreach ($bundles as $bundle) {
															
															if ((int) $bundle->visible !== 0 && $option->date->availability >= $bundle->total) {
															
															?>
															
															<script>fields_<?php echo $option_num.'_'.$sub_option?>['<?php echo $bundle->label?>_<?php echo $option_num.'_'.$sub_option?>'] = 0;</script>
															<div class="form-group row rezgo-bundle-hidden">
																<div class="col-md-3 col-xs-4 max-80">
																	<input type="number" min="0" name="" value="" id="<?php echo $bundle->label?>_<?php echo $option_num.'_'.$sub_option?>" rel="bundle" class="form-control input-sm" />
																</div>
																<label for="<?php echo $bundle->label?>_<?php echo $option_num.'_'.$sub_option?>" class="col-xs-8 control-label rezgo-label-padding-left">
																	x&nbsp;<?php echo $bundle->name?> 
																	<span class="rezgo-pax-price">(&nbsp;<?php echo $site->formatCurrency($bundle->price)?>&nbsp;)</span><br />
																	<span class="rezgo-bundle-makeup"><?php echo $bundle->makeup?></span>
																</label>
																<?php
																	foreach ($bundle->prices as $p => $c) {
																		echo '<input type="hidden" name="add[0]['.$p.'_num]" rel="'.$p.'_'.$option_num.'_'.$sub_option.'" value="" data-multiple="'.$c.'" class="'.$bundle->label.'_'.$option_num.'_'.$sub_option.'" disabled />';
																	}											
																?>
															</div>
															
															<?php
															
															$b++;
													
															} // if ($bundle->visible)
															
														} // foreach ($bundles)
														
													} // if (count($bundles))
													
													
													if ($b >= 1) {
														echo "<script> $('#option_".$option_num."_".$sub_option." .rezgo-bundle-hidden').fadeIn();</script>";
													}
																						
												?>
	
												<div class="text-danger rezgo-option-error" id="error_mobile_text_<?php echo $option_num.'_'.$sub_option?>" style="display:none;"></div>
											</div><!-- end col-sm-8-->
	
											<div class="col-lg-8 col-md-9 col-xs-12 pull-right">
												<?php $cart = $site->getCartState(); ?>
												<button type="submit" class="btn btn-block rezgo-btn-book btn-lg" onclick="return check_<?php echo $option_num.'_'.$sub_option?>();"><?php echo (($cart) ? 'Add to Order' : 'Book Now')?></button>
											</div>
										</div>
									</form>
																	
								<?php } else { ?>
									<div class="rezgo-order-unavailable"><span>Sorry, there is no availability for this option</span></div>
								<?php } // end if ($option->date->availability != 0) ?>
							</div>
						</div>
					</div>
	
					<?php $sub_option++; // increment sub option instead ?>
				<?php } // if ($_REQUEST['type']) ?>
      
      <?php } // if ($option->date->availability >= $option->block_size)?>
    
    <?php } // end foreach($options as $option) ?>
	</div>
  
<?php } else { // no availability, hide this option ?>
	<?php echo $availability_title?>
  <div class="panel panel-default panel-none-available">
    <div class="panel-body">
      <div class="rezgo-order-none-available"><span>Sorry, there are no available options on this day</span></div>
    </div>
  </div>
<?php } ?>