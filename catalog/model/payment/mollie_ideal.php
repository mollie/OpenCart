<?php

/**
 * Copyright (c) 2012-2014, Mollie B.V.
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
 * @version     v5.0.3
 * @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://www.mollie.nl
 */
class ModelPaymentMollieIdeal extends Model
{
	/**
	 * Version of the plugin.
	 */
	const PLUGIN_VERSION = "v5.0.3";

	/**
	 * @var Mollie_API_Client
	 */
	private $mollie_api_client;

	/**
	 * @codeCoverageIgnore
	 * @return Mollie_API_Client
	 */
	protected function getApiClient()
	{
		if (empty($this->mollie_api_client))
		{
			require_once DIR_APPLICATION . "/controller/payment/mollie-api-client/src/Mollie/API/Autoloader.php";

			$this->mollie_api_client = new Mollie_API_Client();
			$this->mollie_api_client->setApiKey($this->config->get('mollie_api_key'));
			$this->mollie_api_client->addVersionString("OpenCart/" . VERSION);
			$this->mollie_api_client->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);
		}

		return $this->mollie_api_client;
	}

	/**
	 * @return Mollie_API_Object_Method[]
	 * @param float $total
	 */
	public function getApplicablePaymentMethods($total)
	{
		$all_methods = $this->getApiClient()->methods->all();

		$applicable_methods = array();

		$amount = round($total, 2);

		foreach ($all_methods as $payment_method)
		{
			if ($payment_method->getMinimumAmount() && $payment_method->getMinimumAmount() > $amount)
			{
				continue;
			}

			if ($payment_method->getMaximumAmount() && $payment_method->getMaximumAmount() < $amount)
			{
				continue;
			}

			$applicable_methods[] = $payment_method;
		}

		return $applicable_methods;
	}

	/**
	 * On the checkout page this method gets called to get information about the payment method.
	 *
	 * @param $address
	 * @param float $total
	 * @return array
	 */
	public function getMethod ($address, $total)
	{
		try
		{
			$payment_methods = $this->getApplicablePaymentMethods($total);
		}
		catch (Mollie_API_Exception $e)
		{
			$this->log->write("Error retrieving all payments methods from Mollie: {$e->getMessage()}.");

			return 		$method_data = array(
				'code'       => 'mollie_ideal',
				'title'      => '<span style="color: red;">Mollie error: ' .$e->getMessage() . '</span>',
				'sort_order' => $this->config->get('mollie_ideal_sort_order')
			);
		}

		if (sizeof($payment_methods) == 1)
		{
			$title = $payment_methods[0]->description;
		}
		elseif (sizeof($payment_methods) > 1)
		{
			// Load language file
			$this->load->language('payment/mollie_ideal');
			$title = $this->language->get('text_title') . " (";

			foreach ($payment_methods as $index => $payment_method)
			{
				if ($index +1 == sizeof($payment_methods))
				{
					$title .= " & ";
				}
				elseif ($index > 0)
				{
					$title .= ", ";
				}

				$title .= $payment_method->description;
			}

			$title .= ")";

		}
		else
		{
			// Payment not possible via Mollie.
			return array();
		}

		$method_data = array(
			'code'       => 'mollie_ideal',
			'title'      => $title,
			'sort_order' => $this->config->get('mollie_ideal_sort_order')
		);

		// return the method information
		return $method_data;
	}

	/**
	 * Get the transaction id matching the order id
	 *
	 * @return int|bool
	 */
	public function getTransactionIdByOrderId ($order_id)
	{
		$q = $this->db->query(
			sprintf(
				"SELECT transaction_id
				 FROM `%1\$smollie_payments`
				 WHERE `order_id` = '%2\$d'",
				DB_PREFIX,
				$this->db->escape($order_id)
			)
		);

		if ($q->num_rows > 0)
		{
			return $q->row["transaction_id"];
		}
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
	 * While report method is in progress this method is getting called to update the order information and status
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
