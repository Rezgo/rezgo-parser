<?
	$company = $site->getCompanyDetails();	
?>
<a name="waiver-top"></a>

<script type="text/javascript" src="<?php echo $site->path;?>/js/jquery.form.js"></script>
<script type="text/javascript" src="<?php echo $site->path;?>/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="<?php echo $site->path;?>/js/bootstrap-select.min.js"></script>

<link href="<?php echo $site->path;?>/css/bootstrap-select.min.css" rel="stylesheet" />

<style media="print">
	#waiver_complete, 
	#booking_pax_wrap, 
	#rezgo-waiver-share,
	#waiver-footer {
		display: none;
	}
	/*#rezgo-waiver-text {
		page-break-before: always;
	}*/
</style>

<style>
	.pax_year { 
		padding-left:0 !important;
		padding-right:12px !important;
	}
	.pax_month { 
		padding-left:12px !important;
		padding-right:12px !important;
	}
	.pax_day { 
		padding-left:12px !important;
		padding-right:0 !important;
	}
	.animated-checkmark {
		stroke: green;
		stroke-dashoffset: 745.74853515625;
		stroke-dasharray: 745.74853515625;
		animation: dash 2s ease-out forwards;
	}
	@keyframes dash {
		0% {
			stroke-dashoffset: 745.74853515625;
		}
		100% {
			stroke-dashoffset: 0;
		}
	}	
	#waiver_sign_check {
		width:130px;
		float:left;
		margin:20px 20px 0 0;
	}
	#waiver_sign_thanks h2 {
		color:#008000;
	}
	.rezgo-waiver-main #rezgo-signed-img img {
		display: inline-block;
		margin: 0 20px 20px;
		max-height: 140px;
	}	
	.bootstrap-select > .dropdown-toggle.bs-placeholder,
	.bootstrap-select > .dropdown-toggle.bs-placeholder:hover,
	.bootstrap-select > .dropdown-toggle.bs-placeholder:focus,
	.bootstrap-select > .dropdown-toggle.bs-placeholder:active {
		color: #000;
	}
	#waiver-sign-close + .tooltip > .tooltip-inner {
		color: #333;
		background-color: #EEE;
	}
	#waiver-sign-close + .tooltip.right > .tooltip-arrow { 
		background-color: #EEE; 
	}
	.rezgo-waiver-group-form {
		display:none;
	}
</style>

<link rel="stylesheet" href="<?php echo $site->path;?>/css/signature-pad.css" />

<?php 
	
	$item_id = '';
	$trans_num = '';
	$show_booking_data = false;
	$show_option_list = false;
	
	$hide_sign_btn = false;
	
	$waiver_type = 'general';
	
	if ($_REQUEST['trans_num'] && strlen($_REQUEST['trans_num']) >= 10) {
		
		$request_trans = $site->waiver_decode($_REQUEST['trans_num']);
		
		$show_booking_data = true;
		
		
		if (strlen($request_trans) === 10) { // booking
			
			$trans_num = $request_trans;
			$waiver_type = 'booking';
			
		} else { // booking pax
			
			$trans_part = explode('-', $request_trans);
			$trans_num = $trans_part[0];
			$pax_id = $trans_part[1];
			//$item_id = $pax_id;
			$waiver_type = 'pax';
			
		}
					
		foreach ($site->getBookings('q='.$trans_num.'&a=waiver,forms') as $booking) { 
		
			$item = $site->getTours('t=uid&q='.$booking->item_id, 0); // &d=2018-06-06
			
			$site->readItem($booking);
			
			if ($booking->availability_type != 'product') {
				
				foreach ($site->getBookingPassengers() as $passenger ) { 
				
					if ($passenger->id == $pax_id) $pax_data = $passenger;
					
					$booking_passengers[] = $passenger;
				
				}
		
			} // if ($booking->availability_type)
			
			 // echo '<pre>' . print_r($pax_data, 1) . '</pre>';
			
			// echo '<pre>' . print_r($booking, 1) . '</pre>';
						
		} // foreach $site->getBookings() 
		
		$waiver_content = $site->getWaiverContent($trans_num);
				
	} else {
		
		if ($_REQUEST['trans_num'] && strlen($_REQUEST['trans_num']) < 10) {
			$item_id = $_REQUEST['trans_num'];
		}
		
		$show_option_list = true;
		$option_list = '';
		
		$tourList = $site->getTours('t=com&q='.$item_id);
		
		foreach($tourList as $item) {
			
			$site->readItem($item);
			
			if ($item->waiver == 1 && (int) $item->waiver['type'] === 1) {
				
				if($last != (string) $item->item) {
					$option_list .= '<option value="'.$item->com.'"'.($item_id == $item->com ? ' selected' : '').'>'.$item->item.'</option>'."\n";
					$last = (string) $item->item;
				}
				
			}
			
		}
	
		$waiver_content = $site->getWaiverContent($item_id, 'com');
		
	} // if ($_REQUEST[trans_num])
	
?>


<div class="container-fluid rezgo-container">

