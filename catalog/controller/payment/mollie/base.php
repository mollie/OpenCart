<?php
/**
 * Copyright (c) 2012-2017, Mollie B.V.
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
 * @property Config $config
 * @property Currency $currency
 * @property array $data
 * @property Document $document
 * @property Language $language
 * @property Loader $load
 * @property Log $log
 * @property ModelCheckoutOrder $model_checkout_order
 * @property Request $request
 * @property Response $response
 * @property Session $session
 * @property URL $url
 *
 * @method render
 */

use Mollie\Api\MollieApiClient;
use Mollie\Api\Types\PaymentStatus;
require_once(dirname(DIR_SYSTEM) . "/catalog/controller/payment/mollie/helper.php");

class ControllerPaymentMollieBase extends Controller
{
	// Current module name - should be overwritten by subclass using one of the values below.
	const MODULE_NAME = null;

	/**
	 * @return MollieAPIClient
	 */
	protected function getAPIClient()
	{
		return MollieHelper::getAPIClient($this->config);
	}

	/**
	 *
	 * Keep a log of Mollie transactions.
	 *
	 * @param $line string
	 * @param $alsoEcho bool
	 */
	protected function writeToMollieLog($line, $alsoEcho = false)
	{
		$log = new Log('Mollie.log');
		$log->write($line);
		if ($alsoEcho) echo $line;
	}

	/**
	 * @return ModelExtensionPaymentMollieBase
	 */
	protected function getModuleModel()
	{
		$model_name = "model_payment_mollie_" . static::MODULE_NAME;

		if (!isset($this->$model_name)) {
			$this->load->model("payment/mollie_" . static::MODULE_NAME);
		}

		return $this->$model_name;
	}

	/**
	 * @return bool
	 */
	protected function getOrderID()
	{
		if (empty($this->session->data['order_id']) && !isset($this->request->get['order_id'])) {
			return false;
		}
		if (isset($this->request->get['order_id'])) {
			return $this->request->get['order_id'];
		}
		return $this->session->data['order_id'];
	}

	/**
	 * Get the order we are processing from OpenCart.
	 *
	 * @return array
	 */
	protected function getOpenCartOrder($order_id)
	{
		$this->load->model("checkout/order");
		// Load last order from session
		return $this->model_checkout_order->getOrder($order_id);
	}

	/**
	 * This gets called by OpenCart at the final checkout step and should generate a confirmation button.
	 * @return string
	 */
	public function index()
	{
		$this->load->language("payment/mollie");

		$payment_method = $this->getAPIClient()->methods->get(static::MODULE_NAME, array('include' => 'issuers'));

		// Set template data.
		$data['action'] = $this->url->link("payment/mollie_" . static::MODULE_NAME . "/payment", "", "SSL");
		$data['image'] = $payment_method->image->size1x;
		$data['message'] = $this->language;
		$data['issuers'] = isset($payment_method->issuers) ? $payment_method->issuers : array();
		$data['text_issuer'] = $this->language->get("text_issuer_" . static::MODULE_NAME);
		$data['set_issuer_url'] = $this->url->link("payment/mollie_" . static::MODULE_NAME . "/set_issuer", "", "SSL");

		// Return HTML output - it will get appended to confirm.tpl.
		return $this->renderTemplate('mollie_checkout_form', $data, array(), false);
	}

