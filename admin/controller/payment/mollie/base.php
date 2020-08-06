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
$vqversion = '';
if (!class_exists('VQMod')) {
     die('<div class="alert alert-warning"><i class="fa fa-exclamation-circle"></i> This extension requires VQMod. Please download and install it on your shop. You can find the latest release <a href="https://github.com/vqmod/vqmod/releases" target="_blank">here</a>!    <button type="button" class="close" data-dismiss="alert">×</button></div>');
} else {
	$vqversion = VQMod::$_vqversion;
}
define("VQ_VERSION", $vqversion);

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

use util\Util;

class ControllerPaymentMollieBase extends Controller {
	// Current module name - should be overwritten by subclass using one of the MollieHelper::MODULE_NAME_* values.
	const MODULE_NAME = NULL;
	const OUTH_URL = 'https://api.mollie.com/oauth2';

	// Initialize var(s)
	protected $error = array();

	// Holds multistore configs
	protected $data = array();

	/**
	 * @param int $store The Store ID
	 * @return MollieApiClient
	 */
	protected function getAPIClient ($store = 0) {
		$data = $this->data;
		$data[MollieHelper::getModuleCode() . "_api_key"] = MollieHelper::getApiKey($store);
		
		return MollieHelper::getAPIClientAdmin($data);
	}

	public function mollieConnect() {

		$this->session->data['mollie_connect_store_id'] = $this->request->get['store_id'];
		if(version_compare(VERSION, '2.3.0.2', '>=') == true) {
			$redirect_uri = HTTPS_SERVER . 'index.php?route=extension/payment/mollie_bancontact/mollieConnectCallback';
		} else {
			$redirect_uri = HTTPS_SERVER . 'index.php?route=payment/mollie_bancontact/mollieConnectCallback';
		}

		$data = array(
			'client_id' => $this->request->get['client_id'],
			'state'		=> isset($this->session->data['user_token']) ? $this->session->data['user_token'] : $this->session->data['token'],
			'redirect_uri'		=> $redirect_uri,
			'scope'		=> 'payments.read payments.write customers.read customers.write profiles.read profiles.write orders.read orders.write organizations.read organizations.write settlements.read',
			'response_type'		=> 'code',
			'approval_prompt'		=> 'auto'
		);

		$queryString = http_build_query($data) . "\n";

        Util::response()->redirectToUrl("https://www.mollie.com/oauth2/authorize?".$queryString);
	}

