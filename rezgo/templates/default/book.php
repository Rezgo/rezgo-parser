<?	
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
	
?>

<script type="text/javascript" src="<?=$site->path?>/js/jquery.form.js"></script><!-- ajaxSubmit -->
<script type="text/javascript" src="<?=$site->path?>/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="<?=$site->path?>/js/jquery.selectboxes.pack.js"></script>
		
<script>
	var elements = new Array();
	
	var split_total = new Array();
	var overall_total = '0';
	
	var form_symbol = '$';
	var form_decimals = '2';
	var form_separator = ',';
	
	// money formatting
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
	
	function clean_money_string(str) {
		// convert to str in case it has strange characters (like a ,)
		str += '';
		// clean (except . and -) and convert back to float
		return parseFloat(str.replace(/[^0-9.-]/, ""));
	}
	
	function add_element(id, name, price, order_num) {
		
		// ensure our array has an array for the actual elements
		if(!elements[order_num]) elements[order_num] = new Array();
		
		var num = add_price = clean_money_string(price);
		if(elements[order_num][id]) num = num + clean_money_string(elements[order_num][id]);
		
		var price = num.formatMoney();

		var display_price = form_symbol + price;
		
		name = name.replace("\\'", "'");
		
		if(!elements[order_num][id]) {
			var content = '<tr><td colspan="3" class="text-right" id="element_' + order_num + '_' + id + '"><strong>' + name + '</strong></td><td class="text-right" id="val_' + order_num + '_' + id + '">' + display_price + '</td></tr>';
			$("#fee_box_" + order_num).html( $("#fee_box_" + order_num).html() + content );
			$("#fee_box_" + order_num).show();
		} else {
			if(document.getElementById('element_' + order_num + '_' + id).style.display == 'none') document.getElementById('element_' + order_num + '_' + id).style.display = '';
			$("#val_" + order_num + "_" + id).html(display_price);
		}	
		elements[order_num][id] = price;
		
		// add to total amount
		var total = split_total[order_num];
		total = clean_money_string(total) + add_price;
		total = total.formatMoney();
		split_total[order_num] = total;
		
		// set the total for this item
		$("#total_value_" + order_num).html(form_symbol + total);
		
		// set the order total if this item doesn't have a deposit set
		if(!$("#deposit_value_" + order_num).html()) {
			overall_total = clean_money_string(overall_total) + add_price;
			overall_total = overall_total.formatMoney();
			
			$("#total_value").html(form_symbol + overall_total);
		}
		
		// if total is greater than 0 then show payment section
		if(overall_total > 0) {
			$('#payment_info').show();
			$('#payment_info_head').show();
		}
	}
	
	function sub_element(id, price, order_num) {
	
		// ensure our array has an array for the actual elements
		if(!elements[order_num]) elements[order_num] = new Array();
	
		if(!elements[order_num][id] || elements[order_num][id] == 0) return false;
	
		var num = sub_price = clean_money_string(price);
		num = clean_money_string(elements[order_num][id]) - num;
		
		var price = num.formatMoney();
		if(price < 0) price = 0;
		
		var display_price = form_symbol + price;
		
		if(price == 0) {	
			document.getElementById('element_' + order_num + '_' + id).style.display = 'none';
		} else {
			document.getElementById('val_' + order_num + '_' + id).innerHTML = display_price;
		}	
		elements[order_num][id] = price;
		
		// sub from total amount
		var total = split_total[order_num];
		total = clean_money_string(total) - sub_price;
		total = total.formatMoney();
		split_total[order_num] = total;
		
		// set the total for this item
		$("#total_value_" + order_num).html(form_symbol + total);
		
		// set the order total if this item doesn't have a deposit set
		if(!$("#deposit_value_" + order_num).html()) {
			overall_total = clean_money_string(overall_total) - sub_price;
			overall_total = overall_total.formatMoney();
			
			$("#total_value").html(form_symbol + overall_total);
		}
	
		// if total is 0 then hide payment section
		if(overall_total <= 0) {
			$('#payment_info').hide();
			$('#payment_info_head').hide();
		}
		
	}
