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
 * @property Config   $config
 * @property DB       $db
 * @property Language $language
 * @property Loader   $load
 * @property Log      $log
 * @property Registry $registry
 * @property Session  $session
 * @property URL      $url
 */
class ModelPaymentMollieIdeal extends Model
{
	/**
	 * Version of the plugin.
	 */
	const PLUGIN_VERSION = "5.2.3";

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
	 * @param  float                      $total
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
	 * @param  string $payment_method_id
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
	 * @param  string $address
	 * @param  float  $total
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
				"code"       => "mollie_ideal",
				"title"      => '<span style="color: red;">Mollie error: ' .$e->getMessage() . '</span>',
				"sort_order" => $this->config->get("mollie_ideal_sort_order"),
				"terms"      => NULL
			);
		}

		// Add a piece of JavaScript to the output buffer.
		$this->setPreOutput($this->getMethodJavaScript($payment_methods));

		$title = NULL;

		// Attempt to find the saved payment method title.
		foreach ($payment_methods as $payment_method)
		{
			if (isset($this->session->data['mollie_method']) && $this->session->data['mollie_method'] == $payment_method->id)
			{
				$title = $payment_method->description;
				break;
			}
		}

		if (!$title)
		{
			if (sizeof($payment_methods) === 1)
			{
				// If no payment method was selected earlier, use the first available payment method's title.
				$payment_method = reset($payment_methods);
				$title          = $payment_method->description;
			}
			else
			{
				// Fall back to the generic module title otherwise.
				$this->load->language("payment/mollie_ideal");
				$title = $this->language->get("text_title");

				// As a last resort, see if we can append payment method titles to the module title.
				if (sizeof($payment_methods) > 1)
				{
					$title_append = array();

					foreach ($payment_methods as $payment_method)
					{
						$title_append[] = $payment_method->description;
					}

					$title .= " (".implode(", ", $title_append).")";
				}
			}
		}

		return array(
			"code"       => "mollie_ideal",
			"title"      => $title,
			"sort_order" => $this->config->get("mollie_ideal_sort_order"),
			"terms"      => NULL
		);
	}

	/**
	 * @param  array  $payment_methods
	 * @return string
	 */
	protected function getMethodJavaScript ($payment_methods)
	{
		if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != "on"))
		{
			$base_url = $this->config->get("config_url");
		}
		else
		{
			$base_url = $this->config->get("config_ssl");
		}

		// Fix for Joomla-based Opencart.
		if (preg_match('~(/.+/com_opencart)/catalog~iu', __FILE__, $matches))
		{
			$base_url = $matches[1];
		}

		$method_report_url = $this->url->link("payment/mollie_ideal/set_checkout_method", "", "SSL");
		$issuer_report_url = $this->url->link("payment/mollie_ideal/set_checkout_issuer", "", "SSL");
		$issuer_text       = $this->language->get("text_issuer");

		// Add some javascript to make it seem like all Mollie methods are top level.
		$js  = '<script type="text/javascript" src="'.rtrim($base_url, "/").'/catalog/view/javascript/mollie_methods.js"></script>'.PHP_EOL;
		$js .= '<script type="text/javascript">'.PHP_EOL;
		$js .= 'init_mollie_methods = function () {'.PHP_EOL;
		$js .= '$ = window.jQuery || window.$;'.PHP_EOL;
		$js .= '$(function() {'.PHP_EOL;
		$js .= 'var mollie = new window.mollie_methods($, "'.$method_report_url.'", "'.$issuer_report_url.'", "'.$issuer_text.'");'.PHP_EOL;

		foreach ($payment_methods as $payment_method)
		{
			$method_selected = intval(isset($this->session->data['mollie_method']) && $this->session->data['mollie_method'] === $payment_method->id);

			$js .= 'mollie.addMethod("'.$payment_method->id.'", "'.$payment_method->description.'", "'.$payment_method->image->normal.'", '.$method_selected.');'.PHP_EOL;

			$issuers = $this->getIssuersForMethod($payment_method->id);

			foreach ($issuers as $issuer)
			{
				$issuer_selected = intval(isset($this->session->data['mollie_issuer']) && $this->session->data['mollie_issuer'] === $issuer->id);

				$js .= 'mollie.addIssuer("'.$payment_method->id.'", "'.$issuer->id.'", "'.$issuer->name.'", '.$issuer_selected.');'.PHP_EOL;
			}
		}

		$js .= 'mollie.appendMethods();'.PHP_EOL;

		$js .= '});'.PHP_EOL;
		$js .= '};'.PHP_EOL;

		// Add to onload just in case jQuery isn't loaded yet. Call directly otherwise. These lines are needed because we have no idea when our JS will be called in various checkout modules.
		$js .= 'if (document.readyState === "loading") {'.PHP_EOL;
		$js .= 'window.onload = init_mollie_methods;'.PHP_EOL;
		$js .= '} else {'.PHP_EOL;
		$js .= 'init_mollie_methods();'.PHP_EOL;
		$js .= '}'.PHP_EOL;

		$js .= '</script>';

		return $js;
	}

	/**
	 * Overwrite the Response object in order to prepend a JS file to the output buffer. Really hacky, but Opencart won't allow us to load scripts yet.
	 *
	 * @param string $prepend
	 */
	protected function setPreOutput ($prepend)
	{
		// Quickcheckout makes things worse by overwriting the entire checkout. Just echo the JS.
		if ($this->isRoute("checkout/checkout"))
		{
			$conf = $this->config->get("quickcheckout");
			if (!empty($conf) && intval($conf['general']['enable']) && intval($conf['general']['main_checkout']))
			{
				echo $prepend;
			}

			return;
		}

		// For the regular checkout and Onecheckout, hijack the response object (really ugly).
		if ($this->isRoute("checkout/payment_method") || $this->isRoute("onecheckout/payment"))
		{
			global $response;

			$response = new ModelPaymentMollieIdealResponse;

			$response->addHeader("Content-Type: text/html; charset=utf-8");
			$response->setCompression($this->config->get("config_compression"));
			$response->setPreOutput($prepend);

			$this->registry->set("response", $response);
		}
	}

	// Check if we're on the right page. Required for JavaScript hack.
	protected function isRoute ($route)
	{
		// Try to fetch the route with a SERVER global fallback.
		if (isset($this->registry->request->get['route']) && mb_strlen($this->registry->request->get['route']))
		{
			return ($route === $this->registry->request->get['route']);
		}

		if (isset($_GET['route']) && mb_strlen($_GET['route']))
		{
			return ($route === $_GET['route']);
		}

		return FALSE;
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


/**
 * Hacky Response extension to ensure prepending HTML to output buffer. See ModelPaymentMollieIdeal::setPreOutput().
 */
class ModelPaymentMollieIdealResponse extends Response
{
	private $prepend;

	public function setPreOutput ($prepend)
	{
		$this->prepend = $prepend;
	}

	public function setOutput ($output)
	{
		if ($this->prepend)
		{
			// Test for JSON objects.
			$json = json_decode($output);

			if ($json !== NULL)
			{
				// Support for Onecheckout module.
				if(is_object($json) && isset($json->output))
				{
					$json->output = $this->prepend . $json->output;
				}
				elseif(is_array($json) && isset($json['output']))
				{
					$json['output'] = $this->prepend . $json['output'];
				}

				$output = json_encode($json);
			}
			else
			{
				$output = $this->prepend . $output;
			}

			// Use the prepended output only once.
			$this->prepend = NULL;
		}

		parent::setOutput($output);
	}
}
