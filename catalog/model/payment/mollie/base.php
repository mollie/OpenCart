<?php

/**
 * Copyright (c) 2012-2015, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @package     Mollie
 * @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @author      Mollie B.V. <info@mollie.com>
 * @copyright   Mollie B.V.
 * @link        https://www.mollie.com
 *
 * @property Config $config
 * @property DB $db
 * @property Language $language
 * @property Loader $load
 * @property Log $log
 * @property Registry $registry
 * @property Session $session
 * @property URL $url
 */
require_once(dirname(DIR_SYSTEM) . "/catalog/controller/payment/mollie/helper.php");
use util\Util;

class ModelPaymentMollieBase extends Model
{
	// Current module name - should be overwritten by subclass using one of the values below.
	const MODULE_NAME = NULL;

	/**
	 * @return MollieApiClient
	 */
	protected function getAPIClient()
	{
		return MollieHelper::getAPIClient($this->config);
	}

	public function numberFormat($amount) {
		$currency = $this->getCurrency();
		$intCurrencies = array("ISK", "JPY");
		if(!in_array($currency, $intCurrencies)) {
			$formattedAmount = number_format((float)$amount, 2, '.', '');
		} else {
			$formattedAmount = number_format($amount, 0);
		}	
		return $formattedAmount;	
	}

	public function getAllActive($data) {
		$allowed_methods = array();
		try {
			$payment_methods = $this->getAPIClient()->methods->allActive($data);
		} catch (Mollie\Api\Exceptions\ApiException $e) {
			$this->log->write("Error retrieving payment methods from Mollie: {$e->getMessage()}.");
			return array();
		}
		
		//Get payment methods allowed for this amount and currency
		foreach ($payment_methods as $allowed_method)
		{
			$allowed_methods[] = $allowed_method->id;
		}
		
		if(empty($allowed_methods)) {
			$data["amount"]["currency"] = "EUR";
			$allowed_methods = $this->getAllActive($data);
		}		
		return $allowed_methods;			
	}

	public function getCurrency() {
		if($this->config->get(MollieHelper::getModuleCode() . "_default_currency") == "DEF") {
			$currency = $this->session->data['currency'];
		} else {
			$currency = $this->config->get(MollieHelper::getModuleCode() . "_default_currency");
		}
		return $currency;
	}

	/**
	 * On the checkout page this method gets called to get information about the payment method.
	 *
	 * @param array $address
	 * @param float $total
	 *
	 * @return array
	 */
	public function getMethod($address, $total)
	{
		$this->load->language("payment/mollie");
		$modelCountry = Util::load()->model('localisation/country');
		$currency = $this->getCurrency();
		$moduleCode = MollieHelper::getModuleCode();
		
		// Return nothing if ApplePay is not available
		if(static::MODULE_NAME == 'applepay') {
			if(isset($_COOKIE['applePay']) && ($_COOKIE['applePay'] == 0)) {
				return NULL;
			}
		}

		// Check total for minimum amount
		$standardTotal = $this->currency->convert($total, $this->config->get("config_currency"), 'EUR');
		if($standardTotal <= 0.01) {
			return NULL;
		}

		try {
			$payment_method = $this->getAPIClient()->methods->get(static::MODULE_NAME);

			// TODO: Add fields in admin for minimum and maximum amount for each payment method to be set in the module.
		} catch (Mollie\Api\Exceptions\ApiException $e) {
			$this->log->write("Error retrieving payment method '" . static::MODULE_NAME . "' from Mollie: {$e->getMessage()}.");

			return NULL;
		}

		// Get billing country	
		if (isset($this->session->data['payment_address']) && !empty($this->session->data['payment_address']['country_id'])) {
			$countryDetails = $modelCountry->getCountry($this->session->data['payment_address']['country_id']);
			$country = $countryDetails['iso_code_2'];
		} else {
			// Get billing country from store address
			$country_id = $this->config->get('config_country_id');
			$countryDetails = $modelCountry->getCountry($country_id);
			$country = $countryDetails['iso_code_2'];
		}

		$total = $this->currency->convert($total, $this->config->get("config_currency"), $currency);		
		$data = array(
            "amount" 		 => ["value" => (string)$this->numberFormat($total), "currency" => $currency],
            "resource" 		 => "orders",
            "includeWallets" => "applepay",
			"billingCountry" => $country
        );        
		
		$allowed_methods = $this->getAllActive($data);
		
		if(!in_array($payment_method->id, $allowed_methods)) {
			return NULL;
		}

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get($moduleCode . "_" . static::MODULE_NAME . "_geo_zone") . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ((bool)$this->config->get($moduleCode . "_" . static::MODULE_NAME . "_geo_zone") && !$query->num_rows) {
			return NULL;
		}

		// Translate payment method (if a translation is available).
		$key = "method_" . $payment_method->id;
		$val = $this->language->get($key);

		if ($key !== $val) {
			$payment_method->description = $val;
		}

		$icon = "";

		if ($this->config->get($moduleCode . "_show_icons")) {
			if ($this->request->server['HTTPS']) {
				$image =  $this->config->get('config_ssl') . 'image/mollie/' . $payment_method->id . '.png';
			} else {
				$image =  $this->config->get('config_url') . 'image/mollie/' . $payment_method->id . '.png';
			}
			$icon = '<img src="' . $image . '" height="20" style="margin:0 5px -5px 0" />';
		}

		return array(
			"code" => "mollie_" . static::MODULE_NAME,
			"title" => $icon . $payment_method->description,
			"sort_order" => $this->config->get($moduleCode . "_" . static::MODULE_NAME . "_sort_order"),
			"terms" => NULL,
		);
	}

