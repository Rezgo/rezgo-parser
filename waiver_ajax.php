<?php 
	// This script handles the booking requests made via ajax by book.php
	
	require('rezgo/include/page_header.php');

	// start a new instance of RezgoSite
	$site = new RezgoSite(secure);
	
	$response = 'empty';

	// save signed waiver
	if ($_REQUEST['action'] == 'sign') {
		
		$result = $site->signWaiver();
		
		if ($result->status == 'signed') {
			
			$response = $result->status;
			//$response = $result->trans;
			
		} else {
			
			$response = (string) $result->error;
			
		}
		
		//print_r($result);
		
	} 
	
	// get waiver content
	if ($_REQUEST['action'] == 'get_waiver') {
		
		$response = $site->getWaiverContent( $_REQUEST['option_id'], 'com' );
		
	}
	
	// get waiver forms for booking pax
	if ($_REQUEST['action'] == 'get_forms') {
		
		foreach ($site->getBookings('q='.$_REQUEST['trans_num'].'&a=waiver,forms') as $booking) { 
		
			$item = $site->getTours('t=uid&q='.$booking->item_id, 0); // &d=2018-06-06
			
			$site->readItem($booking);
			
			if ($booking->availability_type != 'product') {
				
				foreach ($site->getBookingPassengers() as $passenger ) { 
				
					if ($passenger->id == $_REQUEST['pax_id']) $pax_data = $passenger;
				
				}
		
			} // if ($booking->availability_type)
						
		} // foreach $site->getBookings() 
		
		if ($pax_data) {
			
			$waiver_forms = '
			<div class="rezgo-waiver-child">
				<input type="checkbox" id="child" name="child" />  &nbsp;
				<strong>I am signing this waiver on behalf of a child.</strong>
				<div id="rezgo-waiver-child-text" style="display:none;">
				Please enter the child\'s name and birthdate and sign on their behalf.</div>
				<div class="clearfix">&nbsp;</div>
			</div>			
			';
			
			$waiver_forms .= '<div class="rezgo-waiver-instructions">Please complete the following required fields.</div>';
			
			$waiver_forms .= '
			<input type="hidden" name="pax_id" id="pax_id" value="'.$pax_data->id.'" />
			<input type="hidden" name="pax_type" id="pax_type" value="'.$pax_data->type.'" />
			<input type="hidden" name="pax_type_num" id="pax_type_num" value="'.$pax_data->num.'" />

				<div class="rezgo-form-row rezgo-form-one form-group rezgo-pax-first-last">
					<label for="pax_first_name" class="col-xs-2 control-label rezgo-label-right">First <span class="hidden-xs">Name</span></label>
					<div class="col-xs-4 rezgo-form-input">
						<input type="text" class="form-control required" id="pax_first_name" name="pax_first_name" value="'.$pax_data->first_name.'" autocomplete="off" /> 
					</div>
					<label for="pax_last_name" class="col-xs-2 control-label rezgo-label-right">Last <span class="hidden-xs">Name</span></label>
					<div class="col-xs-4 rezgo-form-input">
						<input type="text" class="form-control required" id="pax_last_name" name="pax_last_name" value="'.$pax_data->last_name.'" autocomplete="off" />
					</div>
				</div>

				<div class="rezgo-form-row rezgo-form-one form-group rezgo-pax-phone-email">
					<label for="pax_phone" class="col-xs-2 control-label rezgo-label-right">Phone</label>
					<div class="col-xs-4 rezgo-form-input">
						<input type="text" class="form-control required" id="pax_phone" name="pax_phone" value="'.$pax_data->phone_number.'" autocomplete="off" />
					</div>
					<label for="pax_email" class="col-xs-2 control-label rezgo-label-right">Email</label>
					<div class="col-xs-4 rezgo-form-input">
					<input type="email" class="form-control required" id="pax_email" name="pax_email" value="'.$pax_data->email_address.'" autocomplete="off" />
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
			';
			
			
			foreach ( $pax_data->forms->form as $form ) {
				
				if($form->type == 'text') {
					
					$waiver_forms .= '
						<div class="form-group rezgo-custom-form rezgo-form-input">
							<label><span>'.$form->title.' '.((string) $form->require == '1' ? '<em class="fa fa-asterisk"></em>' : '').'</span></label>  
							<input type="text" class="custom-form-input form-control'.((string) $form->require == '1' ? ' required' : '').'" data-answer="rezgo-waiver-answer-'.$form->id.'" id="rezgo-waiver-form-'.$form->id.'" name="pax_group['.$pax_data->type.']['.$pax_data->num.'][forms]['.$form->id.']" value="'.$form->answer.'" />
							<p class="rezgo-form-comment"><span>'.$form->instructions.'</span></p>
						</div>
					';
					
				}
				
				if($form->type == 'select') {
					
					$waiver_forms .= '
						<div class="form-group rezgo-custom-form rezgo-form-input">
							<label><span>'.$form->title.' '.((string) $form->require == '1' ? '<em class="fa fa-asterisk"></em>' : '').'</span></label>
							<select class="custom-form-input form-control'.((string) $form->require == '1' ? ' required' : '').'" data-answer="rezgo-waiver-answer-'.$form->id.'" id="rezgo-waiver-form-'.$form->id.'" name="pax_group['.$pax_data->type.']['.$pax_data->num.'][forms]['.$form->id.']">
					';
					
					if((string) $form->options) {
						$opt = explode(',', (string)$form->options);
						foreach((array)$opt as $v) {																		
							$waiver_forms .= '<option'.(((string) $form->answer == $v) ? ' selected' : '').'>' . $v . '</option>';
						}
					}
					
					$waiver_forms .= '
							</select>
							<p class="rezgo-form-comment"><span>'.$form->instructions.'</span></p>
						</div>
					';
					
				}
				
				if($form->type == 'multiselect') {
					
					$waiver_forms .= '
						<div class="form-group rezgo-custom-form rezgo-form-input">
							<label><span>'.$form->title.' '.((string) $form->require == '1' ? '<em class="fa fa-asterisk"></em>' : '').'</span></label>
							<select class="custom-form-input form-control'.((string) $form->require == '1' ? ' required' : '').'" multiple="multiple" data-answer="rezgo-waiver-answer-'.$form->id.'" id="rezgo-waiver-form-'.$form->id.'" name="pax_group['.$pax_data->type.']['.$pax_data->num.'][forms]['.$form->id.'][]">
					';
					
					if((string) $form->options) {
						$opt = explode(',', (string)$form->options);
						foreach((array)$opt as $v) {		
							if (strpos((string) $form->answer, ',' === false)) {														
								$waiver_forms .= '<option'.(((string) $form->answer == $v) ? ' selected' : '').'>' . $v . '</option>';
							} else {
								$answers = explode(', ', (string)$form->answer);
								$waiver_forms .= '<option'.((in_array($v, $answers)) ? ' selected' : '').'>' . $v . '</option>';
							}
						}
					}
					
					$waiver_forms .= '
							</select>
							<p class="rezgo-form-comment"><span>'.$form->instructions.'</span></p>
						</div>
					';
					
				}
				
				if($form->type == 'textarea') {
					
					$waiver_forms .= '
						<div class="form-group rezgo-custom-form rezgo-form-input">
							<label><span>'.$form->title.' '.((string) $form->require == '1' ? '<em class="fa fa-asterisk"></em>' : '').'</span></label>
							<textarea class="custom-form-input form-control'.((string) $form->require == '1' ? ' required' : '').'" data-answer="rezgo-waiver-answer-'.$form->id.'" id="rezgo-waiver-form-'.$form->id.'" name="pax_group['.$pax_data->type.']['.$pax_data->num.'][forms]['.$form->id.']" cols="40" rows="4">'.$form->answer.'</textarea>
							<p class="rezgo-form-comment"><span>'.$form->instructions.'</span></p>
						</div>
					';
					
				}
				
				if($form->type == 'checkbox') {
					
					$waiver_forms .= '
						<div class="form-group rezgo-custom-form rezgo-form-input">
							<div class="checkbox rezgo-form-checkbox">
								<label>
									<input type="checkbox" class="custom-form-input'.((string) $form->require == '1' ? ' required' : '').'" data-answer="rezgo-waiver-answer-'.$form->id.'" id="rezgo-waiver-form-'.$form->id.'" name="pax_group['.$pax_data->type.']['.$pax_data->num.'][forms]['.$form->id.']" '.(((string) $form->answer == 'on') ? ' checked' : '').' />
									<span>'.$form->title.'</span>
									 '.((string) $form->require == '1' ? '<em class="fa fa-asterisk"></em>' : '').'
									<p class="rezgo-form-comment"><span>'.$form->instructions.'</span></p>
								</label>
							</div>
						</div>
					';
					
				}
				
			} // foreach ( $pax_data->forms->form )
			
			$response = $waiver_forms;
			
		} else {
			
			$response = '';
			
		}
		
	} // get_forms

	if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		// ajax response if we requested this page correctly
		echo $response;		
	} else {
		// if, for some reason, the ajax form submit failed, then we want to handle the user anyway
		die ('Something went wrong during saving the waiver.');
	}
	
?>