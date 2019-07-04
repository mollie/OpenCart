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
 
//Check if VQMod is installed
if(!class_exists('VQMod')) {
     die('<div class="alert alert-warning"><i class="fa fa-exclamation-circle"></i> This extension requires VQMod. Please download and install it on your shop. You can find the latest release <a href="https://github.com/vqmod/vqmod/releases" target="_blank">here</a>!    <button type="button" class="close" data-dismiss="alert">×</button></div>');
}

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Exceptions\IncompatiblePlatform;
use Mollie\Api\MollieApiClient;

require_once(dirname(DIR_SYSTEM) . "/catalog/controller/payment/mollie/helper.php");

define("MOLLIE_VERSION", MollieHelper::PLUGIN_VERSION);
define("MOLLIE_RELEASE", "v" . MOLLIE_VERSION);
define("MOLLIE_VERSION_URL", "https://api.github.com/repos/mollie/OpenCart/releases/latest");
// Defining arrays in a constant cannot be done with "define" until PHP 7, so using this syntax for backwards compatibility.
const DEPRECATED_METHODS = array('mistercash', 'bitcoin');

if (!defined("MOLLIE_TMP")) {
    define("MOLLIE_TMP", sys_get_temp_dir());
}

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
					`mollie_order_id` varchar(32) NOT NULL,
					`transaction_id` varchar(32) NOT NULL,
					`bank_account` varchar(15) NOT NULL,
					`bank_status` varchar(20) NOT NULL,
					PRIMARY KEY (`order_id`),
					UNIQUE KEY `mollie_order_id` (`mollie_order_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8",
				DB_PREFIX
			));

		$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` MODIFY `payment_method` VARCHAR(255) NOT NULL;");

		// Just install all modules while we're at it.
		$this->installAllModules();
		$this->cleanUp();

		//Add event to create shipment
		if (Util::version()->isMinimal(2.2)) { // Events were added in OC2.2
			$modelEvent = Util::load()->model('extension/event');
			if (MollieHelper::isOpenCart3x()) {
				$modelEvent->deleteEventByCode('mollie_create_shipment');
			} else {
				$modelEvent->deleteEvent('mollie_create_shipment');
			}

			$modelEvent->addEvent('mollie_create_shipment', 'catalog/model/checkout/order/addOrderHistory/after', 'payment/mollie/base/createShipment');
		}
	}
	
	//Check for patch
	public function patch()
    {
        Util::patch()->runPatchesFromFolder('mollie', __FILE__);
    }

	/**
	 * Clean up files that are not needed for the running version of OC.
	 */
	public function cleanUp()
	{
		$adminThemeDir = DIR_APPLICATION . 'view/template/';
		$catalogThemeDir = DIR_CATALOG . 'view/theme/default/template/';

		// Add new column if it doesn't exist yet
		$this->patch();

		// Remove old template from previous version.
		if (file_exists($adminThemeDir . 'extension/payment/mollie_2.tpl')) {
			unlink($adminThemeDir . 'extension/payment/mollie_2.tpl');
			unlink($adminThemeDir . 'payment/mollie_2.tpl');
		}

		//Remove deprecated method files from old version
		$adminControllerDir   = DIR_APPLICATION . 'controller/';
		$adminLanguageDir     = DIR_APPLICATION . 'language/';
		$catalogControllerDir = DIR_CATALOG . 'controller/';
		$catalogModelDir      = DIR_CATALOG . 'model/';

		foreach (DEPRECATED_METHODS as $method) {
			if (file_exists($adminControllerDir . 'extension/payment/mollie_' . $method . '.php')) {
				unlink($adminControllerDir . 'extension/payment/mollie_' . $method . '.php');
			}

			$languageFiles = glob($adminLanguageDir . '*/extension/payment/mollie_' . $method . '.php');
			foreach ($languageFiles as $file) {
				if (file_exists($file)) {
					unlink($file);
				}
			}

			if (file_exists($catalogControllerDir . 'extension/payment/mollie_' . $method . '.php')) {
				unlink($catalogControllerDir . 'extension/payment/mollie_' . $method . '.php');
			}

			if (file_exists($catalogModelDir . 'extension/payment/mollie_' . $method . '.php')) {
				unlink($catalogModelDir . 'extension/payment/mollie_' . $method . '.php');
			}

			if (file_exists($adminControllerDir . 'payment/mollie_' . $method . '.php')) {
				unlink($adminControllerDir . 'payment/mollie_' . $method . '.php');
			}

			$languageFiles = glob($adminLanguageDir . '*/payment/mollie_' . $method . '.php');
			foreach ($languageFiles as $file) {
				if (file_exists($file)) {
					unlink($file);
				}
			}

			if (file_exists($catalogControllerDir . 'payment/mollie_' . $method . '.php')) {
				unlink($catalogControllerDir . 'payment/mollie_' . $method . '.php');
			}

			if (file_exists($catalogModelDir . 'payment/mollie_' . $method . '.php')) {
				unlink($catalogModelDir . 'payment/mollie_' . $method . '.php');
			}
		}

		if (MollieHelper::isOpenCart3x()) {
			if(file_exists($adminThemeDir . 'extension/payment/mollie(max_1.5.6.4).tpl')) {
				unlink($adminThemeDir . 'extension/payment/mollie(max_1.5.6.4).tpl');
				unlink($adminThemeDir . 'payment/mollie(max_1.5.6.4).tpl');
				unlink($catalogThemeDir . 'extension/payment/mollie_return.tpl');
				unlink($catalogThemeDir . 'payment/mollie_return.tpl');
				unlink($catalogThemeDir . 'extension/payment/mollie_checkout_form.tpl');
				unlink($catalogThemeDir . 'payment/mollie_checkout_form.tpl');
			}
			//Remove twig file from old version
			if(file_exists($adminThemeDir . 'extension/payment/mollie.twig')) {
				unlink($adminThemeDir . 'extension/payment/mollie.twig');
			}
			if(file_exists($adminThemeDir . 'payment/mollie.twig')) {
				unlink($adminThemeDir . 'payment/mollie.twig');
			}
		} elseif (MollieHelper::isOpenCart2x()) {
			if(file_exists($adminThemeDir . 'extension/payment/mollie(max_1.5.6.4).tpl')) {
				unlink($adminThemeDir . 'extension/payment/mollie(max_1.5.6.4).tpl');
				unlink($adminThemeDir . 'payment/mollie(max_1.5.6.4).tpl');
				unlink($catalogThemeDir . 'extension/payment/mollie_return.twig');
				unlink($catalogThemeDir . 'payment/mollie_return.twig');
				unlink($catalogThemeDir . 'extension/payment/mollie_checkout_form.twig');
				unlink($catalogThemeDir . 'payment/mollie_checkout_form.twig');
			}
		} else {
			if(file_exists($adminThemeDir . 'extension/payment/mollie.tpl')) {
				unlink($adminThemeDir . 'extension/payment/mollie.tpl');
				unlink($adminThemeDir . 'payment/mollie.tpl');
				unlink($catalogThemeDir . 'extension/payment/mollie_return.twig');
				unlink($catalogThemeDir . 'payment/mollie_return.twig');
				unlink($catalogThemeDir . 'extension/payment/mollie_checkout_form.twig');
				unlink($catalogThemeDir . 'payment/mollie_checkout_form.twig');
			}
		}

		// Remove base.php file from version 8.x
		if (file_exists($adminControllerDir . 'extension/payment/mollie')) {
			$this->delTree($adminControllerDir . 'extension/payment/mollie');
		}

		if (file_exists($catalogControllerDir . 'extension/payment/mollie')) {
			$this->delTree($catalogControllerDir . 'extension/payment/mollie');
		}

		if (file_exists($catalogControllerDir . 'extension/payment/mollie-api-client')) {
			$this->delTree($catalogControllerDir . 'extension/payment/mollie-api-client');
		}

	}

	public function delTree($dir) {
		$files = array_diff(scandir($dir), array('.','..'));
	    foreach ($files as $file) {
	      (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
	    }
	    return rmdir($dir);
	}

	/**
	 * Trigger installation of all Mollie modules.
	 */
	protected function installAllModules ()
	{
		// Load models.
		$extensions = Util::load()->model('extension/extension');
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
		$extensions = Util::load()->model('extension/extension');

		foreach (MollieHelper::$MODULE_NAMES as $module_name)
		{
			$extensions->uninstall("payment", "mollie_" . $module_name);
		}
	}

	//Delete deprecated method data from setting
	public function clearData() {
		foreach (DEPRECATED_METHODS as $method) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `key` LIKE '%$method%'");
			if ($query->num_rows > 0) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `key` LIKE '%$method%'");
			}
		}
	}

	/**
	 * Render the payment method's settings page.
	 */
	public function index ()
	{
		// Double-check if clean-up has been done - For upgrades
		if (empty($this->config->get('mollie_payment_version')) || $this->config->get('mollie_payment_version') < MOLLIE_VERSION) {
			$this->cleanUp();
			Util::config(0)->set('mollie_payment', 'mollie_payment_version', MOLLIE_VERSION);
		}

		$adminThemeDir = DIR_APPLICATION . 'view/template/';
		if (MollieHelper::isOpenCart3x() || MollieHelper::isOpenCart2x()) {
			if(file_exists($adminThemeDir . 'extension/payment/mollie(max_1.5.6.4).tpl') || file_exists($adminThemeDir . 'extension/payment/mollie.twig') || file_exists($adminThemeDir . 'payment/mollie.twig')) {
				$this->cleanUp();
			}
		} else {
			if(file_exists($adminThemeDir . 'extension/payment/mollie.tpl')) {
				$this->cleanUp();
			}
		}

		$adminControllerDir   = DIR_APPLICATION . 'controller/';
		foreach (DEPRECATED_METHODS as $method) {
			if (file_exists($adminControllerDir . 'extension/payment/mollie_' . $method . '.php') || file_exists($adminControllerDir . 'payment/mollie_' . $method . '.php')) {
				$this->cleanUp();
			}
		}

		$catalogControllerDir   = DIR_CATALOG . 'controller/';
		
		// Remove un-used files from version 8.x
		if (file_exists($adminControllerDir . 'extension/payment/mollie') || file_exists($catalogControllerDir . 'extension/payment/mollie') || file_exists($catalogControllerDir . 'extension/payment/mollie-api-client')) {
			$this->cleanUp();
		}

		//Also delete data related to deprecated modules from settings
		$this->clearData();

		//Load language data
		$data = array("version" => MOLLIE_RELEASE);
		Util::load()->language("payment/mollie", $data);
		$this->data = $data;
		$form = Util::form($data);
		
		// Load essential models
		$modelSetting     = Util::load()->model("setting/setting");
		$modelStore       = Util::load()->model("setting/store");
		$modelOrderStatus = Util::load()->model("localisation/order_status");
		$modelGeoZone     = Util::load()->model("localisation/geo_zone");
		Util::load()->library("mollieHttpClient");

		$code = MollieHelper::getModuleCode();

		$form->finish(function ($data) {
			$code = MollieHelper::getModuleCode();
			$redirect = true;
            $stores = Util::info()->stores();
            foreach ($stores as $store) {
            	if(count($stores) > 1) {
            		$configSet = Util::request()->post()->allPrefixed($store["store_id"] . "_");
	                if (!$store["store_id"]) {
	                    $configSet = array_merge($configSet, Util::request()->post()->allPrefixed($code, false));
	                }
	                Util::config($store["store_id"])->set($code, $configSet);
            	} else {
            		if ($this->validate($store["store_id"])) {
	            		$configSet = Util::request()->post()->allPrefixed($store["store_id"] . "_");
		                if (!$store["store_id"]) {
		                    $configSet = array_merge($configSet, Util::request()->post()->allPrefixed($code, false));
		                }
		                Util::config($store["store_id"])->set($code, $configSet);
	            	}
	            	else {
	            		$redirect = false;
	            	}
            	}
            }

            if ($redirect) {
            	Util::session()->success = $data['text_success'];
            	Util::response()->redirectToUrl(Util::url()->link(Util::route()->extension(false, 'payment'), "type=payment"));
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

        $fields = array("show_icons", "show_order_canceled_page", "ideal_description", "api_key", "ideal_processing_status_id", "ideal_expired_status_id", "ideal_canceled_status_id", "ideal_failed_status_id", "ideal_pending_status_id", "ideal_shipping_status_id", "create_shipment_status_id", "create_shipment");
        $settingFields = Util::arrayHelper()->prefixAllValues($code . '_', $fields);

        $storeFormFields = array_merge($settingFields, $paymentStatus, $paymentSortOrder, $paymentGeoZone);

        $data['shops'] = Util::info()->stores();
        foreach ($data['shops'] as &$store) {

            Util::form($store, $store["store_id"])
                ->fillFromPost($storeFormFields)
                ->fillFromConfig($storeFormFields);
        }

        //API key not required for multistores
        $data['api_required'] = true;
        
        if(count($data['shops']) > 1) {
        	$data['api_required'] = false;
        }

        //Breadcrumb
        Util::breadcrumb($data)
            ->add("text_home", "common/home")
            ->add("text_payment", Util::route()->extension(false, 'payment'), "type=payment")
            ->add("heading_title", Util::route()->extension("mollie_" . static::MODULE_NAME, 'payment'));

		// Set data for template
		$data['api_check_url']          = Util::url()->link("payment/mollie_" . static::MODULE_NAME . '/validate_api_key');
		$data['module_name']          = static::MODULE_NAME;
		$data['token']          	  = $this->getTokenUriPart();
		$data['entry_version']          = $this->language->get("entry_version") . " " . MollieHelper::PLUGIN_VERSION;
		$data['update_url'] = $this->getUpdateUrl();

		$data['code'] = $code;

		$data['geo_zones']			=	$modelGeoZone->getGeoZones();
		$data['order_statuses']		=	$modelOrderStatus->getOrderStatuses();

		// Form action url
		$data['action'] = Util::url()->link("payment/mollie_" . static::MODULE_NAME);
		$data['cancel'] = Util::url()->link( Util::route()->extension(false, 'payment'), "type=payment");

		// Load global settings. Some are prefixed with mollie_ideal_ for legacy reasons.
		$settings = array(
			$code . "_api_key"                    				=> NULL,
			$code . "_ideal_description"          				=> "Order %",
			$code . "_show_icons"                 				=> FALSE,
			$code . "_show_order_canceled_page"   				=> FALSE,
			$code . "_ideal_pending_status_id"    				=> 1,
			$code . "_ideal_processing_status_id" 				=> 2,
			$code . "_ideal_canceled_status_id"   				=> 7,
			$code . "_ideal_failed_status_id"     				=> 10,
			$code . "_ideal_expired_status_id"    				=> 14,
			$code . "_ideal_shipping_status_id"   				=> 3,
			$code . "_create_shipment_status_id"  				=> 3,
			$code . "_create_shipment"  		  				=> 1,
		);

		// Check if order complete status is defined in store setting
		$data['is_order_complete_status'] = true;
		$data['order_complete_statuses'] = array();
		if((null == Util::config()->get('config_complete_status')) && (Util::config()->get('config_complete_status_id')) == '') {
			$data['is_order_complete_status'] = false;
		}

		foreach($data['shops'] as &$store)
		{
			$this->data = $store;
			foreach ($settings as $setting_name => $default_value)
			{
				// Attempt to read from post
				if (null != Util::request()->post()->get($store['store_id'] . '_' . $setting_name))
				{
					$data['shops'][$store['store_id']][$setting_name] = Util::request()->post()->get($store['store_id'] . '_' . $setting_name);
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
				$api_methods = $this->getAPIClient()->methods->all(array('resource' => 'orders'));

				foreach ($api_methods as $api_method)
				{
					$allowed_methods[] = $api_method->id;
				}
			}
			catch (Mollie\Api\Exceptions\ApiException $e)
			{
				// If we have an unauthorized request, our API key is likely invalid.
				if ($store[$code . '_api_key'] !== NULL && strpos($e->getMessage(), "Unauthorized request") !== false)
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
				if (isset($store[$store['store_id'] . '_' . $code . '_' . $module_name . '_status']))
				{
					$payment_method['status'] = ($store[$store['store_id'] . '_' . $code . '_' . $module_name . '_status'] == "on");
				}
				else
				{
					$payment_method['status'] = (bool) isset($store[$code . "_" . $module_name . "_status"]) ? $store[$code . "_" . $module_name . "_status"] : null;
				}

				if (isset($store[$store['store_id'] . '_' . $code . '_' . $module_name . '_sort_order']))
				{
					$payment_method['sort_order'] = $store[$store['store_id'] . '_' . $code . '_' . $module_name . '_sort_order'];
				}
				else
				{
					$payment_method['sort_order'] = isset($store[$code . "_" . $module_name . "_sort_order"]) ? $store[$code . "_" . $module_name . "_sort_order"] : null;
				}

				if (isset($store[$store['store_id'] . '_' . $code . '_' . $module_name . '_geo_zone']))
				{
					$payment_method['geo_zone'] = $store[$store['store_id'] . '_' . $code . '_' . $module_name . '_geo_zone'];
				}
				else
				{
					$payment_method['geo_zone'] = isset($store[$code . "_" . $module_name . "_geo_zone"]) ? $store[$code . "_" . $module_name . "_geo_zone"] : null;
				}

				$data['store'][$store['store_id'] . '_' . $code . '_payment_methods'][$module_name] = $payment_method;
			}

			$data['shops'][$store['store_id']]['entry_cstatus'] = $this->checkCommunicationStatus(isset($store[$code . '_api_key']) ? $store[$code . '_api_key'] : null);

			Util::validation($store, $store['store_id'], $this->error)
				->notIsset('error_api_key', 'api_key')
				->notIsset('error_description', 'description');
			
		}
		Util::response()->view("payment/mollie", $data);
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

		if (!Util::request()->post()->get($store . '_' . MollieHelper::getModuleCode() . '_api_key'))
		{
			$this->error[$store]['api_key'] = $this->data["error_api_key"];
		}

		if (!Util::request()->post()->get($store . '_' . MollieHelper::getModuleCode() . '_ideal_description'))
		{
			$this->error[$store]['description'] = $this->data["error_description"];
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
	 * @return string
	 */
	private function getTokenUriPart()
	{
		if (isset($this->session->data['user_token'])) {
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
		$settingModel = Util::load()->model('setting/setting');
		$store_id = $_POST['store_id'];
		$code = MollieHelper::getModuleCode();

		$data = $settingModel->getSetting($code, $store_id);
		$data[$code.'_api_key'] = $_POST['api_key'];
		
		$settingModel->editSetting($code, $data, $store_id);
		return true;
	}

		 private function getUpdateUrl()
    {
        $client = new mollieHttpClient();
        $info = $client->get(MOLLIE_VERSION_URL);
        if ($info["tag_name"] && $info["tag_name"] != MOLLIE_RELEASE && version_compare(MOLLIE_RELEASE, $info["tag_name"], "<")) {
            return Util::url()->link("payment/mollie_" . static::MODULE_NAME . '/update');
        }
        return false;
    }

    function update()
    {
        Util::load()->library("mollieHttpClient");

        //get info
        $client = new mollieHttpClient();
        $info = $client->get(MOLLIE_VERSION_URL);

        //save tmp file
        $temp_file = MOLLIE_TMP . "/mollieUpdate.zip";
        $handle = fopen($temp_file, "w+");
		$content = $client->get($info["assets"][0]["browser_download_url"], false, false);
        fwrite($handle, $content);
        fclose($handle);


        //extract to temp dir
        $temp_dir = MOLLIE_TMP . "/mollieUpdate";
        if (class_exists("ZipArchive")) {
            $zip = new ZipArchive;
            $zip->open($temp_file);
            $zip->extractTo($temp_dir);
            $zip->close();
        } else {
            shell_exec("unzip " . $temp_file . " -d " . $temp_dir);
        }

        //find upload path

        $handle = opendir($temp_dir);
        $upload_dir = $temp_dir . "/upload";
        while ($file = readdir($handle)) {
            if ($file != "." && $file != ".." && is_dir($temp_dir . "/" . $file . "/upload")) {
                $upload_dir = $temp_dir . "/" . $file . "/upload";
                break;
            }
        }

        //copy files
        $handle = opendir($upload_dir);
        while ($file = readdir($handle)) {
            if ($file != "." && $file != "..") {
                $from = $upload_dir . "/" . $file;
                if ($file == "admin") {
                    $to = DIR_APPLICATION;
                } elseif ($file == "system") {
                    $to = DIR_SYSTEM;
                } else {
                    $to = DIR_CATALOG . "../" . $file . "/";
                }
                $this->cpy($from, $to);
            }

        }

        //cleanup
        unlink($temp_file);
        $this->rmDirRecursive($temp_dir);

        //go back
        Util::response()->redirectBack();
    }

    public function rmDirRecursive($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->rmDirRecursive("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    function cpy($source, $dest)
    {
        if (is_dir($source)) {
            $dir_handle = opendir($source);
            while ($file = readdir($dir_handle)) {
                if ($file != "." && $file != "..") {
                    if (is_dir($source . "/" . $file)) {
                        if (!is_dir($dest . "/" . $file)) {
                            mkdir($dest . "/" . $file);
                        }
                        $this->cpy($source . "/" . $file, $dest . "/" . $file);
                    } else {
                        copy($source . "/" . $file, $dest . "/" . $file);
                    }
                }
            }
            closedir($dir_handle);
        } else {
            copy($source, $dest);
        }
    }
}
