<?php
namespace Opencart\Admin\Controller\Extension\Mollie;
use \Opencart\System\Helper AS Helper;

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
use Mollie\mollieHttpClient;

require_once(DIR_EXTENSION . "mollie/system/library/mollie/helper.php");
require_once(DIR_EXTENSION . "mollie/system/library/mollie/mollieHttpClient.php");

define("MOLLIE_VERSION", \MollieHelper::PLUGIN_VERSION);
define("MOLLIE_RELEASE", "v" . MOLLIE_VERSION);
define("MOLLIE_VERSION_URL", "https://api.github.com/repos/mollie/OpenCart/releases/latest");

const DEPRECATED_METHODS = array('giropay', 'sofort', 'paysafecard');

if (!defined("MOLLIE_TMP")) {
    define("MOLLIE_TMP", sys_get_temp_dir());
}

class Mollie extends \Opencart\System\Engine\Controller {
	// Initialize var(s)
	protected $error = array();

	// Holds multistore configs
	private $token;
	public $mollieHelper;

	public function __construct($registry) {
		parent::__construct($registry);
    
    	$this->token = 'user_token='.$this->session->data['user_token'];
    	$this->mollieHelper = new \MollieHelper($registry);
	}

	/**
	 * @param int $store The Store ID
	 * @return MollieApiClient
	 */
	protected function getAPIClientForKey($store = 0) {
		$api_key = $this->mollieHelper->getApiKey($store);

		if ($api_key) {		
			return $this->mollieHelper->getAPIClientForKey($api_key);
		}

		return;
	}

    protected function getAPIClient($store) {
        $data = $this->config;
        $data->set($this->mollieHelper->getModuleCode() . "_api_key", (string)$this->mollieHelper->getApiKey($store));

        return $this->mollieHelper->getAPIClient($data);
    }

    private function getMethodSeparator() {
        $method_separator = '|';

        if(version_compare(VERSION, '4.0.2.0', '>=')) {
            $method_separator = '.';
        }

        return $method_separator;
    }

	/**
	 * This method is executed by OpenCart when the Payment module is installed from the admin. It will create the
	 * required tables.
	 *
	 * @return void
	 */
	public function install (): void {
		// Just install all modules while we're at it.
		$this->installAllModules();

		//Add event to create shipment
		$this->load->model('setting/event');

        $events = [
            "mollie",
            "mollie_create_shipment",
            "mollie_order_info_controller",
            "mollie_order_info_template",
            "mollie_update_message_dashboard",
            "mollie_update_message_dashboard_template",
            "mollie_product_controller",
            "mollie_product_form_template",
            "mollie_product_model",
            "mollie_checkout_controller",
            "mollie_login_controller",
            "mollie_mail_order_controller",
            "mollie_mail_order_template",
            "mollie_get_methods_after",
            "mollie_add_order_after",
            "mollie_edit_order_after",
            "mollie_add_history_after",
            "mollie_payment_method_controller"
        ];

        foreach ($events as $event_code) {
            $this->model_setting_event->deleteEventByCode($event_code);
        }

        $event_data = [
            0 => [
                "code" => "mollie_create_shipment",
                "description" => "Mollie Payment - Create shipment",
                "trigger" => "catalog/model/checkout/order/addHistory/after",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'createShipment',
                "status" => 1,
                "sort_order" => 0
            ],
            1 => [
                "code" => "mollie_order_info_controller",
                "description" => "Mollie Payment - Add mollie data to order controller",
                "trigger" => "admin/view/sale/order_info/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'orderController',
                "status" => 1,
                "sort_order" => 0
            ],
            2 => [
                "code" => "mollie_order_info_template",
                "description" => "Mollie Payment - Add mollie data to order info template",
                "trigger" => "admin/view/sale/order_info/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'orderInfoTemplate',
                "status" => 1,
                "sort_order" => 0
            ],
            3 => [
                "code" => "mollie_update_message_dashboard",
                "description" => "Mollie Payment - Module update message on dashboard",
                "trigger" => "admin/view/common/dashboard/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'addMollieUpgradeToDashboard',
                "status" => 1,
                "sort_order" => 0
            ],
            4 => [
                "code" => "mollie_update_message_dashboard_template",
                "description" => "Mollie Payment - Module update message on dashboard template",
                "trigger" => "admin/view/common/dashboard/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'addMollieUpgradeToDashboardTemplate',
                "status" => 1,
                "sort_order" => 0
            ],
            5 => [
                "code" => "mollie_product_controller",
                "description" => "Mollie Payment - Add mollie data to product controller",
                "trigger" => "admin/view/catalog/product_form/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'productController',
                "status" => 1,
                "sort_order" => 0
            ],
            6 => [
                "code" => "mollie_product_form_template",
                "description" => "Mollie Payment - Add mollie data to product form template",
                "trigger" => "admin/view/catalog/product_form/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'productFormTemplate',
                "status" => 1,
                "sort_order" => 0
            ],
            7 => [
                "code" => "mollie_product_model",
                "description" => "Mollie Payment - Add mollie data to product model",
                "trigger" => "admin/model/catalog/product/addProduct/after",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'productModelAddProductAfter',
                "status" => 1,
                "sort_order" => 0
            ],
            8 => [
                "code" => "mollie_product_model",
                "description" => "Mollie Payment - Add mollie data to product model",
                "trigger" => "admin/model/catalog/product/editProduct/after",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'productModelEditProductAfter',
                "status" => 1,
                "sort_order" => 0
            ],
            9 => [
                "code" => "mollie_checkout_controller",
                "description" => "Mollie Payment - Add mollie data on checkout controller",
                "trigger" => "catalog/controller/checkout/checkout/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'checkoutController',
                "status" => 1,
                "sort_order" => 0
            ],
            10 => [
                "code" => "mollie_login_controller",
                "description" => "Mollie Payment - Add mollie data to login controller",
                "trigger" => "catalog/controller/account/login/token/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'loginController',
                "status" => 1,
                "sort_order" => 0
            ],
            11 => [
                "code" => "mollie_mail_order_controller",
                "description" => "Mollie Payment - Add payment link to order mail controller",
                "trigger" => "catalog/view/mail/order_invoice/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'mailOrderController',
                "status" => 1,
                "sort_order" => 0
            ],
            12 => [
                "code" => "mollie_mail_order_template",
                "description" => "Mollie Payment - Add payment link to order mail template",
                "trigger" => "catalog/view/mail/order_invoice/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'mailOrderTemplate',
                "status" => 1,
                "sort_order" => 0
            ],
            13 => [
                "code" => "mollie_get_methods_after",
                "description" => "Mollie Payment",
                "trigger" => "catalog/model/checkout/payment_method/getMethods/after",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'getPaymentMethodsAfter',
                "status" => 1,
                "sort_order" => 0
            ],
            14 => [
                "code" => "mollie_add_order_after",
                "description" => "Mollie Payment",
                "trigger" => "catalog/model/checkout/order/addOrder/after",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'addOrderAfter',
                "status" => 1,
                "sort_order" => 0
            ],
            15 => [
                "code" => "mollie_edit_order_after",
                "description" => "Mollie Payment",
                "trigger" => "catalog/model/checkout/order/editOrder/after",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'editOrderAfter',
                "status" => 1,
                "sort_order" => 0
            ],
            16 => [
                "code" => "mollie_add_history_after",
                "description" => "Mollie Payment",
                "trigger" => "catalog/model/checkout/order/addHistory/after",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'addHistoryAfter',
                "status" => 1,
                "sort_order" => 0
            ],
            17 => [
                "code" => "mollie_payment_method_controller",
                "description" => "Mollie Payment",
                "trigger" => "catalog/view/checkout/payment_method/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'checkoutPaymentMethodController',
                "status" => 1,
                "sort_order" => 0
            ],
            18 => [
                "code" => "mollie_account_subscription_controller",
                "description" => "Mollie Payment - Add subscription cancel on account subscription",
                "trigger" => "catalog/view/account/subscription_info/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'accountSubscriptionController',
                "status" => 1,
                "sort_order" => 0
            ],
            19 => [
                "code" => "mollie_account_subscription_template",
                "description" => "Mollie Payment - Add subscription cancel on account subscription",
                "trigger" => "catalog/view/account/subscription_info/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'accountSubscriptionTemplate',
                "status" => 1,
                "sort_order" => 0
            ],
        ];

        foreach ($event_data as $event) {
            $this->model_setting_event->addEvent($event);
        }

        $this->load->model('extension/mollie/payment/mollie');

        $this->model_extension_mollie_payment_mollie->install();
	}

