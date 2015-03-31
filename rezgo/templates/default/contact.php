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

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDkCWu6MoROFlsRGoqFj-AXPEApsVjyTiA&sensor=false&libraries=places"></script>		

<script type="text/javascript" src="<?=$site->path?>/js/jquery.selectboxes.pack.js"></script>
<script type="text/javascript" src="<?=$site->path?>/js/jquery.validate.min.js"></script>

<? if (isset($captcha_error)) { ?>
<script type="text/javascript">
	$(document).ready(function () {
		//$.scrollTo('#rezgo-scrollto-captcha', 200);		
		
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
  
    <h1 id="rezgo-contact-head">Contact Us</h1>
    
    <div id="rezgo-about-content"><?=$site->getPageContent('contact')?></div>
    
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
        <div id="contact_success" class="alert alert-success">Thank you for your message.</div>
      </div>
    <? } else { ?>

    <div class="row rezgo-form-group" id="rezgo-contact-form">
      <form class="form-horizontal" id="contact_form" role="form" method="post" action="page_contact?" target="_self">
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
      <div class="col-sm-12 col-md-9">
        <div id="rezgo-company-map"></div>
      </div>
    
    </div>
    
		<script>
      var markersArray = [];
      var map;
      //
      var lat = <?=(($company->map->lat != '') ? $company->map->lat : '40.714623')?>;
      var lon = <?=(($company->map->lon != '') ? $company->map->lon : '-74.006605')?>;
      var zoom = <?=(($company->map->zoom != '' && $company->map->zoom > 0) ? $company->map->zoom : 6)?>;
      
      var myCenter=new google.maps.LatLng(lat, lon);
      function initialize() {
        var mapProp = {
          center:myCenter,
          zoom:zoom,
					scrollwheel: false,
					<? if ($site->config('REZGO_MOBILE_XML')) { ?>
					draggable: false,
					<? } ?>
          mapTypeControl:true,
          sensor:true,
          mapTypeControlOptions: {
            style:google.maps.MapTypeControlStyle.DROPDOWN_MENU
          },
          mapTypeId:google.maps.MapTypeId.ROADMAP
        };
        
        map = new google.maps.Map(document.getElementById("rezgo-company-map"), mapProp);
        
        google.maps.event.addListener(map, 'zoom_changed', function() {
          document.getElementById("zoom").value = map.getZoom();
        });
        
        <? if($company->map->lat != '' && $company->map->lon != '') { ?>								
          
          var marker = new google.maps.Marker({
            position: new google.maps.LatLng(<?=$company->map->lat?>, <?=$company->map->lon?>),
            map: map
          });
          
          markersArray.push(marker);
          
        <? } ?> 
          
      }
      
      function clearOverlays() {
        for (var i = 0; i < markersArray.length; i++ ) {
         markersArray[i].setMap(null);
        }
      }
            
      <? 
        // if there is no lat and lon set, we want to try to center the map on the company address
        // we don't set a tag, we are just trying to make it easier for them to set one.
				
        if($company->map->lat == '' || $company->map->lon == '') { 
      ?>
        geocoder = new google.maps.Geocoder();
        
        geocoder.geocode( { 'address': '<?=$company->address_1?> <?=$company->city?> <?=$company->state_prov?> <?=$company->country?>'}, function(results, status) {
        
          if (status == google.maps.GeocoderStatus.OK) {
            // geocode successful, grab the lat and lon and set the zoom
            // this will direct the map to their address, rather than the above default world view
          
            // get the lat and lng from the location object
            lat = results[0].geometry.location.lat();
            lon = results[0].geometry.location.lng();
            zoom = 13;
                        
            // set a new center for the map to replace the default one above
            myCenter=new google.maps.LatLng(lat, lon);
            
            initialize();
          } else {
            initialize();
          }
    
        });
        
      <? } else { ?>
        google.maps.event.addDomListener(window, 'load', initialize);
      <? } ?>
      
    </script>    
	
  </div><!-- // .rezgo-content-row -->

</div><!-- // .rezgo-container -->

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
	
	
		$('#contact_form').validate({
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
