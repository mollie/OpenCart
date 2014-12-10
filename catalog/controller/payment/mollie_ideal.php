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
 * @property Log $log
 * @property Request $request
 * @property Response $response
 * @property Currency $currency
 * @property Url $url
 * @property ModelCheckoutOrder $model_checkout_order
 * @property ModelPaymentMollieIdeal $model_payment_mollie_ideal
 * @property Loader $load
 * @property Config $config
 * @property Language $language
 * @method render
 * @property $data
 * @property $document
 * @property $session
 */
class ControllerPaymentMollieIdeal extends Controller
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
	 * Get the order we are processing from OpenCart.
	 *
	 * @return array
	 */
	protected function getOpenCartOrder ()
	{
		$this->load->model('checkout/order');

		if (empty($this->session->data['order_id']))
		{
			return array();
		}

		// Load last order from session
		return $this->model_checkout_order->getOrder($this->session->data['order_id']);
	}

	/**
	 * This gets called by OpenCart at the checkout page and generates the payment method.
	 */
	public function index ()
	{
		$this->load->language('payment/mollie_ideal');
		$this->load->model('payment/mollie_ideal');

		// Set template data
		$data['action']  = $this->url->link('payment/mollie_ideal/payment', '', 'SSL');
		$data['message'] = $this->language;

		// Get the applicable payment methods.
		$order = $this->getOpenCartOrder();
		$payment_methods = $this->model_payment_mollie_ideal->getApplicablePaymentMethods($order['total']);

		$data["payment_methods"] = $payment_methods;
		$data['mollie_method']   = NULL;

		if (isset($this->session->data['mollie_method']))
		{
			$data['mollie_method'] = $this->session->data['mollie_method'];
		}

		// Check if view is at default template else use modified template path
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/mollie_checkout_form.tpl'))
		{
			$template = $this->config->get('config_template') . '/template/payment/mollie_checkout_form.tpl';
		}
		else
		{
			$template = 'default/template/payment/mollie_checkout_form.tpl';
		}

		// Return HTML output - it will get appended to confirm.tpl.
		return $this->renderTemplate($template, $data, array(), FALSE);
	}

	/**
	 * The payment action creates the payment and redirects the customer to the selected bank.
	 *
	 * It is called after the last customer performed "Step 6 Confirm Order".
	 */
	public function payment ()
	{
		if ($this->request->server['REQUEST_METHOD'] == 'POST')
		{
			// Load essentials
			$this->load->language('payment/mollie_ideal');
			$this->load->model('payment/mollie_ideal');

			$order  = $this->getOpenCartOrder();

			// Check if a payment already exists for this order and redirect to that paymentUrl if it is still open
			$payment_id = $this->model_payment_mollie_ideal->getTransactionIdByOrderId($order['order_id']);
			if ($payment_id)
			{
				$payment = $this->getApiClient()->payments->get($payment_id);

				if (!empty($payment))
				{
					// A payment for this order has already been created. If the status is still open, we can redirect to
					// this paymentUrl. Otherwise an error must be thrown.
					if ($payment->isOpen() && $order['order_status_id'] === $this->config->get('mollie_ideal_pending_status_id'))
					{
						$this->redirect($payment->getPaymentUrl());
					}
					else
					{
						$this->showErrorPage("A payment already exists for order id {$order['order_id']}.");
						return;
					}
				}
			}

			$amount = $this->currency->convert($order['total'], $this->config->get('config_currency'), 'EUR');

			$amount      = round($amount, 2);
			$description = str_replace('%', $order['order_id'], html_entity_decode($this->config->get('mollie_ideal_description'), ENT_QUOTES, 'UTF-8'));
			$return_url  = $this->url->link('payment/mollie_ideal/callback', '', 'SSL');
			$method      = !empty($this->request->post["mollie_method"]) ? $this->request->post["mollie_method"] : NULL;
			$issuer      = !empty($this->session->data["mollie_issuer"]) ? $this->session->data["mollie_issuer"] : NULL;

			try
			{
				// Create iDEAL object
				$api = $this->getApiClient();

				$data = array(
					"amount"            => $amount,
					"description"       => $description,
					"redirectUrl"       => $return_url,
					"webhookUrl"        => $this->getWebhookUrl(),
					"metadata"          => array(
						"order_id"          => $order["order_id"],
					),
					"method"            => $method,
					"issuer"            => $issuer,

					/*
					 * This data is sent along for credit card payments / fraud checks. You can remove this but you will
					 * have a higher conversion if you leave it here.
					 */

					"billingCity"       => $order['payment_city'],
					"billingRegion"     => $order['payment_zone'],
					"billingPostal"     => $order['payment_postcode'],
					"billingCountry"    => $order['payment_iso_code_2'],

					"shippingAddress"   => $order["shipping_address_1"]  ? $order["shipping_address_1"] : NULL,
					"shippingCity"      => $order["shipping_city"]       ? $order["shipping_city"] : $order["payment_city"],
					"shippingRegion"    => $order["shipping_zone"]       ? $order["shipping_zone"] : $order["payment_zone"],
					"shippingPostal"    => $order["shipping_postcode"]   ? $order["shipping_postcode"] : $order["payment_postcode"],
					"shippingCountry"   => $order["shipping_iso_code_2"] ? $order["shipping_iso_code_2"] : $order["payment_iso_code_2"],
				);

				// Create the payment, if succeeded confirm the order and redirect the customer to the bank
				try
				{
					$payment = $api->payments->create($data);
				}
				catch (Mollie_API_Exception $e)
				{
					// If it fails because of the webhookUrl then clear it and retry.
					if ($e->getField() == "webhookUrl")
					{
						unset($data["webhookUrl"]);
						$payment = $api->payments->create($data);
					}
					else
					{
						throw $e;
					}
				}
			}
			catch (Mollie_Api_Exception $e)
			{
				$this->showErrorPage($e->getMessage());
				return;
			}

			$this->addOrderHistory($order['order_id'], $this->config->get('mollie_ideal_pending_status_id'), $this->language->get('text_redirected'), FALSE); // Pending

			$this->model_payment_mollie_ideal->setPayment($order['order_id'], $payment->id);

			$this->redirect($payment->links->paymentUrl);
		}
	}

	/**
	 * This action is getting called by Mollie to report the payment status
	 */
	public function webhook ()
	{
		// Mollie called this webhook to verify if it was reachable
		if (!empty($this->request->get['testByMollie']))
		{
			// returns status 200
			return;
		}

		$payment_id = isset($this->request->post["id"]) ? $this->request->post["id"] : 0;

		if (empty($payment_id))
		{
			header("HTTP/1.0 400 Bad Request");
			echo "No id received";
			return;
		}

		$payment = $this->getApiClient()->payments->get($payment_id);

		// Load essentials
		$this->load->model('checkout/order');
		$this->load->model('payment/mollie_ideal');
		$this->load->language('payment/mollie_ideal');

		//Get order_id of this transaction from db
		$order    = $this->model_checkout_order->getOrder($payment->metadata->order_id);

		if (!empty($order))
		{
			// Only if the transaction is in 'pending' status
			if ($order['order_status_id'] === $this->config->get('mollie_ideal_pending_status_id'))
			{
				if ($payment->isPaid())
				{
					echo "The payment was received and the order was moved to the processing status.";
					$this->addOrderHistory($order['order_id'], $this->config->get('mollie_ideal_processing_status_id'), $this->language->get('response_success'), TRUE); // Processed
					return;
				}

				if ($payment->status == Mollie_API_Object_Payment::STATUS_CANCELLED)
				{
					echo "The payment was cancelled and the order was moved to the canceled status.";
					$this->addOrderHistory($order['order_id'], $this->config->get('mollie_ideal_canceled_status_id'), $this->language->get('response_cancelled'), FALSE); // Canceled
					return;
				}

				if ($payment->status == Mollie_API_Object_Payment::STATUS_EXPIRED)
				{
					echo "The payment was expired and the order was moved to the expired status.";
					$this->addOrderHistory($order['order_id'], $this->config->get('mollie_ideal_expired_status_id'), $this->language->get('response_expired'), FALSE); // Expired
					return;
				}

				echo "The payment failed for an unknown reason, order was updated.";

				$this->addOrderHistory($order['order_id'], $this->config->get('mollie_ideal_failed_status_id'), $this->language->get('response_unknown'), FALSE); // Fail
			}
			else
			{
				echo "The order was already processed before.";
			}
		}

		echo " Done.";
	}

	/**
	 * From the checkout form store the selected method into the session so we can adjust it on the final checkout page.
	 */
	public function set_checkout_method ()
	{
		$id   = !empty($this->request->post["mollie_method_id"]) ? $this->request->post["mollie_method_id"] : null;
		$desc = !empty($this->request->post["mollie_method_description"]) ? $this->request->post["mollie_method_description"] : '';

		$this->session->data['mollie_method'] = $id;
		$this->session->data['payment_methods']['mollie_ideal']['title'] = htmlspecialchars($desc);
	}

	/**
	 * From the checkout form store the selected issuer into the session so we can adjust it on the final checkout page.
	 */
	public function set_checkout_issuer ()
	{
		$id = !empty($this->request->post["mollie_issuer_id"]) ? $this->request->post["mollie_issuer_id"] : null;

		$this->session->data['mollie_issuer'] = $id;
	}

	/**
	 * Customer returning from the bank with an transaction_id
	 * Depending on what the state of the payment is they get redirected to the corresponding page
	 */
	public function callback ()
	{
		$order = $this->getOpenCartOrder();

		// Load required translations
		$this->load->language('payment/mollie_ideal');

		/*
		 * Now that the customer has returned to our web site, check if we already know if the payment has
		 * succeeded. If the payment is all good, we need to clear the cart.
		 */
		if ($order && $order["order_status_id"] == $this->config->get('mollie_ideal_processing_status_id'))
		{
			$this->redirect($this->url->link('checkout/success', '', 'SSL'));
			return;
		}
		/*
		 * When an order could be found, check if Mollie has reported the new status. When the order status is still
		 * pending, the report is not delivered yet.
		 */
		elseif ($order && $order['order_status_id'] == $this->config->get('mollie_ideal_pending_status_id'))
		{
			// Set template data
			$data['message_title'] = $this->language->get("heading_unknown");
			$data['message_text']  = $this->language->get("msg_unknown");

			if ($this->cart)
			{
				$this->cart->clear();
			}
		}
		/*
		 * When no order could be found the session has probably expired.
		 * The payment has failed.
		 */
		else
		{
			// Set template data
			$data['message_title'] = $this->language->get("heading_failed");
			$data['message_text']  = $this->language->get("msg_failed");
		}

		// Set template data
		$this->document->setTitle($this->language->get('ideal_title'));

		// Breadcrumbs
		$this->setBreadcrumbs($data);

		// Render HTML output
		$this->renderTemplate("mollie_ideal_return", $data, array(
			"column_left",
			"column_right",
			"content_top",
			"content_bottom",
			"footer",
			"header"
		));
	}

	/**
	 * @param &$data
	 */
	protected function setBreadcrumbs (&$data)
	{
		$data['breadcrumbs']   = array();
		$data['breadcrumbs'][] = array(
			'href'      => $this->url->link('common/home', (isset($this->session->data['token'])) ? 'token=' . $this->session->data['token'] : '', 'SSL'),
			'text'      => $this->language->get('text_home'),
			'separator' => FALSE
		);
	}

	/**
	 * @param $message
	 */
	protected function showErrorPage ($message)
	{
		$this->log->write("Error setting up transaction with Mollie: {$message}.");

		$data['mollie_error'] = $message;
		$data['message']      = $this->language;

		// Breadcrumbs
		$this->setBreadcrumbs($data);

		// Render HTML output
		$this->renderTemplate("mollie_payment_error", $data, array(
			"column_left",
			"column_right",
			"content_top",
			"content_bottom",
			"footer",
			"header"
		));
	}

	/**
	 * @return string|null
	 */
	public function getWebhookUrl ()
	{
		$webhook_url = str_replace("/admin", "", $this->url->link('payment/mollie_ideal/webhook', '', 'SSL'));

		return $webhook_url ? $webhook_url : NULL;
	}

	/**
	 * Map payment status history handling for different Opencart versions
	 *
	 * @param int|string $order_id
	 * @param int|string $order_status_id
	 * @param string     $comment
	 * @param bool       $notify
	 */
	protected function addOrderHistory ($order_id, $order_status_id, $comment = "", $notify = FALSE)
	{
		if ($this->isOpencart2())
		{
			$this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $comment, $notify);
		}
		else
		{
			if ($order_status_id === $this->config->get("mollie_ideal_pending_status_id"))
			{
				$this->model_checkout_order->confirm($order_id, $order_status_id, $comment, $notify);
			}
			else
			{
				$this->model_checkout_order->update($order_id, $order_status_id, $comment, $notify);
			}
		}
	}

	/**
	 * Map template handling for different Opencart versions
	 *
	 * @param string $template
	 * @param array  $data
	 * @param array  $common_children
	 * @param bool   $echo
	 */
	protected function renderTemplate ($template, $data, $common_children = array(), $echo = TRUE)
	{
		$template = $this->getTemplatePath($template);

		if ($this->isOpencart2())
		{
			foreach ($common_children as $child)
			{
				$data[$child] = $this->load->controller("common/".$child);
			}

			$html = $this->load->view($template, $data);
		}
		else
		{
			foreach ($data as $field => $value)
			{
				$this->data[$field] = $value;
			}

			$this->template = $template;

			$this->children = array(
				// Leave this line empty so vQmod's search & replace doesn't break our code (see vqmod/xml/vqmod_custom_positions.xml).
			);

			foreach ($common_children as $child)
			{
				$this->children[] = "common/".$child;
			}

			$html = $this->render();
		}

		if ($echo)
		{
			return $this->response->setOutput($html);
		}

		return $html;
	}

	/**
	 * @param  string $template
	 *
	 * @return string
	 */
	protected function getTemplatePath ($template)
	{
		$template = "template/payment/" . $template;

		if ($this->isOpencart2())
		{
			$template .= "_2";
		}

		// Check if template exists
		if (file_exists(DIR_TEMPLATE . $this->config->get("config_template") . "/" . $template . ".tpl"))
		{
			return $this->config->get("config_template") . "/" . $template . ".tpl";
		}

		return "default/" . $template . ".tpl";
	}

	/**
	 * @param string $url
	 * @param int    $status
	 */
	protected function redirect ($url, $status = 302)
	{
		if ($this->isOpencart2())
		{
			$this->response->redirect($url, $status);
		}
		else
		{
			parent::redirect($url, $status);
		}
	}

	/**
	 * @return bool
	 */
	protected function isOpencart2 ()
	{
		return version_compare(VERSION, 2, ">=");
	}
}
