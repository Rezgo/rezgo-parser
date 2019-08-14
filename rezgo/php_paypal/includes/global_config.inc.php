<?php
/*
 * global_config.inc.php
 *
 * PHP Toolkit for PayPal v0.51
 * http://www.paypal.com/pdn
 *
 * Copyright (c) 2004 PayPal Inc
 *
 * Released under Common Public License 1.0
 * http://opensource.org/licenses/cpl.php
 *
 */

//create variable names to perform additional order processing

function create_local_variables() {

$array_name[business]="$_POST[business]";
$array_name[receiver_email]="$_POST[receiver_email]";
$array_name[receiver_id]="$_POST[receiver_id]";
$array_name[item_name]="$_POST[item_name]";
$array_name[item_number]="$_POST[item_number]";
$array_name[quantity]="$_POST[quantity]";
$array_name[invoice]="$_POST[invoice]";
$array_name[custom]="$_POST[custom]";
$array_name[memo]="$_POST[memo]";
$array_name[tax]="$_POST[tax]";
$array_name[option_name1]="$_POST[option_name1]";
$array_name[option_selection1]="$_POST[option_selection1]";
$array_name[option_name2]="$_POST[option_name2]";
$array_name[option_selection2]="$_POST[option_selection2]";
$array_name[num_cart_items]="$_POST[num_cart_items]";
$array_name[mc_gross]="$_POST[mc_gross]";
$array_name[mc_fee]="$_POST[mc_fee]";
$array_name[mc_currency]="$_POST[mc_currency]";
$array_name[settle_amount]="$_POST[settle_amount]";
$array_name[settle_currency]="$_POST[settle_currency]";
$array_name[exchange_rate]="$_POST[exchange_rate]";
$array_name[payment_gross]="$_POST[payment_gross]";
$array_name[payment_fee]="$_POST[payment_fee]";
$array_name[payment_status]="$_POST[payment_status]";
$array_name[pending_reason]="$_POST[pending_reason]";
$array_name[reason_code]="$_POST[reason_code]";
$array_name[payment_date]="$_POST[payment_date]";
$array_name[txn_id]="$_POST[txn_id]";
$array_name[txn_type]="$_POST[txn_type]";
$array_name[payment_type]="$_POST[payment_type]";
$array_name[for_auction]="$_POST[for_auction]";
$array_name[auction_buyer_id]="$_POST[auction_buyer_id]";
$array_name[auction_closing_date]="$_POST[auction_closing_date]";
$array_name[auction_multi_item]="$_POST[auction_multi_item]";
$array_name[first_name]="$_POST[first_name]";
$array_name[last_name]="$_POST[last_name]";
$array_name[payer_business_name]="$_POST[payer_business_name]";
$array_name[address_name]="$_POST[address_name]";
$array_name[address_street]="$_POST[address_street]";
$array_name[address_city]="$_POST[address_city]";
$array_name[address_state]="$_POST[address_state]";
$array_name[address_zip]="$_POST[address_zip]";
$array_name[address_country]="$_POST[address_country]";
$array_name[address_status]="$_POST[address_status]";
$array_name[payer_email]="$_POST[payer_email]";
$array_name[payer_id]="$_POST[payer_id]";
$array_name[payer_status]="$_POST[payer_status]";
$array_name[notify_version]="$_POST[notify_version]";
$array_name[verify_sign]="$_POST[verify_sign]";

return $array_name;

}

//post transaction data using curl

function curlPost($url,$data)  {

global $paypal;

//build post string

foreach($data as $i=>$v) {
$postdata.= $i . "=" . urlencode($v) . "&";
}

$postdata.="cmd=_notify-validate";

//execute curl on the command line

exec("$paypal[curl_location] -d \"$postdata\" $url", $info);

$info=implode(",",$info);

return $info;

}

//posts transaction data using libCurl

function libCurlPost($url,$data)  {

//build post string

foreach($data as $i=>$v) {

$postdata.= $i . "=" . urlencode($v) . "&";

}

$postdata.="cmd=_notify-validate";

$ch=curl_init();

curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_POST,1);
curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);

//Start ob to prevent curl_exec from displaying stuff.
ob_start();
curl_exec($ch);

//Get contents of output buffer
$info=ob_get_contents();
curl_close($ch);

//End ob and erase contents.
ob_end_clean();

return $info;

}