<?php if(!$_REQUEST['headless']) { // hide outer containers when headless ?>

	<div class="jumbotron rezgo-booking">

    <div class="row">
    
      <ol class="breadcrumb rezgo-breadcrumb hidden-xs">
        <li class="active">Waiver</li>
      </ol>
      
    </div>
    
<?php } // end hide outer ?>
    
    <div class="row rezgo-form-group rezgo-waiver-main">
    
      <div class="col-xs-12" id="waiver_error" style="display:none">
      	<h3>An error has occurred while signing.</h3>
        <div id="waiver_error_text" class="lead alert alert-danger" role="alert">
        	<i class="fa fa-exclamation-triangle bigger-110" aria-hidden="true"></i>&nbsp;
					<span></span>
        </div>
        
      </div>
        
      <div class="col-xs-12" id="waiver_complete"<?php echo ($pax_data->signed ? '' : ' style="display:none"')?>>
      
        <div id="waiver_sign_success">
        	
        	<div class="col-xs-12 col-sm-3">
          	<div id="waiver_sign_check">
            <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 98.5 98.5" enable-background="new 0 0 98.5 98.5" xml:space="preserve">
              <path class="animated-checkmark" fill="none" stroke-width="8" stroke-miterlimit="10" d="M81.7,17.8C73.5,9.3,62,4,49.2,4C24.3,4,4,24.3,4,49.2s20.3,45.2,45.2,45.2s45.2-20.3,45.2-45.2c0-8.6-2.4-16.6-6.5-23.4l0,0L45.6,68.2L24.7,47.3"/>
            </svg>
            </div>
          </div>
        	
          <div class="col-xs-12 col-sm-9 align-center" id="waiver_sign_thanks">
          	<h2><span>Thank you for signing</span></h2>
            <?php if ($waiver_type == 'booking') { ?>
            	<p id="waiver_thanks_text">You can now print your waiver or have another passenger sign.</p>
            <?php } else { ?>
            	<p>You can now print your waiver or close this page.</p>
            <?php } ?>
            
            <!--  <?php echo $site->base?>/waiver/<?php echo ($_REQUEST['trans_num'] ? $_REQUEST['trans_num'].'/' : '')?>print  -->
            <div class="col-xs-12 col-md-6" id="rezgo-waiver-print">
              <a href="javascript:void(0);" onclick="window.print();" class="btn btn-lg rezgo-btn-print btn-block">
                <i class="fa fa-print bigger-110"></i>&nbsp;<span>Print Waiver</span>
              </a>
              
              <div class="clearfix hidden-md hidden-lg">&nbsp;</div>
              
              <?php if(!$_REQUEST['trans_num']) { ?>
              <a href="javascript:void(0);" onclick="parent.location.reload();" class="btn btn-lg rezgo-btn-default btn-block" style="padding:6px;">
                <i class="fa fa-refresh bigger-110"></i>&nbsp;<span>Sign Another Waiver</span>
              </a>
              <?php } ?>
            </div>
            
          </div>
          
        </div>
        
        <div class="clearfix hidden-xs">&nbsp;</div>
      
        <hr />
      
      </div>
      
      <div class="clearfix hidden-md hidden-lg">&nbsp;</div>
          
      <div id="rezgo-waiver-text" class="col-sm-12 clearfix">
      
      <?php if ($show_booking_data) { ?>
      
        <div class="col-sm-10" id="rezgo-waiver-heading"><h3>Waiver for <?php echo $booking->tour_name?>&nbsp;(<?php echo $booking->option_name?>)</h3></div>
        
        
        <div class="col-sm-2">
        	<?php if ($waiver_type == 'booking') { ?>
          <span id="rezgo-waiver-share">
            <a href="javascript:void(0);" id="social_url" data-toggle="popover" data-ajaxload="<?php echo $site->base?>/shorturl_ajax.php?url=<?php echo  urlencode('https://'.$_SERVER['HTTP_HOST'].$site->base.'/waiver/'.$_REQUEST['trans_num']) ?>&page=waiver">
              <i class="fa fa-share-alt-square" id="social_url_icon">&nbsp;</i>
            </a>
          </span>
          <?php } ?>
        </div>
        
        <table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="rezgo-td-label">Transaction&nbsp;#:</td>
            <td class="rezgo-td-data"><?php echo $booking->trans_num?></td>
          </tr>
        
          <?php if((string) $booking->date != 'open') { ?>
            <tr>
              <td class="rezgo-td-label">Date:</td>
              <td class="rezgo-td-data"><?php echo date((string) $company->date_format, (int) $booking->date)?>
              <?php if($booking->time != '') { ?> at <?php echo $booking->time?><?php } ?>
              </td>
            </tr>
            <?php } else { ?>
            <?php if ($booking->time) { ?>
              <tr id="rezgo-receipt-booked-for">
                <td class="rezgo-td-label"><span>Time:</span></td>
                <td class="rezgo-td-data"><span><?php echo $booking->time?></span></td>
              </tr>
            <?php } ?>
          <?php } ?>	
        </table>			
        
        <hr />
        
        <?
				if ($waiver_type == 'booking') {
					
					$total_pax = $total_signed = 0;
					$booking_pax_list = '';
						
					foreach ($booking_passengers as $booking_pax) {
						
						$booking_pax_list .= '<a href="javascript:void(0);" class="list-group-item booking_pax_sign '.( $booking_pax->signed ? 'disabled' : '').'" data-id="'.$booking_pax->id.'" id="pax-id-'.$booking_pax->id.'"> ';
						
						$booking_pax_list .= $booking_pax->label .' '. $booking_pax->num .' - '. $booking_pax->first_name .' '. $booking_pax->last_name;
						
						if ( $booking_pax->signed ) {
							$booking_pax_list .= '<span class="badge pax-check"> <i class="fa fa-check"></i><span class="hidden-xs">&nbsp; waiver</span> signed </span>';
							$total_signed++;
						} else {
							$booking_pax_list .= '<span class="badge pax-sign"> <i class="fa fa-pencil"></i>&nbsp; sign <span class="hidden-xs">waiver</span> </span>';
						}
						
						$booking_pax_list .= '</a>';
						$booking_pax_list .= '<input type="hidden" id="rezgo-sign-count-'.$booking_pax->id.'" class="rezgo-sign-count" value="'.(($booking_pax->signed) ? '1' : '0').'" />';
						
						$total_pax++;
						
					}
					
					?>
          
          <div id="booking_pax_wrap">
          	<?php if ($total_signed == $total_pax) { ?>
            <div class="rezgo-waiver-instructions">All passenger waivers have been signed. Thank you.</div>
            <?php } else { ?>
            <div class="rezgo-waiver-instructions">Please select a passenger, then read and sign your waiver.</div>
            <?php } ?>
            <div class="list-group" id="booking_pax_list">
            <?php echo $booking_pax_list; ?>
            <input type="hidden" id="active-pax-id" value="" />
            </div>
          </div>
          
          <?
					
				} // if ($waiver_type == 'booking')
				
				?>		
        
      
      <?php } ?>
      
      <?php if ($show_option_list) { ?>
      
        <div class="rezgo-form-row rezgo-form-one form-group" id="rezgo-waiver-option-choose">
          <label for="pax_option" class="col-xs-2 control-label rezgo-label-left rezgo-waiver-choose"><span class="hidden-xs hidden-sm">Choose </span><span class="hidden-xs hidden-sm hidden-md">your </span>Tour</label>
          <div class="col-xs-10 rezgo-form-input">
            <select name="pax_option" id="pax_option" class="form-control required selectpicker show-tick">
              <option value="">General Waiver</option>
              <?php echo $option_list?>
            </select>                        
          </div>
        </div>
      
      <?php } ?>
        
        <div id="pax_waiver_content"<?php echo (($waiver_type == 'booking') ? ' style="display:none"' : '')?>>
        	<?php echo $waiver_content?>
        </div>
      
      </div>
      
      <div id="rezgo-waiver-form" class="col-sm-12">
            
        <div class="clearfix">
        
          <div class="tab-text">
          
            <div id="waiver_form_container"<?php echo ($pax_data->signed ? ' style="display:none"' : '')?>>
            
              <div class="body">
                <div class="row">
                  <div class="col-md-12">
                                    
                    <form name="pax_waiver_form" id="pax_waiver_form" method="post" target="rezgo_content_frame">
                      
                      <?php if ($waiver_type != 'booking') { ?>
                      <div class="rezgo-waiver-child">
                        <input type="checkbox" id="child" name="child" />  &nbsp;
                        <strong>I am signing this waiver on behalf of a child.</strong>
                        <div id="rezgo-waiver-child-text" style="display:none;">Please enter the child's name and birthdate and sign on their behalf.</div>
                      </div>  
                      <?php } ?>
                    
                      <div class="row rezgo-form-group rezgo-additional-info">
                    
                      <input type="hidden" name="trans_num" id="trans_num" value="<?php echo $trans_num?>" />
                      <input type="hidden" name="pax_item" id="pax_item" value="<?php echo ($item_id == '' ? $booking->item_id : $item_id)?>" />
                      <input type="hidden" name="pax_signature" id="pax_signature" value="" />
                      <textarea name="pax_waiver_text" id="pax_waiver_text" style="display:none; visibility:hidden; height:0;"><?php echo ($trans_num == '' ? $site->getWaiverContent() : $site->getWaiverContent($trans_num));?></textarea>
                      
                      <?php if ($waiver_type == 'pax' || $waiver_type == 'general') { ?>
                      
                      <div class="rezgo-waiver-instructions">Please complete the following required fields.</div>
                      
                      <input type="hidden" name="pax_id" id="pax_id" value="<?php echo $pax_data->id?>" />
                      <input type="hidden" name="pax_type" id="pax_type" value="<?php echo ($pax_data ? $pax_data->type : 'general')?>" />
                      <input type="hidden" name="pax_type_num" id="pax_type_num" value="<?php echo $pax_data->num?>" />
          
                        <div class="rezgo-form-row rezgo-form-one form-group rezgo-pax-first-last">
                          <label for="pax_first_name" class="col-xs-2 control-label rezgo-label-right">First <span class="hidden-xs">Name</span></label>
                          <div class="col-xs-4 rezgo-form-input">
                            <input type="text" class="form-control required" id="pax_first_name" name="pax_first_name" value="<?php echo $pax_data->first_name?>" autocomplete="off" /> 
                          </div>
                          <label for="pax_last_name" class="col-xs-2 control-label rezgo-label-right">Last <span class="hidden-xs">Name</span></label>
                          <div class="col-xs-4 rezgo-form-input">
                            <input type="text" class="form-control required" id="pax_last_name" name="pax_last_name" value="<?php echo $pax_data->last_name?>" autocomplete="off" />
                          </div>
                        </div>
          
                        <div class="rezgo-form-row rezgo-form-one form-group rezgo-pax-phone-email">
                          <label for="pax_phone" class="col-xs-2 control-label rezgo-label-right">Phone</label>
                          <div class="col-xs-4 rezgo-form-input">
                            <input type="text" class="form-control required" id="pax_phone" name="pax_phone" value="<?php echo $pax_data->phone_number?>" autocomplete="off" />
                          </div>
                          <label for="pax_email" class="col-xs-2 control-label rezgo-label-right">Email</label>
                          <div class="col-xs-4 rezgo-form-input">
                          <input type="email" class="form-control required" id="pax_email" name="pax_email" value="<?php echo $pax_data->email_address?>" autocomplete="off" />
                          </div>
                        </div>
          
                        <div class="rezgo-form-row rezgo-form-one form-group" id="pax-birth-wrp">
                          <label for="pax_birthdate" class="col-xs-2 control-label rezgo-label-right">Birth <span class="hidden-xs">Date</span></label>
                          <div class="col-xs-10 rezgo-form-input" id="pax-birth-input">
                            <div class="col-xs-4 rezgo-form-input pax_year">
                              <select name="pax_birthdate[year]" id="pax_birthdate_year" class="form-control required">
                              	<option value=""></option>
                              </select>
                            </div>
                            <div class="col-xs-4 rezgo-form-input pax_month">
                              <select name="pax_birthdate[month]" id="pax_birthdate_month" class="form-control required">
                              	<option value=""></option>
                              </select>
                            </div>
                            <div class="col-xs-4 rezgo-form-input pax_day">
                              <select name="pax_birthdate[day]" id="pax_birthdate_day" class="form-control required">
                              	<option value=""></option>
                              </select>
                            </div>
                          </div>
                        </div>
                        
                        <?php } ?>
                        
                        <a name="waiver-form" target="_parent"></a>
                        
                        <div id="booking_pax_forms" style="display:none;"></div>
                                
                        <?php $group_forms = array(); ?>    
                        
                        <?php if ($pax_data) { // this is a pax booking waiver ?> 
                                                         
													<?php foreach ( $pax_data->forms->form as $form ) { ?>
                              
                            <?php if($form->type != 'checkbox_price') { $group_forms[(int) $form->id] = $form->title; } ?>
                            
                            <?php if($form->type == 'text') { ?>
                              <div class="form-group rezgo-custom-form rezgo-form-input">
                                <label><span><?php echo $form->title?><?php if((string) $form->require == '1') { ?> <em class="fa fa-asterisk"></em><?php } ?></span></label>  
                                <input type="text" class="custom-form-input form-control<?php echo ((string) $form->require == '1') ? ' required' : ''; ?>" data-answer="rezgo-waiver-answer-<?php echo $form->id?>" id="rezgo-waiver-form-<?php echo $form->id?>" name="pax_group[<?php echo $pax_data->type?>][<?php echo $pax_data->num?>][forms][<?php echo $form->id?>]" value="<?php echo $form->answer?>" />
                                <p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>
                              </div>
                            <?php } ?>
    
                            <?php if($form->type == 'select') { ?>
                              <div class="form-group rezgo-custom-form rezgo-form-input">
                                <label><span><?php echo $form->title?><?php if((string) $form->require == '1') { ?> <em class="fa fa-asterisk"></em><?php } ?></span></label>
                                <select class="custom-form-input form-control<?php echo ((string) $form->require == '1') ? ' required' : ''; ?>" data-answer="rezgo-waiver-answer-<?php echo $form->id?>" id="rezgo-waiver-form-<?php echo $form->id?>" name="pax_group[<?php echo $pax_data->type?>][<?php echo $pax_data->num?>][forms][<?php echo $form->id?>]">
                                  <?php 
                                  if((string) $form->options) {
                                    $opt = explode(',', (string)$form->options);
                                    foreach((array)$opt as $v) {																		
                                      echo '<option'.(((string) $form->answer == $v) ? ' selected' : '').'>' . $v . '</option>';
                                    }
                                  }
                                  ?>                                
                                </select>
                                <p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>
                              </div>
                            <?php } ?>
    
                            <?php if($form->type == 'multiselect') { ?>
                              <div class="form-group rezgo-custom-form rezgo-form-input">
                                <label><span><?php echo $form->title?><?php if((string) $form->require == '1') { ?> <em class="fa fa-asterisk"></em><?php } ?></span></label>
                                <select class="custom-form-input form-control<?php echo ((string) $form->require == '1') ? ' required' : ''; ?>" multiple="multiple" data-answer="rezgo-waiver-answer-<?php echo $form->id?>" id="rezgo-waiver-form-<?php echo $form->id?>" name="pax_group[<?php echo $pax_data->type?>][<?php echo $pax_data->num?>][forms][<?php echo $form->id?>][]">
                                  <?php 
                                    if((string) $form->options) {
                                      $opt = explode(',', (string)$form->options);
                                      foreach((array)$opt as $v) {		
                                        if (strpos((string) $form->answer, ',' === false)) {														
                                          echo '<option'.(((string) $form->answer == $v) ? ' selected' : '').'>' . $v . '</option>';
                                        } else {
                                          $answers = explode(', ', (string)$form->answer);
                                          echo '<option'.((in_array($v, $answers)) ? ' selected' : '').'>' . $v . '</option>';
                                        }
                                      }
                                    }
                                  ?>     
                                </select>
                                <p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>
                              </div>
                            <?php } ?>
    
                            <?php if($form->type == 'textarea') { ?>
                              <div class="form-group rezgo-custom-form rezgo-form-input">
                                <label><span><?php echo $form->title?><?php if((string) $form->require == '1') { ?> <em class="fa fa-asterisk"></em><?php } ?></span></label>
                                <textarea class="custom-form-input form-control<?php echo ((string) $form->require == '1') ? ' required' : ''; ?>" data-answer="rezgo-waiver-answer-<?php echo $form->id?>" id="rezgo-waiver-form-<?php echo $form->id?>" name="pax_group[<?php echo $pax_data->type?>][<?php echo $pax_data->num?>][forms][<?php echo $form->id?>]" cols="40" rows="4"><?php echo $form->answer?></textarea>
                                <p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>
                              </div>
                            <?php } ?>
    
                            <?php if($form->type == 'checkbox') { ?>
                              <div class="form-group rezgo-custom-form rezgo-form-input">
                                <div class="checkbox rezgo-form-checkbox">
                                  <label>
                                    <input type="checkbox" class="custom-form-input<?php echo ((string) $form->require == '1') ? ' required' : ''; ?>" data-answer="rezgo-waiver-answer-<?php echo $form->id?>" id="rezgo-waiver-form-<?php echo $form->id?>" name="pax_group[<?php echo $pax_data->type?>][<?php echo $pax_data->num?>][forms][<?php echo $form->id?>]" <?php echo (((string) $form->answer == 'on') ? ' checked' : '')?> />
                                    <span><?php echo $form->title?></span>
                                    <?php if((string) $form->require == '1') { ?> <em class="fa fa-asterisk"></em><?php } ?>
                                    <p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>
                                  </label>
                                </div>
                              </div>
                            <?php } ?>
                                      
                          <?php } // foreach ($pax_data->forms->form as $form ) ?>    
                        
                        <?php 
												
													} // if ($pax_data)
													
													
													if ($waiver_type == 'general' && !$is_portal) {
														
														$waiver_forms = $site->getWaiverForms();
														$f = 0;
												
												?>   
                        
													<?php foreach ( $waiver_forms as $form ) { ?>
                          
                            <input type="hidden" name="pax_group[forms][<?php echo $f?>][id]" value="<?php echo $form->id?>" />
                            <input type="hidden" name="pax_group[forms][<?php echo $f?>][type]" value="<?php echo $form->type?>" />
                            <input type="hidden" name="pax_group[forms][<?php echo $f?>][title]" value="<?php echo $form->title?>" />
                            <input type="hidden" name="pax_group[forms][<?php echo $f?>][instructions]" value="<?php echo $form->instructions?>" />
                            <input type="hidden" name="pax_group[forms][<?php echo $f?>][require]" value="<?php echo $form->require?>" />
                            <input type="hidden" name="pax_group[forms][<?php echo $f?>][options]" value="<?php echo $form->options?>" />
                              
                            <?php if($form->type != 'checkbox_price') { $group_forms[(int) $form->id] = $form->title; } ?>
                            
                            <?php if($form->type == 'text') { ?>
                              <div class="form-group rezgo-custom-form rezgo-form-input">
                                <label><span><?php echo $form->title?><?php if((string) $form->require == '1') { ?> <em class="fa fa-asterisk"></em><?php } ?></span></label>  
                                <input type="text" class="custom-form-input form-control<?php echo ((string) $form->require == '1') ? ' required' : ''; ?>" data-answer="rezgo-waiver-answer-<?php echo $form->id?>" id="rezgo-waiver-form-<?php echo $form->id?>" name="pax_group[forms][<?php echo $f?>][answer]" value="<?php echo $form->answer?>" />
                                <p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>
                              </div>
                            <?php } ?>
    
                            <?php if($form->type == 'select') { ?>
                              <div class="form-group rezgo-custom-form rezgo-form-input">
                                <label><span><?php echo $form->title?><?php if((string) $form->require == '1') { ?> <em class="fa fa-asterisk"></em><?php } ?></span></label>
                                <select class="custom-form-input form-control<?php echo ((string) $form->require == '1') ? ' required' : ''; ?>" data-answer="rezgo-waiver-answer-<?php echo $form->id?>" id="rezgo-waiver-form-<?php echo $form->id?>" name="pax_group[forms][<?php echo $f?>][answer]">
                                  <?php 
                                  if((string) $form->options) {
                                    $opt = explode(',', (string)$form->options);
                                    foreach((array)$opt as $v) {																		
                                      echo '<option'.(((string) $form->answer == $v) ? ' selected' : '').'>' . $v . '</option>';
                                    }
                                  }
                                  ?>                                
                                </select>
                                <p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>
                              </div>
                            <?php } ?>
    
                            <?php if($form->type == 'textarea') { ?>
                              <div class="form-group rezgo-custom-form rezgo-form-input">
                                <label><span><?php echo $form->title?><?php if((string) $form->require == '1') { ?> <em class="fa fa-asterisk"></em><?php } ?></span></label>
                                <textarea class="custom-form-input form-control<?php echo ((string) $form->require == '1') ? ' required' : ''; ?>" data-answer="rezgo-waiver-answer-<?php echo $form->id?>" id="rezgo-waiver-form-<?php echo $form->id?>" name="pax_group[forms][<?php echo $f?>][answer]" cols="40" rows="4"><?php echo $form->answer?></textarea>
                                <p class="rezgo-form-comment"><span><?php echo $form->instructions?></span></p>
                              </div>
                            <?php } ?>
                            
                          <?php $f++; // increment the general form count ?>   
                                   
                          <?php } // foreach ( $waiver_forms as $form ) ?>    
                        
                        <?php } // if ($waiver_type == 'general') ?> 
          
                      </div>
                      
                    </form>
                      
                  </div>
                  
                </div>
              </div>
        
              <div class="footer">
                <div class="row">
                  <div id="rezgo-sign-nav">
	                  
                    <div class="col-xs-12 col-md-6 col-md-offset-6">
                      <button id="sign" class="btn btn-lg rezgo-btn-default btn-block" <?php echo  (($waiver_type == 'booking') ? 'style="display:none;"' : '')?>>
                        <i class="fa fa-pencil bigger-110"></i>
                        <span id="rezgo-sign-nav-txt"> Sign Waiver</span>
                      </button>
                    </div>
                    
                    <div class="clearfix hidden-md hidden-lg">&nbsp;</div>
        
                  </div>
                </div>
              </div>
              
            </div>
                        
          </div>
      
          <div class="tab-sign" style="display:none;">
          
            <div id="signature-pad">
            
              <button id="waiver-sign-close" type="button" class="close" data-toggle="tooltip" data-placement="left" title="back to waiver" aria-label="Close" onClick="backToWaiver();">
                <span aria-hidden="true"><i class="fa fa-times bigger-110"></i></span>
              </button>
            
              <div class="body">
                <p>Please sign in the space below</p>
                <canvas></canvas>
              </div>
              
              <div class="footer">
                <div class="row">
                  <div class="col-xs-6">
                    <button id="clear" class="btn btn-lg rezgo-btn-default btn-block" data-action="clear" type="button">
                      <i class="fa fa-times bigger-110"></i>
                      <span> Clear</span>
                    </button>
                  </div>
                  <div class="col-xs-6">
                    <button id="save" class="btn btn-lg rezgo-btn-book btn-block" data-action="save" type="button">
                      <i class="fa fa-check bigger-110"></i>
                      <span> Save</span>
                    </button>
                  </div>
                </div>
              </div>
            
              <div class="clearfix">&nbsp;</div>
              
            </div>
            
          </div>
          
        </div>
        
      </div>  
              
      <div class="clearfix" id="scroll_form">&nbsp;</div>

    
      <div class="col-xs-12" id="waiver_complete_data" style="display:none">
      
        <h3><span>You entered the following information.</span></h3>
            
        <div class="col-sm-8 col-xs-12">
          <table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
            <tr>
              <td class="rezgo-td-label">Name</td>
              <td class="rezgo-td-data" id="rezgo-waiver-name"><span><?php echo $pax_data->first_name?>&nbsp;<?php echo $pax_data->last_name?></span></td>
            </tr>
            <tr>
              <td class="rezgo-td-label">Phone</td>
              <td class="rezgo-td-data" id="rezgo-waiver-phone"><span><?php echo $pax_data->phone_number?></span></td>
            </tr>
            <tr>
              <td class="rezgo-td-label">Email</td>
              <td class="rezgo-td-data" id="rezgo-waiver-email"><span><?php echo $pax_data->email_address?></span></td>
            </tr>
            <tr style="display:none;" id="rezgo-waiver-birthdate-row">
              <td class="rezgo-td-label">Birth Date</td>
              <td class="rezgo-td-data" id="rezgo-waiver-birthdate"><span></span></td>
            </tr>
            <tr style="display:none;" id="rezgo-waiver-child-row">
              <td class="rezgo-td-label">Child's Waiver</td>
              <td class="rezgo-td-data" id="rezgo-waiver-child"><span></span></td>
            </tr>
            <?
						foreach ($group_forms as $gk => $gv) {
							echo '
							<tr class="rezgo-waiver-group-form">
								<td class="rezgo-td-label">'.$gv.'</td>
								<td class="rezgo-td-data" id="rezgo-waiver-answer-'.$gk.'"><span></span></td>
							</tr>
							';
						}
						?>
          </table>
         </div>
        
        <div class="clearfix">&nbsp;</div>
        
        <div class="row" id="rezgo-signed-img" style="display:none;">
          <hr />
          <div class="col-sm-8 col-xs-12">
            <div class="col-sm-3 col-xs-12">
              <strong>Signature</strong>
            </div>
            <div class="col-sm-9 col-xs-12">
              <img id="signature-img" alt="signature" />
            </div>
          </div>
        </div>
        
        <div class="clearfix">&nbsp;</div>
      
        <hr />
      
      </div>
    
    
    </div><!-- // .rezgo-waiver-main --> 
    