	private function getStores() {
		// multi-stores management
		$this->load->model('setting/store');
		$stores = array();
		$stores[0] = array(
			'store_id' => 0,
			'name'     => $this->config->get('config_name')
		);

		$_stores = $this->model_setting_store->getStores();

		foreach ($_stores as $store) {
			$stores[$store['store_id']] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		return $stores;
	}

    public function cleanUp() {
        // Remove old vqmod mollie.xml file if exixts
        if (file_exists(DIR_SYSTEM . '../vqmod/xml/mollie.xml')) {
            unlink(DIR_SYSTEM . '../vqmod/xml/mollie.xml');
        }

        //Remove deprecated method files from old version
		$extensionDir   = DIR_EXTENSION . 'mollie/';
		$adminControllerDir   = $extensionDir . 'admin/controller/';
		$adminLanguageDir     = $extensionDir . 'admin/language/';
		$catalogControllerDir = $extensionDir . 'catalog/controller/';
		$catalogModelDir      = $extensionDir . 'catalog/model/';

        $files = array();

		foreach (DEPRECATED_METHODS as $method) {
			$files = array(
				$adminControllerDir . 'payment/mollie_' . $method . '.php',
				$catalogControllerDir . 'payment/mollie_' . $method . '.php',
				$catalogModelDir . 'payment/mollie_' . $method . '.php'
			);

			foreach ($files as $file) {
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
    }

	/**
	 * Trigger installation of all Mollie modules.
	 */
	protected function installAllModules () {
		// Load models
		$this->load->model('setting/extension');
		$model = 'model_setting_extension';
		
		$user_id = $this->getUserId();

		foreach ($this->mollieHelper->MODULE_NAMES as $module_name) {
			$extensions = $this->{$model}->getExtensionsByType("payment");
			
			// Install extension.
			$this->{$model}->install("payment", "mollie", "mollie_" . $module_name);

			// First remove permissions to avoid memory overflow
			$this->model_user_user_group->removePermission($user_id, "access", "extension/mollie/payment/mollie_" . $module_name);
			$this->model_user_user_group->removePermission($user_id, "modify", "extension/mollie/payment/mollie_" . $module_name);	
			
			// Set permissions.
			$this->model_user_user_group->addPermission($user_id, "access", "extension/mollie/payment/mollie_" . $module_name);
			$this->model_user_user_group->addPermission($user_id, "modify", "extension/mollie/payment/mollie_" . $module_name);
		}

		// Install Mollie Payment Fee Total
		$extensions = $this->{$model}->getExtensionsByType("total");
		if (!in_array("mollie_payment_fee", $extensions)) {
			$this->{$model}->install("total", "mollie", "mollie_payment_fee");

			// First remove permissions to avoid memory overflow
			$this->model_user_user_group->removePermission($user_id, "access", "extension/mollie/total/mollie_payment_fee");
			$this->model_user_user_group->removePermission($user_id, "modify", "extension/mollie/total/mollie_payment_fee");	
			
			// Set permissions.
			$this->model_user_user_group->addPermission($user_id, "access", "extension/mollie/total/mollie_payment_fee");
			$this->model_user_user_group->addPermission($user_id, "modify", "extension/mollie/total/mollie_payment_fee");
		}
	}

	/**
	 * The method is executed by OpenCart when the Payment module is uninstalled from the admin. It will not drop the Mollie
	 * table at this point - we want to allow the user to toggle payment modules without losing their settings.
	 *
	 * @return void
	 */
	public function uninstall (): void {
		$this->uninstallAllModules();
	}

	/**
	 * Trigger removal of all Mollie modules.
	 */
	protected function uninstallAllModules () {
		$this->load->model('setting/extension');
		$model = 'model_setting_extension';

		foreach ($this->mollieHelper->MODULE_NAMES as $module_name) {
			$this->{$model}->uninstall("payment", "mollie_" . $module_name);
		}
	}

    //Delete deprecated method data from setting
	public function clearData() {
		foreach (DEPRECATED_METHODS as $method) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` LIKE '%$method%'");
			if ($query->num_rows > 0) {
				$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `key` LIKE '%$method%'");
			}

            $this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = 'payment' AND `code` = 'mollie_" . $this->db->escape($method) . "'");
            $this->db->query("DELETE FROM `" . DB_PREFIX . "extension_path` WHERE `path` LIKE '%" . $this->db->escape($method) . "%'");
		}
	}

	private function removePrefix($input, $prefix) {
		$result = [];
        $prefixLen = strlen($prefix);
        foreach ($input as $key => $val) {
            if (substr($key, 0, $prefixLen) == $prefix) {
                $key = substr($key, $prefixLen);
                $result[$key] = $val;
            }
        }
        return $result;
	}

	public function addPrefix($prefix, $input) {
        $result = [];
        foreach ($input as $val) {
            $result[] = $prefix . $val;
        }
        return $result;
    }

	/**
	 * Render the payment method's settings page.
	 */
	public function index (): void {
		// Double check for database and permissions
		$this->install();
        $this->cleanUp();

		// Load essential models
		$this->load->model("localisation/order_status");
		$this->load->model("localisation/geo_zone");
		$this->load->model("localisation/language");
		$this->load->model("localisation/currency");
		$this->load->model('setting/setting');
		$this->load->model('localisation/tax_class');

		$this->document->addScript('view/javascript/ckeditor/ckeditor.js');
		$this->document->addScript('view/javascript/ckeditor/adapters/jquery.js');

		// Double-check if clean-up has been done - For upgrades
		if (null === $this->config->get($this->mollieHelper->getModuleCode() . '_version')) {
			$code = 'code';

			$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `" . $code . "` = '" . $this->db->escape($this->mollieHelper->getModuleCode()) . "', `key` = '" . $this->db->escape($this->mollieHelper->getModuleCode() . '_version') . "', `value` = '" . $this->db->escape(MOLLIE_VERSION) . "'");
		} elseif (version_compare($this->config->get($this->mollieHelper->getModuleCode() . '_version'), MOLLIE_VERSION, '<')) {
			$this->model_setting_setting->editValue($this->mollieHelper->getModuleCode(), $this->mollieHelper->getModuleCode() . '_version', MOLLIE_VERSION);
		}

        //Also delete data related to deprecated modules from settings
		$this->clearData();

		//Load language data
		$data = array("version" => MOLLIE_RELEASE);

		$this->load->language('extension/mollie/payment/mollie');

		$code = $this->mollieHelper->getModuleCode();

        $this->document->setTitle(strip_tags($this->language->get('heading_title')));

        //Set form variables
        $paymentDesc = array();
        $paymentImage = array();
        $paymentStatus = array();
        $paymentSortOrder = array();
        $paymentGeoZone = array();
        $paymentTotalMin = array();
        $paymentTotalMax = array();
        $paymentAPIToUse = array();

        foreach ($this->mollieHelper->MODULE_NAMES as $module_name) {
        	$paymentDesc[]  	= $code . '_' . $module_name . '_description';
        	$paymentImage[] 	= $code . '_' . $module_name . '_image';
        	$paymentStatus[] 	= $code . '_' . $module_name . '_status';
        	$paymentSortOrder[] = $code . '_' . $module_name . '_sort_order';
        	$paymentGeoZone[] 	= $code . '_' . $module_name . '_geo_zone';
        	$paymentTotalMin[]  = $code . '_' . $module_name . '_total_minimum';
        	$paymentTotalMax[]  = $code . '_' . $module_name . '_total_maximum';
        	$paymentAPIToUse[]  = $code . '_' . $module_name . '_api_to_use';
		}

        $fields = array("show_icons", "show_order_canceled_page", "description", "api_key", "ideal_processing_status_id", "ideal_expired_status_id", "ideal_canceled_status_id", "ideal_failed_status_id", "ideal_pending_status_id", "ideal_shipping_status_id", "create_shipment_status_id", "ideal_refund_status_id", "create_shipment", "payment_screen_language", "debug_mode", "mollie_component", "mollie_component_css_base", "mollie_component_css_valid", "mollie_component_css_invalid", "default_currency", "subscription_email", "align_icons", "single_click_payment", "order_expiry_days", "ideal_partial_refund_status_id", "payment_link", "payment_link_email", "partial_credit_order");

        $settingFields = $this->addPrefix($code . '_', $fields);

        $storeFormFields = array_merge($settingFields, $paymentDesc, $paymentImage, $paymentStatus, $paymentSortOrder, $paymentGeoZone, $paymentTotalMin, $paymentTotalMax, $paymentAPIToUse);

        $data['stores'] = $this->getStores();

        //API key not required for multistores
        $data['api_required'] = true;
        
        if(count($data['stores']) > 1) {
        	$data['api_required'] = false;
        }

        $data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
	        'text'      => $this->language->get('text_home'),
	        'href'      => $this->url->link('common/dashboard', $this->token),
	      	'separator' => false
   		);
      
   		$data['breadcrumbs'][] = array(
	       	'text'      => $this->language->get('text_extension'),
	        'href'      => $this->url->link('marketplace/extension', $this->token . '&type=payment')
   		);
		
   		$data['breadcrumbs'][] = array(
	       	'text'      => strip_tags($this->language->get('heading_title')),
	        'href'      => $this->url->link('extension/mollie/payment/mollie_' . static::MODULE_NAME, $this->token)
   		);

		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];

			unset($this->session->data['error']);
		} else {
			if (version_compare(VERSION, '4.0.1.1', '>')) {
				$payment_address = $this->config->get('config_checkout_payment_address');
			} else {
				$payment_address = $this->config->get('config_checkout_address');
			}

			if (!$payment_address) {
				$this->load->model('catalog/product');
					
				$products = $this->model_catalog_product->getProducts();
				foreach ($products as $product) {
					if (!$product['shipping']) {
						$data['error_warning'] = sprintf($this->language->get('error_address'), $this->url->link('setting/setting', $this->token));

						break;
					}
				}
			} else {
				$data['error_warning'] = '';
			}

            $telephone_display = $this->config->get('config_telephone_display');
			$telephone_required = $this->config->get('config_telephone_required');

            if (!$telephone_display || !$telephone_required) {
                $data['error_warning'] = sprintf($this->language->get('error_telephone'), $this->url->link('setting/setting', $this->token));
            }
		}
		
		$data['save'] = $this->url->link('extension/mollie/payment/mollie_' . static::MODULE_NAME . $this->getMethodSeparator() . 'save', $this->token);		
		$data['back'] = $this->url->link('marketplace/extension', $this->token . '&type=payment');

		// Set data for template
        $data['module_name']        = static::MODULE_NAME;
        $data['api_check_url']      = $this->url->link("extension/mollie/payment/mollie_" . static::MODULE_NAME . $this->getMethodSeparator() . "validate_api_key", $this->token);
        $data['entry_version']      = $this->language->get("entry_version") . " " . MOLLIE_VERSION;
        $data['code']               = $code;
		$data['token']          	= $this->token;

		$data['update_url']         = ($this->getUpdateUrl()) ? $this->getUpdateUrl()['updateUrl'] : '';
		if (version_compare(phpversion(), \MollieHelper::MIN_PHP_VERSION, "<")) {
        	$data['text_update']        = ($this->getUpdateUrl()) ? sprintf($this->language->get('text_update_message_warning'), \MollieHelper::NEXT_PHP_VERSION, $this->getUpdateUrl()['updateVersion'], $this->getUpdateUrl()['updateVersion']) : '';
			$data['module_update'] = false;
		} else {
        	$data['text_update']        = ($this->getUpdateUrl()) ? sprintf($this->language->get('text_update_message'), $this->getUpdateUrl()['updateVersion'], $data['update_url'], $this->getUpdateUrl()['updateVersion']) : '';
			$data['module_update'] = true;
		}

		if (isset($_COOKIE["hide_mollie_update_message_version"]) && ($_COOKIE["hide_mollie_update_message_version"] == $this->getUpdateUrl()['updateVersion'])) {
			$data['text_update'] = '';
		}
		
		$data['geo_zones']			= $this->model_localisation_geo_zone->getGeoZones();
		$data['order_statuses']		= $this->model_localisation_order_status->getOrderStatuses();
		$data['languages']			= $this->model_localisation_language->getLanguages();
		foreach ($data['languages'] as &$language) {
			$language['image'] = 'language/'.$language['code'].'/'.$language['code'].'.png';
	    }

		$data['currencies']			= $this->model_localisation_currency->getCurrencies();
		$data['tax_classes']        = $this->model_localisation_tax_class->getTaxClasses();
        $data['method_separator']   = $this->getMethodSeparator();

		$this->load->model('tool/image');

		$no_image = 'no_image.png';

		$data['placeholder'] = $this->model_tool_image->resize($no_image, 100, 100);

		$description = array();
		foreach ($data['languages'] as $_language) {
			$description[$_language['language_id']]['title'] = "Order %";
		}

		// Load global settings. Some are prefixed with mollie_ideal_ for legacy reasons.
		$settings = array(
			$code . "_api_key"                    				=> NULL,
			$code . "_description"          					=> $description,
			$code . "_show_icons"                 				=> FALSE,
			$code . "_align_icons"                 				=> 'left',
			$code . "_show_order_canceled_page"   				=> FALSE,
			$code . "_ideal_pending_status_id"    				=> 1,
			$code . "_ideal_processing_status_id" 				=> 2,
			$code . "_ideal_canceled_status_id"   				=> 7,
			$code . "_ideal_failed_status_id"     				=> 10,
			$code . "_ideal_expired_status_id"    				=> 14,
			$code . "_ideal_shipping_status_id"   				=> 3,
			$code . "_create_shipment_status_id"  				=> 3,
			$code . "_ideal_refund_status_id"  					=> 11,
			$code . "_ideal_partial_refund_status_id"  			=> 11,
			$code . "_create_shipment"  		  				=> 3,
			$code . "_payment_screen_language"  		  		=> 'en-gb',
			$code . "_default_currency"  		  				=> 'DEF',
			$code . "_debug_mode"  		  						=> FALSE,
			$code . "_subscription_email"  		  				=> array(),
			$code . "_mollie_component"  		  				=> FALSE,
			$code . "_single_click_payment"  		  			=> FALSE,
			$code . "_partial_credit_order"  		  			=> FALSE,
			$code . "_order_expiry_days"  		  			    => 25,
			$code . "_payment_link"  		  			        => 0,
			$code . "_payment_link_email"  		  				=> array(),
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

		if((null == $this->config->get('config_complete_status')) && ($this->config->get('config_complete_status_id')) == '') {
			$data['is_order_complete_status'] = false;
		}

		foreach($data['stores'] as &$store) {
			$config_setting = $this->model_setting_setting->getSetting($code, $store['store_id']);
			foreach ($settings as $setting_name => $default_value) {
				// Attempt to read from post
				if (isset($this->request->post[$store['store_id'] . '_' . $setting_name])) {
					$data['stores'][$store['store_id']][$setting_name] = $this->request->post[$store['store_id'] . '_' . $setting_name];
				} else { // Otherwise, attempt to get the setting from the database
					// same as $this->config->get() 
					$stored_setting = null;
					if(isset($config_setting[$setting_name])) {
						$stored_setting = $config_setting[$setting_name];						
					}

					if($stored_setting === NULL && $default_value !== NULL) {
						$data['stores'][$store['store_id']][$setting_name] = $default_value;
					} else {
						$data['stores'][$store['store_id']][$setting_name] = $stored_setting;
					}
				}
			}

			// Check which payment methods we can use with the current API key.
			$allowed_methods = array();
			try {
				$apiClient = $this->getAPIClientForKey($store['store_id']);
				if ($apiClient) {
					$api_methods = $apiClient->methods->allAvailable();
					foreach ($api_methods as $api_method) {
                        if ($api_method->status == 'activated') {
                            if ($api_method->id == 'in3') {
                                $api_method->id = 'in_3';
                            } elseif ($api_method->id == 'przelewy24') {
                                $api_method->id = 'przelewy_24';
                            }
    
                            $allowed_methods[$api_method->id] = array(
                                "method" => $api_method->id,
                                "minimumAmount" => $api_method->minimumAmount,
                                "maximumAmount" => $api_method->maximumAmount
                            );
                        }
					}
				} else {
					$data['error_api_key'] = $this->language->get("error_api_key_invalid");
				}
			} catch (\Mollie\Api\Exceptions\ApiException $e) {
				// If we have an unauthorized request, our API key is likely invalid.
				if ($store[$code . '_api_key'] !== NULL && strpos($e->getMessage(), "Unauthorized request") !== false)
				{
					$data['error_api_key'] = $this->language->get("error_api_key_invalid");
				}
			}

			$data['store_data'][$store['store_id'] . '_' . $code . '_payment_methods'] = array();
			$data['store_data']['creditCardEnabled'] = false;

			foreach ($this->mollieHelper->MODULE_NAMES as $module_name) {

				$payment_method = array();

				$payment_method['name']    = $this->language->get("name_mollie_" . $module_name);
				$payment_method['icon']    = "../image/mollie/" . $module_name . "2x.png";
				$payment_method['allowed'] = array_key_exists($module_name, $allowed_methods);

				if(($module_name == 'creditcard') && $payment_method['allowed']) {
					$data['store_data']['creditCardEnabled'] = true;
				}

				// Make inactive if not allowed
				if (!$payment_method['allowed']) {
					$this->model_setting_setting->editValue($code, $code . '_' . $module_name . '_status', 0, $store['store_id']);
				}

				// Load module specific settings.
				if (isset($config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_status'])) {
					$payment_method['status'] = ($config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_status'] == "on");
				} else {
					$payment_method['status'] = (bool) isset($config_setting[$code . "_" . $module_name . "_status"]) ? $config_setting[$code . "_" . $module_name . "_status"] : null;
				}

				if (isset($config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_description'])) {
					$payment_method['description'] = $config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_description'];
				} else {
					$payment_method['description'] = isset($config_setting[$code . "_" . $module_name . "_description"]) ? $config_setting[$code . "_" . $module_name . "_description"] : null;
				}

				if (isset($config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_image'])) {
					$payment_method['image'] = $config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_image'];
					if(!empty($config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_image'])) {
						$payment_method['thumb'] = $this->model_tool_image->resize($config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_image'], 100, 100);
					} else {
						$payment_method['thumb'] = $this->model_tool_image->resize($no_image, 100, 100);
					}					
				} else {
					$payment_method['image'] = isset($config_setting[$code . "_" . $module_name . "_image"]) ? $config_setting[$code . "_" . $module_name . "_image"] : null;
					$payment_method['thumb'] = (isset($config_setting[$code . "_" . $module_name . "_image"]) && !empty($config_setting[$code . "_" . $module_name . "_image"])) ? $this->model_tool_image->resize($config_setting[$code . "_" . $module_name . "_image"], 100, 100) : $this->model_tool_image->resize($no_image, 100, 100);
				}

				if (isset($config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_sort_order'])) {
					$payment_method['sort_order'] = $config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_sort_order'];
				} else {
					$payment_method['sort_order'] = isset($config_setting[$code . "_" . $module_name . "_sort_order"]) ? $config_setting[$code . "_" . $module_name . "_sort_order"] : null;
				}

				if (isset($config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_geo_zone'])) {
					$payment_method['geo_zone'] = $config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_geo_zone'];
				} else {
					$payment_method['geo_zone'] = isset($config_setting[$code . "_" . $module_name . "_geo_zone"]) ? $config_setting[$code . "_" . $module_name . "_geo_zone"] : null;
				}

				if ($payment_method['allowed']) {
					$minimumAmount = $allowed_methods[$module_name]['minimumAmount']->value;
					$currency      = $allowed_methods[$module_name]['minimumAmount']->currency;
                    
					if ($this->currency->has($currency)) {
						$payment_method['minimumAmount'] = sprintf($this->language->get('text_standard_total'), $this->currency->format($this->currency->convert($minimumAmount, $currency, $this->config->get('config_currency')), $currency));

						if (isset($config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_total_minimum'])) {
							$payment_method['total_minimum'] = $config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_total_minimum'];
						} elseif (isset($config_setting[$code . "_" . $module_name . "_total_minimum"])) {
							$payment_method['total_minimum'] =  $config_setting[$code . "_" . $module_name . "_total_minimum"];
						} else {
							$payment_method['total_minimum'] =  $this->numberFormat($this->currency->convert($minimumAmount, $currency, $this->config->get('config_currency')), $this->config->get('config_currency'));
						}

						if ($allowed_methods[$module_name]['maximumAmount']) {
							$maximumAmount = $allowed_methods[$module_name]['maximumAmount']->value;
							$currency      = $allowed_methods[$module_name]['maximumAmount']->currency;
							$payment_method['maximumAmount'] = sprintf($this->language->get('text_standard_total'), $this->currency->format($this->currency->convert($maximumAmount, $currency, $this->config->get('config_currency')), $currency));
						} else {
							$payment_method['maximumAmount'] =  $this->language->get('text_no_maximum_limit');
						}				

						if (isset($config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_total_maximum'])) {
							$payment_method['total_maximum'] = $config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_total_maximum'];
						} elseif (isset($config_setting[$code . "_" . $module_name . "_total_maximum"])) {
							$payment_method['total_maximum'] =  $config_setting[$code . "_" . $module_name . "_total_maximum"];
						} else {
							$payment_method['total_maximum'] =  ($allowed_methods[$module_name]['maximumAmount']) ? $this->numberFormat($this->currency->convert($maximumAmount, $currency, $this->config->get('config_currency')), $this->config->get('config_currency')) : '';
						}
					} else {
						$payment_method['minimumAmount'] = sprintf($this->language->get('text_standard_total'), $currency . ' ' . $minimumAmount);
						$payment_method['total_minimum'] =  $minimumAmount;

						if ($allowed_methods[$module_name]['maximumAmount']) {	
							$maximumAmount = $allowed_methods[$module_name]['maximumAmount']->value;
							$payment_method['maximumAmount'] = sprintf($this->language->get('text_standard_total'), $currency . ' ' . $maximumAmount);
							$payment_method['total_maximum'] = $maximumAmount;
						} else {
							$payment_method['maximumAmount'] =  $this->language->get('text_no_maximum_limit');
							$payment_method['total_maximum'] = '';
						}
					}
				}	
				
				if (isset($config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_api_to_use'])) {
					$payment_method['api_to_use'] = $config_setting[$store['store_id'] . '_' . $code . '_' . $module_name . '_api_to_use'];
				} else {
					$payment_method['api_to_use'] = isset($config_setting[$code . "_" . $module_name . "_api_to_use"]) ? $config_setting[$code . "_" . $module_name . "_api_to_use"] : null;
				}

				$data['store_data'][$store['store_id'] . '_' . $code . '_payment_methods'][$module_name] = $payment_method;
			}

            // Sort payment methods
            uksort($data['store_data'][$store['store_id'] . '_' . $code . '_payment_methods'], function($a, $b) {
                return $a <=> $b;
            });

			$data['stores'][$store['store_id']]['entry_cstatus'] = $this->checkCommunicationStatus(isset($config_setting[$code . '_api_key']) ? $config_setting[$code . '_api_key'] : null);			
		}

		$data['mollie_version'] = $this->config->get($code . '_version');
		$data['mod_file'] = $this->config->get($code . '_mod_file');

		$data['download'] = $this->url->link("extension/mollie/payment/mollie_" . static::MODULE_NAME . $this->getMethodSeparator() . "download", $this->token);
		$data['clear'] = $this->url->link("extension/mollie/payment/mollie_" . static::MODULE_NAME . $this->getMethodSeparator() . "clear", $this->token);

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

				$data['error_warning'] = sprintf($this->language->get('error_log_warning'), basename($file), round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i]);
			} else {
				$data['log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
			}
		}

		$data['store_email'] = $this->config->get('config_email');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/mollie/payment/mollie', $data));
	}

	public function save(): void {
		$this->load->language('extension/mollie/payment/mollie');

		$json = [];

		$stores = $this->getStores();
		$code = $this->mollieHelper->getModuleCode();

		if (!$this->user->hasPermission('modify', 'extension/mollie/payment/mollie_' . static::MODULE_NAME)) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if (count($stores) <= 1) {
			if (!$this->request->post['0_' . $code . '_api_key']) {
				$json['error']['0_' . $code . '_api_key'] = $this->language->get("error_api_key");
			}
		}

		if (!$json) {
			$this->load->model('setting/setting');
			$this->load->model('localisation/language');

			$this->model_setting_setting->editSetting('module_filter', $this->request->post);

			foreach ($stores as $store) {
				// Set payment method title to default if not provided
				foreach ($this->mollieHelper->MODULE_NAMES as $module_name) {
					$desc = $this->request->post[$store["store_id"] . '_' . $code . '_' . $module_name . '_description'];
					foreach ($this->model_localisation_language->getLanguages() as $language) {
						if (empty($desc[$language['language_id']]['title'])) {
							$this->request->post[$store["store_id"] . '_' . $code . '_' . $module_name . '_description'][$language['language_id']]['title'] = $this->request->post[$store["store_id"] . '_' . $code . '_' . $module_name . '_name'];
						}
					}
				}

				$this->model_setting_setting->editSetting($code, $this->removePrefix($this->request->post, $store["store_id"] . "_"), $store["store_id"]);
			}

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

    /**
     *
     */
    public function validate_api_key(): void {
    	$this->load->language('extension/mollie/payment/mollie');

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
				$client = $this->mollieHelper->getAPIClientForKey($this->request->get['key']);

				if (!$client) {
					$json['invalid'] = true;
					$json['message'] = $this->language->get('error_no_api_client');
				} else {
					$client->methods->allActive();

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
	 * @param string|null
	 * @return string
	 */
	protected function checkCommunicationStatus ($api_key = null) {
		$this->load->language('extension/mollie/payment/mollie');

		if (empty($api_key)) {
			return '<span style="color:red">' .  $this->language->get('error_no_api_key') . '</span>';
		}

		try {
			$client = $this->mollieHelper->getAPIClientForKey($api_key);

			if (!$client) {
				return '<span style="color:red">' . $this->language->get('error_no_api_client') . '</span>';
			}

			$client->methods->allActive();

			return '<span style="color: green">OK</span>';
		} catch (\Mollie\Api\Exceptions\ApiException_IncompatiblePlatform $e) {
			return '<span style="color:red">' . $e->getMessage() . ' ' . $this->language->get('error_api_help') . '</span>';
		} catch (\Mollie\Api\Exceptions\ApiException $e) {
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
		$this->load->model('setting/setting');
		$store_id = $this->request->post['store_id'];
		$code = $this->mollieHelper->getModuleCode();

		$data = $this->model_setting_setting->getSetting($code, $store_id);
		$data[$code.'_api_key'] = $this->request->post['api_key'];
		
		$this->model_setting_setting->editSetting($code, $data, $store_id);
		return true;
	}

	private function getUpdateUrl() {
        $client = new mollieHttpClient();
        $info = $client->get(MOLLIE_VERSION_URL);

        if (isset($info["tag_name"])) {
            if(strpos($info["tag_name"], 'oc4') !== false) {
                $tag_name = explode('_', explode("-", $info["tag_name"])[1]); // New tag_name = oc3_version-oc4_version
            } else {
                $tag_name = ["oc4", $info["tag_name"]]; // Old tag_name = release version
            }
    
            if (isset($tag_name[0]) && ($tag_name[0] == 'oc4')) {
                if (isset($tag_name[1]) && ($tag_name[1] != MOLLIE_VERSION) && version_compare(MOLLIE_VERSION, $tag_name[1], "<")) {
                    $updateUrl = array(
                        "updateUrl" => $this->url->link("extension/mollie/payment/mollie_" . static::MODULE_NAME . $this->getMethodSeparator() . "update", $this->token),
                        "updateVersion" => $tag_name[1]
                    );
        
                    return $updateUrl;
                }
            }
        }
        
        return false;
    }

    public function update() {

		// CHeck for PHP version
		if (version_compare(phpversion(), \MollieHelper::MIN_PHP_VERSION, "<")) {
			$this->response->redirect($this->url->link('extension/mollie/payment/mollie_' . static::MODULE_NAME, $this->token));
		}

        //get info
        $client = new mollieHttpClient();
        $info = $client->get(MOLLIE_VERSION_URL);

        //save tmp file
        $temp_file = MOLLIE_TMP . "/mollieUpdate.zip";
        $handle = fopen($temp_file, "w+");
		
        $browser_download_url = '';
        if (!empty($info["assets"])) {
            foreach($info["assets"] as $asset) {
                if(strpos($asset["name"], 'oc4') !== false) {
                    $browser_download_url = $asset['browser_download_url'];

                    break;
                }
            }
        }

        if (!empty($browser_download_url)) {
            $content = $client->get($browser_download_url, false, false);
        } else {
            $this->response->redirect($this->url->link('extension/mollie/payment/mollie_' . static::MODULE_NAME, $this->token));
        }

        fwrite($handle, $content);
        fclose($handle);


        //extract to temp dir
        $temp_dir = MOLLIE_TMP . "/mollieUpdate";
        if (class_exists("ZipArchive")) {
            $zip = new \ZipArchive;
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
                if ($file == "extension") {
                    $to = DIR_EXTENSION;
                } elseif ($file == "image") {
                    $to = DIR_IMAGE;
                } else {
                    $to = DIR_OPENCART;
                }
                $this->cpy($from, $to);
            }

        }

        //cleanup
        unlink($temp_file);
        $this->rmDirRecursive($temp_dir);

        if (!$this->getUpdateUrl()) {
            $data = array("version" => MOLLIE_RELEASE);
            $this->load->language('extension/mollie/payment/mollie');

            $this->session->data['success'] = sprintf($this->language->get('text_update_success'), MOLLIE_RELEASE);
        }

        //go back
        $this->response->redirect($this->url->link('extension/mollie/payment/mollie_' . static::MODULE_NAME, $this->token));
    }

    public function rmDirRecursive($dir) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->rmDirRecursive("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    private function cpy($source, $dest): void {
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

	public function download(): void {
		$this->load->language('extension/mollie/payment/mollie');

		$filename = 'Mollie.log';
	
		$file = DIR_LOGS . $filename;
	
		if (!is_file($file)) {
			$this->session->data['error'] = sprintf($this->language->get('error_log_warning'), $filename, '0B');
	
			$this->response->redirect($this->url->link('extension/mollie/payment/mollie_' . static::MODULE_NAME, $this->token));
		}
	
		if (!filesize($file)) {
			$this->session->data['error'] = sprintf($this->language->get('error_log_warning'), $filename, '0B');
	
			$this->response->redirect($this->url->link('extension/mollie/payment/mollie_' . static::MODULE_NAME, $this->token));
		}
	
		$this->response->addheader('Pragma: public');
		$this->response->addheader('Expires: 0');
		$this->response->addheader('Content-Description: File Transfer');
		$this->response->addheader('Content-Type: application/octet-stream');
		$this->response->addheader('Content-Disposition: attachment; filename="' . $this->config->get('config_name') . '_' . date('Y-m-d_H-i-s', time()) . '_mollie_error.log"');
		$this->response->addheader('Content-Transfer-Encoding: binary');
	
		$this->response->setOutput(file_get_contents($file, FILE_USE_INCLUDE_PATH, null));
	}
	
	public function clear(): void {
		$this->load->language('extension/mollie/payment/mollie');
	
		$filename = 'Mollie.log';
	
		$json = [];
	
		if (!$this->user->hasPermission('modify', 'extension/mollie/payment/mollie_' . static::MODULE_NAME)) {
			$json['error'] = $this->language->get('error_permission');
		}
	
		$file = DIR_LOGS . $filename;
	
		if (!is_file($file)) {
			$json['error'] = sprintf($this->language->get('error_file'), $filename);
		}
	
		if (!$json) {
			$handle = fopen($file, 'w+');
	
			fclose($handle);
	
			$json['success'] = $this->language->get('text_log_success');
		}
	
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function sendMessage(): void {
		$this->load->language('extension/mollie/payment/mollie');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((Helper\Utf8\strlen($this->request->post['name']) < 3) || (Helper\Utf8\strlen($this->request->post['name']) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}

			if ((Helper\Utf8\strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
				$json['error'] = $this->language->get('error_email');
			}

			if (Helper\Utf8\strlen($this->request->post['subject']) < 3) {
				$json['error'] = $this->language->get('error_subject');
			}

			if (Helper\Utf8\strlen($this->request->post['enquiry']) < 25) {
				$json['error'] = $this->language->get('error_enquiry');
			}

			if (!isset($json['error'])) {
				$name = $this->request->post['name'];
				$email = $this->request->post['email'];
				$subject = $this->request->post['subject'];
				$enquiry = $this->request->post['enquiry'];
				$enquiry .= "<br>Opencart version : " . VERSION;			
				$enquiry .= "<br>Mollie version : " . MOLLIE_VERSION;

				if ($this->config->get('config_mail_engine')) {
					$mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'));
					$mail->parameter = $this->config->get('config_mail_parameter');
					$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
					$mail->smtp_username = $this->config->get('config_mail_smtp_username');
					$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
					$mail->smtp_port = $this->config->get('config_mail_smtp_port');
					$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
		
					$mail->setTo('support.mollie@qualityworks.eu');
					$mail->setFrom($email);
					$mail->setSender($name);
					$mail->setSubject($subject);
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
				}

				$json['success'] = $this->language->get('text_enquiry_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

    public function numberFormat($amount, $currency) {
        $intCurrencies = array("ISK", "JPY");
        if(!in_array($currency, $intCurrencies)) {
            $formattedAmount = number_format((float)$amount, 2, '.', '');
        } else {
            $formattedAmount = number_format($amount, 0);
        }   
        return $formattedAmount;    
    }

    protected function convertCurrency($amount, $currency) {
        $this->load->model("localisation/currency");
        $currencies = $this->model_localisation_currency->getCurrencies();
        $convertedAmount = $amount * $currencies[$currency]['value'];

        return $convertedAmount;
    }

    public function refund() {
        $this->load->language('sale/order');
        $this->load->language('extension/mollie/payment/mollie');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/order');
        $this->load->model('extension/mollie/payment/mollie');

        $json = array();
        $json['error'] = false;

        $log = new \Opencart\System\Library\Log('Mollie.log');
        $mollieHelper = new \MollieHelper($this->registry);
        $moduleCode = $mollieHelper->getModuleCode();
        
        $order_id = $this->request->get['order_id'];
        $order = $this->model_sale_order->getOrder($order_id);

        $mollieOrderDetails = $this->model_extension_mollie_payment_mollie->getMolliePayment($order_id);
        if(!$mollieOrderDetails) {
            $log->write("Mollie order(mollie_order_id) not found for order_id - $order_id");
            $json['error'] = $this->language->get('text_order_not_found');
        }

        if($mollieOrderDetails['refund_id']) {
            $log->write("Refund has been processed already for order_id - $order_id");
            $json['error'] = $this->language->get('text_refunded_already');
        }

        if(!$json['error']) {
            $json['partial_credit_order'] = false;

            $stock_mutation_data = array();

            $order_products = $this->model_sale_order->getProducts($order_id);

            foreach ($order_products as $order_product) {
                $stock_mutation_data[] = array(
                    "order_product_id" => $order_product['order_product_id'],
                    "quantity" => (int)$order_product['quantity']
                );
            }

            if (!empty($mollieOrderDetails['mollie_order_id'])) {
                $mollieOrder = $this->getAPIClient($order['store_id'])->orders->get($mollieOrderDetails['mollie_order_id']);
                if($mollieOrder->isPaid() || $mollieOrder->isShipping() || $mollieOrder->isCompleted()) {

                    $refundObject = $mollieOrder->refundAll([
                        "metadata" => array("order_id" => $order_id)
                    ]);

                    if($refundObject->id) {
                        $log->write("Refund has been processed for order_id - $order_id, mollie_order_id - " . $mollieOrderDetails['mollie_order_id'] . ". Refund id is $refundObject->id.");
                        $json['success'] = $this->language->get('text_refund_success');
                        $json['order_status_id'] = $this->config->get($moduleCode . "_ideal_refund_status_id");
                        $json['comment'] = $this->language->get('text_refund_success');
                        $json['order_id'] = $order_id;

                        $json['date'] = date($this->language->get('date_format_short'));
                        $json['amount'] = $this->currency->format($refundObject->amount->value, $refundObject->amount->currency, 1);
                        $json['status'] = ucfirst($refundObject->status);

                        $this->model_extension_mollie_payment_mollie->updateMolliePayment($mollieOrderDetails['mollie_order_id'], $refundObject->id, 'refunded');

                        $data = array(
                            "refund_id" => $refundObject->id,
                            "order_id" => $order_id,
                            "transaction_id" => $mollieOrderDetails['transaction_id'],
                            "amount" => $refundObject->amount->value,
                            "currency_code" => $refundObject->amount->currency,
                            "status" => $refundObject->status
                        );

                        $this->model_extension_mollie_payment_mollie->addMollieRefund($data);

                        if ($this->config->get($moduleCode . "_partial_credit_order")) {
                            $json['partial_credit_order'] = true;
                        } else {
                            if (!empty($stock_mutation_data)) {
                                $this->model_extension_mollie_payment_mollie->stockMutation($order_id, $stock_mutation_data);
                            }
                        }
                    } else {
                        $log->write("Refund process can not be processed for order_id - $order_id.");
                        $json['error'] = $this->language->get('text_no_refund');
                    }

                } else {
                    $log->write("Refund can not be processed for order_id - $order_id. Order lines that are Paid, Shipping or Completed can be refunded.");
                    $json['error'] = $this->language->get('text_no_refund');
                }
            } else {
                $molliePayment = $this->getAPIClient($order['store_id'])->payments->get($mollieOrderDetails['transaction_id']);
                if($molliePayment->isPaid()) {
                    $amount = $this->numberFormat($this->convertCurrency($order['total'], $order['currency_code']), $order['currency_code']);
                    $refundObject = $molliePayment->refund([
                        "amount" => ["currency" => $order['currency_code'], "value" => (string)$amount],
                        "metadata" => array("order_id" => $order_id, "transaction_id" => $mollieOrderDetails['transaction_id'])
                    ]);

                    if($refundObject->id) {
                        $log->write("Refund has been processed for order_id - $order_id, transaction_id - " . $mollieOrderDetails['transaction_id'] . ". Refund id is $refundObject->id.");
                        $json['success'] = $this->language->get('text_refund_success');
                        $json['order_status_id'] = $this->config->get($moduleCode . "_ideal_refund_status_id");
                        $json['comment'] = $this->language->get('text_refund_success');
                        $json['order_id'] = $order_id;

                        $json['date'] = date($this->language->get('date_format_short'));
                        $json['amount'] = $this->currency->format($refundObject->amount->value, $refundObject->amount->currency, 1);
                        $json['status'] = ucfirst($refundObject->status);

                        $this->model_extension_mollie_payment_mollie->updateMolliePaymentForPaymentAPI($mollieOrderDetails['transaction_id'], $refundObject->id, 'refunded');

                        $data = array(
                            "refund_id" => $refundObject->id,
                            "order_id" => $order_id,
                            "transaction_id" => $mollieOrderDetails['transaction_id'],
                            "amount" => $refundObject->amount->value,
                            "currency_code" => $refundObject->amount->currency,
                            "status" => $refundObject->status
                        );
                        $this->model_extension_mollie_payment_mollie->addMollieRefund($data);

                    } else {
                        $log->write("Refund process can not be processed for order_id - $order_id.");
                        $json['error'] = $this->language->get('text_no_refund');
                    }
                } else {
                    $log->write("Refund can not be processed for order_id - $order_id.");
                    $json['error'] = $this->language->get('text_no_refund');
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));

    }

    public function partialRefund() {
        $this->load->language('sale/order');
        $this->load->language('extension/mollie/payment/mollie');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/order');
        $this->load->model('extension/mollie/payment/mollie');

        $json = array();
        $json['error'] = false;

        $log = new \Opencart\System\Library\Log('Mollie.log');
        $mollieHelper = new \MollieHelper($this->registry);
        $moduleCode = $mollieHelper->getModuleCode();
        
        $order_id = $this->request->get['order_id'];
        $order = $this->model_sale_order->getOrder($order_id);

        $mollieOrderDetails = $this->model_extension_mollie_payment_mollie->getMolliePayment($order_id);
        if(!$mollieOrderDetails) {
            $log->write("Mollie order(mollie_order_id) not found for order_id - $order_id");
            $json['error'] = $this->language->get('text_order_not_found');
        }

        if($mollieOrderDetails['refund_id']) {
            $log->write("Refund has been processed already for order_id - $order_id");
            $json['error'] = $this->language->get('text_refunded_already');
        }

        if ((empty($this->request->post['refund_amount']) || ($this->request->post['refund_amount'] <= 0)) && ($this->request->post['partial_refund_type'] == 'custom_amount')) {
            $json['error'] = $this->language->get('error_refund_amount');
        }

        if (!isset($this->request->post['productline']) && ($this->request->post['partial_refund_type'] == 'productline')) {
            $json['error'] = $this->language->get('error_productline');
        }

        $productline_error = true;
        if (isset($this->request->post['productline'])) {
            foreach ($this->request->post['productline'] as $order_product_id => $line) {
                if (isset($line['selected'])) {
                    $productline_error = false;
                    break;
                }
            }
        }

        if ($productline_error && ($this->request->post['partial_refund_type'] == 'productline')) {
            $json['error'] = $this->language->get('error_productline');
        }

        if(!$json['error']) {
            $json['partial_credit_order'] = false;

            if ($this->request->post['partial_refund_type'] == 'productline') {
                $lines = array();
                $orderProductIDs = array();
                $stock_mutation_data = array();
                foreach ($this->request->post['productline'] as $order_product_id => $line) {
                    if (isset($line['selected'])) {
                        $lines[] = array(
                            "id" => (string)$line['orderline_id'],
                            "quantity" => (int)$line['quantity']
                        );

                        $orderProductIDs[] = $order_product_id;
                    }

                    if (isset($line['stock_mutation'])) {
                        $stock_mutation_data[] = array(
                            "order_product_id" => $order_product_id,
                            "quantity" => (int)$line['quantity']
                        );
                    }
                }
                if (!empty($lines)) {
                    try {
                        $mollieOrder = $this->getAPIClient($order['store_id'])->orders->get($mollieOrderDetails['mollie_order_id']);
                        $refundObject = $mollieOrder->refund([
                            "lines" => $lines,
                            "metadata" => array("order_id" => $order_id, "transaction_id" => $mollieOrderDetails['transaction_id'], "mollie_order_id" => $mollieOrderDetails['mollie_order_id'], "order_product_id" => implode(",", $orderProductIDs))
                        ]);

                        if ($this->config->get($moduleCode . "_partial_credit_order")) {
                            $json['partial_credit_order'] = true;
                        } else {
                            if (!empty($stock_mutation_data)) {
                                $this->model_extension_mollie_payment_mollie->stockMutation($order_id, $stock_mutation_data);
                            }
                        }
                    } catch (\Mollie\Api\Exceptions\ApiException $e) {
                        $log->write("Creating refund failed: " . htmlspecialchars($e->getMessage()));
                        $json['error'] = $this->language->get('text_no_refund');
                    }
                }
            } elseif($this->request->post['partial_refund_type'] == 'custom_amount') {
                try {
                    $amount = $this->numberFormat($this->request->post['refund_amount'], $order['currency_code']);
                    $molliePayment = $this->getAPIClient($order['store_id'])->payments->get($mollieOrderDetails['transaction_id']);
                    $refundObject = $molliePayment->refund([
                        "amount" => ["currency" => $order['currency_code'], "value" => (string)$amount],
                        "metadata" => array("order_id" => $order_id, "transaction_id" => $mollieOrderDetails['transaction_id'])
                    ]);
                } catch (\Mollie\Api\Exceptions\ApiException $e) {
                    $log->write("Creating refund failed: " . htmlspecialchars($e->getMessage()));
                    $json['error'] = $this->language->get('text_no_refund');
                }
            }

            if (!$json['error']) {
                if($refundObject->id) {
                    $amount = $refundObject->amount->value .' '. $refundObject->amount->currency;
                    $log->write('Partial refund of amount ' . $amount . ' has been processed for order_id - ' . $order_id . ' and transaction_id - ' . $mollieOrderDetails['transaction_id'] . '. Refund id is ' . $refundObject->id);
                    $json['success'] = $this->language->get('text_refund_success');
                    $json['order_status_id'] = $this->config->get($moduleCode . "_ideal_partial_refund_status_id");
                    $json['comment'] = sprintf($this->language->get('text_partial_refund_success'), $amount);
                    $json['order_id'] = $order_id;

                    $json['date'] = date($this->language->get('date_format_short'));
                    $json['amount'] = $this->currency->format($refundObject->amount->value, $refundObject->amount->currency, 1);
                    $json['status'] = ucfirst($refundObject->status);

                    $data = array(
                        "refund_id" => $refundObject->id,
                        "order_id" => $order_id,
                        "transaction_id" => $mollieOrderDetails['transaction_id'],
                        "amount" => $refundObject->amount->value,
                        "currency_code" => $refundObject->amount->currency,
                        "status" => $refundObject->status
                    );
                    $this->model_extension_mollie_payment_mollie->addMollieRefund($data);

                } else {
                    $log->write('Partial Refund can not be processed for order_id - ' . $order_id . ' and transaction_id - ' . $mollieOrderDetails['transaction_id']);
                    $json['error'] = $this->language->get('text_no_refund');
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));

    }
    
    public function orderController(&$route, &$data, &$template_code) {
        $this->load->model('sale/order');

        $order_id = (int)$data['order_id'];

        $order_info = $this->model_sale_order->getOrder($order_id);

        if (!empty($order_info) && (int)$order_info['store_id'] >= 0) {
            $this->load->language('extension/mollie/payment/mollie');
            $this->load->model('extension/mollie/payment/mollie');

            $moduleCode = $this->mollieHelper->getModuleCode();
            $apiKey = $this->mollieHelper->getApiKey($order_info['store_id']);

            $data['showRefundButton'] = true;
            $data['showPartialRefundButton'] = true;
            if(!$apiKey || ($apiKey == '')) {
                $data['showRefundButton'] = false;
                $data['showPartialRefundButton'] = false;
            }

            $data['partial_credit_order'] = false;
            if ($this->config->get($moduleCode . "_partial_credit_order")) {
                $data['partial_credit_order'] = true;
            }

            $data['mollie_refunds'] = array();
            $refunds = $this->model_extension_mollie_payment_mollie->getMollieRefunds($this->request->get['order_id']);
            if ($refunds) {
                $data['showRefundButton'] = false;

                foreach ($refunds as $refund) {
                    $mollieRefund = $this->getAPIClient($order_info['store_id'])->payments->get($refund['transaction_id'])->getRefund($refund['refund_id']);
                    if ($mollieRefund->status != $refund['status']) {
                        $this->model_extension_mollie_payment_mollie->updateMollieRefundStatus($refund['refund_id'], $refund['transaction_id'], $mollieRefund->status);
                    }
                    $data['mollie_refunds'][] = array(
                        "date_added" => date($this->language->get('date_format_short'), strtotime($refund['date_created'])),
                        "amount" => $this->currency->format($refund['amount'], $order_info['currency_code'], 1),
                        "status" => ucfirst($mollieRefund->status)
                    );
                }
            }

            $data['currency'] = $order_info['currency_code'];
            $data['store_id'] = $order_info['store_id'];
            
            $data['payment_status'] = '';
            $data['paymentMethod'] = '';
            $molliePaymentDetails = $this->model_extension_mollie_payment_mollie->getMolliePayment($this->request->get['order_id']);
            if(isset($molliePaymentDetails['transaction_id']) && !empty($molliePaymentDetails['transaction_id'])) {
                try {
                    $molliePayment = $this->getAPIClient($order_info['store_id'])->payments->get($molliePaymentDetails['transaction_id']);
                    $data['payment_status'] = $molliePayment->status;
                    $data['paymentMethod'] = $molliePayment->method;
                    
                    // Voucher amount cannot be refunded
                    if ($molliePayment->method == 'voucher') {
                        $data['showRefundButton'] = false;
                    }

                    // Check for refunds and settlements
                    if($molliePayment->hasRefunds()) {
                        $data['payment_status'] = 'refunded';
                    }
                } catch (\Mollie\Api\Exceptions\ApiException $e) {
                    $log = new \Opencart\System\Library\Log('Mollie.log');
                    $log->write(htmlspecialchars($e->getMessage()));
                }
            }
            
            $data['productlines'] = array();
            if ($molliePaymentDetails && !empty($molliePaymentDetails['mollie_order_id'])) {
                try {
                    $order_products = $this->model_sale_order->getProducts($this->request->get['order_id']);

                    $mollieOrder = $this->getAPIClient($order_info['store_id'])->orders->get($molliePaymentDetails['mollie_order_id'], ["embed" => "refunds"]);

                    $refundedLines = array();
                    if(!empty($mollieOrder->_embedded->refunds)) {
                        foreach ($mollieOrder->_embedded->refunds as $refund) {
                            foreach ($refund->lines as $refundedLine) {
                                $refundedLines[] = $refundedLine->id;
                            }                        
                        }
                    }

                    if ($mollieOrder->lines) {
                        foreach ($mollieOrder->lines as $orderline) {
                            if ($orderline->type == 'physical') {
                                foreach ($order_products as $_product) {
                                    if (($orderline->metadata) && ($orderline->metadata->order_product_id == $_product['order_product_id']) && !in_array($orderline->id, $refundedLines)) {
                                        $data['productlines'][] = array(
                                            "id" => $orderline->id,
                                            "name" => $_product['name'],
                                            "option" => $this->model_sale_order->getOptions($this->request->get['order_id'], $_product['order_product_id']),
                                            "quantity" => $orderline->quantity,
                                            "order_product_id" => $orderline->metadata->order_product_id
                                        );
                                    }
                                }
                            }
                        }
                    }
                } catch (\Mollie\Api\Exceptions\ApiException $e) {
                    $log = new \Opencart\System\Library\Log('Mollie.log');
                    $log->write(htmlspecialchars($e->getMessage()));
                }
            }

            $data['mollie_payments'] = array();
            $mollie_payments = $this->model_extension_mollie_payment_mollie->getMolliePayments($this->request->get['order_id']);
            foreach ($mollie_payments as $mollie_payment) {
                $data['mollie_payments'][] = array(
                    "date_added" => date($this->language->get('date_format_short'), strtotime($mollie_payment['date_modified'])),
                    "method" => ucfirst($mollie_payment['method']),
                    "amount" => (isset($mollie_payment['amount']) && !empty($mollie_payment['amount'])) ? $this->currency->format($mollie_payment['amount'], $order_info['currency_code'], 1) : $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']),
                    "status" => ucfirst($mollie_payment['bank_status']),
                );
            }

            if (version_compare(VERSION, '4.0.1.1', '>')) {
                $payment_code = $order_info['payment_method']['code'];
            } else {
                $payment_code = $order_info['payment_code'];
            }

            if (str_contains($payment_code, 'mollie_payment_link')) {
                $paid = false;

                $mollie_payment_links = $this->model_extension_mollie_payment_mollie->getMolliePaymentLinks($this->request->get['order_id']);
                foreach($mollie_payment_links as $mollie_payment_link) {
                    $data['mollie_payments'][] = array(
                        "date_added" => date($this->language->get('date_format_short'), strtotime($mollie_payment_link['date_created'])),
                        "method" => 'N/A',
                        "amount" => (isset($mollie_payment_link['amount']) && !empty($mollie_payment_link['amount'])) ? $this->currency->format($mollie_payment_link['amount'], $order_info['currency_code'], 1) : $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']),
                        "status" => ($mollie_payment_link['date_payment']) ? 'Paid (' . date($this->language->get('date_format_short'), strtotime($mollie_payment_link['date_payment'])) . ')' : 'Open'
                    );

                    if ($mollie_payment_link['date_payment']) {
                        $paid = true;
                    }
                }

                if ($paid) {
                    $data['payment_status'] = 'paid';
                } else {
                    $data['payment_status'] = 'open';
                }
            }

            switch ($data['payment_status']) {
                case 'paid':
                case 'settled':
                    $data['payment_status_class'] = 'success';

                    break;
                case 'failed':
                    $data['payment_status_class'] = 'danger';

                    break;
                case 'expired':
                case 'canceled':
                    $data['payment_status_class'] = 'secondary';

                    break;
                case 'open':
                    $data['payment_status_class'] = 'info';

                    break;
                case 'pending':
                    $data['payment_status_class'] = 'warning';

                    break;
                case 'authorized':
                case 'refunded':
                    $data['payment_status_class'] = 'primary';

                    break;
                default:
                    $data['payment_status_class'] = '';
            }

            if (version_compare(VERSION, '4.0.1.1', '>')) {
                $data['payment_code'] = $order_info['payment_method']['code'];
            } else {
                $data['payment_code'] = $order_info['payment_code'];
            }
            
            $data['order_total'] = $this->currency->format($order_info['total'], $order_info['currency_code'], false, false);
        }

        // Remove default tab
        foreach ($data['tabs'] as $k => $tab) {
            if (str_contains($tab['code'], 'mollie')) {
                unset($data['tabs'][$k]);
            }
        }
    }

    public function orderInfoTemplate(&$route, &$data, &$template_code) {
        $template_buffer = $this->getTemplateBuffer($route, $template_code);

        $search  = [
            '<li class="nav-item"><a href="#tab-additional"',
            '<div id="tab-additional" class="tab-pane">',
            '{{ footer }}',
            '<label for="input-history" class="col-sm-2 col-form-label">{{ entry_comment }}</label>',
            '{{ footer }}',
            '<span id="payment-method-value">{{ payment_method }}</span>'
        ];

		$replace = [
            '{% if payment_status %}
            <li class="nav-item"><a href="#tab-mollie" data-bs-toggle="tab" class="nav-link">{{ tab_mollie }}</a></li>
            {% endif %}<li class="nav-item"><a href="#tab-additional"',
            file_get_contents(DIR_EXTENSION . 'mollie/admin/view/template/payment/mollie_order_info_payment.twig') . '<div id="tab-additional" class="tab-pane">',
            file_get_contents(DIR_EXTENSION . 'mollie/admin/view/template/payment/mollie_order_info_refund_model.twig') . '{{ footer }}',
            file_get_contents(DIR_EXTENSION . 'mollie/admin/view/template/payment/mollie_order_info_payment_link.twig') . '<label for="input-history" class="col-sm-2 col-form-label">{{ entry_comment }}</label>',
            file_get_contents(DIR_EXTENSION . 'mollie/admin/view/template/payment/mollie_order_info_payment_link_model.twig') . '{{ footer }}',
            '<span id="payment-method-value">{{ payment_method }}{% if payment_status %}&nbsp;&nbsp;<span id="payment-status" class="badge bg-{{ payment_status_class }}">{{ payment_status | upper }}</span>{% endif %}</span>'
        ];

		$template_buffer = str_replace($search, $replace, $template_buffer);

        $template_code = $template_buffer;

        return null;
    }

    public function addMollieUpgradeToDashboard(&$route, &$data, &$template_code) {
        $this->load->model('setting/extension');

        $extensions = $this->model_setting_extension->getExtensionsByType('payment');

        $data['mollie_update'] = '';
        foreach ($extensions as $extension) {
            if ($extension['code'] == 'mollie_ideal') {
                require_once(DIR_EXTENSION . "mollie/system/library/mollie/helper.php");
                require_once(DIR_EXTENSION . "mollie/system/library/mollie/mollieHttpClient.php");

                $client = new \Mollie\mollieHttpClient();
                $info = $client->get("https://api.github.com/repos/mollie/OpenCart/releases/latest");

                if (isset($info["tag_name"])) {
                    if(strpos($info["tag_name"], 'oc4') !== false) {
                        $tag_name = explode('_', explode("-", $info["tag_name"])[1]); // New tag_name = oc3_version-oc4_version
                    } else {
                        $tag_name = ["oc4", $info["tag_name"]]; // Old tag_name = release version
                    }
    
                    $mollieHelper = new \MollieHelper($this->registry);
    
                    if (isset($tag_name[0]) && ($tag_name[0] == 'oc4')) {
                        if (isset($tag_name[1]) && ($tag_name[1] != $mollieHelper::PLUGIN_VERSION) && version_compare($mollieHelper::PLUGIN_VERSION, $tag_name[1], "<") && (!isset($_COOKIE["hide_mollie_update_message_version"]) || ($_COOKIE["hide_mollie_update_message_version"] != $tag_name[1]))) {
                            $this->load->language('extension/mollie/payment/mollie');
    
                            $token = 'user_token=' . $this->session->data['user_token'];
    
                            $data['mollie_update'] = sprintf($this->language->get('text_update_message'), $tag_name[1], $this->url->link("extension/mollie/payment/mollie_ideal/update", $token), $tag_name[1]);
                        }
                    }
                }
                
                break;
            }
        }

        // Check if path exists
        $payment_methods = ["alma", "applepay", "bancomatpay", "bancontact", "banktransfer", "belfius", "billie", "blik", "creditcard", "eps", "giftcard", "ideal", "in_3", "kbc", "klarna", "klarnapaylater", "klarnapaynow", "klarnasliceit", "mybank", "payconiq", "paypal", "przelewy_24", "riverty", "satispay", "trustly", "twint", "voucher"];

        $paths = [
            "mollie/admin/controller/payment/",
            "mollie/admin/language/da-dk/payment/",
            "mollie/admin/language/de-de/payment/",
            "mollie/admin/language/en-gb/payment/",
            "mollie/admin/language/es-es/payment/",
            "mollie/admin/language/fr-fr/payment/",
            "mollie/admin/language/it-it/payment/",
            "mollie/admin/language/nb-no/payment/",
            "mollie/admin/language/nl-nl/payment/",
            "mollie/admin/language/pt-pt/payment/",
            "mollie/admin/language/sv-se/payment/",
            "mollie/catalog/controller/payment/",
            "mollie/catalog/model/payment/"
        ];

        $path = $this->model_setting_extension->getPaths('%mollie/admin/controller/payment/mollie_ideal.php');
        if (!empty($path)) {
            $extension_install_id = $path[0]['extension_install_id'];

            foreach ($payment_methods as $payment_method) {
                foreach ($paths as $_path) {
                    $path = $this->model_setting_extension->getPaths('%' . $_path . 'mollie_' . $payment_method . '.php');
    
                    if (empty($path)) {
                        $this->model_setting_extension->addPath($extension_install_id, $_path . 'mollie_' . $payment_method . '.php');
                    }
                }
            }
        }
    }

    public function addMollieUpgradeToDashboardTemplate(&$route, &$data, &$template_code) {
        $template_buffer = $this->getTemplateBuffer($route, $template_code);

        $search  = '{% for row in rows %}';

		$replace = '{% if mollie_update %}
        <div class="alert alert-success alert-dismissible"><i class="fa-solid fa-check-circle"></i> {{ mollie_update }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        {% endif %}{% for row in rows %}';

		$template_buffer = str_replace($search, $replace, $template_buffer);

        $template_code = $template_buffer;

        return null;
    }

    public function productController(&$route, &$data, &$template_code) {
        $this->load->language('extension/mollie/payment/mollie');
        $this->load->model('extension/mollie/payment/mollie');

        $data['voucher_categories'] = ['meal', 'eco', 'gift'];

        if (!empty($data['product_id'])) {
            $voucher_category = $this->model_extension_mollie_payment_mollie->getProductVoucherCategory($data['product_id']);

            if (!empty($voucher_category)) {
                $data['voucher_category'] = $voucher_category;
            } else {
                $data['voucher_category'] = '';
            }
        } else {
            $data['voucher_category'] = '';
        }
    }

    public function productFormTemplate(&$route, &$data, &$template_code) {
        $template_buffer = $this->getTemplateBuffer($route, $template_code);

        $search  = '<label class="col-sm-2 col-form-label">{{ entry_filter }}</label>';

		$replace = file_get_contents(DIR_EXTENSION . 'mollie/admin/view/template/payment/mollie_product_voucher.twig') . '<label class="col-sm-2 col-form-label">{{ entry_filter }}</label>';

		$template_buffer = str_replace($search, $replace, $template_buffer);

        $template_code = $template_buffer;

        return null;
    }

    public function productModelAddProductAfter(&$route, &$args, &$output) {
        $product_id = (int)$args[0];
        $data = (array)$args[1];

        if (isset($data['voucher_category'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET voucher_category = '" . $this->db->escape($data['voucher_category']) . "' WHERE product_id = '" . (int)$product_id . "'");
        }
    }

    public function productModelEditProductAfter(&$route, &$args, &$output) {
        $product_id = (int)$args[0];
        $data = (array)$args[1];

        if (isset($data['voucher_category'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET voucher_category = '" . $this->db->escape($data['voucher_category']) . "' WHERE product_id = '" . (int)$product_id . "'");
        }
    }

    // return template file contents as a string
	protected function getTemplateBuffer( $route, $event_template_buffer ) {
		// if there already is a modified template from view/*/before events use that one
		if ($event_template_buffer) {
			return $event_template_buffer;
		}

        $dir_template = DIR_TEMPLATE;

        $template_file = $dir_template . $route . '.twig';

		if (file_exists( $template_file ) && is_file( $template_file )) {
			return file_get_contents( $template_file );
		}

		exit;
	}

    // Method to call the store front API and return a response.

	/**
	 * @return void
	 */
	public function call(): void {
		$this->load->language('sale/order');

		$json = [];

		if (isset($this->request->get['store_id'])) {
			$store_id = (int)$this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

		if (isset($this->request->get['language'])) {
			$language = $this->request->get['language'];
		} else {
			$language = $this->config->get('config_language');
		}

		if (isset($this->request->get['action'])) {
			$action = $this->request->get['action'];
		} else {
			$action = '';
		}

		if (isset($this->session->data['api_session'])) {
			$session_id = $this->session->data['api_session'];
		} else {
			$session_id = '';
		}

		if (!$this->user->hasPermission('modify', 'sale/order')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if (!$json) {
			// 1. Create a store instance using loader class to call controllers, models, views, libraries
			$this->load->model('setting/store');

			$store = $this->model_setting_store->createStoreInstance($store_id, $language, $session_id);

			// 2. Add the request vars and remove the unneeded ones
			$store->request->get = $this->request->get;
			$store->request->post = $this->request->post;

			$store->request->get['route'] = $action;

			// 3. Remove the unneeded keys
			unset($store->request->get['action']);
			unset($store->request->get['user_token']);

			// Call the required API controller
			$store->load->controller($store->request->get['route']);

			$output = $store->response->getOutput();
		} else {
			$output = json_encode($json);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($output);
	}
}
