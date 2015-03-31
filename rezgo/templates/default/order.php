<? 
$company = $site->getCompanyDetails();
?>

<div class="container-fluid rezgo-container">
  <div class="jumbotron rezgo-booking">
    <div class="row hidden-xs">
      <ol class="breadcrumb rezgo-breadcrumb">
        <li class="active">Your Order</li>
        <li>Guest Information</li>
        <li>Billing Information</li>
        <li>Confirmation</li>
      </ol>
    </div>
  
		<?
      $cart = $site->getCart();
      
      if(!$cart) {
        
        echo '
				<div class="row rezgo-form-group cart_empty">
					<p class="lead">There are no items in your order.</p>
				</div>
				<div class="row" id="rezgo-booking-btn">
					<div class="col-md-4 col-xs-12 rezgo-btn-wrp"><a href="'.$site->base.'/" class="btn rezgo-btn-default btn-lg btn-block">Book More</a></div>
				</div>
				';
        
      } else {
		
				$item_num = 0;
				
				foreach($cart as $item) { 
				
					$site->readItem($item); 
								 
		?>
      
        <div class="row rezgo-form-group">
          
            <div class="col-lg-9 col-sm-8 col-xs-12 rezgo-cart-title">
              <h3>
                <a href="<?=$site->base?>/details/<?=$item->com?>/<?=$site->seoEncode($item->item)?>">
									<?=$item->item?> &mdash; <?=$item->option?>			
                </a>							
							</h3>
              <label>Date: <span class="lead"><?=date((string) $company->date_format, (string) $item->booking_date)?></span></label>
              <? 
	              if($item->discount_rules->rule) {
              		echo '<br><label>Discount: ';
              		unset($discount_string);
									foreach($item->discount_rules->rule as $discount) {	
              			$discount_string .= ($discount_string) ? ', '.$discount : $discount;
              		}
									echo '<span class="rezgo-red">'.$discount_string.'</span>
									</label>';
              	} 
              ?>
            </div>
            <div class="col-lg-3 col-sm-4 col-xs-12">
              <div class="col-sm-12 rezgo-btn-cart-wrp">
              <button type="button" data-toggle="collapse" class="btn rezgo-btn-default btn-block rezgo-pax-edit-btn" data-order-item="<?=$item->uid?>" data-order-com="<?=$item->com?>" data-cart-id="<?=$item->cartID?>" data-book-date="<?=date("Y-m-d", (string)$item->booking_date)?>" href="#pax-edit-<?=$item->cartID?>">Edit Guests</button>        
              </div>
              <? if($site->getCartState()) { ?>
              <div class="col-sm-12 rezgo-btn-cart-wrp">
              <button type="button" class="btn rezgo-btn-remove btn-block" onclick="top.location.href='<?=$site->base?>/order?edit[<?=$item->cartID?>][adult_num]=0';">Remove from Order</button>
              </div>
              <? } ?>
            </div>
        </div>
        <div class="row rezgo-form-group" id="rezgo-cart-pricing-<?=$item->uid?>">  
          <div class="col-sm-12">
          
          <table class="table-responsive">
            <table class="table table-bordered table-striped rezgo-billing-cart">
              <tr>
                <td class="text-right rezgo-billing-type"><label>Type</label></td>
                <td class="text-right rezgo-billing-qty"><label class="hidden-xs">Qty.</label></td>
                <td class="text-right rezgo-billing-cost"><label>Cost</label></td>
                <td class="text-right rezgo-billing-total"><label>Total</label></td>
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
              
              <? if((int) $item->availability < (int) $item->pax_count) { ?>							
              
                <tr>
                  <td colspan="4" class="rezgo-order-unavailable"><span data-toggle="tooltip" data-placement="top" title="This item has become unavailable after it was added to your order"><i class="fa fa-exclamation-triangle"></i> No Longer Available</span></td>
                </tr>                  
              <?  	
                } else {
                  $cart_total += (float) $item->sub_total; 
                }
              ?>
              <tr>
                <td colspan="3" class="text-right"><strong>Sub-total</strong></td>
                <td class="text-right"><?=$site->formatCurrency($item->sub_total)?></td>
              </tr>
            </table>
          </table>
          
          <? if($site->getTourRelated()) { ?>
            <div class="rezgo-related">
              <div class="rezgo-related-label"><span>Related products</span></div>
          	
          	<? foreach($site->getTourRelated() as $related) { ?>
          		
          		<a href="<?=$site->base?>/details/<?=$related->com?>/<?=$site->seoEncode($related->name)?>" class="rezgo-related-link"><?=$related->name?></a><br>
          		
          	<? } ?>
          	
          	</div>
          <? } ?>
          
					<script>
            $(function() {
              $('.rezgo-order-unavailable span').tooltip();
            });
          </script>          
          
          </div>
          
        </div><!-- // .rezgo-form-group -->
        <div class="row rezgo-form-group-short">
          <div class="collapse rezgo-pax-edit-box" id="pax-edit-<?=$item->cartID?>"></div>
        </div>
        <div id="pax-edit-scroll-<?=$item->cartID?>"></div>
        
        <!-- //  order list -->
              
    <? 
		
					$item_num++;
				
				} // end foreach($cart as $item) 
		
		?>
    
			<? if(!$site->isVendor()) { ?>
        <div class="row rezgo-form-group-short">
        <? if (!$_SESSION['rezgo_promo']) { ?>
          <form class="form-inline" id="rezgo-promo-form" role="form" onsubmit="top.location.href = '/order?promo=' + $('#rezgo-promo-code').val(); return false;">
            <label for="rezgo-promo-code"><i class="fa fa-tags"></i>&nbsp;Promo code</label>&nbsp;
            <div class="input-group">
            <input type="text" class="form-control" id="rezgo-promo-code" name="promo" placeholder="Enter Promo Code" value="<?=($_SESSION['rezgo_promo'] ? $_SESSION['rezgo_promo'] : '')?>" />
            <span class="input-group-btn"><button class="btn rezgo-btn-default" type="submit">Apply</button></span>
            </div>
          </form>
        <? } else { ?>
          <label for="rezgo-promo-code"><i class="fa fa-tags"></i>&nbsp;Entered promo code:</label>&nbsp;
        	<span id="rezgo-promo-value"><?=$_SESSION['rezgo_promo']?></span>&nbsp;
          <a id="rezgo-promo-clear" class="btn rezgo-btn-default btn-sm" href="/order?promo=" target="_top">clear</a>
        <? } ?>
        </div>
      <? } ?>
    
      <div class="row">
        <div class="col-sm-12 col-sm-offset-0 col-md-6 col-md-offset-6 rezgo-order-total">Current <span class="hidden-xs">Order</span> Total <?=$site->formatCurrency($cart_total)?></div>
      </div>
      
      <div class="row" id="rezgo-booking-btn">
      	
        <div class="col-md-4 col-sm-6 col-xs-12 rezgo-btn-wrp">
        <? if($site->getCartState()) { ?>
        <a href="<?=$site->base?>/" class="btn rezgo-btn-default btn-lg btn-block">Book More</a>
        <? } ?>&nbsp;
        </div>
        
        <div class="col-md-4 col-md-offset-4 col-sm-6 col-xs-12 rezgo-btn-wrp">
        	<form role="form" action="<?=$site->base?>/book">
	        	<input type="hidden" name="order" value="clear">
         		<? 
       				foreach( $cart as $key => $item ) {
	       				echo '<input type="hidden" name="add['.$item->cartID.'][uid]" value="'.$item->uid.'">';
								echo '<input type="hidden" name="add['.$item->cartID.'][date]" value="'.date("Y-m-d", (string)$item->booking_date).'">';
								
								foreach( $site->getTourPrices($item) as $price ) {
	              	if($item->{$price->name.'_num'}) {
	                  echo '<input type="hidden" name="add['.$item->cartID.']['.$price->name.'_num]" value="'.$item->{$price->name.'_num'}.'">';
	                }
	              }
							}
	         	?>
         		
         		<? if($_COOKIE['rezgo_promo']) { ?><input type="hidden" name="promo" value="<?=$_COOKIE['rezgo_promo']?>"><? } ?>
						<? if($_COOKIE['rezgo_refid_val']) { ?><input type="hidden" name="refid" value="<?=$_COOKIE['rezgo_refid_val']?>"><? } ?>
         		<? if($_COOKIE['show_standard']) { ?><input type="hidden" name="show_standard" value="<?=$_COOKIE['show_standard']?>"><? } ?>
         		
         		<? if(count($cart)) { ?>
         			<input class="btn rezgo-btn-book btn-lg btn-block" value="Proceed to Check Out" type="submit">
         		<? } ?>
         	
         	</form>
        </div>
      </div>
      <!-- //  form btn -->         
       
      
  <? } // end if(!$cart) ?>

	<?
		
		// build 'share this order' link
		
		$pax_nums = array ('adult_num', 'child_num', 'senior_num', 'price4_num', 'price5_num', 'price6_num', 'price7_num', 'price8_num', 'price9_num');
		
    $order_share_link = 'http://'.$_SERVER[HTTP_HOST].$site->base.'/order/?order=clear';	
				
    foreach( $cart as $key => $item ) {
			
      $order_share_link .= '&add['.$item->cartID.'][uid]='.$item->uid.'&add['.$item->cartID.'][date]='.date("Y-m-d", (string)$item->booking_date);

      foreach($pax_nums as $pax) {	
			
				if ($item->{$pax} != '') {
					$order_share_link .= '&add['.$item->cartID.']['.$pax.']='.$item->{$pax};
				}
        
      }
			
    }
		// finally, include promo if set
		if ($_SESSION['rezgo_promo']) {
			$order_share_link .= '&promo='.$_SESSION['rezgo_promo'];
		}
		
  ?>  
  
  
	<? if($site->getCartState() && count($cart)) { ?>
    <a href="javascript:void(0);" id="rezgo-share-order" onclick="$('#rezgo-order-url').toggle('fade');">
    <i class="fa fa-share-alt-square"></i>&nbsp;share this order</a><br />
    <input type="text" id="rezgo-order-url" style="display:none;" class="form-control" onclick="this.select();" value="<?=$order_share_link?>" />
  <? } ?>  
  
  
  </div> <!-- // Jumbotron -->
</div>
<!-- //  Booking form -->
<!-- //  MAIN CONTENT -->

	<script>
  
    $(document).ready(function() {
      
      $('.rezgo-pax-edit-btn').each(function() {
         
        var order_com = $(this).attr('data-order-com'); 
        var order_item = $(this).attr('data-order-item');
        var cart_id = $(this).attr('data-cart-id'); 
        var book_date = $(this).attr('data-book-date'); 
        
        $.ajax({
          url: '<?=$site->base?>/edit_pax.php?com=' + order_com + '&id=' + order_item + '&order_id=' + cart_id + '&date=' + book_date,
          context: document.body,
          success: function(data) {				
            
            $('#pax-edit-' + cart_id).html(data);
                
          }
          
        });	        
        
      });	
			
			
			$('.rezgo-pax-edit-btn').click(function() {
				
        var cart_id = $(this).attr('data-cart-id'); 
				
				var pax_edit_position = $('#pax-edit-scroll-' + cart_id).position();
				
				var pax_edit_scroll = Math.round(pax_edit_position.top);
				
				if ('parentIFrame' in window) {
					setTimeout( 'parentIFrame.scrollTo(0,pax_edit_scroll)', 100 );
				}					
				
			});			
					
      
    });
  
  </script>