<?php if(!$_REQUEST['headless']) { // hide outer containers when headless ?>
    
	</div><!-- // .rezgo-booking --> 

<?php } // end hide outer ?>
  
</div><!-- //	.rezgo-container --> 


<script src="<?php echo $site->path;?>/js/signature_pad.min.js"></script>
<script src="<?php echo $site->path;?>/js/signature_pad_remove_blank.js"></script>

<script>
	var 
<?php if($_REQUEST['headless']) { ?>
	//receiver = parent.window.document.getElementById('test-content').contentWindow,
<?php } ?>	
	waiverForm = document.getElementById('rezgo-waiver-form'),
	signButton = document.getElementById('sign'),
	signBtnTxt = document.getElementById('rezgo-sign-nav-txt'),
	saveButton = document.getElementById('save'),
	signaturePad = document.getElementById('signature-pad'),
	clearButton = signaturePad.querySelector('[data-action=clear]'),
	undoButton = signaturePad.querySelector('[data-action=undo]'),
	canvas = signaturePad.querySelector('canvas'),
	waiverTxt = waiverForm.getElementsByClassName('tab-text')[0],
	waiverTxtBody = waiverTxt.getElementsByClassName('body')[0],
	waiverSignArea = document.getElementById('rezgo-signed-img'),
	waiverSignImg = document.getElementById('signature-img'),
	scrollDownInfo = document.getElementById('scroll-down-info'),
		
	paxSignature = document.getElementById('pax_signature'),
	signaturePad = new SignaturePad(canvas);

	function resizeCanvas() {
		var ratio =  Math.max(window.devicePixelRatio || 1, 1);
		canvas.width = canvas.offsetWidth * ratio;
		canvas.height = canvas.offsetHeight * ratio;
		canvas.getContext("2d").scale(ratio, ratio);
		signaturePad.clear();
	}
	
	function showSignaturePad(e) {
		
		if(!validate_form()) return false;
		
		$("#rezgo-waiver-text").hide();
		$(".tab-text").hide();
		$(".tab-sign").show();
		
		resizeCanvas();
		
		if ('parentIFrame' in window) {										
			parentIFrame.moveToAnchor('waiver-top');
		}			
	}
	
	function validate_form() {
		var valid = $('#pax_waiver_form').valid();
		return valid;
	}
	
	function clearSignature(e) {
		signaturePad.clear();
	}
	
	function saveSignaturePax(e) {
		if (signaturePad.isEmpty()) {
			alert("Please provide a signature first.");
		} else {
			e.preventDefault();
			
			$('#pax_signature').val(signaturePad.toDataURL());
			
			// the field is present? submit normally
			$('#pax_waiver_form').ajaxSubmit({
				url: '<?php echo $site->base?>/waiver_ajax.php', 
				data: { action: 'sign' },
				success: function(response) { 
					if (response == 'signed') {
						addSignature(signaturePad.toDataURL());
					} else {
						signatureError(response);
					}
				} 
			});
						
		}
	}
	
	function addSignature(req) {
		
		<?php if($_REQUEST[callback]) { ?>
		
		parent.window.postMessage("waiver_signed", "*");
		
		<?php } else { ?>
		
		waiverSignArea.style.display = 'block';
		waiverSignImg.src = req;
		
		signButton.disabled = true;
		signButton.className += ' rezgo-waiver-sign-disabled';
	
		$("#rezgo-waiver-text").show();
		$(".tab-text").show();
		$(".tab-sign").hide();
		
		$("#pax_waiver_form input[type!='hidden']").attr('disabled', true); 
		$('#pax_option').prop('disabled', true).trigger("chosen:updated");
		$('.pax_birth_row select').attr('disabled', true);
		signBtnTxt.innerHTML = 'signed';
		$("#waiver_form_container").hide();
		$("#rezgo-waiver-option-choose").hide();
		
		$("#rezgo-waiver-name span").html($('#pax_first_name').val() + ' ' + $('#pax_last_name').val());
		$("#rezgo-waiver-phone span").html($('#pax_phone').val());
		$("#rezgo-waiver-email span").html($('#pax_email').val());
		
		$('.custom-form-input').each(function() {
			var answer_target = $(this).data('answer');
			var val = $(this).val();
			if (val == 'on' && $(this).is(':checked')) {
				val = 'yes';
			} else if (val == 'on' && $(this).not(':checked')) { 
				val = 'no';
			}
			$('#' + answer_target + ' span').html(val);
		});		
		
		$('.rezgo-waiver-group-form').show();
		
		var pax_year_val = $('#pax_birthdate_year').val();
		var pax_month_val = $('#pax_birthdate_month').val();
		var pax_day_val = $('#pax_birthdate_day').val();
		
		if(pax_month_val.length < 2) pax_month_val = '0' + pax_month_val;
		if(pax_day_val.length < 2) pax_day_val = '0' + pax_day_val;
		
		$("#rezgo-waiver-birthdate span").html(pax_year_val + '-' + pax_month_val + '-' + pax_day_val);
		$("#rezgo-waiver-birthdate-row").show();
		
		var child_val = $('#child').val();
		if (child_val == 'on' && $('#child').is(':checked')) {
			child_val = 'yes';
		} else if (child_val == 'on' && $('#child').not(':checked')) { 
			child_val = 'no';
		}
		
		$("#rezgo-waiver-child span").html(child_val);
		$("#rezgo-waiver-child-row").show();
		
		$("#waiver_complete").show();
		
		$("#waiver_complete_data").show();
		
		$('#rezgo-waiver-form').hide(); // interferes on mobile
		
		<?php if ($waiver_type == 'booking') { ?>
		
			var active_pax = $('#active-pax-id').val();
			$('#pax-id-' + active_pax).addClass('disabled');
			$('#pax-id-' + active_pax + ' .badge').html(' <i class="fa fa-check"></i><span class="hidden-xs">&nbsp; waiver</span> signed ');
			$('#pax-id-' + active_pax + ' .badge').removeClass('pax-sign');
			$('#pax-id-' + active_pax + ' .badge').addClass('pax-check');
			
			$('#rezgo-sign-count-' + active_pax).val('1');
				
			var totalPax = 0;	
			var signedTotal = 0;
			
			$('.rezgo-sign-count').each(function() {
				if(this.value == 1) {
					signedTotal++;
				}
				totalPax++;
			});		
			
			if (signedTotal == totalPax) {
				$('#waiver_thanks_text').html('All passengers have now signed.');		
			}
		
		<?php } ?>
		
		if ('parentIFrame' in window) {										
			parentIFrame.moveToAnchor('waiver-top');
		}				
		
		<?php } ?>
		
	}
	
	function signatureError(response) {
		
		<?php if($_REQUEST[callback]) { ?>
		
		parent.window.postMessage("waiver_signed", "*");
		//parent.window.postMessage("waiver_error", "*");
		
		<?php } else { ?>
		
		$(".tab-text").show();
		$(".tab-sign").hide();
		
		$("#waiver_error").show();
		$("#waiver_error_text span").html(response);
		
		$('#pax_waiver_form input').attr('disabled', true);
		$('#pax_option').prop('disabled', true).trigger("chosen:updated");
		$('.pax_birth_row select').attr('disabled', true);
		$("#waiver_form_container").hide();
		$("#rezgo-waiver-option-choose").hide();
		
		$("#rezgo-waiver-name span").html($('#pax_first_name').val() + ' ' + $('#pax_last_name').val());
		$("#rezgo-waiver-phone span").html($('#pax_phone').val());
		$("#rezgo-waiver-email span").html($('#pax_email').val());
		
		
		$('.custom-form-input').each(function() {
			var answer_target = $(this).data('answer');
			var val = $(this).val();
			if (val == 'on' && $(this).is(':checked')) {
				val = 'yes';
			} else if (val == 'on' && $(this).not(':checked')) { 
				val = 'no';
			}
			$('#' + answer_target + ' span').html(val);
		});		
		
		$('.rezgo-waiver-group-form').show();
		
		$("#waiver_complete_data").show();
		
		$('#rezgo-waiver-form').hide(); // interferes on mobile
		
		if ('parentIFrame' in window) {										
			parentIFrame.moveToAnchor('waiver-top');
		}				
		
		<?php } ?>
		
	}
	
	function backToWaiver() {
		
		$("#rezgo-waiver-text").show();
		
		$(".tab-text").show();
		$(".tab-sign").hide();
		
		if ('parentIFrame' in window) {										
			parentIFrame.moveToAnchor('waiver-top');
		}			
	}

	saveButton.addEventListener('click', saveSignaturePax);
	signButton.addEventListener('click', showSignaturePad);
	clearButton.addEventListener('click', clearSignature);

	window.onresize = resizeCanvas;
		
