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
 * @package     Mollie
 * @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://www.mollie.nl
 *
 * @property $config
 * @property $db
 * @property $language
 * @property $load
 * @property $log
 * @property $session
 * @property $url
 */
class ModelPaymentMollieIdeal extends Model
{
	/**
	 * Version of the plugin.
	 */
	const PLUGIN_VERSION = "5.2.0";

	/**
	 * @var Mollie_API_Client
	 */
	protected $_mollie_api_client;

	/**
	 * @return Mollie_API_Client
	 */
	public function getApiClient ()
	{
		if (empty($this->_mollie_api_client))
		{
			require_once DIR_APPLICATION . "/controller/payment/mollie-api-client/src/Mollie/API/Autoloader.php";

			$this->_mollie_api_client = new Mollie_API_Client;
			$this->_mollie_api_client->setApiKey($this->config->get('mollie_api_key'));
			$this->_mollie_api_client->addVersionString("OpenCart/" . VERSION);
			$this->_mollie_api_client->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);
		}

		return $this->_mollie_api_client;
	}

	/**
	 * @param float $total
	 * @return Mollie_API_Object_Method[]
	 */
	public function getApplicablePaymentMethods ($total)
	{
		$all_methods = $this->getApiClient()->methods->all();

		$applicable_methods = array();

		$amount = round($total, 2);

		// Load language file
		$this->load->language('payment/mollie_ideal');

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

			// Translate payment method (if a translation is available)
			$key = 'method_' . $payment_method->id;
			$val = $this->language->get($key);
			if ($key !== $val)
			{
				$payment_method->description = $val;
			}

			$applicable_methods[$payment_method->id] = $payment_method;
		}

		return $applicable_methods;
	}

	/**
	 * @param string $payment_method_id
	 * @return array
	 */
	public function getIssuersForMethod ($payment_method_id)
	{
		$issuers            = $this->getApiClient()->issuers->all();
		$issuers_for_method = array();

		foreach ($issuers as $issuer)
		{
			if ($issuer->method == $payment_method_id)
			{
				$issuers_for_method[] = $issuer;
			}
		}

		return $issuers_for_method;
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

			return array(
				'code'       => 'mollie_ideal',
				'title'      => '<span style="color: red;">Mollie error: ' .$e->getMessage() . '</span>',
				'sort_order' => $this->config->get('mollie_ideal_sort_order')
			);
		}

		$title = '';

		// Show the only Mollie payment method available.
		if (sizeof($payment_methods) == 1)
		{
			$payment_method = reset($payment_methods);
			$title = $payment_method->description;
		}
		// Multiple Mollie payment methods available.
		elseif (sizeof($payment_methods) > 1)
		{
			// FIX for extension Quick Checkout
			if (strpos($_SERVER['REQUEST_URI'], 'quickcheckout/payment_method/validate') !== FALSE)
			{
				foreach ($payment_methods as $payment_method)
				{
					if (isset($this->session->data['mollie_method']) && $this->session->data['mollie_method'] == $payment_method->id)
					{
						// Display correct payment method in admin and confirmation email
						$title = $payment_method->description;
						break;
					}
				}
			}
			else
			{
				// Load language file
				$this->load->language('payment/mollie_ideal');
				$title = $this->language->get('text_title') . " (";

				if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on'))
				{
					$base_url = $this->config->get('config_url');
				}
				else
				{
					$base_url = $this->config->get('config_ssl');
				}

				/**
				 * FIX for Joomla-based OpenCart.
				 */
				if (preg_match('~(/.+/com_opencart)/catalog~iu', __FILE__, $matches))
				{
					$base_url = $matches[1];
				}

				// Add some javascript to make it seem as if all Mollie methods are top level.
				$js  = PHP_EOL.'<script type="text/javascript" src="' . rtrim($base_url, '/') . '/catalog/view/javascript/mollie_methods.js"></script>'.PHP_EOL;
				$js .= '<script type="text/javascript">'.'(function ($) {'.PHP_EOL;

				$i       = 0;
				$checked = NULL;

				foreach ($payment_methods as $payment_method)
				{
					if ($i)
					{
						$title .= ", ";
					}

					if (isset($this->session->data['mollie_method']) && $this->session->data['mollie_method'] === $payment_method->id)
					{
						$checked = $i;
					}

					++$i;

					$title .= $payment_method->description;
					$js    .= "window.mollie_method_add('{$payment_method->id}', '{$payment_method->description}', '{$payment_method->image->normal}');".PHP_EOL;

					$issuers = $this->getIssuersForMethod($payment_method->id);

					foreach ($issuers as $issuer)
					{
						$issuer_selected = (isset($this->session->data['mollie_issuer']) && $this->session->data['mollie_issuer'] === $issuer->id) ? "true" : "false";

						$js .= "window.mollie_issuer_add('{$payment_method->id}', '{$issuer->id}', '{$issuer->name}', {$issuer_selected});".PHP_EOL;
					}
				}

				$title .= ")";

				$js .= 'window.mollie_methods_append("'.$this->url->link('payment/mollie_ideal/set_checkout_method', '', 'SSL').'", "'.$this->url->link('payment/mollie_ideal/set_checkout_issuer', '', 'SSL').'", "'.$this->language->get('text_issuer').'");'.PHP_EOL;
				$js .= '$(".mpm_issuer_rows").hide();'.PHP_EOL;

				if ($checked !== NULL)
				{
					// Select the Mollie payment method (and issuer row if any) saved to session earlier.
					$js .= '$("#mpm_'.$checked.'").prop("checked", true);'.PHP_EOL;
					$js .= 'window.mollie_display_issuers("mpm_'.$checked.'_issuer_row");'.PHP_EOL;
				}
				else
				{
					// Select either the method set to checked in raw HTML or the first method of the list. Note .click() on a Mollie method will fire window.mollie_method_select().
					$js .= '$(\'input[name="payment_method"]:checked, input[name="payment_method"]:first\').click();'.PHP_EOL;
				}

				$js .= "}) (window.jQuery || window.$);</script>";

				// We'd prefer adding JS through Document::addScript, but it is not supported for Payment modules at this point.
				if (!$this->config->get('mollie_ideal_no_js') && strpos($_SERVER['REQUEST_URI'], 'checkout/manual') === FALSE)
				{
					$title .= $js;
				}
			}
		}

		// No applicable payment method found
		if (empty($title))
		{
			return array();
		}

		return array(
			'code'       => 'mollie_ideal',
			'title'      => $title,
			'sort_order' => $this->config->get('mollie_ideal_sort_order')
		);
	}

	/**
	 * Get the transaction id matching the order id
	 *
	 * @param $order_id
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
	 * @param $order_id
	 * @param $transaction_id
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
	 * @param      $transaction_id
	 * @param      $payment_status
	 * @param      $consumer
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
