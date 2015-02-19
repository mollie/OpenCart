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
 * @property DB $db
 * @property Request $request
 * @property Response $response
 * @property URL $url
 * @property Loader $load
 * @property Config $config
 * @property Language $language
 * @property ModelSettingSetting $model_setting_setting
 * @property ModelSettingStore $model_setting_store
 * @property ModelLocalisationOrderStatus $localisation_order_status
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
			$this->model_setting_setting->editSetting("mollie", $this->request->post);

			// Migrate old settings if needed. We used to use "ideal" as setting group, but Opencart 2 requires us to use "mollie".
			$this->model_setting_setting->deleteSetting("ideal");

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		// Set data for template
		$data['heading_title']          = $this->language->get('heading_title');
		$data['title_payment_status']   = $this->language->get('title_payment_status');
		$data['title_mod_about']        = $this->language->get('title_mod_about');
		$data['footer_text']            = $this->language->get('footer_text');

		$data['text_enabled']           = $this->language->get('text_enabled');
		$data['text_disabled']          = $this->language->get('text_disabled');
		$data['text_yes']               = $this->language->get('text_yes');
		$data['text_no']                = $this->language->get('text_no');
		$data['text_none']              = $this->language->get('text_none');
		$data['text_edit']              = $this->language->get('text_edit');

		$data['entry_api_key']          = $this->language->get('entry_api_key');
		$data['entry_description']      = $this->language->get('entry_description');
		$data['entry_status']           = $this->language->get('entry_status');
		$data['entry_mod_status']       = $this->language->get('entry_mod_status');
		$data['entry_comm_status']      = $this->language->get('entry_comm_status');

		$data['help_view_profile']     = $this->language->get('help_view_profile');
		$data['help_api_key']          = $this->language->get('help_api_key');
		$data['help_description']      = $this->language->get('help_description');
		$data['help_status']           = $this->language->get('help_status');

		$data['order_statuses']         = $this->model_localisation_order_status->getOrderStatuses();
		$data['entry_failed_status']    = $this->language->get('entry_failed_status');
		$data['entry_canceled_status']  = $this->language->get('entry_canceled_status');
		$data['entry_pending_status']   = $this->language->get('entry_pending_status');
		$data['entry_expired_status']   = $this->language->get('entry_expired_status');
		$data['entry_processing_status']= $this->language->get('entry_processing_status');
		$data['entry_processed_status'] = $this->language->get('entry_processed_status');

		$data['entry_sort_order']       = $this->language->get('entry_sort_order');
		$data['entry_support']          = $this->language->get('entry_support');
		$data['entry_mstatus']          = $this->_checkModuleStatus();
		$data['entry_cstatus']          = $this->_checkCommunicationStatus();
		$data['entry_module']           = $this->language->get('entry_module');
		$data['entry_version']          = $this->language->get('entry_version') . " " . self::PLUGIN_VERSION;

		$data['button_save']            = $this->language->get('button_save');
		$data['button_cancel']          = $this->language->get('button_cancel');

		$data['tab_general']            = $this->language->get('tab_general');

		// If errors show the error
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		if (isset($this->error['api_key'])) {
			$data['error_api_key'] = $this->error['api_key'];
		} else {
			$data['error_api_key'] = '';
		}
		if (isset($this->error['description'])) {
			$data['error_description'] = $this->error['description'];
		} else {
			$data['error_description'] = '';
		}
		if (isset($this->error['total'])) {
			$data['error_total'] = $this->error['total'];
		} else {
			$data['error_total'] = '';
		}

		$data['error_file_missing'] = $this->language->get('error_file_missing');

		// Breadcrumbs
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'text' => $this->language->get('text_home'),
			'separator' => FALSE
		);

		$data['breadcrumbs'][] = array(
			'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'text' => $this->language->get('text_payment'),
			'separator' => ' :: '
		);

		$data['breadcrumbs'][] = array(
			'href' => $this->url->link('payment/mollie_ideal', 'token=' . $this->session->data['token'], 'SSL'),
			'text' => $this->language->get('heading_title'),
			'separator' => ' :: '
		);

		// Form action url
		$data['action'] = $this->url->link('payment/mollie_ideal', 'token=' . $this->session->data['token'], 'SSL');
		$data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		// Post data
		if (isset($this->request->post['mollie_ideal_status'])) {
			$data['mollie_ideal_status'] = $this->request->post['mollie_ideal_status'];
		} else {
			$data['mollie_ideal_status'] = $this->config->get('mollie_ideal_status');
		}

		if (isset($this->request->post['mollie_api_key'])) {
			$data['mollie_api_key'] = $this->request->post['mollie_api_key'];
		} else {
			$data['mollie_api_key'] = $this->config->get('mollie_api_key');
		}

		if (isset($this->request->post['mollie_ideal_description'])) {
			$data['mollie_ideal_description'] = $this->request->post['mollie_ideal_description'];
		} else {
			$data['mollie_ideal_description'] = $this->config->get('mollie_ideal_description') ? $this->config->get('mollie_ideal_description') : "Order %";
		}
		if (isset($this->request->post['mollie_ideal_failed_status_id'])) {
			$data['mollie_ideal_failed_status_id'] = $this->request->post['mollie_ideal_failed_status_id'];
		} else {
			$data['mollie_ideal_failed_status_id'] = $this->config->get('mollie_ideal_failed_status_id') ? $this->config->get('mollie_ideal_failed_status_id') : 10;
		}
		if (isset($this->request->post['mollie_ideal_canceled_status_id'])) {
			$data['mollie_ideal_canceled_status_id'] = $this->request->post['mollie_ideal_canceled_status_id'];
		} else {
			$data['mollie_ideal_canceled_status_id'] = $this->config->get('mollie_ideal_canceled_status_id') ? $this->config->get('mollie_ideal_canceled_status_id') : 7;
		}
		if (isset($this->request->post['mollie_ideal_expired_status_id'])) {
			$data['mollie_ideal_expired_status_id'] = $this->request->post['mollie_ideal_expired_status_id'];
		} else {
			$data['mollie_ideal_expired_status_id'] = $this->config->get('mollie_ideal_expired_status_id') ? $this->config->get('mollie_ideal_expired_status_id') : 14;
		}
		if (isset($this->request->post['mollie_ideal_pending_status_id'])) {
			$data['mollie_ideal_pending_status_id'] = $this->request->post['mollie_ideal_pending_status_id'];
		} else {
			$data['mollie_ideal_pending_status_id'] = $this->config->get('mollie_ideal_pending_status_id') ? $this->config->get('mollie_ideal_pending_status_id') : 1;
		}
		if (isset($this->request->post['mollie_ideal_processing_status_id'])) {
			$data['mollie_ideal_processing_status_id'] = $this->request->post['mollie_ideal_processing_status_id'];
		} else {
			$data['mollie_ideal_processing_status_id'] = $this->config->get('mollie_ideal_processing_status_id') ? $this->config->get('mollie_ideal_processing_status_id') : 2;
		}
		if (isset($this->request->post['mollie_ideal_sort_order'])) {
			$data['mollie_ideal_sort_order'] = $this->request->post['mollie_ideal_sort_order'];
		} else {
			$data['mollie_ideal_sort_order'] = $this->config->get('mollie_ideal_sort_order');
		}

		// Set different template for Opencart 2 as it uses Bootstrap and a left column
		if ($this->isOpencart2())
		{
			$this->renderTemplate("payment/mollie_ideal_2.tpl", $data, array(
				"header",
				"column_left",
				"footer"
			));
		}
		else
		{
			$this->renderTemplate("payment/mollie_ideal.tpl", $data, array(
				"header",
				"footer"
			));
		}
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
			$this->error['api_key'] = $this->language->get('error_api_key');
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
			DIR_TEMPLATE.'payment/mollie_ideal_2.tpl',
			DIR_CATALOG.'controller/payment/mollie-api-client/',
			DIR_CATALOG.'controller/payment/mollie_ideal.php',
			DIR_CATALOG.'language/english/payment/mollie_ideal.php',
			DIR_CATALOG.'model/payment/mollie_ideal.php',
			DIR_CATALOG.'view/javascript/mollie_methods.js',
			DIR_CATALOG.'view/theme/default/template/payment/mollie_checkout_form.tpl',
			DIR_CATALOG.'view/theme/default/template/payment/mollie_ideal_return.tpl',
			DIR_CATALOG.'view/theme/default/template/payment/mollie_ideal_return_2.tpl',
			DIR_CATALOG.'view/theme/default/template/payment/mollie_payment_error.tpl',
			DIR_CATALOG.'view/theme/default/template/payment/mollie_payment_error_2.tpl'
		);

		foreach ($modFiles as $file)
		{
			if (!file_exists($file))
			{
				$needFiles[] = '<span style="color:red">'.$file.'</span>';
			}
		}

		if (count($needFiles) > 0)
		{
			return $needFiles;
		}

		return '<span style="color:green">OK</span>';
	}

	/**
	 * Version of the plugin.
	 */
	const PLUGIN_VERSION = "5.2.7";

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
			require_once dirname(DIR_APPLICATION) . "/catalog/controller/payment/mollie-api-client/src/Mollie/API/Autoloader.php";

			$this->_mollie_api_client = new Mollie_API_Client;
			$this->_mollie_api_client->setApiKey($this->config->get('mollie_api_key'));
			$this->_mollie_api_client->addVersionString("OpenCart/" . VERSION);
			$this->_mollie_api_client->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);
		}

		return $this->_mollie_api_client;
	}

	/**
	 * @return string
	 */
	protected function _checkCommunicationStatus ()
	{
		try
		{
			$this->getApiClient()->methods->all();
			return '<span style="color: green">OK</span>';
		}
		catch (Mollie_API_Exception $e)
		{
			$message = htmlspecialchars($e->getMessage());
			return "<span style='color:red'>$message</span>";
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

			$this->children = array();

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
