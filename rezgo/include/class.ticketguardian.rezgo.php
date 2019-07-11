<?php

  class RezgoTicketGuardian {
    private $test_url = "https://connect-sandbox.ticketguardian.net";
    private $prod_url = "https://connect.ticketguardian.net";
    private $base_url;

    private $test_mode = REZGO_TICKGUARDIAN_TEST;
    public function __construct() {

			if (!extension_loaded('curl')) {
        throw new \ErrorException('The cURL extension was not found. Please install');
      }
      $this->init();
    }
    private function init() {
      $this->base_url = ($this->test_mode == 1) ? $this->test_url : $this->prod_url;
    }
    function postAuthenticationToken() {
      $url = "{$this->base_url}/api/v2/auth/token";

      $data = array(
        'public_key' => REZGO_TICKGUARDIAN_PK,
        'secret_key' => REZGO_TICKGUARDIAN_SK
      );

			$result = $this->curl_request($url, "POST", "Token", NULL, $data);

      return $result;
    }
    function postQuote($data) {
      $token_result = $this->postAuthenticationToken();
      $url = "{$this->base_url}/api/v2/quote/";

      $result = $this->curl_request($url, "POST", "Quote", $token_result['token'], $data);
      return $result;
    }
    function postOrder($data) {
      $token_result = $this->postAuthenticationToken();
      $url = "{$this->base_url}/api/v2/orders/";

      $result = $this->curl_request($url, "POST", "Order", $token_result['token'], $data);
      return $result;
    }
    function postCharge($data) {
      $token_result = $this->postAuthenticationToken();
      $order_number = $data['order_number'];
      $url = "{$this->base_url}/api/v2/orders/{$order_number}/charge/";
      $result = $this->curl_request($url, "POST", "Charge", $token_result['token'], $data);
      return $result;
    }
    function getPolicies($order_id) {
      $token_result = $this->postAuthenticationToken();
      $url = "{$this->base_url}/api/v2/orders/{$order_id}/policies";
      $result = $this->curl_request($url, "GET", "Policies", $token_result['token']);
      return $result;
    }
    private function curl_request($url, $action, $endpoint, $access_token = NULL, $data = NULL) {
      $headers = array();
      $headers[] = "Content-Type: application/json";
      $headers[] = "Accept: application/json";
      $headers[] = "TG-Version: 2.0.0";
      if($access_token) $headers[] = "Authorization: JWT {$access_token}";
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
      curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_TIMEOUT,30);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      if($action == 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
      } else if($action == 'PUT') {
        curl_setopt($ch, CURLOPT_PUT, 1);
      } else {
        curl_setopt($ch, CURLOPT_HTTPGET , 1);
      }
      if($data) curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($data));
      if(curl_errno($ch)) {
        $r = array('error'=>'curl_error','error_description'=>curl_error($ch));
      } else {
        $r = json_decode(curl_exec($ch), 1);
      }
      $c = array('code' => curl_getinfo($ch, CURLINFO_HTTP_CODE));
      $result = array_merge($r,$c);
      curl_close ($ch);
      $resp_code = $result['code'];
      $resp_message = $result;
      if($resp_code >= '300') {
        // capture the first error message in the response array
        foreach((array) $result['error'] as $ek => $ev) {
          if($ek == 'message') {
            $resp_message	= $ev;
            break;
          }
        }
        if(!$resp_message) $resp_message = "{$action} {$endpoint} Error";
        return "{$resp_code}: {$resp_message}";
      }
      return $result;
    }
    function testApiCalls() {
      $items = $this->getItems();
      $customer = $this->getCustomer();
      $billing = $this->getBilling();
      $card = $this->getCard();
      $order_number = $this->getOrderNumber();
      $http_referrer = $_SERVER['REMOTE_ADDR'];
      $quote_data = array();
      $quote_data["currency"] = 'USD';
      $quote_data["items"] = $items;
      $order_data = array();
      $order_data["customer"] = $customer;
      $order_data["order_number"] = $order_number;
      $order_data["currency"] = 'USD';
      $order_data["items"] = $items;
      $charge_data = array();
      $charge_data["customer"] = $customer;
      $charge_data["billing_address"] = $billing;
      $charge_data["card"] = $card;
      $charge_data["order_number"] = $order_number;
      $quote_resp = $this->postQuote($quote_data);
      $order_resp = $this->postOrder($order_data);
      $policies_resp = $this->getPolicies($order_number);
      $charge_resp = $this->postCharge($charge_data);
      $results = array();
      $results["quote"] = $quote_resp;
      $results["order"] = $order_resp;
      $results["policies"] = $policies_resp;
      $results["charge"] = $charge_resp;
      print_r(json_encode($results));
    }
    private function getOrderNumber() {
      if($this->test_mode) {
        $order_number = rand();
      } else {
      }
      return $order_number;
    }
    private function getItems() {
      if($this->test_mode) {
        $item = array([
            "name"=> "Folk Fest - VIP",
            "reference_number"=> "VIP05156",
            "cost"=> "3"
          ], [
            "name"=> "Folk Fest - GA",
            "reference_number"=> "GA42348",
            "cost"=> "2"
          ]
        );
      }
      return $item;
    }
    private function getCard() {
      if($this->test_mode) {
        $card = array(
          "number"=> "4111111111111111",
          "expire_month"=> "11",
          "expire_year"=>"20",
          "cvv"=> "123"
        );
      }
      return $card;
    }
    private function getBilling() {
      if($this->test_mode) {
        $billing = array(
          "address1"=> "1 Hooli Dr",
          "address2"=> "1905",
          "city"=> "Newport Beach",
          "state"=> "CA",
          "zip_code"=> "92663",
          "country"=> "USA"
        );
      }
      return $billing;
    }
    private function getCustomer() {
      if($this->test_mode) {
        $customer = array(
          "first_name"=>"GMS",
          "last_name"=>"Sinclair",
          "email"=>"gazheek@rezgo.com",
          "phone"=>"1112223344"
        );
      }
      return $customer;
    }
  }