</script>
<script>
	
	$(document).ready(function() {	
	
		$.validator.setDefaults({
				highlight: function(element) {
					if ( $(element).attr("type") == "checkbox" ) {
						$(element).closest('.rezgo-form-checkbox').addClass('has-error');
					} else {
						$(element).closest('.rezgo-form-input').addClass('has-error');
					}
				},
				unhighlight: function(element) {
					if ( $(element).attr("type") == "checkbox" ) {
						$(element).closest('.rezgo-form-checkbox').removeClass('has-error');
					} else {
						$(element).closest('.rezgo-form-input').removeClass('has-error');
					}
					
				},
				focusInvalid: false,
				errorElement: 'span',
				errorClass: 'help-block',
				errorPlacement: function(error, element) {
						if ( $(element).attr("name") == "name" || $(element).attr("name") == "pan" || $(element).attr("name") == "cvv" ) {
								error.hide();
						} else if( $(element).attr("name") == "agree_terms" ) {
								error.insertAfter(element.parent());
						} else if( $(element).attr("type") == "checkbox" ) {
								error.insertAfter(element.siblings('.rezgo-form-comment'));
						} else {
								error.insertAfter(element);
						}
				}
		});	
		
		// set validation messages		
		$('#book').validate({
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
<script type="text/javascript">
// for iFrameResize native version
// MDN PolyFil for IE8 
if (!Array.prototype.forEach){
    Array.prototype.forEach = function(fun /*, thisArg */){
        "use strict";
        if (this === void 0 || this === null || typeof fun !== "function") throw new TypeError();

        var
            t = Object(this),
            len = t.length >>> 0,
            thisArg = arguments.length >= 2 ? arguments[1] : void 0;

        for (var i = 0; i < len; i++)
            if (i in t)
                fun.call(thisArg, t[i], i, t);
    };
}	
</script>

<div class="container-fluid rezgo-container">
<div class="jumbotron rezgo-book-form">

<ul class="nav nav-tabs" id="book_steps" style="display:none">
  <li class="active"><a href="#book_step_one" data-toggle="tab">Step 1</a></li>
  <li><a href="#book_step_two" data-toggle="tab">Step 2</a></li>
</ul>

<form role="form" method="post" id="book">

<div class="tab-content">

  <div class="tab-pane active" id="book_step_one">
    
    <div class="row">
        <ol class="breadcrumb rezgo-breadcrumb hidden-xs">
          <li><a href="/order">Your Order</a></li>
          <li class="active">Guest Information</li>
          <li>Billing Information</li>
          <li>Confirmation</li>
        </ol>
    </div><!-- // .row -->
  
	<? 
    $c = 0;
    $cart = $site->getCart(1); // get the cart, remove any dead entries
    
    if(!count($cart)) {
      $site->sendTo('/'.$site->base);
    } 
		
		$cart_count = count($cart);
  
		// start cart loop for each tour in the order    
    foreach( $cart as $item ) {
      
    	$required_fields = 0;
			
			$site->readItem($item);
			
			if((int) $item->availability >= (int) $item->pax_count) {
				
				// only increment if it's still available
				$c++;
				
			?>

			<!--<pre><?=print_r($item,1)?></pre>-->

			<? if ($item->group == 'hide' && count($site->getTourForms('primary')) == 0) { ?>
      <!--<script>
        $(document).ready(function() {	
        
          $('#book_steps  a:last').tab('show');
          $('#back-to-info').hide();
					/* <?=$c;?> */
          
        });
      </script>-->
      <? } /* too jarring, this needs to be changed */ ?>    
		
		<script>
			split_total[<?=$c?>] = <?=$item->overall_total?>;
		</script>
		
		<input type="hidden" name="booking[<?=$c?>][book]" value="<?=$item->uid?>"> 
	  <input type="hidden" name="booking[<?=$c?>][date]" value="<?=date("Y-m-d", (string)$item->booking_date)?>" />
	  <input type="hidden" name="booking[<?=$c?>][adult_num]" value="<?=$item->adult_num?>" />
	  <input type="hidden" name="booking[<?=$c?>][child_num]" value="<?=$item->child_num?>" />
	  <input type="hidden" name="booking[<?=$c?>][senior_num]" value="<?=$item->senior_num?>" />
	  <input type="hidden" name="booking[<?=$c?>][price4_num]" value="<?=$item->price4_num?>" />
	  <input type="hidden" name="booking[<?=$c?>][price5_num]" value="<?=$item->price5_num?>" />
	  <input type="hidden" name="booking[<?=$c?>][price6_num]" value="<?=$item->price6_num?>" />
	  <input type="hidden" name="booking[<?=$c?>][price7_num]" value="<?=$item->price7_num?>" />
	  <input type="hidden" name="booking[<?=$c?>][price8_num]" value="<?=$item->price8_num?>" />
	  <input type="hidden" name="booking[<?=$c?>][price9_num]" value="<?=$item->price9_num?>" />
    
    <div id="error_scrollto"></div>
    
    <h3><span class="text-info">Booking <?=$c?> of <span class="rezgo-cart-count"></span>&nbsp;</span><br />
    
    <?=$item->item?> &mdash; <?=$item->option?></h3>
    <h4><?=date((string) $company->date_format, (string)$item->booking_date)?></h4>
		<? /*if($_COOKIE['rezgo_promo']) { ?>
    <h4>Promotional Code: <?=$_COOKIE['rezgo_promo']?></h4>
    <? }*/ ?>
    
		<? 
      if($item->discount_rules->rule) {
        echo '<h4>Discount: ';
        unset($discount_string);
        foreach($item->discount_rules->rule as $discount) {	
          $discount_string .= ($discount_string) ? ', '.$discount : $discount;
        }
        echo '<span class="rezgo-red">'.$discount_string.'</span>
        </h4>';
      } 
    ?>
    
	
		<? if($item->group != 'hide') { ?>
    
    <div class="row rezgo-booking-instructions">To finish this booking, please complete the following form. <span id="required_note-<?=$c?>"<? if ($item->group == 'require') { echo ' style="display:inline;"'; } else {  echo ' style="display:none;"'; } ?>>Please note that fields marked with <em class="fa fa-asterisk"></em> are required.</span>
    
    </div><!-- // .row -->
	  
	  <? foreach( $site->getTourPrices($item) as $price ) { ?>	  	
	  	<? foreach( $site->getTourPriceNum($price, $item) as $num ) { ?>
      
      <div class="row rezgo-form-group">
      
        <div class="col-sm-12 rezgo-sub-title"><?=$price->label?> (<?=$num?>)</div>
        <div class="rezgo-form-row rezgo-form-one form-group">
          
          <label for="frm_<?=$c?>_<?=$price->name?>_<?=$num?>_first_name" class="col-sm-2 control-label rezgo-label-right">First&nbsp;Name<? if($item->group == 'require') { ?>&nbsp;<em class="fa fa-asterisk"></em><? } ?></label>
          <div class="col-sm-4 rezgo-form-input">
          <input type="text" class="form-control<? echo ($item->group == 'require') ? ' required' : ''; ?>" id="frm_<?=$c?>_<?=$price->name?>_<?=$num?>_first_name" name="booking[<?=$c?>][tour_group][<?=$price->name?>][<?=$num?>][first_name]" /> 
          </div>
        
          <label for="frm_<?=$c?>_<?=$price->name?>_<?=$num?>_last_name" class="col-sm-2 control-label rezgo-label-right">Last&nbsp;Name<? if($item->group == 'require') { ?>&nbsp;<em class="fa fa-asterisk"></em><? } ?></label>
          <div class="col-sm-4 rezgo-form-input">
          <input type="text" class="form-control<? echo ($item->group == 'require') ? ' required' : ''; ?>" id="frm_<?=$c?>_<?=$price->name?>_<?=$num?>_last_name" name="booking[<?=$c?>][tour_group][<?=$price->name?>][<?=$num?>][last_name]" />
          </div>
          
        </div>
        <div class="rezgo-form-row rezgo-form-one form-group">
          <label for="frm_<?=$c?>_<?=$price->name?>_<?=$num?>_phone" class="col-sm-2 control-label rezgo-label-right">Phone</label>
          <div class="col-sm-4 rezgo-form-input">
          <input type="text" class="form-control" id="frm_<?=$c?>_<?=$price->name?>_<?=$num?>_phone" name="booking[<?=$c?>][tour_group][<?=$price->name?>][<?=$num?>][phone]" />
          </div>
          <label for="frm_<?=$c?>_<?=$price->name?>_<?=$num?>_email" class="col-sm-2 control-label rezgo-label-right">Email</label>
          <div class="col-sm-4 rezgo-form-input">
          <input type="email" class="form-control" id="frm_<?=$c?>_<?=$price->name?>_<?=$num?>_email" name="booking[<?=$c?>][tour_group][<?=$price->name?>][<?=$num?>][email]" />
          </div>
        </div>
        
        <? $form_counter = 1; // form counter to create unique IDs ?>
        
				<? foreach( $site->getTourForms('group') as $form ) { ?>
          
					<? if($form->require) { $required_fields++; } ?>
          
          <? if($form->type == 'text') { ?>
            <div class="form-group rezgo-custom-form rezgo-form-input">
              <label><?=$form->title?><? if($form->require) { ?> <em class="fa fa-asterisk"></em><? } ?></label>
              <input type="text" class="form-control<? echo ($form->require) ? ' required' : ''; ?> " name="booking[<?=$c?>][tour_group][<?=$price->name?>][<?=$num?>][forms][<?=$form->id?>]" />
              
              <p class="rezgo-form-comment"><?=$form->instructions?></p>
            </div>
          <? } ?>
          
          <? if($form->type == 'select') { ?>
            <div class="form-group rezgo-custom-form rezgo-form-input">
              <label><?=$form->title?><? if($form->require) { ?> <em class="fa fa-asterisk"></em><? } ?></label>
              <select class="form-control<? echo ($form->require) ? ' required' : ''; ?>" name="booking[<?=$c?>][tour_group][<?=$price->name?>][<?=$num?>][forms][<?=$form->id?>]">
                <? foreach($form->options as $option) { ?>
                  <option><?=$option?></option>
                <? } ?>
              </select>
               
              <p class="rezgo-form-comment"><?=$form->instructions?></p>
            </div>
          <? } ?>
          
          <? if($form->type == 'multiselect') { ?>
            <div class="form-group rezgo-custom-form rezgo-form-input">
              <label><?=$form->title?><? if($form->require) { ?> <em class="fa fa-asterisk"></em><? } ?></label>
              <select class="form-control<? echo ($form->require) ? ' required' : ''; ?>" multiple="multiple" name="booking[<?=$c?>][tour_group][<?=$price->name?>][<?=$num?>][forms][<?=$form->id?>][]">
                <? foreach($form->options as $option) { ?>
                  <option><?=$option?></option>
                <? } ?>
              </select>
							 
              <p class="rezgo-form-comment"><?=$form->instructions?></p>
            </div>
          <? } ?>
          
          <? if($form->type == 'textarea') { ?>
            <div class="form-group rezgo-custom-form rezgo-form-input">
              <label><?=$form->title?><? if($form->require) { ?> <em class="fa fa-asterisk"></em><? } ?></label>
              <textarea class="form-control<? echo ($form->require) ? ' required' : ''; ?>" name="booking[<?=$c?>][tour_group][<?=$price->name?>][<?=$num?>][forms][<?=$form->id?>]" cols="40" rows="4"></textarea>
              
              <p class="rezgo-form-comment"><?=$form->instructions?></p>
            </div>
          <? } ?>
          
          <? if($form->type == 'checkbox') { ?>
            <div class="form-group rezgo-custom-form rezgo-form-input">
            	<div class="checkbox rezgo-form-checkbox">
                <label for="<?=$form->id?>|<?=addslashes($form->title)?>|<?=$form->price?>|<?=$c?>|<?=$price->name?>|<?=$num?>">
                  <input type="checkbox"<? echo ($form->require) ? ' class="required"' : ''; ?> id="<?=$form->id?>|<?=addslashes($form->title)?>|<?=$form->price?>|<?=$c?>|<?=$price->name?>|<?=$num?>" name="booking[<?=$c?>][tour_group][<?=$price->name?>][<?=$num?>][forms][<?=$form->id?>]" <? if($form->price) { ?>onclick="if(this.checked) { add_element('<?=$form_counter?>', '<?=addslashes(htmlentities($form->title))?>', '<? if($form->price_mod == '-') { ?><?=$form->price_mod?><? } ?><?=$form->price?>', '<?=$c?>'); } else { sub_element('<?=$form_counter?>', '<? if($form->price_mod == '-') { ?><?=$form->price_mod?><? } ?><?=$form->price?>', '<?=$c?>'); }"<? } ?> />
                  <?=$form->title?><? if($form->require) { ?> <em class="fa fa-asterisk"></em><? } ?><? if($form->price) { ?> <em><?=$form->price_mod?> <?=$site->formatCurrency($form->price)?></em><? } ?>
                   
                  <p class="rezgo-form-comment"><?=$form->instructions?></p>
                </label>
              </div>
            </div>
            
          <? } ?>
          
          <? if($form->type == 'checkbox_price') { ?>
            <div class="form-group rezgo-custom-form rezgo-form-input">
            	<div class="checkbox rezgo-form-checkbox">
                <label for="<?=$form->id?>|<?=addslashes($form->title)?>|<?=$form->price?>|<?=$c?>|<?=$price->name?>|<?=$num?>">
                  <input type="checkbox"<? echo ($form->require) ? ' class="required"' : ''; ?> id="<?=$form->id?>|<?=addslashes($form->title)?>|<?=$form->price?>|<?=$c?>|<?=$price->name?>|<?=$num?>" name="booking[<?=$c?>][tour_group][<?=$price->name?>][<?=$num?>][forms][<?=$form->id?>]" <? if($form->price) { ?>onclick="if(this.checked) { add_element('<?=$form_counter?>', '<?=addslashes(htmlentities($form->title))?>', '<? if($form->price_mod == '-') { ?><?=$form->price_mod?><? } ?><?=$form->price?>', '<?=$c?>'); } else { sub_element('<?=$form_counter?>', '<? if($form->price_mod == '-') { ?><?=$form->price_mod?><? } ?><?=$form->price?>', '<?=$c?>'); }"<? } ?> />
                  <?=$form->title?><? if($form->require) { ?> <em class="fa fa-asterisk"></em><? } ?><? if($form->price) { ?> <em><?=$form->price_mod?> <?=$site->formatCurrency($form->price)?></em><? } ?> 
                   
                  <p class="rezgo-form-comment"><?=$form->instructions?></p>
                </label>
              </div>
            </div>
            
          <? } ?>

        <? 
					$form_counter++;
				
					} // end foreach( $site->getTourForms ?>
        
      </div><!-- // .row -->     
			   
			<? } // end foreach( $site->getTourPriceNum ?>
      
		<? } // end foreach( $site->getTourPrices ?>
	  
		<? } // end if($item->group != 'hide') ?>
	
		<? if($site->getTourForms('primary')) { ?>
      
    		<? if($item->group == 'hide') { ?>
        <div class="row rezgo-booking-instructions">To finish this booking, please complete the following form.</div><!-- // .row --> 
        <? } ?>    
		  
      <div class="row rezgo-form-group">
        <div class="col-sm-12 rezgo-sub-title">Additional Information</div>
        <div class="clearfix rezgo-short-clearfix">&nbsp;</div>
     
					<? foreach( $site->getTourForms('primary') as $form ) { ?>
          
          	<? if($form->require) { $required_fields++; } ?>
		  					
						<? if($form->type == 'text') { ?>
							<div class="form-group rezgo-custom-form rezgo-form-input">
								<label><?=$form->title?><? if($form->require) { ?> <em class="fa fa-asterisk"></em><? } ?></label>
		    				<input type="text" class="form-control<? echo ($form->require) ? ' required' : ''; ?>" name="booking[<?=$c?>][tour_forms][<?=$form->id?>]" />
								<p class="rezgo-form-comment"><?=$form->instructions?></p>
		    			</div>
						<? } ?>
						
						<? if($form->type == 'select') { ?>
							<div class="form-group rezgo-custom-form rezgo-form-input">
								<label><?=$form->title?><? if($form->require) { ?> <em class="fa fa-asterisk"></em><? } ?></label>
		    				<select name="booking[<?=$c?>][tour_forms][<?=$form->id?>]"<? echo ($form->require) ? ' class="required"' : ''; ?>>
		    					<? foreach($form->options as $option) { ?>
						    		<option><?=$option?></option>
						    	<? } ?>
						    </select>
								<p class="rezgo-form-comment"><?=$form->instructions?></p>
		    			</div>
						<? } ?>
						
						<? if($form->type == 'multiselect') { ?>
							<div class="form-group rezgo-custom-form rezgo-form-input">
								<label><?=$form->title?><? if($form->require) { ?> <em class="fa fa-asterisk"></em><? } ?></label>
								<select multiple="multiple" name="booking[<?=$c?>][tour_forms][<?=$form->id?>][]"<? echo ($form->require) ? ' class="required"' : ''; ?>>
						    	<? foreach($form->options as $option) { ?>
						    		<option><?=$option?></option>
						    	<? } ?>
						    </select>
								<p class="rezgo-form-comment"><?=$form->instructions?></p>
		    			</div>
						<? } ?>
						
						<? if($form->type == 'textarea') { ?>
							<div class="form-group rezgo-custom-form rezgo-form-input">
								<label><?=$form->title?><? if($form->require) { ?> <em class="fa fa-asterisk"></em><? } ?></label>
		    				<textarea class="form-control<? echo ($form->require) ? ' required' : ''; ?>" name="booking[<?=$c?>][tour_forms][<?=$form->id?>]" cols="40" rows="4"></textarea>
								<p class="rezgo-form-comment"><?=$form->instructions?></p>
		    			</div>
						<? } ?>
						
						<? if($form->type == 'checkbox') { ?>
							<div class="form-group rezgo-custom-form rezgo-form-input">
              	<div class="checkbox rezgo-form-checkbox">
								<label for="<?=$form->id?>|<?=addslashes($form->title)?>|<?=$form->price?>|<?=$c?>|<?=$price->name?>|<?=$num?>">
								<input type="checkbox"<? echo ($form->require) ? ' class="required"' : ''; ?> id="<?=$form->id?>|<?=addslashes($form->title)?>|<?=$form->price?>|<?=$c?>|<?=$price->name?>|<?=$num?>" name="booking[<?=$c?>][tour_forms][<?=$form->id?>]" <? if($form->price) { ?>onclick="if(this.checked) { add_element('<?=$form_counter?>', '<?=addslashes(htmlentities($form->title))?>', '<? if($form->price_mod == '-') { ?><?=$form->price_mod?><? } ?><?=$form->price?>', '<?=$c?>'); } else { sub_element('<?=$form_counter?>', '<? if($form->price_mod == '-') { ?><?=$form->price_mod?><? } ?><?=$form->price?>', '<?=$c?>'); }"<? } ?> />
								<?=$form->title?>
								<? if($form->require) { ?> <em class="fa fa-asterisk"></em><? } ?> 
								<? if($form->price) { ?> <em><?=$form->price_mod?> <?=$site->formatCurrency($form->price)?></em><? } ?> 
									 
                  <p class="rezgo-form-comment"><?=$form->instructions?></p>
								</label>
                </div>
							</div>
						<? } ?>
            
						
						<? if($form->type == 'checkbox_price') { /*not using this form type? */ ?>
							<div class="form-group rezgo-custom-form rezgo-form-input">
              	<div class="checkbox rezgo-form-checkbox">
								<label for="<?=$form->id?>|<?=addslashes($form->title)?>|<?=$form->price?>|<?=$c?>|<?=$price->name?>|<?=$num?>">
								<input type="checkbox"<? echo ($form->require) ? ' class="required"' : ''; ?> id="<?=$form->id?>|<?=addslashes($form->title)?>|<?=$form->price?>|<?=$c?>|<?=$price->name?>|<?=$num?>" name="booking[<?=$c?>][tour_forms][<?=$form->id?>]" <? if($form->price) { ?>onclick="if(this.checked) { add_element('<?=$form_counter?>', '<?=addslashes(htmlentities($form->title))?>', '<? if($form->price_mod == '-') { ?><?=$form->price_mod?><? } ?><?=$form->price?>', '<?=$c?>'); } else { sub_element('<?=$form_counter?>', '<? if($form->price_mod == '-') { ?><?=$form->price_mod?><? } ?><?=$form->price?>', '<?=$c?>'); }"<? } ?> />
								<?=$form->title?>
								<? if($form->require) { ?> <em class="fa fa-asterisk"></em><? } ?>
								<? if($form->price) { ?> <em><?=$form->price_mod?> <?=$site->formatCurrency($form->price)?></em><? } ?> 
									 
                  <p class="rezgo-form-comment"><?=$form->instructions?></p>
								</label>
                </div>
							</div>
						<? } ?>
            		
					<? 
						$form_counter++;
						
						} // end foreach( $site->getTourForms('primary') ?>
      
		  </div><!-- // .row -->
		  
		  <? } // end if($site->getTourForms(primary)) ?>
      
			<?	 
				// check if group info is hidden and there are no primary forms for this item
				if ($item->group == 'hide' && count($site->getTourForms('primary')) == 0) {
					echo '<div>Guest information is not required for booking #'.$c.'</div>';
				}
      ?>
      
      <? if ($required_fields > 0) { ?>
      <script>
				$(document).ready(function () {
					$('#required_note-<?=$c?>').fadeIn();
				});
      </script>
      <? } ?>
      
		<? 
			} else {
				
				$cart_count--;
			
			} // end if((int) $item->availability >= (int) $item->pax_count) ?>
		
	<? } // end foreach($cart as $item) ?>
    
    
    <div class="row" id="rezgo-booking-btn">
			
      <div class="col-sm-6 col-xs-3 rezgo-btn-wrp rezgo-chevron-left">
      <? if($site->getCartState()) { ?>
      <button type="button" class="btn rezgo-btn-default btn-lg center-block" onclick="top.location.href='<?=$site->base?>/order'; return false;"><span class="hidden-xs">Back to order</span><span class="visible-xs-inline"><span class="glyphicon glyphicon-chevron-left"></span></span></button>
      <? } ?>
      </div>      
      <div class="col-sm-6 col-xs-9 rezgo-btn-wrp"><button type="button" class="btn rezgo-btn-book btn-lg center-block" onClick="stepForward();">Continue</button>
      </div>
    </div><!-- // .row -->
  
  </div>
  <!-- // #book_step_one -->
  
	<script>
    $(document).ready(function () {
      $('.rezgo-cart-count').text('<?=$cart_count?>');
    });
  </script>

  
  <div class="tab-pane" id="book_step_two">
  	<div id="step_two_scrollto"></div>
    <div class="row">
        <ol class="breadcrumb rezgo-breadcrumb hidden-xs">
					<? if($site->getCartState()) { ?>
          <li><a href="/order">Your Order</a></li>
          <? } ?>
          <li id="back-to-info"><a href="#" onClick="$('#book_steps  a:first').tab('show'); return false;">Guest Information</a></li>
          <li class="active">Billing Information</li>
      	  <li>Confirmation</li>
        </ol>
    </div><!-- // .row -->
		
		<?
			$c = 0;
			// start cart loop for each booking in the order
			foreach( $cart as $item ) {
				
				$site->readItem($item);
				
				if((int) $item->availability >= (int) $item->pax_count) {
					
					// only increment if it's still available
					$c++;
					
				?>
	    
      <div class="row rezgo-form-group">
      
        <h3 class="rezgo-booking-of"><span class="text-info">Booking <?=$c?> of <span class="rezgo-cart-count"></span>&nbsp;</span><br />
        <?=$item->item?> &mdash; <?=$item->option?></h3>
        <div class="col-md-5 col-sm-12 col-xs-12">
          <table border="0" cellspacing="0" cellpadding="2" class="rezgo-table-list">
            <tr>
              <td class="rezgo-td-label">Date:</td>
              <td class="rezgo-td-data"><?=date((string) $company->date_format, (string) $item->booking_date)?></td>
            </tr>
            <? if($item->duration != '') { ?>
            <tr>
              <td class="rezgo-td-label">Duration:</td>
              <td class="rezgo-td-data"><?=$item->duration?></td>
            </tr>
            <? } ?>
            <? 
              if($item->discount_rules->rule) {
            		echo '<tr><td class="rezgo-td-label">Discount:</td>
            		<td class="rezgo-td-data">';
            		unset($discount_string);
								foreach($item->discount_rules->rule as $discount) {	
            			$discount_string .= ($discount_string) ? ', '.$discount : $discount;
            		}
								echo '<span class="rezgo-red">'.$discount_string.'</span>
								</td>
								</tr>';
            	} 
            ?>
          </table>    
        </div>
        <div class="col-md-7 col-sm-12 col-xs-12">
          <table class="table-responsive">
            <table class="table table-bordered table-striped rezgo-billing-cart">
                <tr>
                  <td class="text-right"><label>Type</label></td>
                  <td class="text-right"><label class="hidden-xs">Qty.</label></td>
                  <td class="text-right"><label>Cost</label></td>
                  <td class="text-right"><label>Total</label></td>
                </tr>
                <? foreach( $site->getTourPrices($item) as $price ) { ?>
                  <? if($item->{$price->name.'_num'}) { ?>
                    <tr>
                      <td class="text-right"><?=$price->label?></td>
                      <td class="text-right"><?=$item->{$price->name.'_num'}?></td>
                      <td class="text-right">
                        <? if($site->exists($price->base)) { ?><span class="discount"><?=$site->formatCurrency($price->base)?></span><? } ?>
                        <?=$site->formatCurrency($price->price)?>
                      </td>
                      <td class="text-right"><? if($site->exists($price->base)) { ?><span class="discount"></span><? } ?><?=$site->formatCurrency($price->total)?></td>
                    </tr>
                  <? } ?>
                <? } ?>
                <tr>
                  <td colspan="3" class="text-right"><strong>Sub-total</strong></td>
                  <td class="text-right"><?=$site->formatCurrency($item->sub_total)?></td>
                </tr>
                <? 
								
									$line_items = $site->getTourLineItems();
								
									foreach( $line_items as $line ) { 
								
                    unset($label_add);
                    if($site->exists($line->percent) || $site->exists($line->multi)) {
                      $label_add = ' (';
                        
                        if($site->exists($line->percent)) $label_add .= $line->percent.'%';
                        if($site->exists($line->multi)) {
                          if(!$site->exists($line->percent)) $label_add .= $site->formatCurrency($line->multi);
                          $label_add .= ' x '.$item->pax;
                            
                        }
                        
                      $label_add .= ')';	
                    }
										
                  ?>
                
                  <tr>
                    <td colspan="3" class="text-right"><strong><?=$line->label?><?=$label_add?></strong></td>
                    <td class="text-right"><?=$site->formatCurrency($line->amount)?></td>
                  </tr>
                  
                <? }  // end foreach( $site->getTourLineItems() ?>
                
                <tbody id="fee_box_<?=$c?>" class="rezgo-fee-box"></tbody>
                
                <tr>
                  <td colspan="3" class="text-right"><strong>Total</strong></td>
                  <td class="text-right"><strong id="total_value_<?=$c?>"><?=$site->formatCurrency($item->overall_total)?></strong></td>
                </tr>
                
                <? if($site->exists($item->deposit)) { ?>
                <tr>
                  <td colspan="3" class="text-right"><strong>Deposit to Pay Now</strong></td>
                  <td class="text-right"><strong id="deposit_value_<?=$c?>"><?=$site->formatCurrency($item->deposit_value)?></strong></td>
                </tr>            
                <? 
                    $complete_booking_total += (float) $item->deposit_value;
                  } else {
                    $complete_booking_total += (float) $item->overall_total;
                  } 
                ?>
                
             </table>
          </table>
        </div>
      </div><!-- // .row -->
      <!-- //  tour description/cost -->
      
      <? } // end if((int) $item->availability >= (int) $item->pax_count) ?>
		  	
		<? } // end foreach( $cart as $item ) ?>
		
		<script>
			overall_total = '<?=$complete_booking_total?>';
			form_decimals = '<?=$item->currency_decimals?>';
			form_symbol = '<?=$item->currency_symbol?>';
			form_separator = '<?=$item->currency_separator?>';
		</script>
    
    <div class="row">
      <div class="col-sm-7 col-xs-12 col-sm-offset-5 rezgo-total-payable">
          Total to Pay Now: <span id="total_value"><?=$site->formatCurrency($complete_booking_total)?></span>
      </div>
      <div class="clearfix visible-xs"></div>
    </div><!-- // .row -->
    <!-- // grand total -->
    
    <div class="row rezgo-form-group rezgo-booking">
      
      <div class="col-xs-12">
        <h3 class="text-info">Billing Information</h3>
        <div class="form-group">
          <label for="tour_first_name" class="control-label">Name</label>
          <div class="rezgo-form-row">
            <div class="col-sm-6 rezgo-form-input"><input type="text" class="form-control" id="tour_first_name" name="tour_first_name" value="<?=$site->requestStr('tour_first_name')?>" /></div>
            <div class="col-sm-6 rezgo-form-input"><input type="text" class="form-control" id="tour_last_name" name="tour_last_name" value="<?=$site->requestStr('tour_last_name')?>" /></div>
          </div>
        </div>
        <div class="form-group">
          <label for="tour_address_1" class="control-label">Address</label>
          <div class="rezgo-form-input col-xs-12">
            <input type="text" class="form-control" id="tour_address_1" name="tour_address_1" />
						<input type="text" class="form-control" id="tour_address_2" name="tour_address_2" />
					</div>
        </div>
        <div class="form-group">
          <div class="rezgo-form-row">
            <label for="tour_city" class="control-label col-sm-8 col-xs-12 rezgo-form-label">City</label>
            <label for="tour_postal_code" class="control-label col-sm-4 hidden-xs rezgo-form-label">Zip/Postal</label>
          </div>
          <div class="rezgo-form-row">
            <div class="col-sm-8 col-xs-12 rezgo-form-input"><input type="text" class="form-control" id="tour_city" name="tour_city" /></div>
            <label for="tour_postal_code" class="control-label col-xs-12 hidden-lg hidden-md hidden-sm rezgo-form-label">Zip/Postal</label>
            <div class="col-sm-4 col-xs-12 rezgo-form-input"><input type="text" class="form-control" id="tour_postal_code" name="tour_postal_code" /></div>
          </div>
        </div>
        <div class="form-group">
          <div class="rezgo-form-row">
            <label for="tour_country" class="control-label col-sm-8 rezgo-form-label">Country</label>
            <label for="tour_stateprov" class="control-label col-sm-4 hidden-xs rezgo-form-label">State/Prov</label>
          </div>
          <div class="rezgo-form-row">
            <div class="col-sm-8 col-xs-12 rezgo-form-input">
						<? $companyCountry = $site->getCompanyCountry(); ?>
            <select name="tour_country" id="tour_country" class="form-control">
              <? foreach( $site->getRegionList() as $iso => $name ) { ?>
                <option value="<?=$iso?>" <?=(($iso == $companyCountry) ? 'selected' : '')?>><?=ucwords($name)?></option>
              <? } ?>
            </select>
            </div>
            <div class="col-sm-4 col-xs-12 rezgo-form-input">
              <div class="rezgo-form-row hidden-lg hidden-md hidden-sm">
                <label for="tour_stateprov" class="control-label col-xs-12 rezgo-form-label">State/Prov</label>
              </div>
              <select id="tour_stateprov" class="form-control" style="display:<?=(($companyCountry != 'ca' && $companyCountry != 'us' && $companyCountry != 'au') ? 'none' : '')?>;"></select>
              <input id="tour_stateprov_txt" class="form-control" name="tour_stateprov" type="text" value="" style="display:<?=(($companyCountry != 'ca' && $companyCountry != 'us' && $companyCountry != 'au') ? '' : 'none')?>;" />
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="rezgo-form-row">
            <label for="tour_email_address" class="control-label col-sm-6 rezgo-form-label">Email</label>
            <label for="tour_phone_number" class="control-label col-sm-6 hidden-xs rezgo-form-label">Phone</label>
          </div>
          <div class="rezgo-form-row">
            <div class="col-sm-6 col-xs-12 rezgo-form-input"><input type="email" class="form-control" id="tour_email_address" name="tour_email_address" /></div>
            <label for="tour_phone_number" class="control-label col-sm-6 col-xs-12 hidden-lg hidden-md hidden-sm rezgo-form-label">Phone</label>
            <div class="col-sm-6 col-xs-12 rezgo-form-input"><input type="text" class="form-control" id="tour_phone_number" name="tour_phone_number" /></div>
          </div>
        </div>       
        
      </div>
      <!-- // left side billing frame -->
      
      <div class="col-xs-12">
      <h3 class="text-info" id="payment_info_head" style="<?=(($complete_booking_total > 0) ? '' : 'display:none;')?>">Payment Information</h3>
      <div class="rezgo-payment-frame" id="payment_info" style="<?=(($complete_booking_total > 0) ? '' : 'display:none;')?>">

        	<div class="form-group" id="payment_methods">
          
					<? 
					
						$card_fa_logos = array(
							'visa' => 'fa-cc-visa',
							'mastercard' => 'fa-cc-mastercard',
							'american express' => 'fa-cc-amex',
							'discover' => 'fa-cc-discover'
						);
	            
            $pmc = 1; // payment method counter 1
            foreach( $site->getPaymentMethods() as $pay ) {
            
              if($pay[name] == 'Credit Cards') {
                echo '<div class="rezgo-input-radio">
								<input type="radio" name="payment_method" id="payment_method_credit" class="rezgo-payment-method" value="Credit Cards" checked onclick="toggleCard();" />&nbsp;&nbsp;<label for="payment_method_credit"><span class="hidden-xs">Credit </span>Card&nbsp;&nbsp;'; 
                
                  foreach( $site->getPaymentCards() as $card ) {
                    echo '<img src="'.$site->path.'/img/logos/'.$card.'.png" class="hidden-xs" />'; 
                    echo '<span class="visible-xs-inline"><i class="fa '.$card_fa_logos[$card].'"></i></span>';
                  }
								
                echo '</label>
                <input type="hidden" name="tour_card_token" id="tour_card_token" value="" />
                <script>
                  $(\'#tour_card_token\').val(\'\');
                  setTimeout(function() {
                    $(\'#payment_method_credit\').attr(\'checked\', true);
                  }, 600);
                </script>
								</div>
                ';
                                    
              } else {
								
                $set_name = ($pay[name] == 'PayPal') ? '<img src="'.$site->path.'/img/logos/paypal.png" class="hidden-xs" /><span class="visible-xs-inline">PayPal&nbsp;&nbsp;<i class="fa fa-cc-paypal"></i></span>' : $pay[name];
                echo '<div class="rezgo-input-radio"><input type="radio" name="payment_method" id="payment_method_'.$pmc.'" class="rezgo-payment-method" value="'.$pay[name].'" onclick="toggleCard();" />&nbsp;&nbsp;<label for="payment_method_'.$pmc.'">'.$set_name.'</label></div>';
                $pmc++;
              }	
                
            } // end foreach( $site->getPaymentMethods()
            
          ?>
          </div><!-- // #payment_methods -->
          <div id="payment_data">
            <? 
            
            $pmdc = 1; // payment method counter 1
            foreach( $site->getPaymentMethods() as $pay ) { 
            
            ?>
              
              <? if($pay[name] == 'Credit Cards') { ?>
                
                <div id="payment_cards">
                  <iframe scrolling="no" frameborder="0" name="tour_payment" id="tour_payment" src="<?=$site->base?>/booking_payment.php"></iframe>
									<script type="text/javascript">
                    iFrameResize ({
                      enablePublicMethods: true,
                      scrolling: true
                    });
                  </script>
                </div>
                                  
              <? } else { ?>
                  
                <div id="payment_method_<?=$pmdc?>_box" class="payment_method_box" style="display:none;">
                  
                  <? if($pay[add]) { ?>
                    
                    <div id="payment_method_<?=$pmdc?>_container" class="payment_method_container">
                      <?=$pay[add]?><br>
                      <input type="text" id="payment_method_<?=$pmdc?>_field" class="payment_method_field" name="payment_method_add" value="" disabled="disabled" />										
                    </div>
                        
                  <? } ?>
                  
                </div>
                
                <? $pmdc++; ?>
                
              <? } ?>
                
            <? } // end  ?>		
            
          </div><!-- // #payment_data -->
        
      </div><!-- // #payment_info -->
      
      <div class="rezgo-form-row">
        <div class="col-sm-12 rezgo-payment-terms">
          
          <!--<div style="height:10px;"></div>-->
      	         
          <div class="rezgo-form-input">
          	<div class="checkbox">
            <label id="rezgo-terms-label">
              <input type="checkbox" id="agree_terms" name="agree_terms" value="1" /><!-- style="margin-top:7px;"-->
              I agree to the <a data-toggle="collapse" class="collapsed" id="rezgo-terms-link" href="#rezgo-terms-panel">Terms and Conditions</a>
            </label>
            </div>              
            <div id="rezgo-terms-panel" class="collapse">
              <?=$site->getPageContent('terms')?>
            </div>
          </div>
          <hr />
          <div id="rezgo-book-terms">
            <div class="help-block" id="terms_credit_card" style="display:<? if(!$site->getPaymentMethods('Credit Cards')) { ?>none<? } ?> ;">
              <? if($site->getGateway() OR $site->isVendor()) { ?>
                <? if($complete_booking_total > 0) { ?>Please note that your credit card will be charged.<br><? } ?>If you are satisfied with your entries, please click the "Complete Booking" button.
              <? } else { ?> 
                <? if($complete_booking_total > 0) { ?>Please note that your credit card will not be charged now. Your transaction information will be stored until your payment is processed. Please see the Terms and Conditions for more information.<br><? } ?>If you are satisfied with your entries, please click the &quot;Complete Booking&quot; button.
              <? } ?>
            </div>
            <div class="help-block" id="terms_other" style="display:<? if($site->getPaymentMethods('Credit Cards')) { ?>none<? } ?>;">
              If you are satisfied with your entries, please click the &quot;Complete Booking&quot; button.
            </div>
          </div>
          <div id="rezgo-book-message" style="display:none;">
            <div id="rezgo-book-message-body"></div>
          </div> 
          
          <!--<div style="height:10px;"></div>-->
          
        </div>
        
      </div>
        
    </div>
    
    </div><!-- //  .rezgo-booking --><!-- // .row -->
		
    <div class="rezgo-form-row">
      <div class="col-sm-6 col-xs-3 rezgo-btn-wrp rezgo-chevron-left">
        <button type="button" class="btn rezgo-btn-default btn-lg btn-block" onClick="stepBack(); return false;"><span class="hidden-xs">Previous Step</span><span class="visible-xs-inline"><span class="glyphicon glyphicon-chevron-left"></span></span></button>
      </div>
      <div class="col-sm-6 col-xs-9 rezgo-btn-wrp">
        <input type="submit" class="btn rezgo-btn-book btn-lg btn-block" id="rezgo-complete-booking" value="Complete Booking" />
      </div>
    </div>
		
  </div><!-- //  #book_step_two -->
  
</div><!-- //  .tab-content -->

</form>

<div class="row">
  <div class="col-sm-12 col-md-6"><p>&nbsp;</p><br /></div>
  <div class="col-sm-12 col-md-6">
    <div class="alert alert-danger" id="rezgo-book-errors">Some required fields are missing. Please complete the highlighted fields.</div>
  </div>
</div><!-- // .row -->  

</div> <!-- //  .jumbotron-->
 
</div><!-- //  .container -->


<script>
	var toComplete = 0;
	var response; // needs to be global to work in timeout
	var paypalAccount = 0;

	var ca_states = <?= json_encode( $site->getRegionList('ca') ); ?>;
	var us_states = <?= json_encode( $site->getRegionList('us') ); ?>;
	var au_states = <?= json_encode( $site->getRegionList('au') ); ?>;	
	
	// catch form submissions
	$('#book').submit(function (evt) {
		evt.preventDefault();
		submit_booking();
	});
	
	$('#tour_country').change(function() {
		var country = $(this).val();
		
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
	
	<? if (in_array($site->getCompanyCountry(), array('ca', 'us', 'au'))) { ?>
		$('#tour_stateprov').addOption(<?=$site->getCompanyCountry();?>_states, false);
		$('#tour_stateprov_txt').val($('#tour_stateprov').val());
	<? } ?>
	
	if (typeof String.prototype.trim != 'function') { // detect native implementation
	  String.prototype.trim = function () {
	    return this.replace(/^\s+/, '').replace(/\s+$/, '');
	  };
	}
	
	// change the modal dialog box or pass the user to the receipt depending on the response
	function show_response()  {
		
		response = response.trim();
	
		if(response != '1') {	
			$('#rezgo-complete-booking').val('Complete Booking');
			$('#rezgo-complete-booking').removeAttr('disabled');		
		}		
		
		if(response == '2') {
			var title = 'No Availability Left';
			var body = 'Sorry, there is not enough availability left for this item on this date.<br />';
		} else if(response == '3') {
			var title = 'Payment Error';
			var body = 'Sorry, your payment could not be completed. Please verify your card details and try again.<br /';
		} else if(response == '4') {
			var title = 'Booking Error';
			var body = 'Sorry, there has been an error with your booking and it can not be completed at this time.<br />';
		} else if(response == '5') {
			// this error should only come up in preview mode without a valid payment method set
			var title = 'Booking Error';
			var body = 'Sorry, you must have a credit card attached to your Rezgo Account in order to complete a booking.<br><br>Please go to "Settings &gt; Rezgo Account" to attach a credit card.<br />';
		} else {
			
			// this section is mostly for debug handling
			if(response.indexOf('STOP::') != -1) {	
				var split = response.split('<br><br>');
				if(split[1] == '2' || split[1] == '3' || split[1] == '4') {
					split[1] = '<br /><br />Error Code: ' + split[1] + '<br />';
				} else {
					split[1] = '<div class="clearfix">&nbsp;</div>BOOKING COMPLETED WITHOUT ERRORS<div class="clearfix">&nbsp;</div><button type="button" class="btn btn-default" onclick="window.location.replace(\'<?=$site->base?>/complete/' + split[1] + '\');">Continue to Receipt</button><div class="clearfix">&nbsp;</div>';
				}
			
				var body = 'DEBUG-STOP ENCOUNTERED<br /><br />' + split[0] + split[1];
			} else {
				// send the user to the receipt page
				top.location.replace("<?=$site->base?>/complete/" + response);
				return true; // stop the html replace
			}
		}
		
		$('#rezgo-book-message-body').html(body);
		$('#rezgo-book-message-body').addClass('alert alert-warning');
		
	}
	
	// this function delays the output so we see the loading graphic
	function delay_response(responseText) {
		response = responseText;
		setTimeout('show_response();', 800);
	}
	
	function validate_form() {
		
		var valid = $("#book").valid();
		
		return valid;
	}
	
	function error_booking() {
		$('#rezgo-book-errors').fadeIn();
		// revisit when scroll resolved
		//$.scrollTo('#error_scrollto', 200);
		// moved to unhighlight
		setTimeout("$('#rezgo-book-errors').fadeOut();", 5000);
		return false;
	}
	
	function submit_booking() {
	
		// do nothing if we are on step 1
		if(toComplete == 0) return false;
		
		var validate_check = validate_form();
			
		$('#rezgo-complete-booking').val('Please wait ...');
		$('#rezgo-complete-booking').attr('disabled','disabled');
		$('#rezgo-book-message-body').removeClass();
		$('#rezgo-book-message-body').html('');
		$('#rezgo-book-message').fadeOut();
		$('#rezgo-book-terms').fadeIn();
		
		
		// only activate on actual form submission, check payment info
		if(toComplete == 1 && overall_total != 0) {
		
			var force_error = 0;
			
			var payment_method = $('input:radio[name=payment_method]:checked').val();				
			
			if(payment_method == 'Credit Cards') {
			
				if(!$('#tour_payment').contents().find('#payment').valid()) {
					force_error = 1;
				}
				
			} else {
				// other payment methods need their additional fields filled
				var id = $('input:radio[name=payment_method]:checked').attr('id');
				if($('#' + id + '_field').length != 0 && !$('#' + id + '_field').val()) { // this payment method has additional data that is empty
					force_error = 1;
					$('#' + id + '_container').css('border-color', '#990000');
				}
			}
		}
					
		if(force_error || !validate_check) {
			
			$('#rezgo-complete-booking').val('Complete Booking');
			$('#rezgo-complete-booking').removeAttr('disabled');
			
			return error_booking();
			
		} else {
			
			if(toComplete == 1) {
				
				$('#rezgo-book-message-body').html('<center>Please wait a moment... <i class="fa fa-circle-o-notch fa-spin"></i></center>');
				
				$('#rezgo-book-terms').fadeOut().promise().done(function(){
					 $('#rezgo-book-message').fadeIn();
				});		
						
				var payment_method = $('input:radio[name=payment_method]:checked').val();
				
				if(payment_method == 'Credit Cards' && overall_total != 0) {
					
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
							$('#book').ajaxSubmit({ 
								url: '<?=$site->base?>/book_ajax.php', 
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
					$('#book').ajaxSubmit({ 
						url: '<?=$site->base?>/book_ajax.php', 
						data: { rezgoAction: 'book' }, 
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
		
		//$.scrollTo('#step_two_scrollto', 200);
		var step_two_position = $('#step_two_scrollto').position();
		var step_two_scroll = Math.round(step_two_position.top);
		
		if ('parentIFrame' in window) {
			setTimeout( 'parentIFrame.scrollTo(0,0)', 100 );
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
		
		$('#book_steps  a:last').tab('show');
		
	}
	
	function stepBack() {
		toComplete = 0;
				
		$('#book_steps  a:first').tab('show');
		
		$("#tour_first_name").removeClass("required");
		$("#tour_last_name").removeClass("required");
		
		$("#tour_address_1").removeClass("required");
		$("#tour_city").removeClass("required");
		$("#tour_country").removeClass("required");
		$("#tour_postal_code").removeClass("required");
		
		$("#tour_phone_number").removeClass("required");
		$("#tour_email_address").removeClass("required");
		
		$("#agree_terms").removeClass("required");
		
		if ('parentIFrame' in window) {
			setTimeout( 'parentIFrame.scrollTo(0,0)', 100 );
		}								
		
	}
	
	function toggleCard() {
		
		if($('input[name=payment_method]:checked').val() == 'Credit Cards') {
			
			<? $pmn = 0; ?>
			<? foreach( $site->getPaymentMethods() as $pay ) { ?>	
				<? if($pay[name] == 'Credit Cards') { ?>
				<? } else { ?>
					<? $pmn++; ?>
					$('#payment_method_<?=$pmn?>_box').fadeOut();
					$('#payment_method_<?=$pmn?>_field').attr('disabled', 'disabled');
				<? } ?>
			<? } ?>	
			
			setTimeout(function() {
				$('#payment_cards').fadeIn();
			}, 450);
			
			document.getElementById("terms_other").style.display = 'none';
			document.getElementById("terms_credit_card").style.display = '';	
					
		} else if($('input[name=payment_method]:checked').val() == 'PayPal') {
			
			<? $pmn = 0; ?>
			<? foreach( $site->getPaymentMethods() as $pay ) { ?>	
				<? if($pay[name] == 'Credit Cards') { ?>
					$('#payment_cards').fadeOut();
				<? } else { ?>
					<? $pmn++; ?>
					$('#payment_method_<?=$pmn?>_box').fadeOut();
					$('#payment_method_<?=$pmn?>_field').attr('disabled', 'disabled');
				<? } ?>
			<? } ?>	
			
			document.getElementById("terms_credit_card").style.display = 'none';
			document.getElementById("terms_other").style.display = '';
			
		} else {
			
			<? $pmn = 0; ?>
			<? foreach( $site->getPaymentMethods() as $pay ) { ?>	
				<? if($pay[name] == 'Credit Cards') { ?>
					$('#payment_cards').fadeOut();
				<? } else { ?>
					<? $pmn++; ?>
					$('#payment_method_<?=$pmn?>_box').fadeOut();
					$('#payment_method_<?=$pmn?>_field').attr('disabled', 'disabled');
				<? } ?>
			<? } ?>	
			
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
		
		$('#book').ajaxSubmit({
			url: '<?=$site->base?>/book_ajax.php',
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
	    if (this.type == 'checkbox' && this.checked == true) {
	    	var split = this.id.split("|");
	    	// if the ID contains a price value then add it
	    	if(split[2]) add_element(split[0], split[1], split[2], split[3]);
	    }
	   });
	};
	
	saveForm('#book');
	
		
</script>
