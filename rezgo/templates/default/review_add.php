<?
	require('recaptchalib.php');
	
	if($_POST['rezgoAction'] == 'contact') {
		
		if ($_POST['hp_rezgo'] != '') {
			
			$bot_request = TRUE;
			
		} else {
			
			$site->cleanRequest();
			
			$resp = recaptcha_check_answer(REZGO_CAPTCHA_PRIV_KEY, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
		
			if (!$resp->is_valid) {
				$captcha_error = 'There was an error with your captcha text, please try again.';
			} else {
				$result = $site->sendContact();
			}			
			
		}

	
	}
?>
	

<script type="text/javascript" src="<?=$site->path?>/js/jquery.selectboxes.pack.js"></script>
<script type="text/javascript" src="<?=$site->path?>/js/jquery.validate.min.js"></script>

<? if (isset($captcha_error)) { ?>
<script type="text/javascript">
	$(document).ready(function () {
		
		var captcha_position = $('#rezgo-scrollto-captcha').position();
		var captcha_scroll = Math.round(captcha_position.top);
		
		if ('parentIFrame' in window) {
			setTimeout( 'parentIFrame.scrollTo(0,captcha_scroll)', 100 );
		}								
						
	});
</script>
<? } ?>

<div class="container-fluid">

  <div class="rezgo-content-row">
  
    <h1 id="rezgo-contact-head">Add a Review</h1>
    
    <!--<div id="rezgo-about-content"><?=$site->getPageContent('review')?></div>-->
    
		<? if($result->status == 1 && $bot_request !== TRUE) { ?>
			<script type="text/javascript">
        $(document).ready(function () {
          
          if ('parentIFrame' in window) {
            parentIFrame.size();
						setTimeout( 'parentIFrame.scrollTo(0,0)', 100 );
          }								
                  
        });
      </script>    
      <div class="row rezgo-form-group">
        <div id="contact_success" class="alert alert-success">Thank you for your review.</div>
      </div>
    <? } else { ?>

    <div class="row rezgo-form-group" id="rezgo-contact-form">
      <form class="form-horizontal" id="review_form" role="form" method="post" action="page_contact?" target="_self">
      	<input type="hidden" name="rezgoAction" value="contact" />
        <div class="form-group">
          <label for="contact_fullname" class="col-sm-2 control-label">Name</label>
          <div class="col-sm-10">
          <input type="text" class="form-control" id="contact_fullname" placeholder="Full Name" required="required" name="full_name" value="<?=$_REQUEST['full_name']?>" />
          </div>
        </div>
        <div class="form-group">
          <span class="required-group">
          <label for="contact_email" class="col-sm-2 control-label">Email</label>
          <div class="col-sm-4">
          <input type="email" class="form-control" id="contact_email" placeholder="Email" required="required" name="email" value="<?=$_REQUEST['email']?>" />
					</div>
          </span>
          <label for="contact_phone" class="col-sm-2 control-label">Phone</label>
          <div class="col-sm-4">
          <input type="text" class="form-control" id="contact_phone" placeholder="Phone Number" name="phone" value="<?=$_REQUEST['phone']?>" />
          </div>
        </div>
        <div class="form-group">
          <label for="contact_address" class="col-sm-2 control-label">Address</label>
          <div class="col-sm-10">
          <input type="text" class="form-control" id="contact_address" placeholder="123 My Street" name="address" value="<?=$_REQUEST['address']?>" />
          </div>
        </div>
        <div class="form-group">
          <label for="contact_city" class="col-sm-2 control-label">City</label>
          <div class="col-sm-10">
          <input type="text" class="form-control" id="contact_city" placeholder="My City" name="city" value="<?=$_REQUEST['city']?>" />
          </div>
        </div>
        <div class="form-group">
          <label for="contact_state" class="col-sm-2 control-label">State</label>
          <div class="col-sm-4">
          <input type="text" class="form-control" id="contact_state" placeholder="My State" name="state_prov" value="<?=$_REQUEST['state_prov']?>" />
          </div>
          <label for="" class="col-sm-2 control-label">Country</label>
          <div class="col-sm-4">
          <select class="form-control" id="contact_country" name="country">
            <? 
						foreach( $site->getRegionList() as $iso => $country_name ) { 
							echo '<option value="'.$iso.'"';
							if ($iso == $_REQUEST['country']) {
								echo ' selected';
							} elseif ($iso == $site->getCompanyCountry() && !$_REQUEST['country']) {
								echo ' selected';
							}
							echo '>'.ucwords($country_name).'</option>';
						}
						?>
          </select>
          </div>
        </div>
        <span id="rezgo-scrollto-captcha"></span>
        <div class="form-group">
          <label for="contact_comment" class="col-sm-2 control-label">Comment</label>
          <div class="col-sm-10">
          <textarea class="form-control" name="body" id="contact_comment" rows="8" wrap="on" required="required"><?=$_REQUEST['body']?></textarea>
          <input type="text" name="hp_rezgo" class="hp_rez" value="" />
          </div>
        </div>
        <? if($site->exists(REZGO_CAPTCHA_PUB_KEY)) { ?>
        <div class="form-group">
          <label for="" class="col-sm-2 control-label">Verification</label>
          <div class="col-sm-10">
            <div id="captcha">
              <?=recaptcha_get_html(REZGO_CAPTCHA_PUB_KEY, null, 1)?>
              <br /><div id="rezgo-captcha-error-container" class="rezgo-captcha-error"<?=(isset($captcha_error) ? '' : ' style="display:none"' )?>><?=$captcha_error?></div>
            </div>
          </div>
        </div>          
        <? } ?>
        
        <div class="col-sm-3 col-sm-offset-9 col-xs-12">
          <input type="submit" class="btn btn-primary btn-lg btn-block" value="Send Request" />
        </div>
      </form>
    </div>
    
    <? } ?>    
    
    <? $company = $site->getCompanyDetails(); ?>
    <div class="rezgo-content-row" id="rezgo-contact-address">
    
      <div class="col-sm-12 col-md-3">
        <address>
        <h3><?=$company->company_name?></h3>
        <?=$company->address_1?> <?=$company->address_2?><br />
        <?=$company->city?>, <? if($site->exists($company->state_prov)) { ?><?=$company->state_prov?>, <? } ?><?=$site->countryName($company->country)?><br />
        <?=$company->postal_code?><br />
        <br />
        <? if($site->exists($company->phone)) { ?>Phone: <?=$company->phone?><br /><? } ?>
        <? if($site->exists($company->fax)) { ?>Fax: <?=$company->fax?><br /><? } ?>
        Email: <?=$company->email?>
        <? if($site->exists($company->tax_id)) { ?>
        <br />
        Tax ID: <?=$company->tax_id?>
        <? } ?>
        </address>
      </div>
    
    </div>    
		    
    <!-- end new HTML -->
	
  </div><!-- end .rezgo-content-row -->

</div><!-- end .rezgo-container -->

<script>
	
	$(document).ready(function() {	
	
		$.validator.setDefaults({
				highlight: function(element) {
						if ($(element).attr("name") == "email" ) {
							$(element).closest('.required-group').addClass('has-error'); // only highlight email
						} else {
							$(element).closest('.form-group').addClass('has-error');
						}
				},
				unhighlight: function(element) {
						if ($(element).attr("name") == "email" ) {
							$(element).closest('.required-group').removeClass('has-error'); // unhighlight email
						} else {
							$(element).closest('.form-group').removeClass('has-error');
						}
				},
				errorElement: 'span',
				errorClass: 'help-block',
				errorPlacement: function(error, element) {
						if(element.parent('.input-group').length) {
								error.insertAfter(element.parent());
						} else {
								error.insertAfter(element);
						}
				}
		});	
	
	
		$('#review_form').validate({
			rules: {
				full_name: {
					required: true
				},
				email: {
					required: true,
					email: true
				},
				body: {
					required: true,
				}
			},
			messages: {
				full_name: {
					required: "Please enter your full name"
				},
				email: {
					required: "Please enter a valid email address"
				},
				body: {
					required: "Please enter a comment"
				}
			}
			
		});
		
	});
	
</script>