	/**
	 * The payment action creates the payment and redirects the customer to the selected bank.
	 *
	 * It is called when the customer submits the button generated in the mollie_checkout_form template.
	 */
	public function payment()
	{
		if ($this->request->server['REQUEST_METHOD'] != "POST") {
			return;
		}
		try {
			$api = $this->getAPIClient();
		} catch (Mollie\Api\Exceptions\ApiException $e) {
			$this->showErrorPage($e->getMessage());
			$this->writeToMollieLog("Creating payment failed, API did not load; " . $e->getMessage());
			return;
		}

		// Load essentials
		$this->load->language("payment/mollie");

		$model = $this->getModuleModel();
		$order_id = $this->getOrderID();
		$order = $this->getOpenCartOrder($order_id);

		$amount = $this->currency->convert($order['total'], $this->config->get("config_currency"), "EUR");
		$amount = round($amount, 2);
		$description = str_replace("%", $order['order_id'], html_entity_decode($this->config->get(MollieHelper::getModuleCode() . "_ideal_description"), ENT_QUOTES, "UTF-8"));
		$return_url = $this->url->link("payment/mollie_" . static::MODULE_NAME . "/callback&order_id=" . $order['order_id'], "", "SSL");
		$issuer = $this->getIssuer();

		try {
			$data = array(
				"amount" => ["currency" => "EUR", "value" => (string)$amount],
				"description" => $description,
				"redirectUrl" => $return_url,
				"webhookUrl" => $this->getWebhookUrl(),
				"metadata" => array("order_id" => $order['order_id']),
				"method" => static::MODULE_NAME,
				"issuer" => $issuer
			);
				/*
				 * This data is sent along for credit card payments / fraud checks. You can remove this but you will
				 * have a higher conversion if you leave it here.
				 */
			$data["billingAddress"] = [
				"streetAndNumber" => $order['payment_address_1'] . ' ' . $order['payment_address_2'],
				"city" => $order['payment_city'],
				"region" => $order['payment_zone'],
				"postalCode" => $order['payment_postcode'],
				"country" => $order['payment_iso_code_2']
			];

			if (!empty($order['shipping_firstname']) || !empty($order['shipping_lastname'])) {
				$data["shippingAddress"] = [
					"streetAndNumber" => $order['shipping_address_1'] . ' ' . $order['shipping_address_2'],
					"city" => $order['shipping_city'],
					"region" => $order['shipping_zone'],
					"postalCode" => $order['shipping_postcode'],
					"country" => $order['shipping_iso_code_2']
				];
			} else {
				$data["shippingAddress"] = $data["billingAddress"];
			}

			$locales = array(
				'en_US',
				'de_AT',
				'de_CH',
				'de_DE',
				'es_ES',
				'fr_BE',
				'fr_FR',
				'nl_BE',
				'nl_NL'
			);
			
			if (strstr($this->session->data['language'], '-')) {
				list ($language, $country) = explode('-', $this->session->data['language']);
				$locale = strtolower($language) . '_' . strtoupper($country);
			} else {
				$locale = strtolower($this->session->data['language']) . '_' . strtoupper($this->session->data['language']);
			}
			
			if (!in_array($locale, $locales)) {
				$locale = 'nl_NL';
			}

			$data['locale'] = $locale;

			$payment = $api->payments->create($data);
		} catch (Mollie\Api\Exceptions\ApiException $e) {
			$this->showErrorPage($e->getMessage());
			$this->writeToMollieLog("Creating payment failed; " . $e->getMessage());
			return;
		}

		// Some payment methods can't be cancelled. They need an initial order status.
		if ($this->startAsPending()) {
			$this->addOrderHistory($order, $this->config->get(MollieHelper::getModuleCode() . "_ideal_pending_status_id"), $this->language->get("text_redirected"), false);
		}

		$model->setPayment($order['order_id'], $payment->id);

		// Redirect to payment gateway.
		$this->redirect($payment->_links->checkout->href);
	}

	/**
	 * Some payment methods can't be cancelled. They need 'pending' as an initial order status.
	 *
	 * @return bool
	 */
	protected function startAsPending()
	{
		return false;
	}

