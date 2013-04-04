<?php

/**
 * Copyright (c) 2013, Mollie B.V.
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
 * @category    Mollie
 * @package     Mollie_Ideal
 * @author      Mollie B.V. (info@mollie.nl)
 * @version     v4.9
 * @copyright   Copyright (c) 2013 Mollie B.V. (http://www.mollie.nl)
 * @license     http://www.opensource.org/licenses/bsd-license.php  Berkeley Software Distribution License (BSD-License 2)
 *
 **/

class ModelPaymentMollieIdeal extends Model
{

	const MOD_VERSION               = '4.9';

	// The possible bank return states
	const BANK_STATUS_SUCCESS       = 'Success';
	const BANK_STATUS_CANCELLED     = 'Cancelled';
	const BANK_STATUS_FAILURE       = 'Failure';
	const BANK_STATUS_EXPIRED       = 'Expired';
	const BANK_STATUS_CHECKEDBEFORE = 'CheckedBefore';

	/**
	 * On the checkout page this method gets called to get information about the payment method
	 * 
	 * @return array
	 */
	public function getMethod ($address, $total)
	{
		// Load language file
		$this->load->language('payment/mollie_ideal');

		$method_data = array(
			'code'       => 'mollie_ideal',
			'title'      => $this->language->get('text_title'),
			'sort_order' => $this->config->get('mollie_ideal_sort_order')
		);

		// return the method information
		return $method_data;
	}

	public function getOrderById ($order_id)
	{
		if (!empty($order_id))
		{
			$this->load->model('checkout/order');

			return $this->model_checkout_order->getOrder($order_id);
		}

		return FALSE;
	}

	/**
	 * Get the orderId matching the transaction_id
	 * 
	 * @return int|bool
	 */
	public function getPaymentById ($transaction_id)
	{
		if (!empty($transaction_id))
		{
			$q = $this->db->query(
				sprintf(
					"SELECT *
					 FROM `%smollie_payments`
					 WHERE `transaction_id` = '%s'",
					 DB_PREFIX,
					 $this->db->escape($transaction_id)
				)
			);

			if ($q->num_rows > 0) {
				return $q->row;
			}
		}

		return FALSE;
	}

	/**
	 * While createPayment is in progress this method is getting called to store the order information
	 *
	 * @return bool
	 */
	public function setPayment ($order_id, $transaction_id)
	{
		if (!empty($order_id) && !empty($transaction_id))
		{
			$this->db->query(
				sprintf(
					"REPLACE INTO `%smollie_payments` (`order_id` ,`transaction_id`, `method`, `created_at`)
					 VALUES ('%s', '%s', 'idl', '%s')",
					 DB_PREFIX,
					 $this->db->escape($order_id),
					 $this->db->escape($transaction_id),
					 $this->db->escape($this->getCurrentDate())
				)
			);

			if ($this->db->countAffected() > 0) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @return string
	 */
	protected function getCurrentDate ()
	{
		return date("Y-m-d H:i:s");
	}

	/**
	 * While report method is in progress this method is getting called to update the order information and status
	 *
	 * @return bool
	 */
	public function updatePayment ($transaction_id, $bank_status, array $consumer = NULL)
	{
		if (!empty($transaction_id) && !empty($bank_status))
		{
			$this->db->query(
				sprintf(
					"UPDATE `%smollie_payments` 
					 SET `bank_status` = '%s', `bank_account` = '%s', `updated_at` = '%s'
					 WHERE `transaction_id` = '%s';",
					 DB_PREFIX,
					 $this->db->escape($bank_status),
					 $this->db->escape($consumer['consumerAccount']),
					 $this->db->escape($this->getCurrentDate()),
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
	 * Get the amount of the order in cents, make sure that we return the right value even if the locale is set to
	 * something different than the default (e.g. nl_NL).
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @return int
	 */
	public function getAmountInCents (ModelCheckoutOrder $order)
	{
		$grand_total = $order['total'];

		if (is_string($grand_total))
		{
			$locale_info = localeconv();

			if ($locale_info['decimal_point'] !== '.')
			{
				$grand_total = strtr($grand_total, array(
					$locale_info['thousands_sep'] => '',
					$locale_info['decimal_point'] => '.',
				));
			}

			$grand_total = floatval($grand_total); // Why U NO work with locales?
		}

		return intval(round(100 * $grand_total));
	}

}

?>