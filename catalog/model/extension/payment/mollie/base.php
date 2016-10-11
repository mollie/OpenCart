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
 * @property Config   $config
 * @property DB       $db
 * @property Language $language
 * @property Loader   $load
 * @property Log      $log
 * @property Registry $registry
 * @property Session  $session
 * @property URL      $url
 */
require_once(dirname(DIR_SYSTEM) . "/catalog/controller/extension/payment/mollie/helper.php");

class ModelExtensionPaymentMollieBase extends Model
{
	// Current module name - should be overwritten by subclass using one of the values below.
	const MODULE_NAME = NULL;

	/**
	 * @return Mollie_API_Client
	 */
	protected function getAPIClient ()
	{
		return MollieHelper::getAPIClient($this->config);
	}

	/**
	 * On the checkout page this method gets called to get information about the payment method.
	 *
	 * @param string $address
	 * @param float  $total
	 *
	 * @return array
	 */
	public function getMethod ($address, $total)
	{
		try
		{
			$payment_method = $this->getAPIClient()->methods->get(static::MODULE_NAME);

			// Quick checkout provides an array wile the default checkout provides only the total.
			$amount = is_array($total) ? $total[0]['value'] : round($total, 2);
			
			$minimum = $payment_method->getMinimumAmount();
			$maximum = $payment_method->getMaximumAmount();

			if ($minimum && $minimum > $amount)
			{
				return NULL;
			}

			if ($maximum && $maximum < $amount)
			{
				return NULL;
			}
		}
		catch (Mollie_API_Exception $e)
		{
			$this->log->write("Error retrieving payment method '" . static::MODULE_NAME . "' from Mollie: {$e->getMessage()}.");

			return NULL;
		}

		// Translate payment method (if a translation is available).
		$this->load->language("extension/payment/mollie");

		$key = "method_" . $payment_method->id;
		$val = $this->language->get($key);

		if ($key !== $val)
		{
			$payment_method->description = $val;
		}

		$icon = "";

		if ($this->config->get("mollie_show_icons"))
		{
			$icon = '<img src="' . htmlspecialchars($payment_method->image->normal) . '" height="20" style="margin:0 5px -5px 0" />';
		}

		return array(
			"code"       => "mollie_" . static::MODULE_NAME,
			"title"      => $icon . $payment_method->description,
			"sort_order" => $this->config->get("mollie_" . static::MODULE_NAME . "_sort_order"),
			"terms"      => NULL,
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
	public function setPayment ($order_id, $transaction_id)
	{
		if (!empty($order_id) && !empty($transaction_id))
		{
			$this->db->query(
				sprintf(
					"REPLACE INTO `%smollie_payments` (`order_id` ,`transaction_id`, `method`)
					 VALUES ('%s', '%s', 'idl')",
					DB_PREFIX,
					$this->db->escape($order_id),
					$this->db->escape($transaction_id)
				)
			);

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
	public function updatePayment ($transaction_id, $payment_status, $consumer = NULL)
	{
		if (!empty($transaction_id) && !empty($payment_status))
		{
			$this->db->query(
				sprintf(
					"UPDATE `%smollie_payments` 
					 SET `bank_status` = '%s'
					 WHERE `transaction_id` = '%s';",
					DB_PREFIX,
					$this->db->escape($payment_status),
					$this->db->escape($transaction_id)
				)
			);

			return $this->db->countAffected() > 0;
		}

		return FALSE;
	}
}
