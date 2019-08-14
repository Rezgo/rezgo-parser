<?php 
	require('rezgo/include/page_header.php');

	// new instance of RezgoSite
	$site = new RezgoSite();
	
	$company = $site->getCompanyDetails();
	
	$response = '';
	
	$pax_totals = array(
		'adult_num' => 'price_adult', 
		'child_num' => 'price_child', 
		'senior_num' => 'price_senior', 
		'price4_num' => 'price4', 
		'price5_num' => 'price5', 
		'price6_num' => 'price6', 
		'price7_num' => 'price7', 
		'price8_num' => 'price8', 
		'price9_num' => 'price9'
	);
	
	// get reviews
	if($_REQUEST['action'] == 'cart') {
		
		$pickup_split = explode("-", $_REQUEST['pickup_id']);
		$pickup = $pickup_split[0];
		
		$cart_update = $site->pickupCart(($_REQUEST['book_id'] - 1), $pickup);
		
		$cart = $site->getCart(1);
		
		foreach($cart as $item) {
			
			$site->readItem($item);
			
			if ((int) $item->uid == $_REQUEST['item_id'] && (int) $item->num == $_REQUEST['book_id']) {
				
				$response .= '
				<tr style="visibility:collapse;padding:0;height:0"><td colspan="4" style="padding:0;height:0;border:none;">
					<input type="hidden" id="pickup_total_'.$_REQUEST['book_id'].'" value="'.$item->overall_total.'" />
					<input type="hidden" id="pickup_deposit_'.$_REQUEST['book_id'].'" value="'.(($item->deposit_value) ? $item->deposit_value : '0').'" />
				</td></tr>';
				
				$line_items = $site->getTourLineItems();
                        
				foreach($line_items as $line) {
					unset($label_add);
	
					if($site->exists($line->percent) || $site->exists($line->multi)) {
						$label_add = ' (';
	
						if($site->exists($line->percent)) {
							$label_add .= $line->percent.'%';
						}
	
						if($site->exists($line->multi)) {
							
							if(!$site->exists($line->percent)) {
								$label_add .= $site->formatCurrency($line->multi);
							}
							
							if($site->exists($line->meta)) {
								
								$line_pax = 0;
								
								foreach ($pax_totals as $p_num => $p_rate) {
									
									if ( (int) $item->{$p_num} > 0 && ((float) $item->date->{$p_rate} > (float) $line->meta)) {
										$line_pax += (int) $item->{$p_num};
									}
									
								}
								
								$label_add .= ' x '.$line_pax;
								
							} else {
								
								$label_add .= ' x '.$item->pax;
								
							}
							
						}
	
						$label_add .= ')';
					}
	
					$response .= '
					<tr class="rezgo-tr-add-cost">
						<td colspan="3" class="text-right rezgo-line-item">
						';
						if ($line->source == 'bundle') {
							$response .= '<strong class="rezgo-line-bundle"><i class="fa fa-archive"></i>&nbsp;'.$line->label.''.$label_add.'</strong>';
						} else {
							$response .= '<strong>'.$line->label.''.$label_add.'</strong>';
						}
						
				$response .= '
						</td>
						<td class="text-right">
							<span class="rezgo-item-tax" rel="'.$line->amount.'">'.$site->formatCurrency($line->amount).'</span>
						</td>
					</tr>
					';
				}
				
			}
			
		}
	
	}

	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		// ajax response if we requested this page correctly
		echo $response;		
	} else {
		// if, for some reason, the ajax form submit failed, then we want to handle the user anyway
		die ('Something went wrong getting reviews.');
	}
	
?>