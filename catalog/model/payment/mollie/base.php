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
require_once(DIR_SYSTEM . "library/mollie/helper.php");

class ModelPaymentMollieBase extends Model
{
	public function __construct($registry) {
        parent::__construct($registry);
        $this->registry = $registry;
        $this->mollieHelper = new MollieHelper($registry);
    }

	/**
	 * @return MollieApiClient
	 */
	protected function getAPIClient()
	{
		return $this->mollieHelper->getAPIClient($this->config);
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

		$this->session->data['mollie_allowed_methods'] = $allowed_methods;
		$this->session->data['mollie_currency'] = $this->session->data["currency"];

		return $allowed_methods;			
	}

	public function getCurrency() {
		if($this->config->get($this->mollieHelper->getModuleCode() . "_default_currency") == "DEF") {
			$currency = $this->session->data['currency'];
		} else {
			$currency = $this->config->get($this->mollieHelper->getModuleCode() . "_default_currency");
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
		// Check required minimum php version to avoid errors
		if (version_compare(phpversion(), MollieHelper::MIN_PHP_VERSION, "<")) {
        	return;
		}
		
		$this->load->language("payment/mollie");
		$this->load->model('localisation/country');
		$this->load->model('tool/image');
		$currency = $this->getCurrency();
		$moduleCode = $this->mollieHelper->getModuleCode();

		// Check total for minimum and maximum amount
		$standardTotal = $this->currency->convert($total, $this->config->get("config_currency"), 'EUR');
		$minimumAmount = 0;
		$maximumAmount = 0;

		if ($this->config->get($moduleCode . "_" . static::MODULE_NAME . "_total_minimum")) {
			if (!empty($this->config->get($moduleCode . "_" . static::MODULE_NAME . "_total_minimum"))) {
				$minimumAmount = (float)$this->config->get($moduleCode . "_" . static::MODULE_NAME . "_total_minimum");
			} else {
				$minimumAmount = 0.01;
			}
		} else {
			$minimumAmount = 0.01;
		}

		if ($this->config->get($moduleCode . "_" . static::MODULE_NAME . "_total_maximum")) {
			if (!empty($this->config->get($moduleCode . "_" . static::MODULE_NAME . "_total_maximum"))) {
				$maximumAmount = (float)$this->config->get($moduleCode . "_" . static::MODULE_NAME . "_total_maximum");
			}
		}

		if ($standardTotal < $this->currency->convert($minimumAmount, $this->config->get("config_currency"), 'EUR')) {
			return NULL;
		}

		if (($maximumAmount > 0) && ($standardTotal > $this->currency->convert($maximumAmount, $this->config->get("config_currency"), 'EUR'))) {
			return NULL;
		}
		

		// Check for order expiry days
		if ((static::MODULE_NAME == 'klarnapaylater') || (static::MODULE_NAME == 'klarnasliceit') || (static::MODULE_NAME == 'klarnapaynow')) {
			if ($this->config->get($moduleCode . "_order_expiry_days") && ($this->config->get($moduleCode . "_order_expiry_days") > 28)) {
				return NULL;
			}
		}

		// Return nothing if ApplePay is not available
		if(static::MODULE_NAME == 'applepay') {
			if(isset($this->session->data['applePay']) && ($this->session->data['applePay'] == 0)) {
				return NULL;
			}
		}

		// Check if Vouchers are available
		if(static::MODULE_NAME == 'voucher') {
			$this->load->model('catalog/product');

			$voucherStatus = false;
			foreach ($this->cart->getProducts() as $cart_product) {
				$productDetails = $this->model_catalog_product->getProduct($cart_product['product_id']);
				if (!empty($productDetails['voucher_category'])) {
					$voucherStatus = true;
				}
			}

			if (!$voucherStatus) {
				return NULL;
			}
		}

		try {
			$payment_method = $this->getAPIClient()->methods->get(static::MODULE_NAME);

			// TODO: Add fields in admin for minimum and maximum amount for each payment method to be set in the module.
		} catch (Mollie\Api\Exceptions\ApiException $e) {
			$this->log->write("Error retrieving payment method '" . static::MODULE_NAME . "' from Mollie: {$e->getMessage()}.");

			return NULL;
		}

		// Double check min and max amount
		if ($payment_method->minimumAmount && $payment_method->maximumAmount) {
			if (($standardTotal < $payment_method->minimumAmount->value) || ($standardTotal > $payment_method->maximumAmount->value)) {
				return NULL;
			}
		}

		// Get billing country	
		if (isset($this->session->data['payment_address']) && !empty($this->session->data['payment_address']['country_id'])) {
			$countryDetails = $this->model_localisation_country->getCountry($this->session->data['payment_address']['country_id']);
			$country = $countryDetails['iso_code_2'];
		} else {
			// Get billing country from store address
			$country_id = $this->config->get('config_country_id');
			$countryDetails = $this->model_localisation_country->getCountry($country_id);
			$country = $countryDetails['iso_code_2'];
		}

		$total = $this->currency->convert($total, $this->config->get("config_currency"), $currency);	
		if (version_compare(VERSION, '1.5.6.4', '<')) {
			$sequence = 'oneoff';
		} elseif ($this->cart->hasRecurringProducts()) {
			$sequence = 'first';
		} else {
			$sequence = 'oneoff';
		}
			
		$data = array(
            "amount" 		 => ["value" => (string)$this->numberFormat($total), "currency" => $currency],
            "resource" 		 => "orders",
            "includeWallets" => "applepay",
			"billingCountry" => $country,
			"sequenceType" => $sequence
        );

		if (isset($this->session->data['mollie_allowed_methods']) && ($this->session->data['mollie_currency'] == $this->session->data["currency"])) {
			$allowed_methods = $this->session->data['mollie_allowed_methods'];
		} else {
			$allowed_methods = $this->getAllActive($data);
		}     
		
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

		// Custom title
		if(isset($this->config->get($moduleCode . "_" . static::MODULE_NAME . "_description")[$this->config->get('config_language_id')])) {
			$title = $this->config->get($moduleCode . "_" . static::MODULE_NAME . "_description")[$this->config->get('config_language_id')]['title'];
		} else {
			$title = $payment_method->description;
		}

		if ($this->config->get($moduleCode . "_show_icons")) {
			if(!empty($this->config->get($moduleCode . "_" . static::MODULE_NAME . "_image"))) {
				$_image = $this->config->get($moduleCode . "_" . static::MODULE_NAME . "_image");
				$image = $this->model_tool_image->resize($_image, 100, 100);
			} else {
				if (isset($this->request->server['HTTPS'])) {
					$image =  $this->config->get('config_ssl') . 'image/mollie/' . $payment_method->id . '.png';
				} else {
					$image =  $this->config->get('config_url') . 'image/mollie/' . $payment_method->id . '.png';
				}
			}			
			$icon = '<img src="' . $image . '" height="20" style="margin:0 5px 0 5px" />';

			if ($this->config->get($moduleCode . "_align_icons") == 'left') {
				$title = $icon . $title;
			} else {
				$title = $title . $icon;
			}
		}

		return array(
			"code" => "mollie_" . static::MODULE_NAME,
			"title" => $title,
			"sort_order" => $this->config->get($moduleCode . "_" . static::MODULE_NAME . "_sort_order"),
			"terms" => NULL
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

	public function setPaymentForPaymentAPI($order_id, $mollie_payment_id, $method)
	{
		$payment_attempt = 1;
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_payments` WHERE `order_id` = '" . $order_id . "' ORDER BY payment_attempt DESC LIMIT 1");		
		if($query->num_rows > 0) {
			$payment_attempt += $query->row['payment_attempt'];
		}
		$bank_account = isset($this->session->data['mollie_issuer']) ? $this->session->data['mollie_issuer'] : NULL;
		if (!empty($order_id) && !empty($mollie_payment_id) && !empty($method)) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "mollie_payments` SET `order_id` = '" . (int)$order_id . "', `transaction_id` = '" . $this->db->escape($mollie_payment_id) . "', `method` = '" . $this->db->escape($method) . "', `bank_account` = '" . $this->db->escape($bank_account) . "', `payment_attempt` = '" . (int)$payment_attempt . "', date_modified = NOW() ON DUPLICATE KEY UPDATE `order_id` = '" . (int)$order_id . "'");

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
			$this->db->query("UPDATE `" . DB_PREFIX . "mollie_payments` SET `transaction_id` = '" . $this->db->escape($data['payment_id']) . "', `bank_status` = '" . $this->db->escape($data['status']) . "', amount = '" . (isset($data['amount']) ? (float)$data['amount'] : 0) . "', date_modified = NOW() WHERE `order_id` = '" . (int)$order_id . "' AND `mollie_order_id` = '" . $this->db->escape($mollie_order_id) . "'");

			return $this->db->countAffected() > 0;
		}
		return FALSE;
	}

	public function updatePaymentForPaymentAPI($order_id, $mollie_payment_id, $data, $consumer = NULL)
	{
		if (!empty($order_id) && !empty($mollie_payment_id)) {
			$this->db->query("UPDATE `" . DB_PREFIX . "mollie_payments` SET `bank_status` = '" . $this->db->escape($data['status']) . "', amount = '" . (isset($data['amount']) ? (float)$data['amount'] : 0) . "', date_modified = NOW() WHERE `order_id` = '" . (int)$order_id . "' AND `transaction_id` = '" . $this->db->escape($mollie_payment_id) . "'");

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
		if (!empty($order_id)) {
			if (!empty($mollie_order_id)) {
				$this->db->query("UPDATE `" . DB_PREFIX . "mollie_payments` SET `transaction_id` = '" . $this->db->escape($data['payment_id']) . "', `bank_status` = '" . $this->db->escape($data['status']) . "', `refund_id` = '" . $this->db->escape($data['refund_id']) . "' WHERE `order_id` = '" . (int)$order_id . "' AND `mollie_order_id` = '" . $this->db->escape($mollie_order_id) . "'");
			} else {
				$this->db->query("UPDATE `" . DB_PREFIX . "mollie_payments` SET `bank_status` = '" . $this->db->escape($data['status']) . "', `refund_id` = '" . $this->db->escape($data['refund_id']) . "' WHERE `order_id` = '" . (int)$order_id . "' AND `transaction_id` = '" . $this->db->escape($data['payment_id']) . "'");
			}

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

	public function addCustomer($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "mollie_customers` SET `customer_id` = '" . (int)$data['customer_id'] . "', `mollie_customer_id` = '" . $this->db->escape($data['mollie_customer_id']) . "', `email` = '" . $this->db->escape($data['email']) . "', `date_created` = NOW()");
	}

	public function getMollieCustomer($email) {
		if (!empty($email)) {
			$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_customers` WHERE `email` = '" . $email . "'");
			if($results->num_rows == 0) return '';
			return $results->row['mollie_customer_id'];
		}
		return '';
	}

	public function recurringPayment($item, $subscription_id, $mollie_order_id = '', $mollie_payment_id = '') {

		$this->load->model('checkout/recurring');
		$this->load->language("payment/mollie");
		$order_id_rand = $this->session->data['order_id'] . '-' . $subscription_id;
		//trial information
		if ($item['recurring']['trial'] == 1) {
			$price = $item['recurring']['trial_price'];
			$trial_amt = $this->currency->format($this->tax->calculate($item['recurring']['trial_price'], $item['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], false, false) * $item['quantity'] . ' ' . $this->session->data['currency'];
			$trial_text = sprintf($this->language->get('text_trial'), $trial_amt, $item['recurring']['trial_cycle'], $item['recurring']['trial_frequency'], $item['recurring']['trial_duration']);
		} else {
			$price = $item['recurring']['price'];
			$trial_text = '';
		}

		$recurring_amt = $this->currency->format($this->tax->calculate($item['recurring']['price'], $item['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], false, false) * $item['quantity'] . ' ' . $this->session->data['currency'];
		$recurring_description = $trial_text . sprintf($this->language->get('text_recurring'), $recurring_amt, $item['recurring']['cycle'], $item['recurring']['frequency']);

		if ($item['recurring']['duration'] > 0) {
			$recurring_description .= sprintf($this->language->get('text_length'), $item['recurring']['duration']);
		}

		if (version_compare(VERSION, '3.0', '>=')) {
			if (version_compare(VERSION, '3.0.3.7', '>=')) {
				$order_recurring_id = $this->model_checkout_recurring->addRecurring($this->session->data['order_id'], $recurring_description, $item);
			} else {
				$order_recurring_id = $this->model_checkout_recurring->addRecurring($this->session->data['order_id'], $recurring_description, $item['recurring']);
			}
		} else {
			$order_recurring_id = $this->model_checkout_recurring->create($item, $this->session->data['order_id'], $recurring_description);
		}
		
		$this->model_checkout_recurring->editReference($order_recurring_id, $order_id_rand);

		$next_payment = new DateTime('now');
		$subscription_end = new DateTime('now');

		if ($item['recurring']['duration'] != 0) {
			$next_payment = $this->calculateSchedule($item['recurring']['frequency'], $next_payment, $item['recurring']['cycle']);
			$subscription_end = $this->calculateSchedule($item['recurring']['frequency'], $subscription_end, $item['recurring']['cycle'] * $item['recurring']['duration']);
		} else {
			$next_payment = $this->calculateSchedule($item['recurring']['frequency'], $next_payment, $item['recurring']['cycle']);
			$subscription_end = new DateTime('0000-00-00');
		}

		$this->addProfileTransaction($order_recurring_id, $order_id_rand, $price, 1);

		if (!empty(mollie_order_id)) {
			$this->db->query("UPDATE `" . DB_PREFIX . "mollie_payments` SET `subscription_id` = '" . $this->db->escape($subscription_id) . "', `next_payment` = '" . $this->db->escape(date_format($next_payment, 'Y-m-d H:i:s')) . "', subscription_end = '" . $this->db->escape(date_format($subscription_end, 'Y-m-d H:i:s')) . "', order_recurring_id = '" . $this->db->escape($order_recurring_id) . "' WHERE `order_id` = '" . (int)$this->session->data['order_id'] . "' AND `mollie_order_id` = '" . $this->db->escape($mollie_order_id) . "'");
		} else {
			$this->db->query("UPDATE `" . DB_PREFIX . "mollie_payments` SET `subscription_id` = '" . $this->db->escape($subscription_id) . "', `next_payment` = '" . $this->db->escape(date_format($next_payment, 'Y-m-d H:i:s')) . "', subscription_end = '" . $this->db->escape(date_format($subscription_end, 'Y-m-d H:i:s')) . "', order_recurring_id = '" . $this->db->escape($order_recurring_id) . "' WHERE `order_id` = '" . (int)$this->session->data['order_id'] . "' AND `mollie_payment_id` = '" . $this->db->escape($mollie_payment_id) . "'");
		}
	}

	private function calculateSchedule($frequency, $next_payment, $cycle) {
		if ($frequency == 'semi_month') {
			$day = date_format($next_payment, 'd');
			$value = 15 - $day;
			$isEven = false;
			if ($cycle % 2 == 0) {
				$isEven = true;
			}

			$odd = ($cycle + 1) / 2;
			$plus_even = ($cycle / 2) + 1;
			$minus_even = $cycle / 2;

			if ($day == 1) {
				$odd = $odd - 1;
				$plus_even = $plus_even - 1;
				$day = 16;
			}

			if ($day <= 15 && $isEven) {
				$next_payment->modify('+' . $value . ' day');
				$next_payment->modify('+' . $minus_even . ' month');
			} elseif ($day <= 15) {
				$next_payment->modify('first day of this month');
				$next_payment->modify('+' . $odd . ' month');
			} elseif ($day > 15 && $isEven) {
				$next_payment->modify('first day of this month');
				$next_payment->modify('+' . $plus_even . ' month');
			} elseif ($day > 15) {
				$next_payment->modify('+' . $value . ' day');
				$next_payment->modify('+' . $odd . ' month');
			}
		} else {
			$next_payment->modify('+' . $cycle . ' ' . $frequency);
		}
		return $next_payment;
	}

	private function addProfileTransaction($order_recurring_id, $order_code, $price, $type) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction` SET `order_recurring_id` = '" . (int)$order_recurring_id . "', `date_added` = NOW(), `amount` = '" . (float)$price . "', `type` = '" . (int)$type . "', `reference` = '" . $this->db->escape($order_code) . "'");
	}

	public function getPaymentBySubscriptionID($subscription_id)
	{
		if (!empty($subscription_id)) {
			$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mollie_payments` WHERE `subscription_id` = '" . $subscription_id . "'");
			if($results->num_rows == 0) return '';
			return $results->row;
		}
		return '';
	}

	public function addRecurringPayment($data) {
		$profile = $this->getProfile($data['order_recurring_id']);
		$recurring_order = $this->getPaymentBySubscriptionID($data['subscription_id']);

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($profile['order_id']);

		$price = $this->currency->convert($data['amount'], $data['currency'], $this->config->get("config_currency")); // Convert to default currency to display the right amount in the admin
		$frequency = $profile['recurring_frequency'];
		$cycle = $profile['recurring_cycle'];

		$today = new DateTime('now');
		$unlimited = new DateTime('0000-00-00');
		$next_payment = new DateTime($recurring_order['next_payment']);
		$subscription_end = new DateTime($recurring_order['subscription_end']);

		if($data['status'] == 'paid') {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "mollie_recurring_payments` SET `transaction_id` = '" . $this->db->escape($data['transaction_id']) . "', `subscription_id` = '" . $this->db->escape($data['subscription_id']) . "', `mollie_customer_id` = '" . $this->db->escape($data['mollie_customer_id']) . "', `method` = '" . $this->db->escape($data['method']) . "', `status` = '" . $this->db->escape($data['status']) . "', `order_recurring_id` = '" . (int)$data['order_recurring_id'] . "', `date_created` = NOW()");

			$this->addProfileTransaction($profile['order_recurring_id'], $recurring_order['order_id'] .'-'. $recurring_order['subscription_id'], $price, 1);
			$next_payment = $this->calculateSchedule($frequency, $next_payment, $cycle);
			$next_payment = date_format($next_payment, 'Y-m-d H:i:s');

			if($subscription_end > $today || $subscription_end == $unlimited) {
				$this->db->query("UPDATE `" . DB_PREFIX . "mollie_payments` SET `next_payment` = '" . $this->db->escape($next_payment) . "' WHERE `subscription_id` = '" . $data['subscription_id'] . "'");

				// Send mail to the customer
				$subject = $this->config->get($this->mollieHelper->getModuleCode() . "_recurring_email")[$this->config->get('config_language_id')]['subject'];
				$body = $this->config->get($this->mollieHelper->getModuleCode() . "_recurring_email")[$this->config->get('config_language_id')]['body'];
				$find = array("{firstname}", "{lastname}", "{next_payment}", "{product_name}", "{order_id}", "{store_name}");
				$replace = array($order_info['firstname'], $order_info['lastname'], date('d-m-Y', strtotime($next_payment)), $profile['recurring_name'], $profile['order_id'], $order_info['store_name']);
				$body = html_entity_decode(str_replace($find, $replace, $body), ENT_QUOTES, 'UTF-8');
		
				$mail = new Mail($this->config->get('config_mail_engine'));
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
		
				$mail->setTo($order_info['email']);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
				$mail->setSubject(html_entity_decode($subject), ENT_QUOTES, 'UTF-8');
				$mail->setHtml($body);
				$mail->send();
			}
		} else {
			$this->addProfileTransaction($profile['order_recurring_id'], $recurring_order['order_id'] .'-'. $recurring_order['subscription_id'], $price, 4);
		}
	}

	private function getProfile($order_recurring_id) {
		$qry = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_recurring WHERE order_recurring_id = " . (int)$order_recurring_id);
		return $qry->row;
	}
}
