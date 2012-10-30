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
 * @version     v4.4
 * @copyright   Copyright (c) 2012 Mollie B.V. (http://www.mollie.nl)
 * @license     http://www.opensource.org/licenses/bsd-license.php  Berkeley Software Distribution License (BSD-License 2)
 * 
 **/

require_once('ideal.class.php');

class ControllerPaymentMollieIdeal extends Controller
{
	/**
	 * @param $mollie_ideal_partnerid
	 * @codeCoverageIgnore
	 * @return iDEAL_Payment
	 */
	protected function getIdealPaymentObject($mollie_ideal_partnerid)
	{
		return new iDEAL_Payment($mollie_ideal_partnerid);
	}

	/**
	 * This gets called by OpenCart at the checkout page and generates the paymentmethod
	 */
	public function index ()
	{
		// Create iDEAL object
		$ideal = $this->getIdealPaymentObject($this->config->get('mollie_ideal_partnerid'));
		$ideal->setProfileKey($this->config->get('mollie_ideal_profilekey'));
		$ideal->setTestmode($this->config->get('mollie_ideal_testmode'));

		// Set template data
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['banks']          = $ideal->getBanks();
		$this->data['action']         = $this->url->link('payment/mollie_ideal/payment', '', 'SSL');

		// Check if view is at default template else use modified template path
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/mollie_ideal_banks.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/mollie_ideal_banks.tpl';
		} else {
			$this->template = 'default/template/payment/mollie_ideal_banks.tpl';
		}

		// Render HTML output
		$this->render();
	}

	/**
	 * The payment action creates the iDEAL payment and redirects the customer to the selected bank
	 */
	public function payment ()
	{
		if ($this->request->server['REQUEST_METHOD'] == 'POST')
		{
			// Load essentials
			$this->load->model('checkout/order');
			$this->load->model('payment/mollie_ideal');
			$this->load->language('payment/mollie_ideal');

			// Create iDEAL object
			$ideal = $this->getIdealPaymentObject($this->config->get('mollie_ideal_partnerid'));
			$ideal->setProfileKey($this->config->get('mollie_ideal_profilekey'));
			$ideal->setTestmode($this->config->get('mollie_ideal_testmode'));

			if (isset($this->request->post['transaction_id']))
			{
				// Load failed order and payment
				$payment = $this->model_payment_mollie_ideal->getPaymentById($this->request->post['transaction_id']);
				$order   = $this->model_payment_mollie_ideal->getOrderById($payment['order_id']);
			} else {
				// Load last order from session
				$order = $this->model_payment_mollie_ideal->getOrderById($this->session->data['order_id']);
			}

			// Assign required vars for createPayment
			$bank_id     = $this->request->post['bank_id'];
			$amount      = intval(round($order['total'] * 100));
			$description = str_replace('%', $order['order_id'], html_entity_decode($this->config->get('mollie_ideal_description'), ENT_QUOTES, 'UTF-8'));
			$return_url  = $this->url->link('payment/mollie_ideal/status', '', 'SSL');
			$report_url  = $this->url->link('payment/mollie_ideal/report', '', 'SSL');

			try
			{
				// Create the payment, if succeeded confirm the order and redirect the customer to the bank
				if ($ideal->createPayment($bank_id, $amount, $description, $return_url, $report_url))
				{
					if (isset($this->request->post['transaction_id'])) {
						$this->model_checkout_order->update($order['order_id'], $this->config->get('mollie_ideal_processing_status_id'), $this->language->get('text_redirected'), FALSE);
					} else {
						$this->model_checkout_order->confirm($order['order_id'], $this->config->get('mollie_ideal_processing_status_id'), $this->language->get('text_redirected'), FALSE);
					}

					$this->model_payment_mollie_ideal->setPayment($order['order_id'], $ideal->getTransactionId());
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

				global $log;

				if ($this->config->get('config_error_log')) {
					$log->write('PHP ' . $e->getCode() . ':  ' . $e->getMessage() . ' in ' . __FILE__ . ' on line ' . __LINE__);
				}
			}
		}
	}

	/**
	 * This action is getting called by Mollie to report the payment status
	 */
	public function report ()
	{
		if (!empty($this->request->get['transaction_id']))
		{
			// Create iDEAL object
			$ideal = $this->getIdealPaymentObject($this->config->get('mollie_ideal_partnerid'));
			$ideal->setProfileKey($this->config->get('mollie_ideal_profilekey'));

			// Get transaction_id from URL
			$transaction_id = $this->request->get['transaction_id'];

			// Check payment
			$ideal->checkPayment($transaction_id);

			// Load essentials
			$this->load->model('checkout/order');
			$this->load->model('payment/mollie_ideal');
			$this->load->language('payment/mollie_ideal');

			//Get order_id of this transaction from db
			$payment  = $this->model_payment_mollie_ideal->getPaymentById($transaction_id);
			$order    = $this->model_payment_mollie_ideal->getOrderById($payment['order_id']);
			$consumer = NULL;

			if (!empty($order))
			{
				// Only if the transaction is in 'processing' status
				if ($order['order_status_id'] == $this->config->get('mollie_ideal_processing_status_id') && $ideal->getBankStatus() != ModelPaymentMollieIdeal::BANK_STATUS_CHECKEDBEFORE)
				{
					$amount = intval(round($order['total'] * 100));

					// Check if the order amount is the same as paid amount
					if ($amount == $ideal->getAmount())
					{
						switch ($ideal->getBankStatus())
						{
							case ModelPaymentMollieIdeal::BANK_STATUS_SUCCESS:
								$consumer = $ideal->getConsumerInfo();
								$this->model_checkout_order->update($order['order_id'], $this->config->get('mollie_ideal_processed_status_id'), $this->language->get('response_success'), TRUE); // Processed
								break;
							case ModelPaymentMollieIdeal::BANK_STATUS_CANCELLED:
								$this->model_checkout_order->update($order['order_id'], $this->config->get('mollie_ideal_canceled_status_id'), $this->language->get('response_cancelled'), FALSE); // Canceled
								break;
							case ModelPaymentMollieIdeal::BANK_STATUS_FAILURE:
								$this->model_checkout_order->update($order['order_id'], $this->config->get('mollie_ideal_failed_status_id'), $this->language->get('response_failed'), TRUE); // Fail
								break;
							case ModelPaymentMollieIdeal::BANK_STATUS_EXPIRED:
								$this->model_checkout_order->update($order['order_id'], $this->config->get('mollie_ideal_expired_status_id'), $this->language->get('response_expired'), FALSE); // Expired
								break;
							default:
								$this->model_checkout_order->update($order['order_id'], $this->config->get('mollie_ideal_failed_status_id'), $this->language->get('response_unkown'), FALSE); // Fail
								break;
						}
					}
					else
					{
						$this->model_checkout_order->update($order['order_id'], $this->config->get('mollie_ideal_failed_status_id'), $this->language->get('response_fraud'), FALSE); // Fraude
					}

					$this->model_payment_mollie_ideal->updatePayment($payment['transaction_id'], $ideal->getBankStatus(), $consumer);
				}
				else
				{
					$this->model_checkout_order->update($order['order_id'], $this->config->get('mollie_ideal_failed_status_id'), $this->language->get('response_failed'), TRUE); // Fail
				}
			}
		}
	}

	/**
	 * Customer returning from the bank with an transaction_id
	 * Depending on what the state of the payment is they get redirected to the corresponding page
	 */
	public function status ()
	{
		if (!empty($this->request->get['transaction_id']))
		{
			// transaction id
			$transaction_id = $this->request->get['transaction_id'];

			// Create iDEAL object
			$ideal = $this->getIdealPaymentObject($this->config->get('mollie_ideal_partnerid'));
			$ideal->setProfileKey($this->config->get('mollie_ideal_profilekey'));
			$ideal->setTestmode($this->config->get('mollie_ideal_testmode'));

			// Load essential settings
			$this->load->model('payment/mollie_ideal');
			$this->load->language('payment/mollie_ideal');

			$payment = $this->model_payment_mollie_ideal->getPaymentById($transaction_id);

			/*
			 * Now that the customer has returned to our web site, check if we already know if the payment has
			 * succeeded. It the payment is all good, we need to clear the cart.
			 */
			if (isset($payment['bank_status']) && $payment['bank_status'] == ModelPaymentMollieIdeal::BANK_STATUS_SUCCESS)
			{
				/** @var $this->cart Cart */
				$this->cart->clear();
			}

			$order   = $this->model_payment_mollie_ideal->getOrderById($payment['order_id']);

			// Set template data
			$this->document->setTitle($this->language->get('ideal_title'));
			$this->data['payment'] = $payment;
			$this->data['order']   = $order;
			$this->data['message'] = $this->language;
			$this->data['banks']   = $ideal->getBanks();
			$this->data['action']  = $this->url->link('payment/mollie_ideal/payment', '', 'SSL');

			// Breadcrumbs
			$this->data['breadcrumbs']   = array();
			$this->data['breadcrumbs'][] = array(
				'href'      => $this->url->link('common/home', (isset($this->session->data['token'])) ? 'token=' . $this->session->data['token'] : '', 'SSL'),
				'text'      => $this->language->get('text_home'),
				'separator' => FALSE
			);

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
	}

}

?>