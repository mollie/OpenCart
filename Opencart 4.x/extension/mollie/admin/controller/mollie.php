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
use Mollie\Api\Http\Requests\CreatePaymentRefundRequest;
use Mollie\Api\Http\Data\Money;

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

    	$this->token = 'user_token=' . $this->session->data['user_token'];
    	$this->mollieHelper = new \MollieHelper($registry);
	}

	/**
	 * Get Mollie API Client using Store ID
     * * @param int $store The Store ID
	 * @return MollieApiClient|null
	 */
	protected function getAPIClientForKey(int $store = 0): ?MollieApiClient {
		$api_key = $this->mollieHelper->getApiKey($store);

		if (!empty($api_key)) {
			return $this->mollieHelper->getAPIClientForKey($api_key);
		}

		return null;
	}

    /**
     * Get Mollie API Client via config
     * * @param int|string $store
     * @return MollieApiClient|null
     */
    protected function getAPIClient(int|string $store): ?MollieApiClient {
        $data = $this->config;
        $data->set($this->mollieHelper->getModuleCode() . "_api_key", (string)$this->mollieHelper->getApiKey($store));

        return $this->mollieHelper->getAPIClient($data);
    }

    /**
     * Handle the separator difference between OC 4.0.1.x and 4.0.2.x+
     * * @return string
     */
    private function getMethodSeparator(): string {
        $method_separator = '|';

        if(version_compare(VERSION, '4.0.2.0', '>=')) {
            $method_separator = '.';
        }

        return $method_separator;
    }

	/**
	 * This method is executed by OpenCart when the Payment module is installed from the admin.
     * It will create the required events and tables.
	 *
	 * @return void
	 */
	public function install(): void {
		// Just install all modules while we're at it.
		$this->installAllModules();

		// Add event triggers
		$this->load->model('setting/event');

        $events = [
            "mollie",
            "mollie_create_shipment",
            "mollie_order_info_controller",
            "mollie_order_info_template",
            "mollie_customer_order_info_controller",
            "mollie_customer_order_info_template",
            "mollie_update_message_dashboard",
            "mollie_update_message_dashboard_template",
            "mollie_product_controller",
            "mollie_product_form_template",
            "mollie_product_model_add",
            "mollie_product_model_edit",
            "mollie_checkout_controller",
            "mollie_login_controller",
            "mollie_mail_order_controller",
            "mollie_mail_order_template",
            "mollie_mail_history_controller",
            "mollie_mail_history_template",
            "mollie_get_methods_after",
            "mollie_add_order_after",
            "mollie_edit_order_after",
            "mollie_add_history_after",
            "mollie_payment_method_controller",
			"mollie_account_subscription_controller",
			"mollie_account_subscription_template",
            "mollie_create_shipment_2"
        ];

        foreach ($events as $event_code) {
            $this->model_setting_event->deleteEventByCode($event_code);
        }

        $event_data = [
            0 => [
                "code" => "mollie_create_shipment",
                "description" => "Mollie Payment - Create shipment",
                "trigger" => "catalog/model/checkout/order.addHistory/after",
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
                "code" => "mollie_product_model_add",
                "description" => "Mollie Payment - Add mollie data to product model (Add)",
                "trigger" => "admin/model/catalog/product.addProduct/after",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'productModelAddProductAfter',
                "status" => 1,
                "sort_order" => 0
            ],
            8 => [
                "code" => "mollie_product_model_edit",
                "description" => "Mollie Payment - Add mollie data to product model (Edit)",
                "trigger" => "admin/model/catalog/product.editProduct/after",
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
                "trigger" => "catalog/view/mail/order_add/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'mailOrderController',
                "status" => 1,
                "sort_order" => 0
            ],
            12 => [
                "code" => "mollie_mail_order_template",
                "description" => "Mollie Payment - Add payment link to order mail template",
                "trigger" => "catalog/view/mail/order_add/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'mailOrderTemplate',
                "status" => 1,
                "sort_order" => 0
            ],
			13 => [
                "code" => "mollie_mail_history_controller",
                "description" => "Mollie Payment - Add payment link to history mail controller",
                "trigger" => "catalog/view/mail/order_history/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'mailOrderController',
                "status" => 1,
                "sort_order" => 0
            ],
            14 => [
                "code" => "mollie_mail_history_template",
                "description" => "Mollie Payment - Add payment link to history mail template",
                "trigger" => "catalog/view/mail/order_history/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'mailOrderTemplate',
                "status" => 1,
                "sort_order" => 0
            ],
            15 => [
                "code" => "mollie_get_methods_after",
                "description" => "Mollie Payment",
                "trigger" => "catalog/model/checkout/payment_method.getMethods/after",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'getPaymentMethodsAfter',
                "status" => 1,
                "sort_order" => 0
            ],
            16 => [
                "code" => "mollie_add_order_after",
                "description" => "Mollie Payment",
                "trigger" => "catalog/model/checkout/order.addOrder/after",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'addOrderAfter',
                "status" => 1,
                "sort_order" => 0
            ],
            17 => [ // the trigger can also be: admin/model/sale/order.editOrder/after
                "code" => "mollie_edit_order_after",
                "description" => "Mollie Payment",
                "trigger" => "catalog/model/checkout/order.editOrder/after",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'editOrderAfter',
                "status" => 1,
                "sort_order" => 0
            ],
            18 => [
                "code" => "mollie_add_history_after",
                "description" => "Mollie Payment",
                "trigger" => "catalog/model/checkout/order.addHistory/after",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'addHistoryAfter',
                "status" => 1,
                "sort_order" => 0
            ],
            19 => [
                "code" => "mollie_payment_method_controller",
                "description" => "Mollie Payment",
                "trigger" => "catalog/view/checkout/payment_method/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'checkoutPaymentMethodController',
                "status" => 1,
                "sort_order" => 0
            ],
            20 => [
                "code" => "mollie_account_subscription_controller",
                "description" => "Mollie Payment - Add subscription cancel on account subscription",
                "trigger" => "catalog/view/*/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'accountSubscriptionController',
                "status" => 1,
                "sort_order" => 0
            ],
            21 => [
                "code" => "mollie_account_subscription_template",
                "description" => "Mollie Payment - Add subscription cancel on account subscription",
                "trigger" => "catalog/view/*/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'accountSubscriptionTemplate',
                "status" => 1,
                "sort_order" => 0
            ],
			22 => [
                "code" => "mollie_customer_order_info_controller",
                "description" => "Mollie Payment - Add open amount to customer order info",
                "trigger" => "catalog/view/*/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'customerOrderInfoController',
                "status" => 1,
                "sort_order" => 0
            ],
            23 => [
                "code" => "mollie_customer_order_info_template",
                "description" => "Mollie Payment - Show open amount alert in customer order info",
                "trigger" => "catalog/view/*/before",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'customerOrderInfoTemplate',
                "status" => 1,
                "sort_order" => 0
            ],
            24 => [
                "code" => "mollie_create_shipment_2",
                "description" => "Mollie Payment - Create shipment",
                "trigger" => "catalog/model/checkout/order/addHistory/after",
                "action" => 'extension/mollie/payment/mollie_ideal' . $this->getMethodSeparator() . 'createShipment',
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

    /**
     * Retrieve all OpenCart stores
     * * @return array
     */
	private function getStores(): array {
		$this->load->model('setting/store');

        $stores = [];
		$stores[0] = [
			'store_id' => 0,
			'name'     => (string)$this->config->get('config_name')
		];

		$_stores = $this->model_setting_store->getStores();

		foreach ($_stores as $store) {
			$stores[$store['store_id']] = [
				'store_id' => $store['store_id'],
				'name'     => (string)$store['name']
			];
		}

		return $stores;
	}

    /**
     * Cleanup old/deprecated Mollie files
     * * @return void
     */
    public function cleanUp(): void {
        if (file_exists(DIR_SYSTEM . '../vqmod/xml/mollie.xml')) {
            unlink(DIR_SYSTEM . '../vqmod/xml/mollie.xml');
        }

		$extensionDir         = DIR_EXTENSION . 'mollie/';
		$adminControllerDir   = $extensionDir . 'admin/controller/';
		$adminLanguageDir     = $extensionDir . 'admin/language/';
		$catalogControllerDir = $extensionDir . 'catalog/controller/';
		$catalogModelDir      = $extensionDir . 'catalog/model/';

		foreach (DEPRECATED_METHODS as $method) {
			$files = [
				$adminControllerDir . 'payment/mollie_' . $method . '.php',
				$catalogControllerDir . 'payment/mollie_' . $method . '.php',
				$catalogModelDir . 'payment/mollie_' . $method . '.php'
			];

			foreach ($files as $file) {
				if (file_exists($file)) {
					unlink($file);
				}
			}

			$languageFiles = glob($adminLanguageDir . '*/payment/mollie_' . $method . '.php');
            if (is_array($languageFiles)) {
                foreach ($languageFiles as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
            }
		}
    }

	/**
	 * Trigger installation of all Mollie modules.
     * * @return void
	 */
	protected function installAllModules(): void {
		$this->load->model('setting/extension');
		$model = 'model_setting_extension';
		$user_id = $this->getUserId();

		foreach ($this->mollieHelper->MODULE_NAMES as $module_name) {
			$extensions = $this->{$model}->getExtensionsByType("payment");

			$this->{$model}->install("payment", "mollie", "mollie_" . $module_name);

			$this->model_user_user_group->removePermission($user_id, "access", "extension/mollie/payment/mollie_" . $module_name);
			$this->model_user_user_group->removePermission($user_id, "modify", "extension/mollie/payment/mollie_" . $module_name);

			$this->model_user_user_group->addPermission($user_id, "access", "extension/mollie/payment/mollie_" . $module_name);
			$this->model_user_user_group->addPermission($user_id, "modify", "extension/mollie/payment/mollie_" . $module_name);
		}

		$extensions = $this->{$model}->getExtensionsByType("total");
		if (!in_array("mollie_payment_fee", $extensions)) {
			$this->{$model}->install("total", "mollie", "mollie_payment_fee");

			$this->model_user_user_group->removePermission($user_id, "access", "extension/mollie/total/mollie_payment_fee");
			$this->model_user_user_group->removePermission($user_id, "modify", "extension/mollie/total/mollie_payment_fee");

			$this->model_user_user_group->addPermission($user_id, "access", "extension/mollie/total/mollie_payment_fee");
			$this->model_user_user_group->addPermission($user_id, "modify", "extension/mollie/total/mollie_payment_fee");
		}
	}

	/**
	* The method is executed by OpenCart when the Payment module is uninstalled from the admin.
	*
	* @return void
	*/
	public function uninstall(): void {
		$this->uninstallAllModules();

		$this->load->model('setting/event');

		$events = [
			"mollie",
			"mollie_create_shipment",
			"mollie_order_info_controller",
			"mollie_order_info_template",
			"mollie_customer_order_info_controller",
            "mollie_customer_order_info_template",
			"mollie_update_message_dashboard",
			"mollie_update_message_dashboard_template",
			"mollie_product_controller",
			"mollie_product_form_template",
			"mollie_product_model_add",
			"mollie_product_model_edit",
			"mollie_checkout_controller",
			"mollie_login_controller",
			"mollie_mail_order_controller",
			"mollie_mail_order_template",
			"mollie_mail_history_controller",
            "mollie_mail_history_template",
			"mollie_get_methods_after",
			"mollie_add_order_after",
			"mollie_edit_order_after",
			"mollie_add_history_after",
			"mollie_payment_method_controller",
			"mollie_account_subscription_controller",
			"mollie_account_subscription_template",
            "mollie_create_shipment_2"
		];

		foreach ($events as $event_code) {
			$this->model_setting_event->deleteEventByCode($event_code);
		}
	}

	/**
	 * Trigger removal of all Mollie modules.
     * * @return void
	 */
	protected function uninstallAllModules(): void {
		$this->load->model('setting/extension');
		$model = 'model_setting_extension';

		foreach ($this->mollieHelper->MODULE_NAMES as $module_name) {
			$this->{$model}->uninstall("payment", "mollie_" . $module_name);
		}
	}

    /**
     * Delete deprecated method data from DB settings
     * * @return void
     */
	public function clearData(): void {
		foreach (DEPRECATED_METHODS as $method) {
            // PHP 8.1 Safe DB Escaping for security
            $escaped_method = $this->db->escape($method);

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `key` LIKE '%{$escaped_method}%'");
			if ($query->num_rows > 0) {
				$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `key` LIKE '%{$escaped_method}%'");
			}

            $this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = 'payment' AND `code` = 'mollie_{$escaped_method}'");
            $this->db->query("DELETE FROM `" . DB_PREFIX . "extension_path` WHERE `path` LIKE '%{$escaped_method}%'");
		}
	}

    public function updatePath() {
        $this->load->model('setting/extension');

        $payment_methods = ["alma", "applepay", "bancomatpay", "bancontact", "banktransfer", "belfius", "billie", "blik", "creditcard", "directdebit", "eps", "giftcard", "ideal", "in3", "kbc", "klarna", "klarnapaylater", "klarnapaynow", "klarnasliceit", "mybank", "payconiq", "paypal", "paysafecard", "przelewy24", "riverty", "satispay", "trustly", "twint", "voucher", "multibanco", "bizum", "mbway", "paybybank", "swish", "wero"];

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

        $path_check = $this->model_setting_extension->getPaths('%mollie/admin/controller/payment/mollie_ideal.php');

        if (!empty($path_check)) {
            $extension_install_id = $path_check[0]['extension_install_id'];

            foreach ($payment_methods as $payment_method) {
                foreach ($paths as $_path) {
                    $full_path = $_path . 'mollie_' . $payment_method . '.php';

                    if (is_file(DIR_EXTENSION . $full_path)) {
                        $db_path = $this->model_setting_extension->getPaths('%' . $full_path);

                        if (empty($db_path)) {
                            $this->model_setting_extension->addPath($extension_install_id, $full_path);
                        }
                    }
                }
            }
        }
    }

    /**
     * Strip prefixes safely (PHP 8.1 string checks)
     */
	private function removePrefix(array $input, string $prefix): array {
		$result = [];
        $prefixLen = strlen($prefix);
        foreach ($input as $key => $val) {
            // PHP 8+ strict start check
            if (str_starts_with((string)$key, $prefix)) {
                $newKey = substr((string)$key, $prefixLen);
                $result[$newKey] = $val;
            }
        }
        return $result;
	}

    /**
     * Add prefixes safely
     */
	public function addPrefix(string $prefix, array $input): array {
        $result = [];
        foreach ($input as $val) {
            $result[] = $prefix . (string)$val;
        }
        return $result;
    }

	/**
	 * Render the payment method's settings page.
	 */
	public function index(): void {
		// Double check for database and permissions
		$this->install();
        $this->cleanUp();
        $this->updatePath();

		// Load essential models
		$this->load->model("localisation/order_status");
		$this->load->model("localisation/geo_zone");
		$this->load->model("localisation/language");
		$this->load->model("localisation/currency");
		$this->load->model('setting/setting');
		$this->load->model('localisation/tax_class');

		$this->document->addScript('view/javascript/ckeditor/ckeditor.js');
		$this->document->addScript('view/javascript/ckeditor/adapters/jquery.js');

		$code = $this->mollieHelper->getModuleCode();

		// Double-check if clean-up has been done - For upgrades
		if (null === $this->config->get($code . '_version')) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($code . '_version') . "', `value` = '" . $this->db->escape(MOLLIE_VERSION) . "'");
		} elseif (version_compare((string)$this->config->get($code . '_version'), MOLLIE_VERSION, '<')) {
			$this->model_setting_setting->editValue($code, $code . '_version', MOLLIE_VERSION);
		}

        // Also delete data related to deprecated modules from settings
		$this->clearData();

		// Load language data
		$data = ["version" => MOLLIE_RELEASE];

		$this->load->language('extension/mollie/payment/mollie');

        $this->document->setTitle(strip_tags($this->language->get('heading_title')));

        // Set form variables
        $paymentDesc = [];
        $paymentImage = [];
        $paymentStatus = [];
        $paymentSortOrder = [];
        $paymentGeoZone = [];
        $paymentTotalMin = [];
        $paymentTotalMax = [];
        $paymentAPIToUse = [];

        foreach ($this->mollieHelper->MODULE_NAMES as $module_name) {
        	$paymentDesc[]      = $code . '_' . $module_name . '_description';
        	$paymentImage[]     = $code . '_' . $module_name . '_image';
        	$paymentStatus[]    = $code . '_' . $module_name . '_status';
        	$paymentSortOrder[] = $code . '_' . $module_name . '_sort_order';
        	$paymentGeoZone[]   = $code . '_' . $module_name . '_geo_zone';
        	$paymentTotalMin[]  = $code . '_' . $module_name . '_total_minimum';
        	$paymentTotalMax[]  = $code . '_' . $module_name . '_total_maximum';
        	$paymentAPIToUse[]  = $code . '_' . $module_name . '_api_to_use';
		}

        $fields = ["show_icons", "show_order_canceled_page", "description", "api_key", "ideal_processing_status_id", "ideal_expired_status_id", "ideal_canceled_status_id", "ideal_failed_status_id", "ideal_pending_status_id", "ideal_shipping_status_id", "create_shipment_status_id", "ideal_refund_status_id", "create_shipment", "payment_screen_language", "debug_mode", "mollie_component", "mollie_component_css_base", "mollie_component_css_valid", "mollie_component_css_invalid", "default_currency", "subscription_email", "align_icons", "single_click_payment", "order_expiry_days", "ideal_partial_refund_status_id", "payment_link", "payment_link_email", "partial_credit_order"];

        $settingFields = $this->addPrefix($code . '_', $fields);

        $storeFormFields = array_merge($settingFields, $paymentDesc, $paymentImage, $paymentStatus, $paymentSortOrder, $paymentGeoZone, $paymentTotalMin, $paymentTotalMax, $paymentAPIToUse);

        $data['stores'] = $this->getStores();

        // API key not required for multistores
        $data['api_required'] = (count($data['stores']) <= 1);

        $data['breadcrumbs'] = [];

   		$data['breadcrumbs'][] = [
	        'text'      => $this->language->get('text_home'),
	        'href'      => $this->url->link('common/dashboard', $this->token),
	      	'separator' => false
   		];

   		$data['breadcrumbs'][] = [
	       	'text'      => $this->language->get('text_extension'),
	        'href'      => $this->url->link('marketplace/extension', $this->token . '&type=payment')
   		];

   		$data['breadcrumbs'][] = [
	       	'text'      => strip_tags($this->language->get('heading_title')),
	        'href'      => $this->url->link('extension/mollie/payment/mollie_' . static::MODULE_NAME, $this->token)
   		];

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

		$update_url_data            = $this->getUpdateUrl();
		$data['update_url']         = $update_url_data ? $update_url_data['updateUrl'] : '';
        $data['text_update']        = '';
        $data['module_update']      = false;

        if ($update_url_data) {
            if (version_compare(phpversion(), \MollieHelper::NEXT_PHP_VERSION, "<") && ((int)$update_url_data['updateVersion'] > (int)MOLLIE_VERSION)) {
                $data['text_update'] = $update_url_data ? sprintf($this->language->get('text_update_message_warning'), \MollieHelper::NEXT_PHP_VERSION, $update_url_data['updateVersion'], $update_url_data['updateVersion']) : '';
            } else {
                $data['text_update'] = $update_url_data ? sprintf($this->language->get('text_update_message'), $update_url_data['updateVersion'], $data['update_url'], $update_url_data['updateVersion']) : '';
                $data['module_update'] = true;
            }

            $cookie_version = $_COOKIE["hide_mollie_update_message_version"] ?? '';
            if ($update_url_data && $cookie_version == $update_url_data['updateVersion']) {
                $data['text_update'] = '';
            }
        }

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$data['languages'] = $this->model_localisation_language->getLanguages();

		foreach ($data['languages'] as &$language) {
			$language['image'] = $this->getLanguageImage($language['code']);
		}

		$data['currencies'] = $this->model_localisation_currency->getCurrencies();
		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
        $data['method_separator'] = $this->getMethodSeparator();

		$this->load->model('tool/image');
		$no_image = 'no_image.png';
		$data['placeholder'] = $this->model_tool_image->resize($no_image, 100, 100);

		$description = [];
		foreach ($data['languages'] as $_language) {
			$description[$_language['language_id']]['title'] = "Order %";
		}

		// Load global settings.
		$settings = [
			$code . "_api_key"                    				=> NULL,
			$code . "_description"          					=> $description,
			$code . "_show_icons"                 				=> FALSE,
			$code . "_align_icons"                 				=> 'left',
			$code . "_show_order_canceled_page"   				=> FALSE,
			$code . "_ideal_pending_status_id"    				=> 1,
			$code . "_ideal_pending_status_notify"    			=> FALSE,
			$code . "_ideal_processing_status_id" 				=> 2,
			$code . "_ideal_processing_status_notify" 			=> FALSE,
			$code . "_ideal_canceled_status_id"   				=> 7,
			$code . "_ideal_canceled_status_notify"   			=> FALSE,
			$code . "_ideal_failed_status_id"     				=> 10,
			$code . "_ideal_failed_status_notify"     			=> FALSE,
			$code . "_ideal_expired_status_id"    				=> 14,
			$code . "_ideal_expired_status_notify"    			=> FALSE,
			$code . "_ideal_shipping_status_id"   				=> 3,
			$code . "_ideal_shipping_status_notify"   			=> FALSE,
			$code . "_create_shipment_status_id"  				=> 3,
			$code . "_ideal_refund_status_id"  					=> 11,
			$code . "_ideal_refund_status_notify"  				=> FALSE,
			$code . "_ideal_partial_refund_status_id"  			=> 11,
			$code . "_ideal_partial_refund_status_notify"  		=> FALSE,
			$code . "_create_shipment"  		  				=> 3,
			$code . "_payment_screen_language"  		  		=> 'en-gb',
			$code . "_default_currency"  		  				=> 'DEF',
			$code . "_debug_mode"  		  						=> FALSE,
			$code . "_recurring_email"  		  				=> [],
			$code . "_mollie_component"  		  				=> FALSE,
			$code . "_single_click_payment"  		  			=> FALSE,
			$code . "_partial_credit_order"  		  			=> FALSE,
			$code . "_order_expiry_days"  		  			    => 25,
			$code . "_payment_link"  		  			        => 0,
			$code . "_payment_link_email"  		  				=> [],
			$code . "_mollie_component_css_base"  		  		=> [
																	"background_color" => "#fff",
																	"color"			   => "#555",
																	"font_size"		   => "12px",
																	"other_css"		   => "border-width: 1px;\nborder-style: solid;\nborder-color: #ccc;\nborder-radius: 4px;\npadding: 8px;"
																	],
			$code . "_mollie_component_css_valid"  		  		=> [
																	"background_color" => "#fff",
																	"color"			   => "#090",
																	"font_size"		   => "12px",
																	"other_css"		   => "border-width: 1px;\nborder-style: solid;\nborder-color: #090;\nborder-radius: 4px;\npadding: 8px;"
																	],
			$code . "_mollie_component_css_invalid"  		  	=> [
																	"background_color" => "#fff",
																	"color"			   => "#f00",
																	"font_size"		   => "12px",
																	"other_css"		   => "border-width: 1px;\nborder-style: solid;\nborder-color: #f00;\nborder-radius: 4px;\npadding: 8px;"
																	],
		];

		// Check if order complete status is defined in store setting
		$data['is_order_complete_status'] = true;
		$data['order_complete_statuses'] = [];

		if (null === $this->config->get('config_complete_status') && $this->config->get('config_complete_status_id') == '') {
			$data['is_order_complete_status'] = false;
		}

		foreach ($data['stores'] as &$store) {
			$config_setting = $this->model_setting_setting->getSetting($code, $store['store_id']);

			foreach ($settings as $setting_name => $default_value) {
				if (isset($this->request->post[$store['store_id'] . '_' . $setting_name])) {
					$data['stores'][$store['store_id']][$setting_name] = $this->request->post[$store['store_id'] . '_' . $setting_name];
				} else {
					$stored_setting = $config_setting[$setting_name] ?? null;

					if ($stored_setting === null && $default_value !== null) {
						$data['stores'][$store['store_id']][$setting_name] = $default_value;
					} else {
						$data['stores'][$store['store_id']][$setting_name] = $stored_setting;
					}
				}
			}

			// Check which payment methods we can use with the current API key.
			$allowed_methods = [];
			try {
				$apiClient = $this->getAPIClientForKey($store['store_id']);
				if ($apiClient) {
					$api_methods = $apiClient->methods->all();
					foreach ($api_methods as $api_method) {
                        if ($api_method->status == 'activated') {
                            if ($api_method->id == 'in3') {
                                $api_method->id = 'in_3';
                            } elseif ($api_method->id == 'przelewy24') {
                                $api_method->id = 'przelewy_24';
                            }

                            $allowed_methods[$api_method->id] = [
                                "method" => $api_method->id,
                                "minimumAmount" => $api_method->minimumAmount,
                                "maximumAmount" => $api_method->maximumAmount
                            ];
                        }
					}
				} else {
					$data['error_api_key'] = $this->language->get("error_api_key_invalid");
				}
			} catch (\Mollie\Api\Exceptions\ApiException $e) {
				if (isset($store[$code . '_api_key']) && str_contains($e->getMessage(), "Unauthorized request")) {
					$data['error_api_key'] = $this->language->get("error_api_key_invalid");
				}
			}

			$data['store_data'][$store['store_id'] . '_' . $code . '_payment_methods'] = [];
			$data['store_data']['creditCardEnabled'] = false;

			foreach ($this->mollieHelper->MODULE_NAMES as $module_name) {

				$payment_method = [];

				$payment_method['name']    = $this->language->get("name_mollie_" . $module_name);
				$payment_method['icon']    = "../image/mollie/" . $module_name . "2x.png";
				$payment_method['allowed'] = array_key_exists($module_name, $allowed_methods);

				if (($module_name == 'creditcard') && $payment_method['allowed']) {
					$data['store_data']['creditCardEnabled'] = true;
				}

				if (!$payment_method['allowed']) {
					$this->model_setting_setting->editValue($code, $code . '_' . $module_name . '_status', 0, $store['store_id']);
				}

				// Load module specific settings using PHP 8.1 safe null coalescing
				$store_prefix = $store['store_id'] . '_' . $code . '_' . $module_name;
				$global_prefix = $code . '_' . $module_name;

				$payment_method['status'] = isset($config_setting[$store_prefix . '_status'])
					? ($config_setting[$store_prefix . '_status'] == "on" || $config_setting[$store_prefix . '_status'] == 1)
					: (bool)($config_setting[$global_prefix . '_status'] ?? false);

				$payment_method['description'] = $config_setting[$store_prefix . '_description']
					?? $config_setting[$global_prefix . '_description'] ?? null;

				$img_val = $config_setting[$store_prefix . '_image'] ?? $config_setting[$global_prefix . '_image'] ?? null;
				$payment_method['image'] = $img_val;
				$payment_method['thumb'] = !empty($img_val) ? $this->model_tool_image->resize($img_val, 100, 100) : $this->model_tool_image->resize($no_image, 100, 100);

				$payment_method['sort_order'] = $config_setting[$store_prefix . '_sort_order']
					?? $config_setting[$global_prefix . '_sort_order'] ?? null;

				$payment_method['geo_zone'] = $config_setting[$store_prefix . '_geo_zone']
					?? $config_setting[$global_prefix . '_geo_zone'] ?? null;

				if ($payment_method['allowed']) {
					$minimumAmount = $allowed_methods[$module_name]['minimumAmount']->value;
					$currency      = $allowed_methods[$module_name]['minimumAmount']->currency;

					if ($this->currency->has($currency)) {
						$payment_method['minimumAmount'] = sprintf($this->language->get('text_standard_total'), $this->currency->format($this->currency->convert($minimumAmount, $currency, (string)$this->config->get('config_currency')), $currency));

						$payment_method['total_minimum'] = $config_setting[$store_prefix . '_total_minimum']
							?? $config_setting[$global_prefix . '_total_minimum']
							?? $this->numberFormat($this->currency->convert($minimumAmount, $currency, (string)$this->config->get('config_currency')), (string)$this->config->get('config_currency'));

						if (!empty($allowed_methods[$module_name]['maximumAmount'])) {
							$maximumAmount = $allowed_methods[$module_name]['maximumAmount']->value;
							$currency      = $allowed_methods[$module_name]['maximumAmount']->currency;
							$payment_method['maximumAmount'] = sprintf($this->language->get('text_standard_total'), $this->currency->format($this->currency->convert($maximumAmount, $currency, (string)$this->config->get('config_currency')), $currency));
						} else {
							$payment_method['maximumAmount'] = $this->language->get('text_no_maximum_limit');
						}

						$payment_method['total_maximum'] = $config_setting[$store_prefix . '_total_maximum']
							?? $config_setting[$global_prefix . '_total_maximum']
							?? (!empty($allowed_methods[$module_name]['maximumAmount']) ? $this->numberFormat($this->currency->convert($maximumAmount, $currency, (string)$this->config->get('config_currency')), (string)$this->config->get('config_currency')) : '');
					} else {
						$payment_method['minimumAmount'] = sprintf($this->language->get('text_standard_total'), $currency . ' ' . $minimumAmount);
						$payment_method['total_minimum'] = $minimumAmount;

						if (!empty($allowed_methods[$module_name]['maximumAmount'])) {
							$maximumAmount = $allowed_methods[$module_name]['maximumAmount']->value;
							$payment_method['maximumAmount'] = sprintf($this->language->get('text_standard_total'), $currency . ' ' . $maximumAmount);
							$payment_method['total_maximum'] = $maximumAmount;
						} else {
							$payment_method['maximumAmount'] = $this->language->get('text_no_maximum_limit');
							$payment_method['total_maximum'] = '';
						}
					}
				}

				$data['store_data'][$store['store_id'] . '_' . $code . '_payment_methods'][$module_name] = $payment_method;
			}

            // Sort payment methods (PHP 8 safe sorting)
            uksort($data['store_data'][$store['store_id'] . '_' . $code . '_payment_methods'], function($a, $b) {
                return $a <=> $b;
            });

			$data['stores'][$store['store_id']]['entry_cstatus'] = $this->checkCommunicationStatus($config_setting[$code . '_api_key'] ?? null);
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
				$suffix = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
				$i = 0;

				while (($size / 1024) > 1) {
					$size = $size / 1024;
					$i++;
				}

				$data['error_warning'] = sprintf($this->language->get('error_log_warning'), basename($file), round((float)substr((string)$size, 0, (int)strpos((string)$size, '.') + 4), 2) . $suffix[$i]);
			} else {
				$data['log'] = file_get_contents($file, false, null);
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
			if (empty($this->request->post['0_' . $code . '_api_key'])) {
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
					$desc = $this->request->post[$store["store_id"] . '_' . $code . '_' . $module_name . '_description'] ?? [];
					foreach ($this->model_localisation_language->getLanguages() as $language) {
						if (empty($desc[$language['language_id']]['title'])) {
							$fallback_name = $this->request->post[$store["store_id"] . '_' . $code . '_' . $module_name . '_name'] ?? '';
							$this->request->post[$store["store_id"] . '_' . $code . '_' . $module_name . '_description'][$language['language_id']]['title'] = $fallback_name;
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

    public function validate_api_key(): void {
    	$this->load->language('extension/mollie/payment/mollie');

		$json = [
			'error' => false,
			'invalid' => false,
			'valid' => false,
			'message' => '',
		];

		if (empty($this->request->get['key'])) {
			$json['invalid'] = true;
			$json['message'] = $this->language->get('error_no_api_client');
		} else {
			try {
				$client = $this->mollieHelper->getAPIClientForKey((string)$this->request->get['key']);

				if (!$client) {
					$json['invalid'] = true;
					$json['message'] = $this->language->get('error_no_api_client');
				} else {
					$client->methods->allEnabled();

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
	 * @param string|null $api_key
	 * @return string
	 */
	protected function checkCommunicationStatus(?string $api_key = null): string {
		$this->load->language('extension/mollie/payment/mollie');

		if (empty($api_key)) {
			return '<span style="color:red">' .  $this->language->get('error_no_api_key') . '</span>';
		}

		try {
			$client = $this->mollieHelper->getAPIClientForKey($api_key);

			if (!$client) {
				return '<span style="color:red">' . $this->language->get('error_no_api_client') . '</span>';
			}

			$client->methods->allEnabled();

			return '<span style="color: green">OK</span>';
		} catch (IncompatiblePlatform $e) {
			return '<span style="color:red">' . $e->getMessage() . ' ' . $this->language->get('error_api_help') . '</span>';
		} catch (ApiException $e) {
			return '<span style="color:red">' . sprintf($this->language->get('error_comm_failed'), htmlspecialchars($e->getMessage()), (isset($client) ? htmlspecialchars($client->getApiEndpoint()) : 'Mollie')) . '</span>';
		}
	}

	/**
	 * @return string
	 */
	private function getTokenUriPart(): string {
		if (isset($this->session->data['user_token'])) {
			return 'user_token=' . (string)$this->session->data['user_token'];
		}

		return 'token=' . (string)($this->session->data['token'] ?? '');
	}

	private function getUserId(): int {
		$this->load->model('user/user_group');

		if (method_exists($this->user, 'getGroupId')) {
			return (int)$this->user->getGroupId();
		}

		return (int)$this->user->getId();
	}

	public function saveAPIKey(): bool {
		$this->load->model('setting/setting');
		$store_id = (int)($this->request->post['store_id'] ?? 0);
		$code = $this->mollieHelper->getModuleCode();

		$data = $this->model_setting_setting->getSetting($code, $store_id);
		$data[$code.'_api_key'] = (string)($this->request->post['api_key'] ?? '');

		$this->model_setting_setting->editSetting($code, $data, $store_id);
		return true;
	}

	private function getUpdateUrl(): array|bool {
        $client = new mollieHttpClient();
        $info = $client->get(MOLLIE_VERSION_URL);

        if (isset($info["tag_name"])) {
            if (str_contains((string)$info["tag_name"], 'oc4')) {
                $parts = explode("-", $info["tag_name"]);
                $tag_name = isset($parts[1]) ? explode('_', $parts[1]) : [];
            } else {
                $tag_name = ["oc4", $info["tag_name"]];
            }

            if (isset($tag_name[0]) && ($tag_name[0] == 'oc4')) {
                if (isset($tag_name[1]) && ($tag_name[1] != MOLLIE_VERSION) && version_compare(MOLLIE_VERSION, $tag_name[1], "<")) {
                    return [
                        "updateUrl" => $this->url->link("extension/mollie/payment/mollie_" . static::MODULE_NAME . $this->getMethodSeparator() . "update", $this->token),
                        "updateVersion" => $tag_name[1]
                    ];
                }
            }
        }

        return false;
    }

	protected function getLanguageImage(string $code): string {
		if (is_file(DIR_LANGUAGE . $code . '/' . $code . '.png')) {
			return 'language/' . $code . '/' . $code . '.png';
		}

		$files = glob(DIR_EXTENSION . '*/admin/language/' . $code . '/' . $code . '.png');
		if ($files) {
			$relative_path = str_replace(DIR_OPENCART, '', $files[0]);
			return HTTP_CATALOG . ltrim(str_replace(['\\', ' '], ['/', '%20'], $relative_path), '/');
		}

		if (is_file(DIR_OPENCART . 'catalog/language/' . $code . '/' . $code . '.png')) {
			return HTTP_CATALOG . 'catalog/language/' . $code . '/' . $code . '.png';
		}

		return '';
	}

    public function update(): void {
        if (!$this->getUpdateUrl() || (version_compare(phpversion(), \MollieHelper::NEXT_PHP_VERSION, "<") && ((int)$this->getUpdateUrl()['updateVersion'] > (int)MOLLIE_VERSION))) {
			$this->response->redirect($this->url->link('extension/mollie/payment/mollie_' . static::MODULE_NAME, $this->token));
            return;
		}

        $client = new mollieHttpClient();
        $info = $client->get(MOLLIE_VERSION_URL);

        $temp_file = MOLLIE_TMP . "/mollieUpdate.zip";
        $handle = fopen($temp_file, "w+");

        $browser_download_url = '';
        if (!empty($info["assets"])) {
            foreach($info["assets"] as $asset) {
                if(str_contains((string)$asset["name"], 'oc4')) {
                    $browser_download_url = $asset['browser_download_url'];
                    break;
                }
            }
        }

        if (!empty($browser_download_url)) {
            $content = $client->get($browser_download_url, false, false);
        } else {
            $this->response->redirect($this->url->link('extension/mollie/payment/mollie_' . static::MODULE_NAME, $this->token));
            return;
        }

        fwrite($handle, $content);
        fclose($handle);

        $temp_dir = MOLLIE_TMP . "/mollieUpdate";
        if (class_exists("ZipArchive")) {
            $zip = new \ZipArchive;
            $zip->open($temp_file);
            $zip->extractTo($temp_dir);
            $zip->close();
        } else {
            shell_exec("unzip " . escapeshellarg($temp_file) . " -d " . escapeshellarg($temp_dir));
        }

        $handle_dir = opendir($temp_dir);
        $upload_dir = $temp_dir . "/upload";
        while (false !== ($file = readdir($handle_dir))) {
            if ($file != "." && $file != ".." && is_dir($temp_dir . "/" . $file . "/upload")) {
                $upload_dir = $temp_dir . "/" . $file . "/upload";
                break;
            }
        }
        closedir($handle_dir);

        if (is_dir($upload_dir)) {
            $handle_up = opendir($upload_dir);
            while (false !== ($file = readdir($handle_up))) {
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
            closedir($handle_up);
        }

        unlink($temp_file);
        $this->rmDirRecursive($temp_dir);

        if (!$this->getUpdateUrl()) {
            $this->load->language('extension/mollie/payment/mollie');
            $this->session->data['success'] = sprintf($this->language->get('text_update_success'), MOLLIE_RELEASE);
        }

        $this->response->redirect($this->url->link('extension/mollie/payment/mollie_' . static::MODULE_NAME, $this->token));
    }

    public function rmDirRecursive(string $dir): bool {
        if (!is_dir($dir)) {
            return false;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->rmDirRecursive("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    private function cpy(string $source, string $dest): void {
        if (is_dir($source)) {
            $dir_handle = opendir($source);
            while (false !== ($file = readdir($dir_handle))) {
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

		if (!is_file($file) || !filesize($file)) {
			$this->session->data['error'] = sprintf($this->language->get('error_log_warning'), $filename, '0B');
			$this->response->redirect($this->url->link('extension/mollie/payment/mollie_' . static::MODULE_NAME, $this->token));
            return;
		}

		$this->response->addheader('Pragma: public');
		$this->response->addheader('Expires: 0');
		$this->response->addheader('Content-Description: File Transfer');
		$this->response->addheader('Content-Type: application/octet-stream');
		$this->response->addheader('Content-Disposition: attachment; filename="' . (string)$this->config->get('config_name') . '_' . date('Y-m-d_H-i-s') . '_mollie_error.log"');
		$this->response->addheader('Content-Transfer-Encoding: binary');

		$this->response->setOutput(file_get_contents($file, false, null));
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
            if ($handle) {
			    fclose($handle);
            }
			$json['success'] = $this->language->get('text_log_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function sendMessage(): void {
		$this->load->language('extension/mollie/payment/mollie');
		$json = [];

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $name = (string)($this->request->post['name'] ?? '');
            $email = (string)($this->request->post['email'] ?? '');
            $subject = (string)($this->request->post['subject'] ?? '');
            $enquiry = (string)($this->request->post['enquiry'] ?? '');

			if ((mb_strlen($name) < 3) || (mb_strlen($name) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}

			if ((mb_strlen($email) > 96) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$json['error'] = $this->language->get('error_email');
			}

			if (mb_strlen($subject) < 3) {
				$json['error'] = $this->language->get('error_subject');
			}

			if (mb_strlen($enquiry) < 25) {
				$json['error'] = $this->language->get('error_enquiry');
			}

			if (!isset($json['error'])) {
				$enquiry .= "<br>Opencart version : " . VERSION;
				$enquiry .= "<br>Mollie version : " . MOLLIE_VERSION;

				if ($this->config->get('config_mail_engine')) {
					$mail = new \Opencart\System\Library\Mail($this->config->get('config_mail_engine'));
					$mail->parameter = (string)$this->config->get('config_mail_parameter');
					$mail->smtp_hostname = (string)$this->config->get('config_mail_smtp_hostname');
					$mail->smtp_username = (string)$this->config->get('config_mail_smtp_username');
					$mail->smtp_password = html_entity_decode((string)$this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
					$mail->smtp_port = (int)$this->config->get('config_mail_smtp_port');
					$mail->smtp_timeout = (int)$this->config->get('config_mail_smtp_timeout');

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

    public function numberFormat(float|string $amount, string $currency): string {
        $intCurrencies = ["ISK", "JPY"];
        if (!in_array($currency, $intCurrencies)) {
            $formattedAmount = number_format((float)$amount, 2, '.', '');
        } else {
            $formattedAmount = number_format((float)$amount, 0, '.', '');
        }
        return $formattedAmount;
    }

    protected function convertCurrency(float|string $amount, string $currency): float {
        $this->load->model("localisation/currency");
        $currencies = $this->model_localisation_currency->getCurrencies();

        $val = isset($currencies[$currency]['value']) ? (float)$currencies[$currency]['value'] : 1.0;
        return (float)$amount * $val;
    }

    public function refund(): void {
        $this->load->language('sale/order');
        $this->load->language('extension/mollie/payment/mollie');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/order');
        $this->load->model('extension/mollie/payment/mollie');

        $json = [];
        $json['error'] = false;

        $log = new \Opencart\System\Library\Log('Mollie.log');
        $mollieHelper = new \MollieHelper($this->registry);
        $moduleCode = $mollieHelper->getModuleCode();

        $order_id = (int)($this->request->get['order_id'] ?? 0);

        if ($order_id <= 0) {
            $json['error'] = $this->language->get('text_order_not_found');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $order = $this->model_sale_order->getOrder($order_id);
        $molliePaymentDetails = $this->model_extension_mollie_payment_mollie->getMolliePayment($order_id);

        if (!$molliePaymentDetails) {
            $log->write("Mollie order(mollie_payment_id) not found for order_id - $order_id");
            $json['error'] = $this->language->get('text_order_not_found');
        } elseif (!empty($molliePaymentDetails['refund_id'])) {
            $log->write("Refund has been processed already for order_id - $order_id");
            $json['error'] = $this->language->get('text_refunded_already');
        }

        if (!$json['error']) {
            $json['partial_credit_order'] = false;
            $stock_mutation_data = [];
            $order_products = $this->model_sale_order->getProducts($order_id);

            foreach ($order_products as $order_product) {
                $stock_mutation_data[] = [
                    "order_product_id" => $order_product['order_product_id'],
                    "quantity" => (int)$order_product['quantity']
                ];
            }

            if (!empty($molliePaymentDetails['transaction_id'])) {
                $molliePayment = $this->getAPIClient($order['store_id'])->payments->get($molliePaymentDetails['transaction_id']);
                if ($molliePayment->isPaid()) {
                    $amount = $this->numberFormat($this->convertCurrency((float)$order['total'], $order['currency_code']), $order['currency_code']);
                    $refundObject = $this->getAPIClient($order['store_id'])->send(
                        new CreatePaymentRefundRequest(
                            paymentId: $molliePaymentDetails['transaction_id'],
                            description: "Refund for order - $order_id",
                            amount: new Money(currency: $order['currency_code'], value: (string)$amount),
                            metadata: [
                                "order_id" => $order_id,
                                "order_product_id" => json_encode($stock_mutation_data),
                                "transaction_id" => $molliePaymentDetails['transaction_id']
                            ]
                        )
                    );

                    if ($refundObject->id) {
                        $log->write("Refund has been processed for order_id - $order_id, transaction_id - " . $molliePaymentDetails['transaction_id'] . ". Refund id is $refundObject->id.");
                        $json['success'] = $this->language->get('text_refund_success');
                        $json['order_status_id'] = $this->config->get($moduleCode . "_ideal_refund_status_id");
                        $json['notify_customer'] = ($this->config->get($moduleCode . "_ideal_refund_status_notify")) ? true : false;
                        $json['comment'] = $this->language->get('text_refund_success');
                        $json['order_id'] = $order_id;

                        $json['date'] = date($this->language->get('date_format_short'));
                        $json['amount'] = $this->currency->format($refundObject->amount->value, $refundObject->amount->currency, 1);
                        $json['status'] = ucfirst($refundObject->status);

                        $this->model_extension_mollie_payment_mollie->updateMolliePaymentForPaymentAPI($molliePaymentDetails['transaction_id'], $refundObject->id, 'refunded');

                        $data = [
                            "refund_id" => $refundObject->id,
                            "order_id" => $order_id,
                            "transaction_id" => $molliePaymentDetails['transaction_id'],
                            "amount" => $refundObject->amount->value,
                            "currency_code" => $refundObject->amount->currency,
                            "status" => $refundObject->status
                        ];
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

    public function partialRefund(): void {
        $this->load->language('sale/order');
        $this->load->language('extension/mollie/payment/mollie');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/order');
        $this->load->model('extension/mollie/payment/mollie');

        $json = [];
        $json['error'] = false;

        $log = new \Opencart\System\Library\Log('Mollie.log');
        $mollieHelper = new \MollieHelper($this->registry);
        $moduleCode = $mollieHelper->getModuleCode();

        $order_id = (int)($this->request->get['order_id'] ?? 0);

        if ($order_id <= 0) {
            $json['error'] = $this->language->get('text_order_not_found');
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $order = $this->model_sale_order->getOrder($order_id);

        $molliePaymentDetails = $this->model_extension_mollie_payment_mollie->getMolliePayment($order_id);
        if (!$molliePaymentDetails) {
            $log->write("Mollie order(mollie_payment_id) not found for order_id - $order_id");
            $json['error'] = $this->language->get('text_order_not_found');
        } elseif (!empty($molliePaymentDetails['refund_id'])) {
            $log->write("Refund has been processed already for order_id - $order_id");
            $json['error'] = $this->language->get('text_refunded_already');
        }

        $partial_refund_type = $this->request->post['partial_refund_type'] ?? '';
        $refund_amount = (float)($this->request->post['refund_amount'] ?? 0);

        if (($refund_amount <= 0) && ($partial_refund_type == 'custom_amount')) {
            $json['error'] = $this->language->get('error_refund_amount');
        }

        if (!isset($this->request->post['productline']) && ($partial_refund_type == 'productline')) {
            $json['error'] = $this->language->get('error_productline');
        }

        $productline_error = true;
        if (isset($this->request->post['productline']) && is_array($this->request->post['productline'])) {
            foreach ($this->request->post['productline'] as $line) {
                if (isset($line['selected'])) {
                    $productline_error = false;
                    break;
                }
            }
        }

        if ($productline_error && ($partial_refund_type == 'productline')) {
            $json['error'] = $this->language->get('error_productline');
        }

        if (!$json['error']) {
            $json['partial_credit_order'] = false;

            $order_products = $this->model_sale_order->getProducts($order_id);

            $refundObject = null;

            if ($partial_refund_type == 'productline') {
                $lines = [];
                $orderProductIDs = [];
                $stock_mutation_data = [];
                foreach ($this->request->post['productline'] as $order_product_id => $line) {
                    if (isset($line['selected'])) {
                        $lines[] = [
                            "order_product_id" => $order_product_id,
                            "quantity" => (int)$line['quantity']
                        ];

                        $orderProductIDs[] = $order_product_id;
                    }

                    if (isset($line['stock_mutation'])) {
                        $stock_mutation_data[] = [
                            "order_product_id" => $order_product_id,
                            "quantity" => (int)$line['quantity']
                        ];
                    }
                }
                if (!empty($lines)) {
                    if (!empty($molliePaymentDetails['transaction_id'])) {
                        try {
                            $amount = 0;
                            foreach ($order_products as $product) {
                                if (in_array($product['order_product_id'], $orderProductIDs)) {
                                    $amount += $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order['currency_code'], false, false);
                                }
                            }

                            $refundObject = $this->getAPIClient($order['store_id'])->send(
                                new CreatePaymentRefundRequest(
                                    paymentId: $molliePaymentDetails['transaction_id'],
                                    description: "Partial refund for order - $order_id",
                                    amount: new Money(currency: $order['currency_code'], value: (string)$this->numberFormat($amount, $order['currency_code'])),
                                    metadata: [
                                        "order_id" => $order_id,
                                        "order_product_id" => json_encode($lines),
                                        "transaction_id" => $molliePaymentDetails['transaction_id']
                                    ]
                                )
                            );

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
                }
            } elseif ($partial_refund_type == 'custom_amount') {
                try {
                    $amount = $this->numberFormat($refund_amount, $order['currency_code']);
                    $refundObject = $this->getAPIClient($order['store_id'])->send(
                        new CreatePaymentRefundRequest(
                            paymentId: $molliePaymentDetails['transaction_id'],
                            description: "Partial refund for order - $order_id",
                            amount: new Money(currency: $order['currency_code'], value: (string)$amount),
                            metadata: [
                                "order_id" => $order_id,
                                "transaction_id" => $molliePaymentDetails['transaction_id']
                            ]
                        )
                    );
                } catch (\Mollie\Api\Exceptions\ApiException $e) {
                    $log->write("Creating refund failed: " . htmlspecialchars($e->getMessage()));
                    $json['error'] = $this->language->get('text_no_refund');
                }
            }

            if (!$json['error'] && $refundObject && $refundObject->id) {
                $amount = $refundObject->amount->value .' '. $refundObject->amount->currency;
                $log->write('Partial refund of amount ' . $amount . ' has been processed for order_id - ' . $order_id . ' and transaction_id - ' . $molliePaymentDetails['transaction_id'] . '. Refund id is ' . $refundObject->id);
                $json['success'] = $this->language->get('text_refund_success');
                $json['order_status_id'] = $this->config->get($moduleCode . "_ideal_partial_refund_status_id");
                $json['notify_customer'] = ($this->config->get($moduleCode . "_ideal_partial_refund_status_notify")) ? true : false;
                $json['comment'] = sprintf($this->language->get('text_partial_refund_success'), $amount);
                $json['order_id'] = $order_id;

                $json['date'] = date($this->language->get('date_format_short'));
                $json['amount'] = $this->currency->format($refundObject->amount->value, $refundObject->amount->currency, 1);
                $json['status'] = ucfirst($refundObject->status);

                $data = [
                    "refund_id" => $refundObject->id,
                    "order_id" => $order_id,
                    "transaction_id" => $molliePaymentDetails['transaction_id'],
                    "amount" => $refundObject->amount->value,
                    "currency_code" => $refundObject->amount->currency,
                    "status" => $refundObject->status
                ];
                $this->model_extension_mollie_payment_mollie->addMollieRefund($data);

            } elseif (!$json['error']) {
                $log->write('Partial Refund can not be processed for order_id - ' . $order_id . ' and transaction_id - ' . ($molliePaymentDetails['transaction_id'] ?? 'unknown'));
                $json['error'] = $this->language->get('text_no_refund');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function orderController(string &$route, array &$data): void {
        $this->load->model('sale/order');

        $order_id = (int)($data['order_id'] ?? $this->request->get['order_id'] ?? 0);

        if ($order_id <= 0) {
            return;
        }

        $order_info = $this->model_sale_order->getOrder($order_id);

        if (!empty($order_info) && (int)$order_info['store_id'] >= 0) {
            $this->load->language('extension/mollie/payment/mollie');
            $this->load->language('sale/order');
            $this->load->model('extension/mollie/payment/mollie');

            if (!isset($this->mollieHelper)) {
                return;
            }

            $payment_code = (string)($order_info['payment_method']['code'] ?? $order_info['payment_code'] ?? '');
            $moduleCode = $this->mollieHelper->getModuleCode();
            $data['mollie_pending_status_id'] = (int)$this->config->get($moduleCode . '_ideal_pending_status_id');

            $molliePaymentDetails = $this->model_extension_mollie_payment_mollie->getMolliePayment($order_id);
            $mollie_payments = $this->model_extension_mollie_payment_mollie->getMolliePayments($order_id);
            $mollie_payment_links = $this->model_extension_mollie_payment_mollie->getMolliePaymentLinks($order_id);

            $is_mollie_order = str_contains($payment_code, 'mollie') || !empty($molliePaymentDetails) || !empty($mollie_payments) || !empty($mollie_payment_links);

            $data['currency'] = $order_info['currency_code'];
            $data['store_id'] = $order_info['store_id'];
            $data['payment_code'] = $payment_code;
            $data['order_total'] = $this->currency->format((float)$order_info['total'], $order_info['currency_code'], false, false);

            $data['payment_status'] = false;
            $data['payment_status_class'] = '';
            $data['paymentMethod'] = '';
            $data['showRefundButton'] = false;
            $data['showPartialRefundButton'] = false;
            $data['mollie_refunds'] = [];
            $data['productlines'] = [];
            $data['capture_payment'] = false;

            if ($is_mollie_order) {
                $apiKey = $this->mollieHelper->getApiKey($order_info['store_id']);

                $data['showRefundButton'] = (bool)$apiKey;
                $data['showPartialRefundButton'] = (bool)$apiKey;
                $data['partial_credit_order'] = (bool)$this->config->get($moduleCode . "_partial_credit_order");
                $data['payment_status'] = 'open';

                $refunds = $this->model_extension_mollie_payment_mollie->getMollieRefunds($order_id);
                if ($refunds) {
                    $data['showRefundButton'] = false;
                    foreach ($refunds as $refund) {
                        try {
                            $mollieRefund = $this->getAPIClient($order_info['store_id'])->payments->get($refund['transaction_id'])->getRefund($refund['refund_id']);
                            if ($mollieRefund->status != $refund['status']) {
                                $this->model_extension_mollie_payment_mollie->updateMollieRefundStatus($refund['refund_id'], $refund['transaction_id'], $mollieRefund->status);
                            }
                            $data['mollie_refunds'][] = [
                                "date_added" => date($this->language->get('date_format_short'), strtotime($refund['date_created'])),
                                "amount"     => $this->currency->format((float)$refund['amount'], $order_info['currency_code'], 1),
                                "status"     => ucfirst($mollieRefund->status)
                            ];
                        } catch (\Exception $e) {
                            $this->log->write('Mollie Refund Error: ' . $e->getMessage());
                        }
                    }
                }

                if (!empty($molliePaymentDetails['transaction_id'])) {
                    try {
                        $molliePayment = $this->getAPIClient($order_info['store_id'])->payments->get($molliePaymentDetails['transaction_id']);
                        $data['payment_status'] = $molliePayment->status;
                        $data['paymentMethod'] = $molliePayment->method;
                        if ($molliePayment->method === 'voucher') {
                            $data['showRefundButton'] = false;
                        }
                        if ($molliePayment->hasRefunds()) {
                            $data['payment_status'] = 'refunded';
                        }

                        if (!empty($molliePaymentDetails['mollie_order_id']) && ($data['payment_status'] == 'authorized')) {
                            $data['capture_payment'] = true;
                        }
                    } catch (\Exception $e) {
                        $this->log->write('Mollie API Error: ' . $e->getMessage());
                    }

                    try {
                        $order_products = $this->model_sale_order->getProducts($order_id);
                        $molliePayment = $this->getAPIClient($order_info['store_id'])->payments->get($molliePaymentDetails['transaction_id'], ["embed" => "refunds"]);

                        $refundedLines = [];
                        if(!empty($molliePayment->_embedded->refunds)) {
                            foreach ($molliePayment->_embedded->refunds as $refund) {
                                $order_product_ids = [];

                                if (isset($refund->metadata) && isset($refund->metadata->order_product_id)) {
                                    $order_product_ids = json_decode($refund->metadata->order_product_id, true);
                                }

                                if (is_array($order_product_ids)) {
                                    foreach ($order_product_ids as $order_product) {
                                        $refundedLines[] = $order_product['order_product_id'];
                                    }
                                }
                            }
                        }

                        foreach ($order_products as $_product) {
                            if (!in_array($_product['order_product_id'], $refundedLines)) {
                                $productlines[] = array(
                                    "name" => $_product['name'],
                                    "option" => $this->model_sale_order->getOptions($order_id, $_product['order_product_id']),
                                    "quantity" => $_product['quantity'],
                                    "order_product_id" => $_product['order_product_id']
                                );
                            }
                        }
                    } catch (\Exception $e) {
                        $this->log->write('Mollie API Error: ' . $e->getMessage());
                    }
                }

                foreach ($mollie_payments as $mollie_payment) {
                    $amount_format = !empty($mollie_payment['amount']) ?
                                     $this->currency->format((float)$mollie_payment['amount'], $order_info['currency_code'], 1) :
                                     $this->currency->format((float)$order_info['total'], $order_info['currency_code'], (float)$order_info['currency_value']);

                    $data['mollie_payments'][] = [
                        "date_added" => date($this->language->get('date_format_short'), strtotime($mollie_payment['date_modified'])),
                        "method" => ucfirst((string)$mollie_payment['method']),
                        "amount" => $amount_format,
                        "status" => ucfirst((string)$mollie_payment['bank_status']),
                    ];
                }

                if (str_contains($payment_code, 'mollie_payment_link')) {
                    $paid = false;
                    foreach ($mollie_payment_links as $mollie_payment_link) {
                        $amount_format = !empty($mollie_payment_link['amount']) ?
                                         $this->currency->format((float)$mollie_payment_link['amount'], $order_info['currency_code'], 1) :
                                         $this->currency->format((float)$order_info['total'], $order_info['currency_code'], (float)$order_info['currency_value']);

                        $status_text = !empty($mollie_payment_link['date_payment']) ?
                                       'Paid (' . date($this->language->get('date_format_short'), strtotime($mollie_payment_link['date_payment'])) . ')' :
                                       'Open';

                        $data['mollie_payments'][] = [
                            "date_added" => date($this->language->get('date_format_short'), strtotime($mollie_payment_link['date_created'])),
                            "method" => 'N/A',
                            "amount" => $amount_format,
                            "status" => $status_text
                        ];

                        if (!empty($mollie_payment_link['date_payment'])) {
                            $paid = true;
                        }
                    }
                    $data['payment_status'] = $paid ? 'paid' : 'open';
                }

                $data['payment_status_class'] = match ($data['payment_status']) {
                    'paid', 'settled' => 'success',
                    'failed' => 'danger',
                    'expired', 'canceled' => 'secondary',
                    'open' => 'info',
                    'pending' => 'warning',
                    'authorized', 'refunded' => 'primary',
                    default => '',
                };
            }
        }

        if (!empty($data['tabs']) && is_array($data['tabs'])) {
            foreach ($data['tabs'] as $k => $tab) {
                if (isset($tab['code']) && str_contains((string)$tab['code'], 'mollie')) {
                    unset($data['tabs'][$k]);
                }
            }
        }
    }

    public function orderInfoTemplate(string &$route, array &$data, mixed &$template_code): void {
        $template_buffer = $this->getTemplateBuffer($route, $template_code);

        if (empty($template_buffer)) {
            return;
        }

        $search_tab = '/(<li class="nav-item">\s*<a href="#tab-additional"[^>]*>)/';
        $mollie_tab = '{% if payment_status %}
        <li class="nav-item"><a href="#tab-mollie" data-bs-toggle="tab" class="nav-link">{{ tab_mollie }}</a></li>
        {% endif %}';

        $template_buffer = preg_replace($search_tab, $mollie_tab . PHP_EOL . '$1', $template_buffer, 1);

        $search_content = '/(<div id="tab-additional"[^>]*>)/';
        $file_payment = DIR_EXTENSION . 'mollie/admin/view/template/payment/mollie_order_info_payment.twig';

        if (is_file($file_payment)) {
            $content = file_get_contents($file_payment);
            $template_buffer = preg_replace($search_content, '{% if payment_status %}' . $content . '{% endif %}' . PHP_EOL . '$1', $template_buffer, 1);
        }

        $search_history = '/(<label[^>]*for="input-history"[^>]*>)/';
        $file_link = DIR_EXTENSION . 'mollie/admin/view/template/payment/mollie_order_info_payment_link.twig';

        if (is_file($file_link)) {
            $content_link = file_get_contents($file_link);
            $template_buffer = preg_replace($search_history, $content_link . PHP_EOL . '$1', $template_buffer, 1);
        }

        $search_badge = '/(<span[^>]*id="payment-method-value"[^>]*>.*?<\/span>)/is';
        $badge_html = '{% if payment_status %}&nbsp;&nbsp;<span id="payment-status" class="badge bg-{{ payment_status_class }}">{{ payment_status | upper }}</span>{% if capture_payment %}&nbsp;&nbsp;<i class="fa-solid fa-triangle-exclamation text-warning" data-bs-toggle="tooltip" title="{{ text_capture_payment }}"></i>{% endif %}{% endif %}';

        $template_buffer = preg_replace($search_badge, '$1 ' . $badge_html, $template_buffer, 1);

        $search_badge = '<div id="output-payment-method">{{ payment_method_name }}</div>';
        $badge_html = '<div id="output-payment-method">{{ payment_method_name }}{% if payment_status %}&nbsp;&nbsp;<span id="payment-status" class="badge bg-{{ payment_status_class }}">{{ payment_status | upper }}</span>{% if capture_payment %}&nbsp;&nbsp;<i class="fa-solid fa-triangle-exclamation text-warning" data-bs-toggle="tooltip" title="{{ text_capture_payment }}"></i>{% endif %}{% endif %}</div>';

        $template_buffer = str_replace($search_badge, $badge_html, $template_buffer);

        $modals = '';

        $file_refund_modal = DIR_EXTENSION . 'mollie/admin/view/template/payment/mollie_order_info_refund_model.twig';
        if (is_file($file_refund_modal)) {
            $modals .= '{% if payment_status %}' . file_get_contents($file_refund_modal) . '{% endif %}' . PHP_EOL;
        }

        $file_link_modal = DIR_EXTENSION . 'mollie/admin/view/template/payment/mollie_order_info_payment_link_model.twig';
        if (is_file($file_link_modal)) {
            $modals .= file_get_contents($file_link_modal) . PHP_EOL;
        }

        if ($modals) {
            $template_buffer = str_replace('{{ footer }}', $modals . '{{ footer }}', $template_buffer);
        }

        $template_code = $template_buffer;
    }

    public function addMollieUpgradeToDashboard(string &$route, array &$data, mixed &$template_code): void {
		$this->load->model('setting/extension');

		$data['mollie_update'] = '';

		$extensions = $this->model_setting_extension->getExtensionsByType('payment');

		$mollie_installed = false;

		foreach ($extensions as $extension) {
			if ($extension['code'] == 'mollie_ideal') {
				$mollie_installed = true;
				break;
			}
		}

		if ($mollie_installed) {
			if (file_exists(DIR_EXTENSION . "mollie/system/library/mollie/helper.php")) {
				require_once(DIR_EXTENSION . "mollie/system/library/mollie/helper.php");
			}
			if (file_exists(DIR_EXTENSION . "mollie/system/library/mollie/mollieHttpClient.php")) {
				require_once(DIR_EXTENSION . "mollie/system/library/mollie/mollieHttpClient.php");
			}

			if (class_exists('\Mollie\mollieHttpClient') && class_exists('\MollieHelper')) {
				$client = new \Mollie\mollieHttpClient();
				$info = $client->get("https://api.github.com/repos/mollie/OpenCart/releases/latest");

				if (isset($info["tag_name"])) {
					if (str_contains((string)$info["tag_name"], 'oc4')) {
						$parts = explode("-", $info["tag_name"]);
						if (isset($parts[1])) {
							$tag_name = explode('_', $parts[1]);
						} else {
							$tag_name = [];
						}
					} else {
						$tag_name = ["oc4", $info["tag_name"]];
					}

					$mollieHelper = new \MollieHelper($this->registry);

					if (isset($tag_name[0], $tag_name[1]) && ($tag_name[0] == 'oc4')) {
						$cookie_name = "hide_mollie_update_message_version";
						$cookie_val = $_COOKIE[$cookie_name] ?? '';

						if (($tag_name[1] != $mollieHelper::PLUGIN_VERSION) &&
							version_compare($mollieHelper::PLUGIN_VERSION, $tag_name[1], "<") &&
							($cookie_val != $tag_name[1])) {

							$this->load->language('extension/mollie/payment/mollie');
							$this->load->language('common/dashboard');

							$text_update = $this->language->get('text_update_message');
							$token = 'user_token=' . $this->session->data['user_token'];
							$update_url = $this->url->link("extension/mollie/payment/mollie_ideal.update", $token);

							$data['mollie_update'] = sprintf($text_update, $tag_name[1], $update_url, $tag_name[1]);
						}
					}
				}
			}

            $this->updatePath();
		}
	}

    public function addMollieUpgradeToDashboardTemplate(string &$route, array &$data, mixed &$template_code): void {
		$template_buffer = $this->getTemplateBuffer($route, $template_code);

		if (empty($template_buffer)) {
			return;
		}

		$mollie_alert = '{% if mollie_update %}
		<div class="alert alert-success alert-dismissible"><i class="fa-solid fa-check-circle"></i> {{ mollie_update }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
		{% endif %}';

		$search_pattern = '/{%\s*for\s+row\s+in\s+rows\s*%}/';

		if (preg_match($search_pattern, $template_buffer)) {
			$template_code = preg_replace($search_pattern, $mollie_alert . PHP_EOL . '$0', $template_buffer, 1);
			return;
		}

		$search_fallback = '/<div class="container-fluid">/i';

		if (preg_match($search_fallback, $template_buffer)) {
			$template_code = preg_replace($search_fallback, '$0' . PHP_EOL . $mollie_alert, $template_buffer, 1);
		}
	}

    public function productController(string &$route, array &$data): void {
		$this->load->language('extension/mollie/payment/mollie');
		$this->load->language('catalog/product');

		$this->load->model('extension/mollie/payment/mollie');

		$data['voucher_categories'] = ['meal', 'eco', 'gift'];

		if (!empty($data['product_id'])) {
			$data['voucher_category'] = $this->model_extension_mollie_payment_mollie->getProductVoucherCategory((int)$data['product_id']);
		} else {
			$data['voucher_category'] = '';
		}

		if (!isset($data['master_id'])) {
			$data['master_id'] = 0;
		}
	}

    public function productFormTemplate(string &$route, array &$data, mixed &$template_code): void {
		$template_buffer = $this->getTemplateBuffer($route, $template_code);

		if (empty($template_buffer)) {
			return;
		}

		$search_pattern = '/(<div class="row mb-3">\s*<label[^>]*>\s*{{\s*entry_filter\s*}}\s*<\/label>)/s';

		$file = DIR_EXTENSION . 'mollie/admin/view/template/payment/mollie_product_voucher.twig';

		if (is_file($file)) {
			$mollie_content = file_get_contents($file);
			$mollie_row = '<div class="row mb-3">' . trim($mollie_content) . '</div>';
			$replaced_buffer = preg_replace($search_pattern, $mollie_row . PHP_EOL . '$1', $template_buffer, 1);

			if ($replaced_buffer !== null && $replaced_buffer !== $template_buffer) {
				$template_code = $replaced_buffer;
			} else {
				$fallback_pattern = '/(<div class="row mb-3">\s*<label[^>]*>\s*{{\s*entry_category\s*}}\s*<\/label>)/s';

				$replaced_buffer_fallback = preg_replace($fallback_pattern, $mollie_row . PHP_EOL . '$1', $template_buffer, 1);

				if ($replaced_buffer_fallback !== null && $replaced_buffer_fallback !== $template_buffer) {
					$template_code = $replaced_buffer_fallback;
				} else {
					$this->log->write('Could not find a suitable place for the Voucher field in product_form.');
				}
			}
		}
	}

    public function productModelAddProductAfter(string &$route, array &$args, mixed &$output): void {
		$product_id = (int)$output;
		$data = (array)($args[0] ?? []);

		if ($product_id && isset($data['voucher_category'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET voucher_category = '" . $this->db->escape((string)$data['voucher_category']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}
	}

	public function productModelEditProductAfter(string &$route, array &$args, mixed &$output): void {
		$product_id = (int)($args[0] ?? 0);
		$data = (array)($args[1] ?? []);

		if ($product_id && isset($data['voucher_category'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET voucher_category = '" . $this->db->escape((string)$data['voucher_category']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}
	}

	protected function getTemplateBuffer(string $route, string $event_template_buffer): string {
		if (!empty($event_template_buffer)) {
			return $event_template_buffer;
		}

		$clean_route = $route;
		if (str_contains($route, 'catalog/')) {
			$clean_route = substr($route, strpos($route, 'catalog/'));
		}

		$files = [
			DIR_EXTENSION . 'ocmod/admin/view/template/' . $clean_route . '.twig',
			DIR_APPLICATION . 'view/template/' . $clean_route . '.twig'
		];

		foreach ($files as $file) {
			if (is_file($file)) {
				return file_get_contents($file);
			}
		}

		$this->log->write('Template file not found for route: ' . $clean_route);
		return '';
	}

	public function call(): void {
		$this->load->language('sale/order');

		$json = [];

		$store_id = (int)($this->request->get['store_id'] ?? 0);
		$language = (string)($this->request->get['language'] ?? $this->config->get('config_language'));
		$action = (string)($this->request->get['action'] ?? '');
		$session_id = (string)($this->session->data['api_session'] ?? '');

		if (!$this->user->hasPermission('modify', 'sale/order')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/store');
			$store = $this->model_setting_store->createStoreInstance($store_id, $language, $session_id);

			$store->request->get = $this->request->get;
			$store->request->post = $this->request->post;

			$store->request->get['route'] = $action;

			unset($store->request->get['action']);
			unset($store->request->get['user_token']);

			$store->load->controller($store->request->get['route']);

			$output = $store->response->getOutput();
		} else {
			$output = json_encode($json);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($output);
	}
}