	/**
	 * This action is getting called by Mollie to report the payment status
	 */
	public function webhook()
	{
		if(empty($this->request->post['id'])) {
			header("HTTP/1.0 400 Bad Request");
			$this->writeToMollieLog("Webhook called but no ID received.", true);
			return;
		}

		$moduleCode = MollieHelper::getModuleCode();
		$payment_id = $this->request->post['id'];
		$this->writeToMollieLog("Received webhook for payment_id " . $payment_id);

		$payment = $this->getAPIClient()->payments->get($payment_id);

		// Load essentials
		$this->load->model("checkout/order");
		$this->getModuleModel();
		$this->load->language("payment/mollie");

		//Get order_id of this transaction from db
		$order = $this->model_checkout_order->getOrder($payment->metadata->order_id);

		if (empty($order)) {
			header("HTTP/1.0 404 Not Found");
			echo "Could not find order.";
			return;
		}

		// Only process the status if the order is stateless or in 'pending' status.
		if (!empty($order['order_status_id']) && $order['order_status_id'] != $this->config->get($moduleCode . "_ideal_pending_status_id")) {
			$this->writeToMollieLog("The order was already processed before (order status ID: " . intval($order['order_status_id']) . ")");
			return;
		}

		// Order paid ('processed').
		if ($payment->isPaid()) {
			$new_status_id = intval($this->config->get($moduleCode . "_ideal_processing_status_id"));

			if (!$new_status_id) {
				$this->writeToMollieLog("The payment has been received. No 'processing' status ID is configured, so the order status could not be updated.", true);
				return;
			}
			$this->addOrderHistory($order, $new_status_id, $this->language->get("response_success"), true);
			$this->writeToMollieLog("The payment was received and the order was moved to the 'processing' status (new status ID: {$new_status_id}.", true);
			return;
		}

		// Order cancelled.
		if ($payment->status == PaymentStatus::STATUS_CANCELED) {
			$new_status_id = intval($this->config->get($moduleCode . "_ideal_canceled_status_id"));

			if (!$new_status_id) {
				$this->writeToMollieLog("The payment was cancelled. No 'cancelled' status ID is configured, so the order status could not be updated.", true);
				return;
			}
			$this->addOrderHistory($order, $new_status_id, $this->language->get("response_cancelled"), false);
			$this->writeToMollieLog("The payment was cancelled and the order was moved to the 'cancelled' status (new status ID: {$new_status_id}).", true);
			return;
		}

		// Order expired.
		if ($payment->status == PaymentStatus::STATUS_EXPIRED) {
			$new_status_id = intval($this->config->get($moduleCode . "_ideal_expired_status_id"));

			if (!$new_status_id) {
				$this->writeToMollieLog("The payment expired. No 'expired' status ID is configured, so the order status could not be updated.", true);
				return;
			}
			$this->addOrderHistory($order, $new_status_id, $this->language->get("response_expired"), false);
			$this->writeToMollieLog("The payment expired and the order was moved to the 'expired' status (new status ID: {$new_status_id}).", true);
			return;
		}

		// Otherwise, order failed.
		$new_status_id = intval($this->config->get($moduleCode . "_ideal_failed_status_id"));

		if (!$new_status_id) {
			$this->writeToMollieLog("The payment failed. No 'failed' status ID is configured, so the order status could not be updated.", true);
			return;
		}
		$this->addOrderHistory($order, $new_status_id, $this->language->get("response_unknown"), false);
		$this->writeToMollieLog("The payment failed for an unknown reason and the order was moved to the 'failed' status (new status ID: {$new_status_id}).", true);
		return;

	}

	/**
	 * Gets called via AJAX from the checkout form to store the selected issuer.
	 */
	public function set_issuer()
	{
		if (!empty($this->request->post['mollie_issuer_id'])) {
			$this->session->data['mollie_issuer'] = $this->request->post['mollie_issuer_id'];
		} else {
			$this->session->data['mollie_issuer'] = null;
		}

		echo $this->session->data['mollie_issuer'];
	}

	/**
	 * Retrieve the issuer if one was selected. Return null otherwise.
	 *
	 * @return string|null
	 */
	protected function getIssuer()
	{
		if (!empty($this->request->post['mollie_issuer'])) {
			return $this->request->post['mollie_issuer'];
		}

		if (!empty($this->session->data['mollie_issuer'])) {
			return $this->session->data['mollie_issuer'];
		}

		return null;
	}

