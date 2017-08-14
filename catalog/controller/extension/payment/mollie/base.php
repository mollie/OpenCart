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
require_once(dirname(DIR_SYSTEM) . "/catalog/controller/extension/payment/mollie/helper.php");

class ControllerExtensionPaymentMollieBase extends Controller
{
	// Current module name - should be overwritten by subclass using one of the values below.
	const MODULE_NAME = null;

	/**
	 * @return Mollie_API_Client
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
		$model_name = "model_extension_payment_mollie_" . static::MODULE_NAME;

		if (!isset($this->$model_name)) {
			$this->load->model("extension/payment/mollie_" . static::MODULE_NAME);
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
		$this->load->language("extension/payment/mollie");

		$payment_method = $this->getAPIClient()->methods->get(static::MODULE_NAME, array('include' => 'issuers'));

		// Set template data.
		$data['action'] = $this->url->link("extension/payment/mollie_" . static::MODULE_NAME . "/payment", "", "SSL");
		$data['image'] = $payment_method->image->normal;
		$data['message'] = $this->language;
		$data['issuers'] = isset($payment_method->issuers) ? $payment_method->issuers : array();
		$data['text_issuer'] = $this->language->get("text_issuer_" . static::MODULE_NAME);
		$data['set_issuer_url'] = $this->url->link("extension/payment/mollie_" . static::MODULE_NAME . "/set_issuer", "", "SSL");

		// Return HTML output - it will get appended to confirm.tpl.
		return $this->renderTemplate("mollie_checkout_form", $data, array(), false);
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
		} catch (Mollie_API_Exception $e) {
			$this->showErrorPage($e->getMessage());
			$this->writeToMollieLog("Creating payment failed, API did not load; " . $e->getMessage());
			return;
		}

		// Load essentials
		$this->load->language("extension/payment/mollie");

		$model = $this->getModuleModel();
		$order_id = $this->getOrderID();
		$order = $this->getOpenCartOrder($order_id);

		$amount = $this->currency->convert($order['total'], $this->config->get("config_currency"), "EUR");

		$amount = round($amount, 2);
		$description = str_replace("%", $order['order_id'], html_entity_decode($this->config->get("payment_mollie_ideal_description"), ENT_QUOTES, "UTF-8"));
		$return_url = $this->url->link("extension/payment/mollie_" . static::MODULE_NAME . "/callback&order_id=" . $order['order_id'], "", "SSL");
		$issuer = $this->getIssuer();

		list ($language, $country) = explode('-', $this->session->data['language']);
		$locale = strtolower($language) . '_' . strtoupper($country);

		try {
			$data = array(
				"amount" => $amount,
				"description" => $description,
				"redirectUrl" => $return_url,
				"webhookUrl" => $this->getWebhookUrl(),
				"metadata" => array("order_id" => $order['order_id']),
				"method" => static::MODULE_NAME,
				"issuer" => $issuer,
				"locale" => $locale,

				/*
				 * This data is sent along for credit card payments / fraud checks. You can remove this but you will
				 * have a higher conversion if you leave it here.
				 */

				"billingCity" => $order['payment_city'],
				"billingRegion" => $order['payment_zone'],
				"billingPostal" => $order['payment_postcode'],
				"billingCountry" => $order['payment_iso_code_2'],

