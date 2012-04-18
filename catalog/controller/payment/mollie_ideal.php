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

require_once('ideal.class.php');

class ControllerPaymentMollieIdeal extends Controller
{

	/**
	 * This gets called by OpenCart at the checkout page and generates the paymentmethod
	 */
	protected function index()
	{
		// Load essential settings
		$this->load->model('checkout/order');
		$oinfo = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		// Create iDEAL object
		$ideal = new iDEAL_Payment($this->config->get('mollie_ideal_partnerid'));
		$ideal->setProfileKey($this->config->get('mollie_ideal_profilekey'));
		$ideal->setTestmode($this->config->get('mollie_ideal_testmode'));

		// Set template data
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['arr_banks'] = $ideal->getBanks();
		$this->data['action'] = $this->url->link('payment/mollie_ideal/payment');

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
	 * The payment action creates the iDEAL payment and redirects the customer to the selected bank
	 */
	public function payment()
	{
		// Load essential settings
		$this->load->model('checkout/order');
		$this->load->model('payment/mollie_ideal');
		$this->load->language('payment/mollie_ideal');

		// Create iDEAL object
		$ideal = new iDEAL_Payment($this->config->get('mollie_ideal_partnerid'));
		$ideal->setProfileKey($this->config->get('mollie_ideal_profilekey'));
		$ideal->setTestmode($this->config->get('mollie_ideal_testmode'));

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		// Assign required vars for createPayment
		$bank_id = $this->request->post['bank_id'];
		$amount = intval($this->currency->format($order_info['total'], "EUR", 100, false));
		$description = str_replace('%', $order_info['order_id'], html_entity_decode($this->config->get('mollie_ideal_description'), ENT_QUOTES, 'UTF-8'));
		$return_url = $this->url->link('payment/mollie_ideal/returnurl');
		$report_url = $this->url->link('payment/mollie_ideal/report');

		try
		{
			// Create the payment, if succeeded confirm the order and redirect the customer to the bank
			if ($ideal->createPayment($bank_id, $amount, $description, $return_url, $report_url))
			{
				$this->model_checkout_order->confirm($order_info['order_id'], "2", $this->language->get('text_redirected'));
				$this->model_payment_mollie_ideal->setOrder($order_info['order_id'], $ideal->getTransactionId());
				$this->redirect($ideal->getBankURL());
			}
			else
			{
				throw new Exception($ideal->getErrorMessage());
			}
		}
		catch (Exception $e)
		{
			echo("Kon geen betaling aanmaken, neem contact op met de beheerder.<br /><br/>
				Error melding voor de beheerder: " . $e->getMessage()
			);
		}
	}

	/**
	 * This action is getting called by Mollie to report the payment status
	 */
	public function report()
	{
		if (!empty($this->request->get['transaction_id']))
		{
			// Get transaction_id from URL
			$transactionId = $this->request->get['transaction_id'];

			// Create iDEAL object
			$ideal = new iDEAL_Payment($this->config->get('mollie_ideal_partnerid'));
			$ideal->setProfileKey($this->config->get('mollie_ideal_profilekey'));
			$ideal->checkPayment($transactionId);

			// Load essential settings
			$this->load->model('checkout/order');
			$this->load->model('payment/mollie_ideal');
			$this->load->language('payment/mollie_ideal');

			//Get order_id of this transaction from db
			$order = $this->model_payment_mollie_ideal->getOrderById($transactionId);

			if (!empty($order))
			{
				$order_info = $this->model_checkout_order->getOrder($order['order_id']);
				if($order_info['order_status_id'] == 2)
				{
					$amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) * 100;

					// Check if the order amount is the same as paid amount
					if (intval($ideal->getAmount()) == intval($amount))
					{
						switch ($ideal->getBankStatus())
						{
							case "Success":
								$this->model_checkout_order->update($order['order_id'], "15", $this->language->get('response_success'), true); // Processed
								break;
							case "Cancelled":
								$this->model_checkout_order->update($order['order_id'], "7", $this->language->get('response_cancelled'), true); // Canceled
								break;
							case "Failure":
								$this->model_checkout_order->update($order['order_id'], "10", $this->language->get('response_failed'), true); // Fail
								break;
							case "Expired":
								$this->model_checkout_order->update($order['order_id'], "14", $this->language->get('response_expired'), true); // Expired
								break;
							case "CheckedBefore":
								$this->model_checkout_order->update($order['order_id'], "", $this->language->get('response_checked'), false); // Already Checked
								break;
							default:
								$this->model_checkout_order->update($order['order_id'], "10", $this->language->get('response_unkown'), false); // Fail
								break;
						}
						$consumer = $ideal->getConsumerInfo();
						$this->model_payment_mollie_ideal->updateOrder($order['order_id'], $ideal->getBankStatus(), $consumer['consumerAccount']);
					}
					else
					{
						$this->model_checkout_order->update($order['order_id'], "10", $this->language->get('response_fraud'), false); // Fraude
					}
				}
			}
		}
	}

	/**
	 * Customer returning from the bank with an transaction_id
	 * Depending on what the state of the payment is they get redirected to the corresponding page
	 */
	public function returnurl()
	{
		$transactionId = $this->request->get['transaction_id'];

		if (!empty($transactionId))
		{
			$this->load->model('payment/mollie_ideal');

			$order = $this->model_payment_mollie_ideal->getOrderById($transactionId);

			if ($order['bank_status'] == "Success")
			{
				$this->redirect($this->url->link('checkout/success'));
			}
			else
			{
				$this->redirect($this->url->link('payment/mollie_ideal/fail'));
			}
		}
		else
		{
			$this->redirect($this->url->link('payment/mollie_ideal/fail'));
		}
	}

	/**
	 * The fail page gets generated if an payment has failed
	 */
	public function fail()
	{
		// Load essential settings
		$this->load->model('payment/mollie_ideal');
		$this->load->model('checkout/order');
		$this->load->language('payment/mollie_ideal');

		// Set template data
		$this->document->setTitle($this->language->get('ideal_title'));
		$this->data['heading_title'] = $this->language->get('ideal_title');
		$this->data['msg_failed'] = $this->language->get('msg_failed');

		// Breadcrumbs
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'href' => $this->url->link('common/home', (isset($this->session->data['token'])) ? 'token=' . $this->session->data['token'] : '', 'SSL'),
			'text' => $this->language->get('text_home'),
			'separator' => FALSE
		);

		// check if template exists
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/mollie_ideal_fail.tpl'))
		{
			$this->template = $this->config->get('config_template') . '/template/payment/mollie_ideal_fail.tpl';
		}
		else
		{
			$this->template = 'default/template/payment/mollie_ideal_fail.tpl';
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
}

?>