	/**
	 * Customer returning from the bank with an transaction_id
	 * Depending on what the state of the payment is they get redirected to the corresponding page
	 *
	 * @return string
	 */
	public function callback()
	{
		$moduleCode = MollieHelper::getModuleCode();
		$order_id = $this->getOrderID();

		if ($order_id === false) {
			$this->writeToMollieLog("Failed to get order id.");

			return $this->showReturnPage(
				$this->language->get("heading_failed"),
				$this->language->get("msg_failed")
			);
		}

		$order = $this->getOpenCartOrder($order_id);

		if (empty($order)) {
			$this->writeToMollieLog("Failed to get order for order id: " . $order_id);

			return $this->showReturnPage(
				$this->language->get("heading_failed"),
				$this->language->get("msg_failed")
			);
		}

		$this->writeToMollieLog("Received callback for order " . $order_id);

		// Load required translations.
		$this->load->language("payment/mollie");

		// Double-check whether or not the status of the order is correct.
		$model = $this->getModuleModel();

		$paid_status_id = intval($this->config->get($moduleCode . "_ideal_processing_status_id"));
		$payment_id = $model->getPaymentID($order['order_id']);

		if ($payment_id === false) {
			$this->writeToMollieLog("Error getting payment id for order " . $order['order_id']);

			return $this->showReturnPage(
				$this->language->get("heading_failed"),
				$this->language->get("msg_failed")
			);
		}

		$payment = $this->getAPIClient()->payments->get($payment_id);

		if ($payment->isPaid() && $order['order_status_id'] != $paid_status_id) {
			$this->addOrderHistory($order, $paid_status_id, $this->language->get("response_success"), true);
			$order['order_status_id'] = $paid_status_id;
		}

		// Show a 'transaction failed' page if we couldn't find the order or if the payment failed.
		$failed_status_id = $this->config->get($moduleCode . "_ideal_failed_status_id");

		if (!$order || ($failed_status_id && $order['order_status_id'] == $failed_status_id)) {
			if ($failed_status_id && $order['order_status_id'] == $failed_status_id) {
				$this->writeToMollieLog("Error payment failed for order " . $order['order_id']);
			} else {
				$this->writeToMollieLog("Error couldn't find order");
			}

			return $this->showReturnPage(
				$this->language->get("heading_failed"),
				$this->language->get("msg_failed")
			);
		}

		// If the order status is 'processing' (i.e. 'paid'), redirect to OpenCart's default 'success' page.
		if ($order["order_status_id"] == $this->config->get($moduleCode . "_ideal_processing_status_id")) {
			$this->writeToMollieLog("Success redirect to success page for order " . $order['order_id']);
			if ($this->cart) {
				$this->cart->clear();
			}

			// Redirect to 'success' page.
			$this->redirect($this->url->link("checkout/success", "", "SSL"));
			return '';
		}

		// If the status is 'pending' (i.e. a bank transfer), the report is not delivered yet.
		if ($order['order_status_id'] == $this->config->get($moduleCode . "_ideal_pending_status_id")) {
			$this->writeToMollieLog("Unknown payment status for order " . $order['order_id']);
			if ($this->cart) {
				$this->cart->clear();
			}

			return $this->showReturnPage(
				$this->language->get("heading_unknown"),
				$this->language->get("msg_unknown"),
				null,
				false
			);
		}

		// The status is probably 'cancelled'. Allow the admin to redirect their customers back to the shopping cart directly in these cases.
		if (!(bool)$this->config->get($moduleCode . "_show_order_canceled_page")) {
			$this->redirect($this->url->link("checkout/checkout", "", "SSL"));
		}

		// Show a 'transaction failed' page if all else fails.
		$this->writeToMollieLog("Everything else failed for order " . $order['order_id']);

		return $this->showReturnPage(
			$this->language->get("heading_failed"),
			$this->language->get("msg_failed")
		);
	}

