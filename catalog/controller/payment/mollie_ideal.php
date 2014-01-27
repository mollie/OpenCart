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
 * @version     v5.0.2
 * @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://www.mollie.nl
 *
 * @property Log $log
 * @property Request $request
 * @property Response $response
 * @property Url $url
 * @property ModelCheckoutOrder $model_checkout_order
 * @property ModelPaymentMollieIdeal $model_payment_mollie_ideal
 * @property Loader $load
 * @property Config $config
 * @property Language $language
 */
class ControllerPaymentMollieIdeal extends Controller
{
	/**
	 * Version of the plugin.
	 */
	const PLUGIN_VERSION = "v5.0.2";

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
	 * Get the order we are processing from OpenCart.
	 *
	 * @return array
	 */
	protected function getOpenCartOrder ()
	{
		$this->load->model('checkout/order');

		// Load last order from session
		return $this->model_checkout_order->getOrder($this->session->data['order_id']);
	}

	/**
	 * This gets called by OpenCart at the checkout page and generates the paymentmethod
	 */
	public function index ()
	{
		$this->load->language('payment/mollie_ideal');
		$this->load->model('payment/mollie_ideal');

		// Set template data
		$this->data['action']          = $this->url->link('payment/mollie_ideal/payment', '', 'SSL');
		$this->data['message']         = $this->language;

		// Get the applicable payment methods.
		$order = $this->getOpenCartOrder();
		$payment_methods = $this->model_payment_mollie_ideal->getApplicablePaymentMethods($order['total']);
		$this->data["payment_methods"] = $payment_methods;

		// Check if view is at default template else use modified template path
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/mollie_ideal_banks.tpl'))
		{
			$this->template = $this->config->get('config_template') . '/template/payment/mollie_ideal_banks.tpl';
		}
		else
		{
			$this->template = 'default/template/payment/mollie_ideal_banks.tpl';
		}

		// Render HTML output
		$this->render();
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

			$order = $this->getOpenCartOrder();

			$amount      = round(floatval($order['total']), 2);
			$description = str_replace('%', $order['order_id'], html_entity_decode($this->config->get('mollie_ideal_description'), ENT_QUOTES, 'UTF-8'));
			$return_url  = $this->url->link('payment/mollie_ideal/callback', '', 'SSL');
			$method      = isset($this->request->post["mollie_method"]) ? $this->request->post["mollie_method"] : NULL;

			try
			{
				// Create iDEAL object
				$api = $this->getApiClient();

				// Create the payment, if succeeded confirm the order and redirect the customer to the bank
				$payment = $api->payments->create(array(
					"amount"            => $amount,
					"description"       => $description,
					"redirectUrl"       => $return_url,
					"metadata"          => array(
						"order_id"          => $order["order_id"],
					),

					"method"            => $method,

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
				));
			}
			catch (Mollie_Api_Exception $e)
			{
				$this->log->write("Error setting up transaction with Mollie: {$e->getMessage()}.");

				$this->data['mollie_error'] = $e->getMessage();
				$this->data['message']      = $this->language;

				// check if template exists
				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/mollie_payment_error.tpl'))
				{
					$this->template = $this->config->get('config_template') . '/template/payment/mollie_payment_error.tpl';
				}
				else
				{
					$this->template = 'default/template/payment/mollie_payment_error.tpl';
				}

				$this->children = array(
					'common/column_left',
					'common/column_right',
					'common/content_top',
					'common/content_bottom',
					'common/footer',
					'common/header'
				);

				// Breadcrumbs
				$this->setBreadcrumbs();

				// Render HTML output
				$this->response->setOutput($this->render());
				return;
			}

			$this->model_checkout_order->confirm($order['order_id'], $this->config->get('mollie_ideal_processing_status_id'), $this->language->get('text_redirected'), FALSE);

			$this->load->model('payment/mollie_ideal');
			$this->model_payment_mollie_ideal->setPayment($order['order_id'], $payment->id);

			$this->redirect($payment->links->paymentUrl);
		}
	}

	/**
	 * This action is getting called by Mollie to report the payment status
	 */
	public function webhook ()
	{
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
			// Only if the transaction is in 'processing' status
			if ($order['order_status_id'] == $this->config->get('mollie_ideal_processing_status_id'))
			{
				if ($payment->isPaid())
				{
					echo "The payment was received and the order was moved to the processed status.";
					$this->model_checkout_order->update($order['order_id'], $this->config->get('mollie_ideal_processed_status_id'), $this->language->get('response_success'), TRUE); // Processed
					return;
				}

				if ($payment->status == Mollie_API_Object_Payment::STATUS_CANCELLED)
				{
					echo "The payment was cancelled and the order was moved to the canceled status.";
					$this->model_checkout_order->update($order['order_id'], $this->config->get('mollie_ideal_canceled_status_id'), $this->language->get('response_cancelled'), FALSE); // Canceled
					return;
				}

				if ($payment->status == Mollie_API_Object_Payment::STATUS_EXPIRED)
				{
					echo "The payment was expired and the order was moved to the expired status.";
					$this->model_checkout_order->update($order['order_id'], $this->config->get('mollie_ideal_expired_status_id'), $this->language->get('response_expired'), FALSE); // Expired
					return;
				}

				echo "The payment failed for an unknown reason, order was updated.";
				$this->model_checkout_order->update($order['order_id'], $this->config->get('mollie_ideal_failed_status_id'), $this->language->get('response_unknown'), FALSE); // Fail
			}
			else
			{
				echo "The order was already processed before.";
			}
		}

		echo " Done.";
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
		 * succeeded. It the payment is all good, we need to clear the cart.
		 */
		if ($order && $order["order_status_id"] == $this->config->get('mollie_ideal_processed_status_id'))
		{
			$this->redirect($this->url->link('checkout/success'));
			return;
		}
		/*
		 * When no order could be found the session has probably expired
		 * When an order could be found, check if Mollie has reported the new status. When the order status is still
		 * processing, the report is not delivered yet.
		 */
		elseif (!$order || $order['order_status_id'] == $this->config->get('mollie_ideal_processing_status_id'))
		{
			// Set template data
			$this->data['message_title']   = $this->language->get("heading_unknown");
			$this->data['message_text']    = $this->language->get("msg_unknown");
		}
		/*
		 * The payment has failed. We will allow the customer a retry.
		 */
		else
		{
			// Set template data
			$this->data['message_title']   = $this->language->get("heading_failed");
			$this->data['message_text']    = $this->language->get("msg_failed");
		}

		// Set template data
		$this->document->setTitle($this->language->get('ideal_title'));

		// Breadcrumbs
		$this->setBreadcrumbs();

		// check if template exists
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/mollie_ideal_return.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/mollie_ideal_return.tpl';
		} else {
			$this->template = 'default/template/payment/mollie_ideal_return.tpl';
		}

		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);

		// Render HTML output
		$this->response->setOutput($this->render());
	}

	protected function setBreadcrumbs ()
	{
		$this->data['breadcrumbs']   = array();
		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('common/home', (isset($this->session->data['token'])) ? 'token=' . $this->session->data['token'] : '', 'SSL'),
			'text'      => $this->language->get('text_home'),
			'separator' => FALSE
		);
	}
}