				"shippingAddress" => $order['shipping_address_1'] ? $order['shipping_address_1'] : null,
				"shippingCity" => $order['shipping_city'] ? $order['shipping_city'] : $order['payment_city'],
				"shippingRegion" => $order['shipping_zone'] ? $order['shipping_zone'] : $order['payment_zone'],
				"shippingPostal" => $order['shipping_postcode'] ? $order['shipping_postcode'] : $order['payment_postcode'],
				"shippingCountry" => $order['shipping_iso_code_2'] ? $order['shipping_iso_code_2'] : $order['payment_iso_code_2'],
			);
			$payment = $api->payments->create($data);
		} catch (Mollie_Api_Exception $e) {
			$this->showErrorPage($e->getMessage());
			$this->writeToMollieLog("Creating payment failed; " . $e->getMessage());
			return;
		}

		// Some payment methods can't be cancelled. They need an initial order status.
		if ($this->startAsPending()) {
			$this->addOrderHistory($order, $this->config->get("payment_mollie_ideal_pending_status_id"), $this->language->get("text_redirected"), false);
		}

		$model->setPayment($order['order_id'], $payment->id);

		// Redirect to payment gateway.
		$this->redirect($payment->links->paymentUrl);
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

		$payment_id = $this->request->post['id'];
		$this->writeToMollieLog("Received webhook for payment_id " . $payment_id);

		$payment = $this->getAPIClient()->payments->get($payment_id);

		// Load essentials
		$this->load->model("checkout/order");
		$this->getModuleModel();
		$this->load->language("extension/payment/mollie");

		//Get order_id of this transaction from db
		$order = $this->model_checkout_order->getOrder($payment->metadata->order_id);

		if (empty($order)) {
			header("HTTP/1.0 404 Not Found");
			echo "Could not find order.";
			return;
		}

		// Only process the status if the order is stateless or in 'pending' status.
		if (!empty($order['order_status_id']) && $order['order_status_id'] != $this->config->get("payment_mollie_ideal_pending_status_id")) {
			$this->writeToMollieLog("The order was already processed before (order status ID: " . intval($order['order_status_id']) . ")");
			return;
		}

		// Order paid ('processed').
		if ($payment->isPaid()) {
			$new_status_id = intval($this->config->get("payment_mollie_ideal_processing_status_id"));

			if (!$new_status_id) {
				$this->writeToMollieLog("The payment has been received. No 'processing' status ID is configured, so the order status could not be updated.", true);
				return;
			}
			$this->addOrderHistory($order, $new_status_id, $this->language->get("response_success"), true);
			$this->writeToMollieLog("The payment was received and the order was moved to the 'processing' status (new status ID: {$new_status_id}.", true);
			return;
		}

		// Order cancelled.
		if ($payment->status == Mollie_API_Object_Payment::STATUS_CANCELLED) {
			$new_status_id = intval($this->config->get("payment_mollie_ideal_canceled_status_id"));

			if (!$new_status_id) {
				$this->writeToMollieLog("The payment was cancelled. No 'cancelled' status ID is configured, so the order status could not be updated.", true);
				return;
			}
			$this->addOrderHistory($order, $new_status_id, $this->language->get("response_cancelled"), false);
			$this->writeToMollieLog("The payment was cancelled and the order was moved to the 'cancelled' status (new status ID: {$new_status_id}).", true);
			return;
		}

		// Order expired.
		if ($payment->status == Mollie_API_Object_Payment::STATUS_EXPIRED) {
			$new_status_id = intval($this->config->get("payment_mollie_ideal_expired_status_id"));

			if (!$new_status_id) {
				$this->writeToMollieLog("The payment expired. No 'expired' status ID is configured, so the order status could not be updated.", true);
				return;
			}
			$this->addOrderHistory($order, $new_status_id, $this->language->get("response_expired"), false);
			$this->writeToMollieLog("The payment expired and the order was moved to the 'expired' status (new status ID: {$new_status_id}).", true);
			return;
		}

		// Otherwise, order failed.
		$new_status_id = intval($this->config->get("payment_mollie_ideal_failed_status_id"));

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

		// Load required translations.
		$this->load->language("extension/payment/mollie");

		// Double-check whether or not the status of the order is correct.
		$model = $this->getModuleModel();

		$paid_status_id = intval($this->config->get("mollie_ideal_processing_status_id"));
		$payment_id = $model->getPaymentID($order['order_id']);

		if ($payment_id === false) {
			$this->writeToMollieLog("Error getting payment id for order " . $order['order_id']);

			return $this->showReturnPage(
				$this->language->get("heading_failed"),
				$this->language->get("msg_failed")
			);
		}

		$payment = $this->getAPIClient()->payments->get($payment_id);

		if ($payment->isPaid() && $order['order_status_id'] == 0) {
			$this->addOrderHistory($order, $paid_status_id, $this->language->get("response_success"), true);
			$order['order_status_id'] = $paid_status_id;
		}

		// Show a 'transaction failed' page if we couldn't find the order or if the payment failed.
		$failed_status_id = $this->config->get("payment_mollie_ideal_failed_status_id");

		if (!$order || ($failed_status_id && $order['order_status_id'] == $failed_status_id)) {
			return $this->showReturnPage(
				$this->language->get("heading_failed"),
				$this->language->get("msg_failed")
			);
		}

		// If the order status is 'processing' (i.e. 'paid'), redirect to OpenCart's default 'success' page.
		if ($order["order_status_id"] == $this->config->get("payment_mollie_ideal_processing_status_id")) {
			if ($this->cart) {
				$this->cart->clear();
			}

			// Redirect to 'success' page.
			$this->redirect($this->url->link("checkout/success", "", "SSL"));
			return '';
		}

		// If the status is 'pending' (i.e. a bank transfer), the report is not delivered yet.
		if ($order['order_status_id'] == $this->config->get("payment_mollie_ideal_pending_status_id")) {
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
		if (!(bool)$this->config->get("payment_mollie_show_order_canceled_page")) {
			$this->redirect($this->url->link("checkout/checkout", "", "SSL"));
		}

		// Show a 'transaction failed' page if all else fails.
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
		$this->load->language("extension/payment/mollie");

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
		$this->load->language("extension/payment/mollie");

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
		$system_webhook_url = $this->url->link("extension/payment/mollie_" . static::MODULE_NAME . "/webhook", "", "SSL");

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
		$this->model_checkout_order->addOrderHistory($order['order_id'], $order_status_id, $comment, $notify);
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
		foreach ($common_children as $child) {
			$data[$child] = $this->load->controller("common/" . $child);
		}

		$html = $this->load->view('extension/payment/' . $template, $data);

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