	/**
	 * @param &$data
	 */
	protected function setBreadcrumbs(&$data)
	{
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			"href" => $this->url->link("common/home", (isset($this->session->data['token'])) ? "token=" . $this->session->data['token'] : "", "SSL"),
			"text" => $this->language->get("text_home"),
			"separator" => false,
		);
	}

	/**
	 * @param $message
	 *
	 * @return string
	 */
	protected function showErrorPage($message)
	{
		$this->load->language("payment/mollie");

		$this->log->write("Error setting up transaction with Mollie: {$message}.");

		return $this->showReturnPage(
			$this->language->get("heading_error"),
			$this->language->get("text_error"),
			$message
		);
	}

	/**
	 * Render a return page.
	 *
	 * @param string $title The title of the status page.
	 * @param string $body The status message.
	 * @param string|null $api_error Show an API error, if applicable.
	 * @param bool $show_retry_button Show a retry button that redirects the customer back to the checkout page.
	 *
	 * @return string
	 */
	protected function showReturnPage($title, $body, $api_error = null, $show_retry_button = true)
	{
		$this->load->language("payment/mollie");

		$data['message_title'] = $title;
		$data['message_text'] = $body;

		if ($api_error) {
			$data['mollie_error'] = $api_error;
		}

		if ($show_retry_button) {
			$data['checkout_url'] = $this->url->link("checkout/checkout", "", "SSL");
			$data['button_retry'] = $this->language->get("button_retry");
		}

		$this->document->setTitle($this->language->get("ideal_title"));

		$this->setBreadcrumbs($data);

		return $this->renderTemplate("mollie_return", $data, array(
			"column_left",
			"column_right",
			"content_top",
			"content_bottom",
			"footer",
			"header",
		));
	}

	/**
	 * We check for and remove the admin url in the webhook link.
	 *
	 * @return string|null
	 */
	public function getWebhookUrl()
	{
		$system_webhook_url = $this->url->link("payment/mollie_" . static::MODULE_NAME . "/webhook", "", "SSL");

		if (strpos($system_webhook_url, $this->getAdminDirectory()) !== false) {
			return str_replace($this->getAdminDirectory(), "", $system_webhook_url);
		}

		return $system_webhook_url ? $system_webhook_url : null;
	}

	/**
	 * Retrieves the admin directoryname from the catalog and admin urls.
	 *
	 * @return string
	 */
	protected function getAdminDirectory()
	{
		// if no default admin URL defined in the config, use the default admin directory.
		if (!defined('HTTP_ADMIN')) {
			return "admin/";
		}

		return str_replace(HTTP_SERVER, "", HTTP_ADMIN);
	}

	/**
	 * Map payment status history handling for different Opencart versions.
	 *
	 * @param array $order
	 * @param int|string $order_status_id
	 * @param string $comment
	 * @param bool $notify
	 */
	protected function addOrderHistory($order, $order_status_id, $comment = "", $notify = false)
	{
		if (MollieHelper::isOpenCart2x()) {
			$this->model_checkout_order->addOrderHistory($order['order_id'], $order_status_id, $comment, $notify);
		} else {
			if (empty($order['order_status_id'])) {
				$this->model_checkout_order->confirm($order['order_id'], $order_status_id, $comment, $notify);
			} else {
				$this->model_checkout_order->update($order['order_id'], $order_status_id, $comment, $notify);
			}
		}
	}

	/**
	 * Map template handling for different Opencart versions.
	 *
	 * @param string $template
	 * @param array $data
	 * @param array $common_children
	 * @param bool $echo
	 * @return string
	 */
	protected function renderTemplate($template, $data, $common_children = array(), $echo = true)
	{
		if (!MollieHelper::isOpenCart3x()) {
			$template .= '.tpl';
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/' . $template)) {
			$template = $this->config->get('config_template') . '/template/payment/' . $template;
		} else if (file_exists(DIR_TEMPLATE . 'default/template/payment/' . $template)) {
			$template = 'default/template/payment/' . $template;
		} else {
			$template = 'payment/' . $template;
		}

		if (MollieHelper::isOpenCart2x()) {
			foreach ($common_children as $child) {
				$data[$child] = $this->load->controller("common/" . $child);
			}

			$html = $this->load->view($template, $data);
		} else {
			$this->template = $template;
			$this->children = array();

			foreach ($data as $field => $value) {
				$this->data[$field] = $value;
			}

			foreach($common_children as $child) {
				if ($child === 'column_left') {
					continue;
				}

				$this->children[] = "common/" . $child;
			}

			$html = $this->render();
		}

		if ($echo) {
			return $this->response->setOutput($html);
		}

		return $html;
	}

	/**
	 * @param string $url
	 * @param int $status
	 */
	protected function redirect($url, $status = 302)
	{
		$this->response->redirect($url, $status);
	}
}
