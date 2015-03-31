<?php
/*
 * config.inc.php
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

//Configuration Settings

$paypal[business]="$_POST[business]";
// SITE URL
//live
//$paypal[site_url]="https://".$_POST[domain].".rezgo.com/";
//dev
$paypal[site_url]="https://".$_POST[domain]."/";

$paypal[success_url]="complete/".$_POST[encoded_transaction_id];
$paypal[image_url]="";

$paypal[cancel_url]=$_POST[cancel_return];
//$paypal[notify_url]="php_paypal/ipn/ipn.php";
$paypal[notify_url]="rezgo/php_paypal/ipn/ipn.php";
$paypal[return_method]="2"; //1=GET 2=POST
$paypal[currency_code]="$_POST[currency_code]"; //[USD,GBP,JPY,CAD,EUR]
$paypal[lc]="US";

// PAYPAL ENVIRONMENT
//live
$paypal[url]="https://www.paypal.com/cgi-bin/webscr";
//sandbox
//$paypal[url]="https://www.sandbox.paypal.com/cgi-bin/webscr";

$paypal[post_method]="fso"; //fso=fsockopen(); curl=curl command line libCurl=php compiled with libCurl support
$paypal[curl_location]="/usr/local/bin/curl";

$paypal[bn]="Rezgocom_SP_PPS";
$paypal[cmd]="_xclick";
//$paypal[cmd]="_notify-validate";

//Payment Page Settings
$paypal[display_comment]="0"; //0=yes 1=no
$paypal[comment_header]="Comments";
$paypal[continue_button_text]="Click here to complete your booking";//"Continue >>";
$paypal[background_color]=""; //""=white 1=black
$paypal[display_shipping_address]="1"; //""=yes 1=no
$paypal[display_comment]="1"; //""=yes 1=no


//Product Settings
$paypal[item_name]=stripslashes($_POST[item_name]);
$paypal[item_number]=$_POST[item_number];
$paypal[amount]=$_POST[amount];
$paypal[on0]="$_POST[on0]";
$paypal[os0]="$_POST[os0]";
$paypal[on1]="$_POST[on1]";
$paypal[os1]="$_POST[os1]";
$paypal[quantity]=$_POST[quantity];
$paypal[edit_quantity]=""; //1=yes ""=no
//$paypal[invoice]="$_POST[invoice]";
$paypal[invoice]=$_POST[item_number];
$paypal[tax]="$_POST[tax]";

//Shipping and Taxes
$paypal[shipping_amount]="$_POST[shipping_amount]";
$paypal[shipping_amount_per_item]="";
$paypal[handling_amount]="";
$paypal[custom]=$_POST[paypal_signature];

//Customer Settings
$paypal[firstname]="$_POST[firstname]";
$paypal[lastname]="$_POST[lastname]";
$paypal[address1]="$_POST[address1]";
$paypal[address2]="$_POST[address2]";
$paypal[city]="$_POST[city]";
$paypal[state]="$_POST[state]";
$paypal[country]="$_POST[country]";
$paypal[zip]="$_POST[zip]";
$paypal[email]="$_POST[email]";
$paypal[phone_1]="$_POST[phone]";
$paypal[phone_2]="$_POST[phone2]";
$paypal[phone_3]="$_POST[phone3]";

?>