	/**
	 * While createPayment is in progress this method is getting called to store the order information.
	 *
	 * @param $order_id
	 * @param $transaction_id
	 *
	 * @return bool
	 */
	public function setPayment($order_id, $mollie_order_id, $method)
	{
		$payment_attempt = 1;
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_payments` WHERE `order_id` = '" . $order_id . "' ORDER BY payment_attempt DESC LIMIT 1");		
		if($query->num_rows > 0) {
			$payment_attempt += $query->row['payment_attempt'];
		}
		$bank_account = isset($this->session->data['mollie_issuer']) ? $this->session->data['mollie_issuer'] : NULL;
		if (!empty($order_id) && !empty($mollie_order_id) && !empty($method)) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "mollie_payments` SET `order_id` = '" . (int)$order_id . "', `mollie_order_id` = '" . $this->db->escape($mollie_order_id) . "', `method` = '" . $this->db->escape($method) . "', `bank_account` = '" . $this->db->escape($bank_account) . "', `payment_attempt` = '" . (int)$payment_attempt . "', date_modified = NOW() ON DUPLICATE KEY UPDATE `order_id` = '" . (int)$order_id . "'");

			if ($this->db->countAffected() > 0) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * While report method is in progress this method is getting called to update the order information and status.
	 *
	 * @param $transaction_id
	 * @param $payment_status
	 * @param $consumer
	 *
	 * @return bool
	 */
	public function updatePayment($order_id, $mollie_order_id, $data, $consumer = NULL)
	{
		if (!empty($order_id) && !empty($mollie_order_id)) {
			$this->db->query("UPDATE `" . DB_PREFIX . "mollie_payments` SET `transaction_id` = '" . $this->db->escape($data['payment_id']) . "', `bank_status` = '" . $this->db->escape($data['status']) . "', date_modified = NOW() WHERE `order_id` = '" . (int)$order_id . "' AND `mollie_order_id` = '" . $this->db->escape($mollie_order_id) . "'");

			return $this->db->countAffected() > 0;
		}
		return FALSE;
	}

	public function getPaymentID($order_id)
	{
		if (!empty($order_id)) {
			$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_payments` WHERE `order_id` = '" . $order_id . "'");
			if($results->num_rows == 0) return FALSE;
			return $results->row['transaction_id'];
		}
		return FALSE;
	}

	public function getOrderID($order_id)
	{
		if (!empty($order_id)) {
			$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_payments` WHERE `order_id` = '" . $order_id . "' ORDER BY payment_attempt DESC LIMIT 1");
			if($results->num_rows == 0) return FALSE;
			return $results->row['mollie_order_id'];
		}
		return FALSE;
	}

	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getCouponDetails($orderID) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$orderID . "' AND code = 'coupon'");
		return $query->row;
	}

	public function getVoucherDetails($orderID) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$orderID . "' AND code = 'voucher'");
		return $query->row;		
	}

	public function getRewardPointDetails($orderID) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$orderID . "' AND code = 'reward'");
		return $query->row;		
	}

	public function getOtherOrderTotals($orderID) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$orderID . "' AND code != 'shipping' AND code != 'tax' AND code != 'voucher' AND code != 'sub_total' AND code != 'coupon' AND code != 'reward' AND code != 'total'");

		return $query->rows;
	}

	public function getPayment($order_id)
	{
		if (!empty($order_id)) {
			$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_payments` WHERE `order_id` = '" . $order_id . "'");
			if($results->num_rows == 0) return FALSE;
			return $results->row;
		}
		return FALSE;
	}

	public function cancelReturn($order_id, $mollie_order_id, $data) {
		if (!empty($order_id) && !empty($mollie_order_id)) {
			$this->db->query("UPDATE `" . DB_PREFIX . "mollie_payments` 
					 SET `transaction_id` = '" . $this->db->escape($data['payment_id']) . "', `bank_status` = '" . $this->db->escape($data['status']) . "', `refund_id` = '" . $this->db->escape($data['refund_id']) . "'
					 WHERE `order_id` = '" . (int)$order_id . "' AND `mollie_order_id` = '" . $this->db->escape($mollie_order_id) . "'");

			return $this->db->countAffected() > 0;
		}
	}

	public function checkMollieOrderID($mollie_order_id)
	{
		if (!empty($mollie_order_id)) {
			$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_payments` WHERE `mollie_order_id` = '" . $mollie_order_id . "'");
			if($results->num_rows == 0) return FALSE;
			return TRUE;
		}

		return FALSE;
	}

	public function getOrderStatuses($order_id) {
		$results = $this->db->query("SELECT DISTINCT order_status_id FROM `" . DB_PREFIX . "order_history` WHERE `order_id` = '" . $order_id . "'");
		
		$orderStatuses = array();
		if(!empty($results->rows)) {
			foreach($results->rows as $row) {
				$orderStatuses[] = $row['order_status_id'];
			}
		}

		return $orderStatuses;
	}

}
