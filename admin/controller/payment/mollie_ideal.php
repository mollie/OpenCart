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
 * @version     v5.0
 * @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://www.mollie.nl
 */
class ControllerPaymentMollieIdeal extends Controller
{
	// Initialize var(s)
	private $error = array();

	/**
	 * This method is executed by OpenCart when the Payment module is installed from the admin. It will create the
	 * required tables.
	 *
	 * @return void
	 */
	public function install ()
	{
		$this->db->query(
			sprintf(
				"CREATE TABLE IF NOT EXISTS `%smollie_payments` (
					`order_id` int(11) unsigned NOT NULL,
					`method` enum('idl') NOT NULL DEFAULT 'idl',
					`transaction_id` varchar(32) NOT NULL,
					`bank_account` varchar(15) NOT NULL,
					`bank_status` varchar(20) NOT NULL,
					PRIMARY KEY (`order_id`),
					UNIQUE KEY `transaction_id` (`transaction_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8",
				DB_PREFIX
			));
	}

	/**
	 * The method is executed by OpenCart when the Payment module is uninstalled from the admin. It will drop any Mollie
	 * tables.
	 *
	 * @return void
	 */
	public function uninstall ()
	{
		$this->db->query(sprintf(
			"DROP TABLE IF EXISTS `%smollie_payments`",
			DB_PREFIX));
	}

	/**
	 * The backend for iDEAL
	 */
	public function index()
	{
		// Load essential models
		$this->load->language('payment/mollie_ideal');
		$this->load->model('setting/setting');
		$this->load->model('setting/store');
		$this->load->model('localisation/order_status');

		$this->document->setTitle($this->language->get('heading_title'));

		// Call validate method on POST
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate()))
		{
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('ideal', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		// Set data for template
		$this->data['heading_title']          = $this->language->get('heading_title');
		$this->data['footer_text']            = $this->language->get('footer_text');

		$this->data['text_enabled']           = $this->language->get('text_enabled');
		$this->data['text_disabled']          = $this->language->get('text_disabled');
		$this->data['text_yes']               = $this->language->get('text_yes');
		$this->data['text_no']                = $this->language->get('text_no');
		$this->data['text_none']              = $this->language->get('text_none');

		$this->data['entry_status']           = $this->language->get('entry_status');
		$this->data['entry_api_key']          = $this->language->get('entry_api_key');
		$this->data['entry_webhook']          = $this->language->get('entry_webhook');
		$this->data['entry_webhook_help']     = $this->language->get('entry_webhook_help');
		$this->data['entry_description']      = $this->language->get('entry_description');

		$this->data['order_statuses']         = $this->model_localisation_order_status->getOrderStatuses();
		$this->data['entry_failed_status']    = $this->language->get('entry_failed_status');
		$this->data['entry_canceled_status']  = $this->language->get('entry_canceled_status');
		$this->data['entry_pending_status']   = $this->language->get('entry_pending_status');
		$this->data['entry_expired_status']   = $this->language->get('entry_expired_status');
		$this->data['entry_processing_status']= $this->language->get('entry_processing_status');
		$this->data['entry_processed_status'] = $this->language->get('entry_processed_status');

		$this->data['entry_sort_order']       = $this->language->get('entry_sort_order');
		$this->data['entry_support']          = $this->language->get('entry_support');
		$this->data['entry_status']           = $this->language->get('entry_status');
		$this->data['entry_mstatus']          = $this->_checkModuleStatus();
		$this->data['entry_cstatus']          = $this->_checkCommunicationStatus();
		$this->data['entry_module']           = $this->language->get('entry_module');
		$this->data['entry_version']          = $this->language->get('entry_version');

		$this->data['button_save']            = $this->language->get('button_save');
		$this->data['button_cancel']          = $this->language->get('button_cancel');

		$this->data['tab_general']            = $this->language->get('tab_general');

		// If errors show the error
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		if (isset($this->error['api_key'])) {
			$this->data['error_api_key'] = $this->error['api_key'];
		} else {
			$this->data['error_profilekey'] = '';
		}
		if (isset($this->error['description'])) {
			$this->data['error_description'] = $this->error['description'];
		} else {
			$this->data['error_description'] = '';
		}
		if (isset($this->error['total'])) {
			$this->data['error_total'] = $this->error['total'];
		} else {
			$this->data['error_total'] = '';
		}

		$this->data['webhook_url'] = str_replace("/admin", "", $this->url->link('payment/mollie_ideal/webhook', '', 'SSL'));

		// Breadcrumbs
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'text' => $this->language->get('text_home'),
			'separator' => FALSE
		);

		$this->data['breadcrumbs'][] = array(
			'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'text' => $this->language->get('text_payment'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'href' => $this->url->link('payment/mollie_ideal', 'token=' . $this->session->data['token'], 'SSL'),
			'text' => $this->language->get('heading_title'),
			'separator' => ' :: '
		);

		// Form action url
		$this->data['action'] = $this->url->link('payment/mollie_ideal', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		// Post data
		if (isset($this->request->post['mollie_ideal_status'])) {
			$this->data['mollie_ideal_status'] = $this->request->post['mollie_ideal_status'];
		} else {
			$this->data['mollie_ideal_status'] = $this->config->get('mollie_ideal_status');
		}
		if (isset($this->request->post['mollie_api_key'])) {
			$this->data['mollie_api_key'] = $this->request->post['mollie_api_key'];
		} else {
			$this->data['mollie_api_key'] = $this->config->get('mollie_api_key');
		}
		if (isset($this->request->post['mollie_ideal_description'])) {
			$this->data['mollie_ideal_description'] = $this->request->post['mollie_ideal_description'];
		} else {
			$this->data['mollie_ideal_description'] = $this->config->get('mollie_ideal_description') ? $this->config->get('mollie_ideal_description') : "Order %";
		}
		if (isset($this->request->post['mollie_ideal_failed_status_id'])) {
			$this->data['mollie_ideal_failed_status_id'] = $this->request->post['mollie_ideal_failed_status_id'];
		} else {
			$this->data['mollie_ideal_failed_status_id'] = $this->config->get('mollie_ideal_failed_status_id') ? $this->config->get('mollie_ideal_failed_status_id') : 10;
		}
		if (isset($this->request->post['mollie_ideal_canceled_status_id'])) {
			$this->data['mollie_ideal_canceled_status_id'] = $this->request->post['mollie_ideal_canceled_status_id'];
		} else {
			$this->data['mollie_ideal_canceled_status_id'] = $this->config->get('mollie_ideal_canceled_status_id') ? $this->config->get('mollie_ideal_canceled_status_id') : 7;
		}
		if (isset($this->request->post['mollie_ideal_expired_status_id'])) {
			$this->data['mollie_ideal_expired_status_id'] = $this->request->post['mollie_ideal_expired_status_id'];
		} else {
			$this->data['mollie_ideal_expired_status_id'] = $this->config->get('mollie_ideal_expired_status_id') ? $this->config->get('mollie_ideal_expired_status_id') : 14;
		}
		if (isset($this->request->post['mollie_ideal_pending_status_id'])) {
			$this->data['mollie_ideal_pending_status_id'] = $this->request->post['mollie_ideal_pending_status_id'];
		} else {
			$this->data['mollie_ideal_pending_status_id'] = $this->config->get('mollie_ideal_pending_status_id') ? $this->config->get('mollie_ideal_pending_status_id') : 1;
		}
		if (isset($this->request->post['mollie_ideal_processing_status_id'])) {
			$this->data['mollie_ideal_processing_status_id'] = $this->request->post['mollie_ideal_processing_status_id'];
		} else {
			$this->data['mollie_ideal_processing_status_id'] = $this->config->get('mollie_ideal_processing_status_id') ? $this->config->get('mollie_ideal_processing_status_id') : 2;
		}
		if (isset($this->request->post['mollie_ideal_processed_status_id'])) {
			$this->data['mollie_ideal_processed_status_id'] = $this->request->post['mollie_ideal_processed_status_id'];
		} else {
			$this->data['mollie_ideal_processed_status_id'] = $this->config->get('mollie_ideal_processed_status_id') ? $this->config->get('mollie_ideal_processed_status_id') : 15;
		}
		if (isset($this->request->post['mollie_ideal_sort_order'])) {
			$this->data['mollie_ideal_sort_order'] = $this->request->post['mollie_ideal_sort_order'];
		} else {
			$this->data['mollie_ideal_sort_order'] = $this->config->get('mollie_ideal_sort_order');
		}

		// Set template
		$this->template = 'payment/mollie_ideal.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
	}

	/**
	 * Check the post and check if the user has permission to edit the module settings
	 *
	 * @return bool
	 */
	private function validate()
	{
		if (!$this->user->hasPermission('modify', 'payment/mollie_ideal'))
		{
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['mollie_api_key'])
		{
			$this->error['api_key'] = $this->language->get('mollie_api_key');
		}

		if (!$this->request->post['mollie_ideal_description'])
		{
			$this->error['description'] = $this->language->get('error_description');
		}

		return sizeof($this->error) == 0;
	}

	protected function _checkModuleStatus()
	{
		$needFiles = array();
		$modFiles  = array(
			DIR_APPLICATION.'controller/payment/mollie_ideal.php',
			DIR_APPLICATION.'language/english/payment/mollie_ideal.php',
			DIR_TEMPLATE.'payment/mollie_ideal.tpl',
			DIR_CATALOG.'controller/payment/mollie_ideal.php',
			DIR_CATALOG.'language/english/payment/mollie_ideal.php',
			DIR_CATALOG.'model/payment/mollie_ideal.php',
			DIR_CATALOG.'view/theme/default/template/payment/mollie_ideal_banks.tpl',
			DIR_CATALOG.'view/theme/default/template/payment/mollie_ideal_return.tpl',
		);

		foreach ($modFiles as $file)
		{
			if(!file_exists($file)) {
				$needFiles[] = '<span style="color:red">'.$file.'</span>';
			}
		}

		if (count($needFiles) > 0) {
			return $needFiles;
		} else {
			return '<span style="color:green">Ok!</span>';
		}

		return NULL;
	}

	/**
	 * Version of the plugin.
	 */
	const PLUGIN_VERSION = "v5.0";

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
			require_once dirname(DIR_APPLICATION) . "/catalog/controller/payment/mollie-api-client/src/Mollie/API/Autoloader.php";

			$this->mollie_api_client = new Mollie_API_Client();
			$this->mollie_api_client->setApiKey($this->config->get('mollie_api_key'));
			$this->mollie_api_client->addVersionString("OpenCart/" . VERSION);
			$this->mollie_api_client->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);
		}

		return $this->mollie_api_client;
	}

	protected function _checkCommunicationStatus()
	{
		try
		{
			$this->getApiClient()->methods->all();
			return '<span style="color: green;">Ok!</span>';
		}
		catch (Mollie_API_Exception $e)
		{
			return '<span style="color: red;"> ' . htmlspecialchars($e->getMessage()). '</span>';
		}
	}
}