	public function mollieConnectCallback() {

		Util::load()->language("payment/mollie");
		$settingModel = Util::load()->model("setting/setting");
		$code = MollieHelper::getModuleCode();

		if(isset($this->request->get['error'])) {
			Util::session()->warning = $this->request->get['error_description'];
			Util::response()->redirect(Util::route()->extension("mollie_" . static::MODULE_NAME, 'payment'));
		}

		if(isset($this->session->data['user_token'])) {
			$token = $this->session->data['user_token'];
		} else {
			$token = $this->session->data['token'];
		}

		if(!isset($this->request->get['state']) || ($this->request->get['state'] != $token)) {
			return new Action('common/login');
		}

		if(version_compare(VERSION, '2.3.0.2', '>=') == true) {
			$redirect_uri = HTTPS_SERVER . 'index.php?route=extension/payment/mollie_bancontact/mollieConnectCallback';
		} else {
			$redirect_uri = HTTPS_SERVER . 'index.php?route=payment/mollie_bancontact/mollieConnectCallback';
		}

		$settingData = $settingModel->getSetting($code, $this->session->data['mollie_connect_store_id']);

		$data = array(
			'client_id' => $settingData[$code . '_client_id'],
            'client_secret' => $settingData[$code . '_client_secret'],
			'grant_type' => 'authorization_code',
			'code' => $this->request->get['code'],
			'redirect_uri'		=> $redirect_uri
		);

		$result = MollieHelper::curlRequest('tokens', $data);

		// Save refresh token
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `key` = '" . $code . "_refresh_token' AND `store_id` = '" . $this->session->data['mollie_connect_store_id'] . "'");

		if(version_compare(VERSION, '2.0', '<') == true) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . $this->session->data['mollie_connect_store_id'] . "', `group` = '" . $code . "', `key` = '" . $code . "_refresh_token', `value` = '" . $result->refresh_token . "'");
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . $this->session->data['mollie_connect_store_id'] . "', `code` = '" . $code . "', `key` = '" . $code . "_refresh_token', `value` = '" . $result->refresh_token . "'");
		}

		$this->session->data['mollie_access_token'][$this->session->data['mollie_connect_store_id']] = $result->access_token;
		unset($this->session->data['mollie_connect_store_id']);

		Util::session()->success = $this->language->get('text_connection_success');
		Util::response()->redirectToUrl(Util::url()->link(Util::route()->extension("mollie_" . static::MODULE_NAME, 'payment')));

	}

	public function enablePaymentMethod() {
		Util::load()->language("payment/mollie");
		$code = MollieHelper::getModuleCode();

		$method  = $this->request->get['method'];
		$api_key = MollieHelper::getSettingValue($code . '_api_key', $this->request->get['store_id']);
		try
			{
				$api = MollieHelper::getAPIClientForKey($api_key);

				$profile = $api->profiles->getCurrent();

				$mollie = MollieHelper::getAPIClientForAccessToken($this->session->data['mollie_access_token'][$this->request->get['store_id']]);
				$profile = $mollie->profiles->get($profile->id);
				$profile->enableMethod($method);

				Util::session()->success = $this->language->get('text_success');
				Util::response()->redirectToUrl(Util::url()->link(Util::route()->extension("mollie_" . static::MODULE_NAME, 'payment')));

			}
			catch (Mollie\Api\Exceptions\ApiException $e)
			{
				Util::session()->warning = $this->language->get('text_error');
				Util::response()->redirectToUrl(Util::url()->link(Util::route()->extension("mollie_" . static::MODULE_NAME, 'payment')));
			}
	}

	public function disablePaymentMethod() {
		Util::load()->language("payment/mollie");
		$code = MollieHelper::getModuleCode();

		$method  = $this->request->get['method'];
		$api_key = MollieHelper::getSettingValue($code . '_api_key', $this->request->get['store_id']);
		try
			{
				$api = MollieHelper::getAPIClientForKey($api_key);

				$profile = $api->profiles->getCurrent();

				$mollie = MollieHelper::getAPIClientForAccessToken($this->session->data['mollie_access_token'][$this->request->get['store_id']]);
				$profile = $mollie->profiles->get($profile->id);
				$profile->disableMethod($method);

				Util::session()->success = $this->language->get('text_success');
				Util::response()->redirectToUrl(Util::url()->link(Util::route()->extension("mollie_" . static::MODULE_NAME, 'payment')));

			}
			catch (Mollie\Api\Exceptions\ApiException $e)
			{
				Util::session()->warning = $this->language->get('text_error');
				Util::response()->redirectToUrl(Util::url()->link(Util::route()->extension("mollie_" . static::MODULE_NAME, 'payment')));
			}
	}

	/**
	 * This method is executed by OpenCart when the Payment module is installed from the admin. It will create the
	 * required tables.
	 *
	 * @return void
	 */
	public function install () {
		// Just install all modules while we're at it.
		$this->installAllModules();
		$this->cleanUp();
		// Run database patch
		$this->patch();

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
	public function patch() {
        Util::patch()->runPatchesFromFolder('mollie', __FILE__);
    }

	/**
	 * Clean up files that are not needed for the running version of OC.
	 */
	public function cleanUp() {
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

		$files = array();

		foreach (DEPRECATED_METHODS as $method) {
			$files = array(
				$adminControllerDir . 'extension/payment/mollie_' . $method . '.php',
				$catalogControllerDir . 'extension/payment/mollie_' . $method . '.php',
				$catalogModelDir . 'extension/payment/mollie_' . $method . '.php',
				$adminControllerDir . 'payment/mollie_' . $method . '.php',
				$catalogControllerDir . 'payment/mollie_' . $method . '.php',
				$catalogModelDir . 'payment/mollie_' . $method . '.php'
			);

			foreach ($files as $file) {
				if (file_exists($file)) {
					unlink($file);
				}
			}

			$languageFiles = glob($adminLanguageDir . '*/extension/payment/mollie_' . $method . '.php');
			foreach ($languageFiles as $file) {
				if (file_exists($file)) {
					unlink($file);
				}
			}

			$languageFiles = glob($adminLanguageDir . '*/payment/mollie_' . $method . '.php');
			foreach ($languageFiles as $file) {
				if (file_exists($file)) {
					unlink($file);
				}
			}
		}

		if (MollieHelper::isOpenCart3x()) {
			$files = array(
				$adminThemeDir . 'extension/payment/mollie(max_1.5.6.4).tpl',
				$adminThemeDir . 'payment/mollie(max_1.5.6.4).tpl',
				$catalogThemeDir . 'extension/payment/mollie_return.tpl',
				$catalogThemeDir . 'payment/mollie_return.tpl',
				$catalogThemeDir . 'extension/payment/mollie_checkout_form.tpl',
				$catalogThemeDir . 'payment/mollie_checkout_form.tpl',
				$adminThemeDir . 'extension/payment/mollie.twig', //Remove twig file from old version
				$adminThemeDir . 'payment/mollie.twig' //Remove twig file from old version
			);
			
		} elseif (MollieHelper::isOpenCart2x()) {
			$files = array(
				$adminThemeDir . 'extension/payment/mollie(max_1.5.6.4).tpl',
				$adminThemeDir . 'payment/mollie(max_1.5.6.4).tpl',
				$catalogThemeDir . 'extension/payment/mollie_return.twig',
				$catalogThemeDir . 'payment/mollie_return.twig',
				$catalogThemeDir . 'extension/payment/mollie_checkout_form.twig',
				$catalogThemeDir . 'payment/mollie_checkout_form.twig'
			);
			
		} else {
			$files = array(
				$adminThemeDir . 'extension/payment/mollie.tpl',
				$adminThemeDir . 'payment/mollie.tpl',
				$catalogThemeDir . 'extension/payment/mollie_return.twig',
				$catalogThemeDir . 'payment/mollie_return.twig',
				$catalogThemeDir . 'extension/payment/mollie_checkout_form.twig',
				$catalogThemeDir . 'payment/mollie_checkout_form.twig'
			);
			
		}

		foreach ($files as $file) {
			if (file_exists($file)) {
				unlink($file);
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

		if (file_exists(DIR_APPLICATION . '../vqmod/xml/mollie_onepage_no_givenname.xml')) {
			unlink(DIR_APPLICATION . '../vqmod/xml/mollie_onepage_no_givenname.xml');
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
	 * Insert variables that are added in later versions.
	*/
	public function updateSettings() {
		$code = MollieHelper::getModuleCode();
        $stores = Util::info()->stores();
        $vars = array(
        	'default_currency' => 'DEF' // variable => default value
        );
        foreach($stores as $store) {
        	foreach($vars as $var=>$val) {
        		if (null == Util::config($store['store_id'])->get($code . '_' . $var, true)) {
					Util::config($store['store_id'])->setValue($code, $code . '_' . $var, $val);
				}
        	}
        }
	}

	/**
	 * Trigger installation of all Mollie modules.
	 */
	protected function installAllModules () {
		// Load models.
		$extensions = Util::load()->model('extension/extension');
		$user_id = $this->getUserId();

		foreach (MollieHelper::$MODULE_NAMES as $module_name) {
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
	public function uninstall () {
		$this->uninstallAllModules();
	}

	/**
	 * Trigger removal of all Mollie modules.
	 */
	protected function uninstallAllModules () {
		$extensions = Util::load()->model('extension/extension');

		foreach (MollieHelper::$MODULE_NAMES as $module_name) {
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
	public function index () {
		// Double-check if clean-up has been done - For upgrades
		if (empty($this->config->get('mollie_payment_version')) || $this->config->get('mollie_payment_version') < MOLLIE_VERSION) {
			Util::config(0)->set('mollie_payment', 'mollie_payment_version', MOLLIE_VERSION);
		}

		// Run cleanup
		$this->cleanUp();

		//Also delete data related to deprecated modules from settings
		$this->clearData();

		// Run database patch
		$this->patch();

		// Update settings with newly added variables
		$this->updateSettings();

		//Load language data
		$data = array("version" => MOLLIE_RELEASE);
		Util::load()->language("payment/mollie", $data);
		$this->data = $data;
		$form = Util::form($data);
		
		// Load essential models
		$modelOrderStatus = Util::load()->model("localisation/order_status");
		$modelGeoZone     = Util::load()->model("localisation/geo_zone");
		$modelLanguage     = Util::load()->model("localisation/language");
		$modelCurrency     = Util::load()->model("localisation/currency");
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

            	// Remove refresh token if app credentials are changed
            	$removeToken = false;
            	$settingData = Util::request()->post()->allPrefixed($store["store_id"] . "_");
            	if(!empty($this->session->data['app_data'])) {
            		if(isset($this->session->data['app_data'][$store["store_id"]]['client_id']) && ($this->session->data['app_data'][$store["store_id"]]['client_id'] != $settingData[$code . '_client_id'])) {
            			$removeToken = true;
            		} else if (isset($this->session->data['app_data'][$store["store_id"]]['client_secret']) && ($this->session->data['app_data'][$store["store_id"]]['client_secret'] != $settingData[$code . '_client_secret'])) {
            			$removeToken = true;
            		}
            	}

            	if($removeToken) {
            		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `key` = '" . $code . "_refresh_token' AND `store_id` = '" . $store["store_id"] . "'");
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

        $fields = array("show_icons", "show_order_canceled_page", "ideal_description", "api_key", "client_id", "client_secret", "refresh_token", "ideal_processing_status_id", "ideal_expired_status_id", "ideal_canceled_status_id", "ideal_failed_status_id", "ideal_pending_status_id", "ideal_shipping_status_id", "create_shipment_status_id", "ideal_refund_status_id", "create_shipment", "payment_screen_language", "debug_mode", "mollie_component", "mollie_component_css_base", "mollie_component_css_valid", "mollie_component_css_invalid", "default_currency");

        $settingFields = Util::arrayHelper()->prefixAllValues($code . '_', $fields);

        $storeFormFields = array_merge($settingFields, $paymentStatus, $paymentSortOrder, $paymentGeoZone);

        $data['stores'] = Util::info()->stores();
        foreach ($data['stores'] as &$store) {

            Util::form($store, $store["store_id"])
                ->fillFromPost($storeFormFields)
                ->fillFromConfig($storeFormFields);
        }

        // Generate access token
        foreach ($data['stores'] as &$store) {
            $accessToken = MollieHelper::generateAccessToken($store["store_id"]);
            $this->session->data['mollie_access_token'][$store["store_id"]] = $accessToken;
        }

        //API key not required for multistores
        $data['api_required'] = true;
        
        if(count($data['stores']) > 1) {
        	$data['api_required'] = false;
        }

        //Breadcrumb
        Util::breadcrumb($data)
            ->add("text_home", "common/home")
            ->add("text_payment", Util::route()->extension(false, 'payment'), "type=payment")
            ->add("heading_title", Util::route()->extension("mollie_" . static::MODULE_NAME, 'payment'));

		// Set data for template
        $data['module_name']        = static::MODULE_NAME;
        $data['api_check_url']      = Util::url()->link("payment/mollie_" . static::MODULE_NAME . '/validate_api_key');
        $data['entry_version']      = $this->language->get("entry_version") . " " . MollieHelper::PLUGIN_VERSION;
        $data['code']               = $code;
		$data['token']          	= $this->getTokenUriPart();
		$data['update_url']         = ($this->getUpdateUrl()) ? $this->getUpdateUrl()['updateUrl'] : '';
        $data['text_update']        = ($this->getUpdateUrl()) ? sprintf($this->language->get('text_update_message'), $this->getUpdateUrl()['updateVersion'], $data['update_url']) : '';
		$data['geo_zones']			= $modelGeoZone->getGeoZones();
		$data['order_statuses']		= $modelOrderStatus->getOrderStatuses();
		$data['languages']			= $modelLanguage->getLanguages();
		$data['currencies']			= $modelCurrency->getCurrencies();

		$imageModel = Util::load()->model('tool/image');
		if (is_file(DIR_IMAGE . 'mollie_connect.png')) {
			$data['image'] = $imageModel->resize('mollie_connect.png', 400, 90);
		} else {
			$data['image'] = '';
		}

		if(isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			$this->session->data['success'] = '';
		} else {
			$data['success'] = '';
		}

		if(isset($this->session->data['warning'])) {
			$data['warning'] = $this->session->data['warning'];
			$this->session->data['warning'] = '';
		} else {
			$data['warning'] = '';
		}

		// Form action url
		$data['action'] = Util::url()->link("payment/mollie_" . static::MODULE_NAME);
		$data['cancel'] = Util::url()->link( Util::route()->extension(false, 'payment'), "type=payment");

		// Load global settings. Some are prefixed with mollie_ideal_ for legacy reasons.
		$settings = array(
			$code . "_api_key"                    				=> NULL,
			$code . "_client_id"                    			=> NULL,
			$code . "_client_secret"                    		=> NULL,
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
			$code . "_ideal_refund_status_id"  					=> 11,
			$code . "_create_shipment"  		  				=> 3,
			$code . "_refresh_token"  		  					=> '',
			$code . "_payment_screen_language"  		  		=> 'en-gb',
			$code . "_default_currency"  		  				=> 'DEF',
			$code . "_debug_mode"  		  						=> FALSE,
			$code . "_mollie_component"  		  				=> FALSE,
			$code . "_mollie_component_css_base"  		  		=> array(
																	"background_color" => "#fff",
																	"color"			   => "#555",
																	"font_size"		   => "12px",
																	"other_css"		   => "border-width: 1px;\nborder-style: solid;\nborder-color: #ccc;\nborder-radius: 4px;\npadding: 8px;"
																	),
			$code . "_mollie_component_css_valid"  		  		=> array(
																	"background_color" => "#fff",
																	"color"			   => "#090",
																	"font_size"		   => "12px",
																	"other_css"		   => "border-width: 1px;\nborder-style: solid;\nborder-color: #090;\nborder-radius: 4px;\npadding: 8px;"
																	),
			$code . "_mollie_component_css_invalid"  		  	=> array(
																	"background_color" => "#fff",
																	"color"			   => "#f00",
																	"font_size"		   => "12px",
																	"other_css"		   => "border-width: 1px;\nborder-style: solid;\nborder-color: #f00;\nborder-radius: 4px;\npadding: 8px;"
																	),
		);

		// Check if order complete status is defined in store setting
		$data['is_order_complete_status'] = true;
		$data['order_complete_statuses'] = array();

		if((null == Util::config()->get('config_complete_status')) && (Util::config()->get('config_complete_status_id')) == '') {
			$data['is_order_complete_status'] = false;
		}

		foreach($data['stores'] as &$store) {
			$this->data = $store;

			foreach ($settings as $setting_name => $default_value) {
				// Attempt to read from post
				if (null != Util::request()->post()->get($store['store_id'] . '_' . $setting_name)) {
					$data['stores'][$store['store_id']][$setting_name] = Util::request()->post()->get($store['store_id'] . '_' . $setting_name);
				} else { // Otherwise, attempt to get the setting from the database
					// same as $this->config->get() 
					$stored_setting = null;
					if(isset($this->data[$setting_name])) {
						if(!empty($this->data[$setting_name])) {
							$stored_setting = $this->data[$setting_name];
						} elseif($default_value !== NULL) {
							$stored_setting = $default_value;
						}						
					}

					if($stored_setting === NULL && $default_value !== NULL) {
						$data['stores'][$store['store_id']][$setting_name] = $default_value;
					} else {
						$data['stores'][$store['store_id']][$setting_name] = $stored_setting;
					}
				}
			}

			// Check mollie connection
			$data['stores'][$store['store_id']]['mollie_connection'] = false;
			$data['stores'][$store['store_id']]['show_mollie_connect_button'] = true;
			if(null != $this->data[MollieHelper::getModuleCode() . '_refresh_token']) {
				$data['stores'][$store['store_id']]['mollie_connection'] = true;
			}

			if(isset($this->session->data['mollie_access_token'][$store['store_id']]) && !empty($this->session->data['mollie_access_token'][$store['store_id']])) {
				$data['stores'][$store['store_id']]['show_mollie_connect_button'] = false;
			}

			$data['stores'][$store['store_id']]['mollie_connect'] = Util::url()->link("payment/mollie_" . static::MODULE_NAME . "/mollieConnect", "client_id=" . $this->data[MollieHelper::getModuleCode() . '_client_id'] . "&store_id=" . $store['store_id']);

			if(version_compare(VERSION, '2.3.0.2', '>=') == true) {
				$data['stores'][$store['store_id']]['redirect_uri'] = HTTPS_SERVER . 'index.php?route=extension/payment/mollie_bancontact/mollieConnectCallback';
			} else {
				$data['stores'][$store['store_id']]['redirect_uri'] = HTTPS_SERVER . 'index.php?route=payment/mollie_bancontact/mollieConnectCallback';
			}
			
			// Check which payment methods we can use with the current API key.
			$allowed_methods = array();
			try {
				$api_methods = $this->getAPIClient($store['store_id'])->methods->allActive(array('resource' => 'orders', 'includeWallets' => 'applepay'));
				foreach ($api_methods as $api_method) {
					$allowed_methods[] = $api_method->id;
				}
			} catch (Mollie\Api\Exceptions\ApiException $e) {
				// If we have an unauthorized request, our API key is likely invalid.
				if ($store[$code . '_api_key'] !== NULL && strpos($e->getMessage(), "Unauthorized request") !== false)
				{
					$data['error_api_key'] = $this->language->get("error_api_key_invalid");
				}
			}

			$data['store_data'][$store['store_id'] . '_' . $code . '_payment_methods'] = array();
			$data['store_data']['creditCardEnabled'] = false;

			foreach (MollieHelper::$MODULE_NAMES as $module_name) {
				$payment_method = array();

				$payment_method['name']    = $this->language->get("name_mollie_" . $module_name);
				$payment_method['disable']    = Util::url()->link("payment/mollie_" . static::MODULE_NAME . "/disablePaymentMethod", "method=" . $module_name . "&store_id=" . $store['store_id']);
				$payment_method['enable']     = Util::url()->link("payment/mollie_" . static::MODULE_NAME . "/enablePaymentMethod", "method=" . $module_name . "&store_id=" . $store['store_id']);
				$payment_method['icon']    = "../image/mollie/" . $module_name . "2x.png";
				$payment_method['allowed'] = in_array($module_name, $allowed_methods);

				if(($module_name == 'creditcard') && $payment_method['allowed']) {
					$data['store_data']['creditCardEnabled'] = true;
				}

				// Load module specific settings.
				if (isset($store[$store['store_id'] . '_' . $code . '_' . $module_name . '_status'])) {
					$payment_method['status'] = ($store[$store['store_id'] . '_' . $code . '_' . $module_name . '_status'] == "on");
				} else {
					$payment_method['status'] = (bool) isset($store[$code . "_" . $module_name . "_status"]) ? $store[$code . "_" . $module_name . "_status"] : null;
				}

				if (isset($store[$store['store_id'] . '_' . $code . '_' . $module_name . '_sort_order'])) {
					$payment_method['sort_order'] = $store[$store['store_id'] . '_' . $code . '_' . $module_name . '_sort_order'];
				} else {
					$payment_method['sort_order'] = isset($store[$code . "_" . $module_name . "_sort_order"]) ? $store[$code . "_" . $module_name . "_sort_order"] : null;
				}

				if (isset($store[$store['store_id'] . '_' . $code . '_' . $module_name . '_geo_zone'])) {
					$payment_method['geo_zone'] = $store[$store['store_id'] . '_' . $code . '_' . $module_name . '_geo_zone'];
				} else {
					$payment_method['geo_zone'] = isset($store[$code . "_" . $module_name . "_geo_zone"]) ? $store[$code . "_" . $module_name . "_geo_zone"] : null;
				}

				$data['store_data'][$store['store_id'] . '_' . $code . '_payment_methods'][$module_name] = $payment_method;
			}

			$data['stores'][$store['store_id']]['entry_cstatus'] = $this->checkCommunicationStatus(isset($store[$code . '_api_key']) ? $store[$code . '_api_key'] : null);

			Util::validation($store, $store['store_id'], $this->error)
				->notIsset('error_api_key', 'api_key')
				->notIsset('error_description', 'description');
			
		}

		//Error log
		$data['download'] = Util::url()->link("payment/mollie_" . static::MODULE_NAME . '/download');
		$data['clear'] = Util::url()->link("payment/mollie_" . static::MODULE_NAME . '/clear');

		$data['log'] = '';

		$file = DIR_LOGS . 'Mollie.log';

		if (file_exists($file)) {
			$size = filesize($file);

			if ($size >= 5242880) {
				$suffix = array(
					'B',
					'KB',
					'MB',
					'GB',
					'TB',
					'PB',
					'EB',
					'ZB',
					'YB'
				);

				$i = 0;

				while (($size / 1024) > 1) {
					$size = $size / 1024;
					$i++;
				}

				$data['error_warning'] = sprintf($this->language->get('error_warning'), basename($file), round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i]);
			} else {
				$data['log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
			}
		}

		// Save client_id and client_secret in the session and remove refresh_token from the setting if these cerdentials are changed after save
		$appData = array();

		foreach($data['stores'] as $store_id=>$setting_data) {
			$appData[$store_id] = array(
				'client_id' => $setting_data[$code . '_client_id'],
				'client_secret' => $setting_data[$code . '_client_secret']
			);
		}
		
		$this->session->data['app_data'] = $appData;
		$data['store_email'] = Util::config()->get('config_email');

		Util::response()->view("payment/mollie", $data);
	}

    /**
     *
     */
    public function validate_api_key() {
    	Util::load()->language("payment/mollie");
		$json = array(
			'error' => false,
			'invalid' => false,
			'valid' => false,
			'message' => '',
		);

		if (empty($this->request->get['key'])) {
			$json['invalid'] = true;
			$json['message'] = $this->language->get('error_no_api_client');
		} else {
			try {
				$client = MollieHelper::getAPIClientForKey($this->request->get['key']);

				if (!$client) {
					$json['invalid'] = true;
					$json['message'] = $this->language->get('error_no_api_client');
				} else {
					$client->methods->all();

					$json['valid'] = true;
					$json['message'] = 'Ok.';
				}
			} catch (IncompatiblePlatform $e) {
				$json['error'] = true;
				$json['message'] = $e->getMessage() . ' ' . $this->language->get('error_api_help');
			} catch (ApiException $e) {
				$json['error'] = true;
				$json['message'] = sprintf($this->language->get('error_comm_failed'), htmlspecialchars($e->getMessage()), (isset($client) ? htmlspecialchars($client->getApiEndpoint()) : 'Mollie'));
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
	private function validate ($store = 0) {
		if (!$this->user->hasPermission("modify", "payment/mollie_" . static::MODULE_NAME))
		{
			$this->error['warning'] = $this->language->get("error_permission");
		}

		if (!Util::request()->post()->get($store . '_' . MollieHelper::getModuleCode() . '_api_key'))
		{
			$this->error[$store]['api_key'] = $this->data["error_api_key"];
		}

		// if (!Util::request()->post()->get($store . '_' . MollieHelper::getModuleCode() . '_ideal_description'))
		// {
		// 	$this->error[$store]['description'] = $this->data["error_description"];
		// }
		
		return (count($this->error) == 0);
	}

	/**
	 * @param string|null
	 * @return string
	 */
	protected function checkCommunicationStatus ($api_key = null) {
		Util::load()->language("payment/mollie");
		if (empty($api_key)) {
			return '<span style="color:red">' .  $this->language->get('error_no_api_key') . '</span>';
		}

		try {
			$client = MollieHelper::getAPIClientForKey($api_key);

			if (!$client) {
				return '<span style="color:red">' . $this->language->get('error_no_api_client') . '</span>';
			}

			$client->methods->all();

			return '<span style="color: green">OK</span>';
		} catch (Mollie\Api\Exceptions\ApiException_IncompatiblePlatform $e) {
			return '<span style="color:red">' . $e->getMessage() . ' ' . $this->language->get('error_api_help') . '</span>';
		} catch (Mollie\Api\Exceptions\ApiException $e) {
			return '<span style="color:red">' . sprintf($this->language->get('error_comm_failed'), htmlspecialchars($e->getMessage()), (isset($client) ? htmlspecialchars($client->getApiEndpoint()) : 'Mollie')) . '</span>';				
		}
	}

	/**
	 * @return string
	 */
	private function getTokenUriPart() {
		if (isset($this->session->data['user_token'])) {
			return 'user_token=' . $this->session->data['user_token'];
		}

		return 'token=' . $this->session->data['token'];
	}

	private function getUserId() {
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

	public function saveAppData() {
		$json = array();
		$settingModel = Util::load()->model('setting/setting');
		$store_id = $_POST['store_id'];
		$code = MollieHelper::getModuleCode();

		$data = $settingModel->getSetting($code, $store_id);
		$data[$code.'_client_id'] = $_POST['client_id'];
		$data[$code.'_client_secret'] = $_POST['client_secret'];
		
		$settingModel->editSetting($code, $data, $store_id);

		$json['connect_url'] = Util::url()->link("payment/mollie_" . static::MODULE_NAME . "/mollieConnect", "client_id=" . $_POST['client_id'] . "&store_id=" . $store_id);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function getUpdateUrl() {
        $client = new mollieHttpClient();
        $info = $client->get(MOLLIE_VERSION_URL);
        if (isset($info["tag_name"]) && ($info["tag_name"] != MOLLIE_VERSION) && version_compare(MOLLIE_VERSION, $info["tag_name"], "<")) {
            $updateUrl = array(
                "updateUrl" => Util::url()->link("payment/mollie_" . static::MODULE_NAME . "/update"),
                "updateVersion" => $info["tag_name"]
            );

            return $updateUrl;
        }
        return false;
    }

    function update() {
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

        if (!$this->getUpdateUrl()) {
            $data = array("version" => MOLLIE_RELEASE);
            Util::load()->language("payment/mollie", $data);
            $this->session->data['success'] = sprintf($this->language->get('text_update_success'), MOLLIE_RELEASE);
        }

        //go back
        Util::response()->redirect(Util::route()->extension("mollie_ideal", 'payment'));
    }

    public function rmDirRecursive($dir) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->rmDirRecursive("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    function cpy($source, $dest) {
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

    public function download() {
		Util::load()->language("payment/mollie");

		$file = DIR_LOGS . 'Mollie.log';

		if (file_exists($file) && filesize($file) > 0) {
			Util::response()->addheader('Pragma', 'public');
			Util::response()->addheader('Expires', '0');
			Util::response()->addheader('Content-Description', 'File Transfer');
			Util::response()->addheader('Content-Type', 'application/octet-stream');
			Util::response()->addheader('Content-Disposition', 'attachment; filename="' . $this->config->get('config_name') . '_' . date('Y-m-d_H-i-s', time()) . '_mollie_error.log"');
			Util::response()->addheader('Content-Transfer-Encoding', 'binary');

			$this->response->setOutput(file_get_contents($file, FILE_USE_INCLUDE_PATH, null));
		} else {
			Util::session()->warning = sprintf($this->language->get('error_log_warning'), basename($file), '0B');

			Util::response()->redirectBack();
		}
	}
	
	public function clear() {
		Util::load()->language("payment/mollie");

		$file = DIR_LOGS . 'Mollie.log';

		$handle = fopen($file, 'w+');

		fclose($handle);

		Util::session()->success = $this->language->get('text_log_success');

		Util::response()->redirectBack();
	}

	public function sendMessage() {
		Util::load()->language("payment/mollie");

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}

			if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
				$json['error'] = $this->language->get('error_email');
			}

			if (utf8_strlen($this->request->post['subject']) < 3) {
				$json['error'] = $this->language->get('error_subject');
			}

			if (utf8_strlen($this->request->post['enquiry']) < 25) {
				$json['error'] = $this->language->get('error_enquiry');
			}

			if (!isset($json['error'])) {
				$name = $this->request->post['name'];
				$email = $this->request->post['email'];
				$subject = $this->request->post['subject'];
				$enquiry = $this->request->post['enquiry'];
				$enquiry .= "<br>Opencart version : " . VERSION;
				$enquiry .= "<br>VQMod version : " . VQ_VERSION;
				$enquiry .= "<br>Mollie version : " . MOLLIE_VERSION;

				$mail = new Mail();
				$mail->protocol = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
	
				$mail->setTo('support.mollie@qualityworks.eu');
				$mail->setFrom($email);
				$mail->setSender(html_entity_decode($name, ENT_QUOTES, 'UTF-8'));
				$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
				$mail->setHtml($enquiry);

				$file = DIR_LOGS . 'Mollie.log';
				if (file_exists($file) && filesize($file) < 2147483648) {
					$mail->addAttachment($file);
				}

				$file = DIR_LOGS . 'error.log';
				if (file_exists($file) && filesize($file) < 2147483648) {
					$mail->addAttachment($file);
				}

				$mail->send();

				$json['success'] = $this->language->get('text_enquiry_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