</script>

<script>

	$(document).ready(function() {
			
		var monthNames = [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ];
		
		// populate year select box
		for (i = new Date().getFullYear(); i > 1900; i--){
				$('#pax_birthdate_year').append($('<option />').val(i).html(i));
		}
		// populate month select box
		for (i = 1; i < 13; i++){
				$('#pax_birthdate_month').append($('<option />').val(i).html(monthNames[i - 1]));
		}
		// populate day select box
		updateNumberOfDays(); 
		
		// listen for change events
		$('#pax_birthdate_year, #pax_birthdate_month').change(function(){
				updateNumberOfDays(); 
		});
		
		// update days based on current month/year
		function updateNumberOfDays(){
			$('#pax_birthdate_day').html('');
			month = $('#pax_birthdate_month').val();
			year = $('#pax_birthdate_year').val();
			days = daysInMonth(month, year);
		
			$('#pax_birthdate_day').append($('<option />'));
		
			for(i=1; i < days+1 ; i++){
				$('#pax_birthdate_day').append($('<option />').val(i).html(pad(i)));
			}
		}
		
		// helper functions
		function daysInMonth(month, year) {
			return new Date(year, month, 0).getDate();
		}		
				
		function pad(num) {
			var s = num+'';
			while (s.length < 2) s = '0' + s;
			return s;
		}	
		
		// validation setup
		$.validator.setDefaults({
			highlight: function(element) {
				$(element).closest('.rezgo-form-input').addClass('has-error');
				$(element).closest('.form-group').addClass('has-error');
			},
			unhighlight: function(element) {
				$(element).closest('.rezgo-form-input').removeClass('has-error');
				$(element).closest('.form-group').removeClass('has-error');
			},
			ignore: ":hidden:not(.required)", 
			focusInvalid: false,
			errorElement: 'span',
			errorClass: 'help-block',
			errorPlacement: function(error, element) {
				error.insertAfter(element);
			}

		});
		

		$('#pax_waiver_form').validate({
			rules: { 
				pax_birthdate_year:"required",
				pax_birthdate_month:"required",
				pax_birthdate_day:"required"
			},
			messages: {
				pax_option: {
					required: "Choose your Tour"
				},
				pax_first_name: {
					required: "Enter your first name"
				},
				pax_last_name: {
					required: "Enter your last name"
				},
				pax_phone: {
					required: "Enter your phone number"
				},
				pax_email: {
					required: "Enter a valid email address"
				},
				'pax_birthdate[year]': {
					required: "Please enter your birth year"
				},
				'pax_birthdate[month]': {
					required: "Please enter your birth month"
				},
				'pax_birthdate[day]': {
					required: "Please enter your birth day"
				}
				
			}
		});
				
		$('#pax_option').change(function(){ 
			var option_id = $(this).val();
			$('#pax_item').val(option_id);
			
			$.ajax({
				url: '<?php echo $site->base?>/waiver_ajax.php?action=get_waiver&option_id=' + option_id,
				context: document.body,
				success: function(data) {
					$('#pax_waiver_content').html(data); 
					$('#pax_waiver_text').val(data); 
				}
			});
			
		});
				
		$('.booking_pax_sign').click(function(e){ 
		
			e.preventDefault();
		
			var pax_id = $(this).data('id');
			
			$('#active-pax-id').val(pax_id);
			
			$.ajax({
				url: '<?php echo $site->base?>/waiver_ajax.php?action=get_forms&trans_num=<?php echo $trans_num?>&pax_id=' + pax_id,
				context: document.body,
				success: function(data) {
					
					$('#booking_pax_forms').html(data); 
					
					// RE-populate year select box
					for (i = new Date().getFullYear(); i > 1900; i--){
							$('#pax_birthdate_year').append($('<option />').val(i).html(i));
					}
					// RE-populate month select box
					for (i = 1; i < 13; i++){
							$('#pax_birthdate_month').append($('<option />').val(i).html(monthNames[i - 1]));
					}
					// RE-populate day select box
					updateNumberOfDays(); 
					
					// listen again for change events
					$('#pax_birthdate_year, #pax_birthdate_month').change(function(){
							updateNumberOfDays(); 
					});
					
					$('#waiver_complete').hide();
					$('#waiver_complete_data').hide();
					
					$('#rezgo-waiver-form').show(); 
					$('#waiver_form_container').show(); 
					
					$('#rezgo-sign-nav-txt').text(' Sign Waiver');
					$('#sign').show(); 
					$('#sign').prop("disabled", false);
					$('#sign').removeClass('rezgo-waiver-sign-disabled');
					
					$('#pax_waiver_content').show();
					$('#booking_pax_forms').show(); 
					
					var pax_form_position = $('#scroll_form').position();
					var pax_edit_scroll = Math.round(pax_form_position.top);
		
					if('parentIFrame' in window) {
						setTimeout(function () {
							// parentIFrame.scrollTo(0,pax_edit_scroll);
							parentIFrame.sendMessage(pax_edit_scroll);
						}, 100);
					}					
					
				}
			});
			
		});
		
		$('#waiver-sign-close').tooltip();
		
		// handle short url popover
		$('*[data-ajaxload]').bind('click',function() {
			var e=$(this);
			e.unbind('click');
			$.get(e.data('ajaxload'),function(d){
				e.popover({
					html : true,
					title: false,
					placement: 'left',
					content: d,
					}).popover('show');
			});
		});
		
		$('body').on('click', function (e) {
			$('[data-toggle="popover"]').each(function () {
				if (!$(this).is(e.target) && e.target.id != 'rezgo-short-url' && $(this).has(e.target).length === 0) {
					$(this).popover('hide');
				}
			});
		});
		
		$('#pax_waiver_form').on('click', '#child', function(){
			$('#rezgo-waiver-child-text').slideToggle('slow');
		});		

	});
</script>