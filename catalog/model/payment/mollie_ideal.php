<?php

/**
 * Copyright (c) 2012, Mollie B.V.
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
 * @version     v4.0.0
 * @copyright   Copyright (c) 2012 Mollie B.V. (http://www.mollie.nl)
 * @license     http://www.opensource.org/licenses/bsd-license.php  Berkeley Software Distribution License (BSD-License 2)
 *
 **/

class ModelPaymentMollieIdeal extends Model
{

	/**
	 * On the checkout page this method gets called to get information about the payment method
	 * 
	 * @return array
	 */
	public function getMethod()
	{
		// Load language file
		$this->load->language('payment/mollie_ideal');

		$method_data = array(
			'code' => 'mollie_ideal',
			'title' => $this->language->get('text_title'),
			'sort_order' => $this->config->get('mollie_sort_order')
		);

		// return the method information
		return $method_data;
	}

	/**
	 * Get the orderId matching the transaction_id
	 * 
	 * @return int|bool
	 */
	public function getOrderById($transaction_id)
	{
		$q = $this->db->query(
			sprintf(
				"SELECT `order_id`, `bank_status`, `bank_account` 
				FROM `%smollie_payments` 
				WHERE `transaction_id` = '%s';",
				DB_PREFIX,
				mysql_real_escape_string($transaction_id)
			)
		);

		if ($q->num_rows > 0)
		{
			return $q->row;
		}

		return false;
	}

	/**
	 * While createPayment is in progress this method is getting called to store the order information
	 *
	 * @return bool
	 */
	public function setOrder($order_id, $transaction_id)
	{
		if (!empty($order_id) && !empty($transaction_id))
		{
			$q = $this->db->query(
				sprintf(
					"REPLACE INTO `%smollie_payments` (`order_id` ,`transaction_id`, `method`) 
					VALUES ('%s', '%s', 'idl')",
					DB_PREFIX,
					mysql_real_escape_string($order_id),
					mysql_real_escape_string($transaction_id)
				)
			);

			if ($this->db->countAffected() > 0)
			{
				return true;
			}

			return false;
		}

		return false;
	}

	/**
	 * While report method is in progress this method is getting called to update the order information and status
	 *
	 * @return bool
	 */
	public function updateOrder($order_id, $bank_status, $bank_account = NULL)
	{
		if (!empty($order_id) && !empty($bank_status))
		{
			$q = $this->db->query(
				sprintf(
					"UPDATE `%smollie_payments` 
					SET `bank_status` = '%s', `bank_account` = '%s'
					WHERE `order_id` = '%s';",
					DB_PREFIX,
					mysql_real_escape_string($bank_status),
					mysql_real_escape_string($bank_account),
					mysql_real_escape_string($order_id)
				)
			);

			if ($this->db->countAffected() > 0)
			{
				return true;
			}

			return false;
		}

		return false;
	}

}

?>