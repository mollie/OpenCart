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
 * @property Config                       $config
 * @property DB                           $db
 * @property Language                     $language
 * @property Loader                       $load
 * @property ModelSettingSetting          $model_setting_setting
 * @property ModelSettingStore            $model_setting_store
 * @property ModelLocalisationOrderStatus $model_localisation_order_status
 * @property Request                      $request
 * @property Response                     $response
 * @property URL                          $url
 * @property User                         $user
 */
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Exceptions\IncompatiblePlatform;
use Mollie\Api\MollieApiClient;

require_once(dirname(DIR_SYSTEM) . "/catalog/controller/payment/mollie/helper.php");

require_once(DIR_SYSTEM . "comercia/util.php");

use comercia\Util;

class ControllerPaymentMollieBase extends Controller
{
	// Current module name - should be overwritten by subclass using one of the MollieHelper::MODULE_NAME_* values.
	const MODULE_NAME = NULL;

	// Initialize var(s)
	protected $error = array();

	// Holds multistore configs
	protected $data = array();

	/**
	 * @param int $store The Store ID
	 * @return MollieApiClient
	 */
	protected function getAPIClient ($store = 0)
	{
		return MollieHelper::getAPIClientAdmin($this->data);
	}

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

		$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` MODIFY `payment_method` VARCHAR(255) NOT NULL;");

		// Just install all modules while we're at it.
		$this->installAllModules();
		$this->cleanUp();
	}

	/**
	 * Clean up files that are not needed for the running version of OC.
	 */
	public function cleanUp()
	{
		$adminThemeDir = DIR_APPLICATION . 'view/template/extension/payment/';
		$catalogThemeDir = DIR_CATALOG . 'view/theme/default/template/extension/payment/';

		// Remove old template from previous version.
		if (file_exists($adminThemeDir . 'mollie_2.tpl')) {
			unlink($adminThemeDir . 'mollie_2.tpl');
		}

		if (MollieHelper::isOpenCart3x()) {
			unlink($adminThemeDir . 'mollie_1.tpl');
			unlink($adminThemeDir . 'mollie.tpl');
			unlink($catalogThemeDir . 'mollie_return.tpl');
			unlink($catalogThemeDir . 'mollie_checkout_form.tpl');
		} elseif (MollieHelper::isOpenCart2x()) {
			unlink($adminThemeDir . 'mollie_1.tpl');
			unlink($adminThemeDir . 'mollie.twig');
			unlink($catalogThemeDir . 'mollie_return.twig');
			unlink($catalogThemeDir . 'mollie_checkout_form.twig');
		} else {
			unlink($adminThemeDir . 'mollie.tpl');
			unlink($adminThemeDir . 'mollie.twig');
			unlink($catalogThemeDir . 'mollie_return.twig');
			unlink($catalogThemeDir . 'mollie_checkout_form.twig');
		}
	}

	/**
	 * Trigger installation of all Mollie modules.
	 */
	protected function installAllModules ()
	{
		// Load models.
		$extensions = $this->getExtensionModel();
		$user_id = $this->getUserId();

		foreach (MollieHelper::$MODULE_NAMES as $module_name)
		{
			// Install extension.
			$extensions->install("payment", "mollie_" . $module_name);

			// Set permissions.
			$this->model_user_user_group->addPermission($user_id, "access", "payment/mollie_" . $module_name);
			$this->model_user_user_group->addPermission($user_id, "access", "extension/payment/mollie_" . $module_name);
			$this->model_user_user_group->addPermission($user_id, "modify", "payment/mollie_" . $module_name);
			$this->model_user_user_group->addPermission($user_id, "modify", "extension/payment/mollie_" . $module_name);
		}
	}

	/**
	 * The method is executed by OpenCart when the Payment module is uninstalled from the admin. It will not drop the Mollie
	 * table at this point - we want to allow the user to toggle payment modules without losing their settings.
	 *
	 * @return void
	 */
	public function uninstall ()
	{
		$this->uninstallAllModules();
	}

	/**
	 * Trigger removal of all Mollie modules.
	 */
	protected function uninstallAllModules ()
	{
		$extensions = $this->getExtensionModel();

		foreach (MollieHelper::$MODULE_NAMES as $module_name)
		{
			$extensions->uninstall("payment", "mollie_" . $module_name);
		}
	}

	/**
	 * Render the payment method's settings page.
	 */
	public function index ()
	{
		// Double-check if clean-up has been done - For upgrades
		$adminThemeDir = DIR_APPLICATION . 'view/template/extension/payment/';
		if (MollieHelper::isOpenCart3x() || MollieHelper::isOpenCart2x()) {
			if(file_exists($adminThemeDir . 'mollie_1.tpl')) {
				$this->cleanUp();
			}
		} else {
			if(file_exists($adminThemeDir . 'mollie.tpl')) {
				$this->cleanUp();
			}
		}

		//Load language data
		$data = array();
		Util::load()->language("payment/mollie", $data);
		$form = Util::form($data);
		
		// Load essential models
		$modelSetting     = Util::load()->model("setting/setting");
		$modelStore       = Util::load()->model("setting/store");
		$modelOrderStatus = Util::load()->model("localisation/order_status");
		$modelGeoZone     = Util::load()->model("localisation/geo_zone");

		$code = MollieHelper::getModuleCode();

		$form->finish(function ($data) {
			$code = MollieHelper::getModuleCode();
			$redirect = false;
            $stores = Util::info()->stores();
            foreach ($stores as $store) {
            	if ($this->validate($store["store_id"])) {
            		$configSet = Util::request()->post()->allPrefixed($store["store_id"] . "_");
	                if (!$store["store_id"]) {
	                    $configSet = array_merge($configSet, Util::request()->post()->allPrefixed($code, false));
	                }
	                Util::config($store["store_id"])->set($code, $configSet);

	                $redirect = true;
            	}
            }

            if ($redirect) {
            	Util::session()->success = $data['text_success'];
            	Util::response()->redirectToUrl(Util::url()->link(Util::route()->extension(), "type=payment"));
            }
        });

        //title
        Util::document()
            ->setTitle(Util::language()->heading_title);

        //place the prepared data into the form
        $form
            ->fillFromSessionClear("error_warning", "success");

        //Set form variables
        $paymentStatus = array();
        $paymentSortOrder = array();
        $paymentGeoZone = array();

        foreach (MollieHelper::$MODULE_NAMES as $module_name) {
        	$paymentStatus[] 	= $code . '_' . $module_name . '_status';
        	$paymentSortOrder[] = $code . '_' . $module_name . '_sort_order';
        	$paymentGeoZone[] 	= $code . '_' . $module_name . '_geo_zone';
		}

        $settingFields = array($code . "_creditcard_max_amount", $code . "_show_icons", $code . "_show_order_canceled_page", $code . "_ideal_description", $code . "_api_key", $code . "_ideal_processing_status_id", $code . "_ideal_expired_status_id", $code . "_ideal_canceled_status_id", $code . "_ideal_failed_status_id", $code . "_ideal_pending_status_id");

        $storeFormFields = array_merge($settingFields, $paymentStatus, $paymentSortOrder, $paymentGeoZone);

        $data['shops'] = Util::info()->stores();
        foreach ($data['shops'] as &$store) {

            Util::form($store, $store["store_id"])
                ->fillFromPost($storeFormFields)
                ->fillFromConfig($storeFormFields);
        }

        //Breadcrumb
        Util::breadcrumb($data)
            ->add("text_home", "common/home")
            ->add("text_payment", "payment", "type=payment")
            ->add("heading_title", "payment/mollie_" . static::MODULE_NAME);

		// Set data for template
		$data['api_check_url']          = Util::url()->link("payment/mollie_" . static::MODULE_NAME . '/validate_api_key');
		$data['module_name']          = static::MODULE_NAME;
		$data['token']          	  = $this->getTokenUriPart();
		$data['entry_version']          = $this->language->get("entry_version") . " " . MollieHelper::PLUGIN_VERSION;

		//$data['shops'] = $data['stores'];
		$data['code'] = $code;

		$data['geo_zones']			=	$modelGeoZone->getGeoZones();
		$data['order_statuses']		=	$modelOrderStatus->getOrderStatuses();

		$shops = $data['shops'];
		foreach($shops as $store)
		{
			$data['shops'][$store['store_id']]['entry_cstatus'] = $this->checkCommunicationStatus(isset($store[$code . '_api_key']) ? $store[$code . '_api_key'] : null);

			if (isset($this->error[$store['store_id']]['api_key'])) {
				$data['shops'][$store['store_id']]['error_api_key'] = $this->error[$store['store_id']]['api_key'];
			} else {
				$data['shops'][$store['store_id']]['error_api_key'] = '';
			}

			if (isset($this->error[$store['store_id']]['description'])) {
				$data['shops'][$store['store_id']]['error_description'] = $this->error[$store['store_id']]['description'];
			} else {
				$data['shops'][$store['store_id']]['error_description'] = '';
			}

			if (isset($this->error[$store['store_id']]['show_icons'])) {
				$data['shops'][$store['store_id']]['error_show_icons'] = $this->error[$store['store_id']]['show_icons'];
			} else {
				$data['shops'][$store['store_id']]['error_show_icons'] = '';
			}

			if (isset($this->error[$store['store_id']]['show_order_canceled_page'])) {
				$data['shops'][$store['store_id']]['show_order_canceled_page'] = $this->error[$store['store_id']]['show_order_canceled_page'];
			} else {
				$data['shops'][$store['store_id']]['show_order_canceled_page'] = '';
			}

			if (isset($this->error[$store['store_id']]['total'])) {
				$data['shops'][$store['store_id']]['error_total'] = $this->error[$store['store_id']]['total'];
			} else {
				$data['shops'][$store['store_id']]['error_total'] = '';
			}
		}
	
		// Form action url
		$data['action'] = Util::url()->link("payment/mollie_" . static::MODULE_NAME);
		$data['cancel'] = Util::url()->link( Util::route()->extension(), "type=payment");

		// Load global settings. Some are prefixed with mollie_ideal_ for legacy reasons.
		$settings = array(
			$code . "_api_key"                    => NULL,
			$code . "_ideal_description"          => "Order %",
			$code . "_show_icons"                 => FALSE,
			$code . "_show_order_canceled_page"   => TRUE,
			$code . "_ideal_pending_status_id"    => 1,
			$code . "_ideal_processing_status_id" => 2,
			$code . "_ideal_canceled_status_id"   => 7,
			$code . "_ideal_failed_status_id"     => 10,
			$code . "_ideal_expired_status_id"    => 14,
			$code . "_creditcard_max_amount"      => NULL,
		);

		foreach($shops as $store)
		{
			$this->data = $store;
			foreach ($settings as $setting_name => $default_value)
			{
				// Attempt to read from post
				if (isset(Util::request()->post()->{$store['store_id'] . '_' . $setting_name}))
				{
					$data['shops'][$store['store_id']][$setting_name] = Util::request()->post()->{$store['store_id'] . '_' . $setting_name};
				}

				// Otherwise, attempt to get the setting from the database
				else
				{
					// same as $this->config->get() 
					$stored_setting = !empty($this->data[$setting_name]) ? $this->data[$setting_name] : null;

					if($stored_setting === NULL && $default_value !== NULL)
					{
						$data['shops'][$store['store_id']][$setting_name] = $default_value;
					}
					else
					{
						$data['shops'][$store['store_id']][$setting_name] = $stored_setting;
					}
				}
			}

			
			// Check which payment methods we can use with the current API key.
			$allowed_methods = array();
			try
			{
				$api_methods = $this->getAPIClient($store['store_id'])->methods->all();

				foreach ($api_methods as $api_method)
				{
					$allowed_methods[] = $api_method->id;
				}
			}
			catch (Mollie\Api\Exceptions\ApiException $e)
			{
				// If we have an unauthorized request, our API key is likely invalid.
				if ($store[$code . '_api_key'] !== NULL && strpos($e->getMessage(), "Unauthorized request") >= 0)
				{
					$data['error_api_key'] = $this->language->get("error_api_key_invalid");
				}
			}

			$data['store'][$store['store_id'] . '_' . $code . '_payment_methods'] = array();

			foreach (MollieHelper::$MODULE_NAMES as $module_name)
			{
				$payment_method = array();

				$payment_method['name']    = $this->language->get("name_mollie_" . $module_name);
				$payment_method['icon']    = "https://www.mollie.com/images/payscreen/methods/" . $module_name . ".png";
				$payment_method['allowed'] = in_array($module_name, $allowed_methods);

				// Load module specific settings.
				if (isset(Util::request()->post()->{$store['store_id'] . '_' . $code . '_' . $module_name . '_status'}))
				{
					$payment_method['status'] = (Util::request()->post()->{$store['store_id'] . '_' . $code . '_' . $module_name . '_status'} == "on");
				}
				else
				{
					$payment_method['status'] = (bool) isset($store[$code . "_" . $module_name . "_status"]) ? $store[$code . "_" . $module_name . "_status"] : null;
				}

				if (isset(Util::request()->post()->{$store['store_id'] . '_' . $code . '_' . $module_name . '_sort_order'}))
				{
					$payment_method['sort_order'] = Util::request()->post()->{$store['store_id'] . '_' . $code . '_' . $module_name . '_sort_order'};
				}
				else
				{
					$payment_method['sort_order'] = isset($store[$code . "_" . $module_name . "_sort_order"]) ? $store[$code . "_" . $module_name . "_sort_order"] : null;
				}

				if (isset(Util::request()->post()->{$store['store_id'] . '_' . $code . '_' . $module_name . '_geo_zone'}))
				{
					$payment_method['geo_zone'] = Util::request()->post()->{$store['store_id'] . '_' . $code . '_' . $module_name . '_geo_zone'};
				}
				else
				{
					$payment_method['geo_zone'] = isset($store[$code . "_" . $module_name . "_geo_zone"]) ? $store[$code . "_" . $module_name . "_geo_zone"] : null;
				}

				$data['store'][$store['store_id'] . '_' . $code . '_payment_methods'][$module_name] = $payment_method;
			}
			
		}
		$template = 'mollie';
		$this->renderTemplate($template, $data, array(
			"header",
			"column_left",
			"footer",
		));
	}

    /**
     *
     */
    public function validate_api_key()
	{
		$json = array(
			'error' => false,
			'invalid' => false,
			'valid' => false,
			'message' => '',
		);

		if (empty($this->request->get['key'])) {
			$json['invalid'] = true;
			$json['message'] = 'API client not found.';
		} else {
			try {
				$client = MollieHelper::getAPIClientForKey($this->request->get['key']);

				if (!$client) {
					$json['invalid'] = true;
					$json['message'] = 'API client not found.';
				} else {
					$client->methods->all();

					$json['valid'] = true;
					$json['message'] = 'Ok.';
				}
			} catch (IncompatiblePlatform $e) {
				$json['error'] = true;
				$json['message'] = $e->getMessage() . ' You can ask your hosting provider to help with this.';
			} catch (ApiException $e) {
				$json['error'] = true;
				$json['message'] = '<strong>Communicating with Mollie failed:</strong><br/>'
					. htmlspecialchars($e->getMessage())
					. '<br/><br/>'
					. 'Please check the following conditions. You can ask your hosting provider to help with this.'
					. '<ul>'
					. '<li>Make sure outside connections to ' . (isset($client) ? htmlspecialchars($client->getApiEndpoint()) : 'Mollie') . ' are not blocked.</li>'
					. '<li>Make sure SSL v3 is disabled on your server. Mollie does not support SSL v3.</li>'
					. '<li>Make sure your server is up-to-date and the latest security patches have been installed.</li>'
					. '</ul><br/>'
					. 'Contact <a href="mailto:info@mollie.nl">info@mollie.nl</a> if this still does not fix your problem.';
			}
		}


		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Check the post and check if the user has permission to edit the module settings
	 * @param int $store The store id
	 * @return bool
	 */
	private function validate ($store = 0)
	{
		if (!$this->user->hasPermission("modify", "payment/mollie_" . static::MODULE_NAME))
		{
			$this->error['warning'] = $this->language->get("error_permission");
		}

		if (!Util::request()->post()->{$store . '_' . MollieHelper::getModuleCode() . '_api_key'})
		{
			$this->error[$store]['api_key'] = $this->language->get("error_api_key");
		}

		if (!Util::request()->post()->{$store . '_' . MollieHelper::getModuleCode() . '_ideal_description'})
		{
			$this->error[$store]['description'] = $this->language->get("error_description");
		}
		
		return (count($this->error) == 0);
	}

	/**
	 * @param string|null
	 * @return string
	 */
	protected function checkCommunicationStatus ($api_key = null)
	{
		if (empty($api_key)) {
			return '<span style="color:red">No API key provided. Please insert your API key.</span>';
		}

		try {
			$client = MollieHelper::getAPIClientForKey($api_key);

			if (!$client) {
				return '<span style="color:red">API client not found.</span>';
			}

			$client->methods->all();

			return '<span style="color: green">OK</span>';
		} catch (Mollie\Api\Exceptions\ApiException_IncompatiblePlatform $e) {
			return '<span style="color:red">' . $e->getMessage() . ' You can ask your hosting provider to help with this.</span>';
		} catch (Mollie\Api\Exceptions\ApiException $e) {
			return '<span style="color:red">'
				. '<strong>Communicating with Mollie failed:</strong><br/>'
				. htmlspecialchars($e->getMessage())
				. '</span><br/><br/>'

				. 'Please check the following conditions. You can ask your hosting provider to help with this.'
				. '<ul>'
				. '<li>Make sure outside connections to ' . ($client ? htmlspecialchars($client->getApiEndpoint()) : 'Mollie') . ' are not blocked.</li>'
				. '<li>Make sure SSL v3 is disabled on your server. Mollie does not support SSL v3.</li>'
				. '<li>Make sure your server is up-to-date and the latest security patches have been installed.</li>'
				. '</ul><br/>'

				. 'Contact <a href="mailto:info@mollie.nl">info@mollie.nl</a> if this still does not fix your problem.';
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
		if (!MollieHelper::isOpenCart3x()) {
			$template .= '.tpl';
		}

		if (MollieHelper::isOpenCart2x()) {
			foreach ($common_children as $child) {
				$data[$child] = $this->load->controller("common/" . $child);
			}

			if (MollieHelper::isOpenCart3x()) {
		        $this->config->set('template_engine', 'template');
		       $html = $this->load->view('payment/'.$template, $data);
		      } else {
		        $html = $this->load->view('payment/'.$template, $data);
		      }

		} else {
			$template = 'mollie_1.tpl';
			$this->template = 'payment/'.$template;
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
	 * Get the extension installation handler.
	 *
	 * @return Model
	 */
	protected function getExtensionModel()
	{
		if (Util::version()->isMinimal("3")) {
			$this->load->model('setting/extension');
			return $this->model_setting_extension;
		}

		if (Util::version()->isMinimal("2")) {
			$this->load->model('extension/extension');
			return $this->model_extension_extension;
		}

		$this->load->model('setting/extension');
		return $this->model_setting_extension;
	}

	/**
	 * @return string
	 */
	private function getTokenUriPart()
	{
		if (MollieHelper::isOpenCart3x()) {
			return 'user_token=' . $this->session->data['user_token'];
		}

		return 'token=' . $this->session->data['token'];
	}

	private function getUserId()
	{
		$this->load->model('user/user_group');

		if (method_exists($this->user, 'getGroupId')) {
			return $this->user->getGroupId();
		}

		return $this->user->getId();
	}

	public function saveAPIKey() {
		$this->load->model('setting/setting');
		$store_id = $_POST['store_id'];
		$code = MollieHelper::getModuleCode();
		$data = array(
			$code.'_api_key' => $_POST['api_key']
		);
		$this->model_setting_setting->editSetting($code, $data, $store_id);
		return true;
	}
}