//posts transaction data using fsockopen.
function fsockPost($url,$data) {

//Parse url
$web=parse_url($url);

//build post string
foreach($data as $i=>$v) {
$postdata.= $i . "=" . urlencode($v) . "&";
}

$postdata.="cmd=_notify-validate";

//Set the port number
if($web[scheme] == "https") { $web[port]="443";  $ssl="ssl://"; } else { $web[port]="80"; }

//Create paypal connection
$fp=@fsockopen($ssl . $web[host],$web[port],$errnum,$errstr,30);

//Error checking
if(!$fp) { echo "$errnum: $errstr"; }

//Post Data
else {

  fputs($fp, "POST $web[path] HTTP/1.1\r\n");
  fputs($fp, "Host: $web[host]\r\n");
  fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
  fputs($fp, "Content-length: ".strlen($postdata)."\r\n");
  fputs($fp, "Connection: close\r\n\r\n");
  fputs($fp, $postdata . "\r\n\r\n");

//loop through the response from the server
while(!feof($fp)) { $info[]=@fgets($fp, 1024); }

//close fp - we are done with it
fclose($fp);

//break up results into a string
$info=implode(",",$info);

}

return $info;

   }

//Display Paypal Hidden Variables

function showVariables() {

global $paypal;

?>
<!-- PayPal Configuration -->
<input type="hidden" name="business" value="<?php echo $paypal[business]?>">
<input type="hidden" name="currency_code" value="<?php echo $paypal[currency_code]?>">
<input type="hidden" name="cmd" value="<?php echo $paypal[cmd]?>">
<input type="hidden" name="image_url" value="<?php echo "$paypal[site_url]$paypal[image_url]"; ?>">
<input type="hidden" name="return" value="<?php echo "$paypal[site_url]$paypal[success_url]"; ?>">
<input type="hidden" name="cancel_return" value="<?php echo "$paypal[cancel_url]"; ?>">
<input type="hidden" name="notify_url" value="<?php echo "$paypal[site_url]$paypal[notify_url]"; ?>">
<input type="hidden" name="rm" value="<?php echo $paypal[return_method]?>">

<input type="hidden" name="lc" value="<?php echo $paypal[lc]?>">
<input type="hidden" name="bn" value="<?php echo $paypal[bn]?>">
<input type="hidden" name="cbt" value="<?php echo $paypal[continue_button_text]?>">

<!-- Payment Page Information -->
<input type="hidden" name="no_shipping" value="<?php echo $paypal[display_shipping_address]?>">
<input type="hidden" name="no_note" value="<?php echo $paypal[display_comment]?>">
<input type="hidden" name="cn" value="<?php echo $paypal[comment_header]?>">
<input type="hidden" name="cs" value="<?php echo $paypal[background_color]?>">

<!-- Product Information -->
<input type="hidden" name="item_name" value="<?php echo $paypal[item_name]?>">
<input type="hidden" name="amount" value="<?php echo $paypal[amount]?>">
<input type="hidden" name="quantity" value="<?php echo $paypal[quantity]?>">
<input type="hidden" name="item_number" value="<?php echo $paypal[item_number]?>">
<input type="hidden" name="undefined_quantity" value="<?php echo $paypal[edit_quantity]?>">
<input type="hidden" name="on0" value="<?php echo $paypal[on0]?>">
<input type="hidden" name="os0" value="<?php echo $paypal[os0]?>">
<input type="hidden" name="on1" value="<?php echo $paypal[on1]?>">
<input type="hidden" name="os1" value="<?php echo $paypal[os1]?>">

<!-- Shipping and Misc Information -->
<input type="hidden" name="shipping" value="<?php echo $paypal[shipping_amount]?>">
<input type="hidden" name="shipping2" value="<?php echo $paypal[shipping_amount_per_item]?>">
<input type="hidden" name="handling" value="<?php echo $paypal[handling_amount]?>">
<input type="hidden" name="tax" value="<?php echo $paypal[tax]?>">
<input type="hidden" name="custom" value="<?php echo $paypal[custom]?>">
<input type="hidden" name="invoice" value="<?php echo $paypal[invoice]?>">

<!-- Customer Information -->
<input type="hidden" name="first_name" value="<?php echo $paypal[firstname]?>">
<input type="hidden" name="last_name" value="<?php echo $paypal[lastname]?>">
<input type="hidden" name="address1" value="<?php echo $paypal[address1]?>">
<input type="hidden" name="address2" value="<?php echo $paypal[address2]?>">
<input type="hidden" name="city" value="<?php echo $paypal[city]?>">
<input type="hidden" name="state" value="<?php echo $paypal[state]?>">
<input type="hidden" name="zip" value="<?php echo $paypal[zip]?>">
<input type="hidden" name="email" value="<?php echo $paypal[email]?>">
<input type="hidden" name="night_phone_a" value="<?php echo $paypal[phone_1]?>">
<input type="hidden" name="night_phone_b" value="<?php echo $paypal[phone_2]?>">
<input type="hidden" name="night_phone_c" value="<?php echo $paypal[phone_3]?>">

<?php } ?>