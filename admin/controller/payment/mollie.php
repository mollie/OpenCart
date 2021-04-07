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
if(version_compare(VERSION, '2.0', '<')) {
	if (!class_exists('VQMod')) {
	     die('<div class="alert alert-warning"><i class="fa fa-exclamation-circle"></i> This extension requires VQMod. Please download and install it on your shop. You can find the latest release <a href="https://github.com/vqmod/vqmod/releases" target="_blank">here</a>!    <button type="button" class="close" data-dismiss="alert">Ã—</button></div>');
	}
}

if (class_exists('VQMod')) {     
	$vqversion = VQMod::$_vqversion;
}
define("VQ_VERSION", $vqversion);

// if (is_file(DIR_SYSTEM.'../vqmod/xml/mollie.xml') && is_file(DIR_SYSTEM.'../system/mollie.ocmod.xml')) {
//   die('<div class="alert alert-warning"><i class="fa fa-exclamation-circle"></i> Warning : Both VQMOD and OCMOD version are installed<br/>- delete /vqmod/xml/mollie.xml if you want to use OCMOD version<br/>- or delete /system/mollie.ocmod.xml if you want to use VQMOD version. OCMOD is recommended for opencart versions 2.x and later. <button type="button" class="close" data-dismiss="alert">Ã—</button></div>');
// }

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Exceptions\IncompatiblePlatform;
use Mollie\Api\MollieApiClient;

require_once(dirname(DIR_SYSTEM) . "/catalog/controller/payment/mollie-api-client/helper.php");

define("MOLLIE_VERSION", MollieHelper::PLUGIN_VERSION);
define("MOLLIE_RELEASE", "v" . MOLLIE_VERSION);
define("MOLLIE_VERSION_URL", "https://api.github.com/repos/mollie/OpenCart/releases/latest");
// Defining arrays in a constant cannot be done with "define" until PHP 7, so using this syntax for backwards compatibility.
const DEPRECATED_METHODS = array('mistercash', 'bitcoin');

if (!defined("MOLLIE_TMP")) {
    define("MOLLIE_TMP", sys_get_temp_dir());
}

class ControllerPaymentMollie extends Controller {
	const OUTH_URL = 'https://api.mollie.com/oauth2';

	// Initialize var(s)
	protected $error = array();

	// Holds multistore configs
	protected $data = array();
	private $token;
	public $mollieHelper;

	public function __construct($registry) {
		parent::__construct($registry);
    
    	$this->token = isset($this->session->data['user_token']) ? 'user_token='.$this->session->data['user_token'] : 'token='.$this->session->data['token'];
    	$this->mollieHelper = new MollieHelper($registry);
	}

	/**
	 * @param int $store The Store ID
	 * @return MollieApiClient
	 */
	protected function getAPIClient ($store = 0) {
		$data = $this->data;
		$data[$this->mollieHelper->getModuleCode() . "_api_key"] = $this->mollieHelper->getApiKey($store);
		
		return $this->mollieHelper->getAPIClientAdmin($data);
	}

	public function mollieConnect() {

		$this->session->data['mollie_connect_store_id'] = $this->request->get['store_id'];
		if(version_compare(VERSION, '2.3', '>=')) {
			$redirect_uri = HTTPS_SERVER . 'index.php?route=extension/payment/mollie/mollieConnectCallback';
		} else {
			$redirect_uri = HTTPS_SERVER . 'index.php?route=payment/mollie/mollieConnectCallback';
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

        $this->response->redirect("https://www.mollie.com/oauth2/authorize?".$queryString);
	}

	public function mollieConnectCallback() {
		if (version_compare(VERSION, '2.3', '>=')) {
	      $this->load->language('extension/payment/mollie');
	    } else {
	      $this->load->language('payment/mollie');
	    }
		
		$this->load->model('setting/setting');
		$code = $this->mollieHelper->getModuleCode();

		if(isset($this->request->get['error'])) {
			$this->session->data['warning'] = $this->request->get['error_description'];
			if (version_compare(VERSION, '2.3', '>=')) {
				$this->response->redirect($this->url->link('extension/payment/mollie', $this->token, true));
			} elseif (version_compare(VERSION, '2', '>=')) {
				$this->response->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
			} else {
				$this->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
			}
		}

		$token = isset($this->session->data['user_token']) ? $this->session->data['user_token'] : $this->session->data['token'];

		if(!isset($this->request->get['state']) || ($this->request->get['state'] != $token)) {
			return new Action('common/login');
		}

		if(version_compare(VERSION, '2.3', '>=')) {
			$redirect_uri = HTTPS_SERVER . 'index.php?route=extension/payment/mollie/mollieConnectCallback';
		} else {
			$redirect_uri = HTTPS_SERVER . 'index.php?route=payment/mollie/mollieConnectCallback';
		}

		$settingData = $this->model_setting_setting->getSetting($code, $this->session->data['mollie_connect_store_id']);

		$data = array(
			'client_id' => $settingData[$code . '_client_id'],
            'client_secret' => $settingData[$code . '_client_secret'],
			'grant_type' => 'authorization_code',
			'code' => $this->request->get['code'],
			'redirect_uri'		=> $redirect_uri
		);

		$result = $this->mollieHelper->curlRequest('tokens', $data);

		// Save refresh token
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `key` = '" . $code . "_refresh_token' AND `store_id` = '" . $this->session->data['mollie_connect_store_id'] . "'");

		if(version_compare(VERSION, '2.0', '<')) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . $this->session->data['mollie_connect_store_id'] . "', `group` = '" . $code . "', `key` = '" . $code . "_refresh_token', `value` = '" . $result->refresh_token . "'");
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . $this->session->data['mollie_connect_store_id'] . "', `code` = '" . $code . "', `key` = '" . $code . "_refresh_token', `value` = '" . $result->refresh_token . "'");
		}

		$this->session->data['mollie_access_token'][$this->session->data['mollie_connect_store_id']] = $result->access_token;
		unset($this->session->data['mollie_connect_store_id']);

		$this->session->data['success'] = $this->language->get('text_connection_success');
		
		if (version_compare(VERSION, '2.3', '>=')) {
			$this->response->redirect($this->url->link('extension/payment/mollie', $this->token, true));
		} elseif (version_compare(VERSION, '2', '>=')) {
			$this->response->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
		} else {
			$this->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
		}

	}

	public function enablePaymentMethod() {
		if (version_compare(VERSION, '2.3', '>=')) {
	      $this->load->language('extension/payment/mollie');
	    } else {
	      $this->load->language('payment/mollie');
	    }
		$code = $this->mollieHelper->getModuleCode();

		$method  = $this->request->get['method'];
		$api_key = $this->mollieHelper->getSettingValue($code . '_api_key', $this->request->get['store_id']);
		try
			{
				$api = $this->mollieHelper->getAPIClientForKey($api_key);

				$profile = $api->profiles->getCurrent();

				$mollie = $this->mollieHelper->getAPIClientForAccessToken($this->session->data['mollie_access_token'][$this->request->get['store_id']]);
				$profile = $mollie->profiles->get($profile->id);
				$profile->enableMethod($method);

				$this->session->data['success'] = $this->language->get('text_success');
				if (version_compare(VERSION, '2.3', '>=')) {
					$this->response->redirect($this->url->link('extension/payment/mollie', $this->token, true));
				} elseif (version_compare(VERSION, '2', '>=')) {
					$this->response->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
				} else {
					$this->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
				}

			}
			catch (Mollie\Api\Exceptions\ApiException $e)
			{
				$this->session->data['warning'] = $this->language->get('text_error');
				if (version_compare(VERSION, '2.3', '>=')) {
					$this->response->redirect($this->url->link('extension/payment/mollie', $this->token, true));
				} elseif (version_compare(VERSION, '2', '>=')) {
					$this->response->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
				} else {
					$this->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
				}
			}
	}

	public function disablePaymentMethod() {
		if (version_compare(VERSION, '2.3', '>=')) {
	      $this->load->language('extension/payment/mollie');
	    } else {
	      $this->load->language('payment/mollie');
	    }
		$code = $this->mollieHelper->getModuleCode();

		$method  = $this->request->get['method'];
		$api_key = $this->mollieHelper->getSettingValue($code . '_api_key', $this->request->get['store_id']);
		try
			{
				$api = $this->mollieHelper->getAPIClientForKey($api_key);

				$profile = $api->profiles->getCurrent();

				$mollie = $this->mollieHelper->getAPIClientForAccessToken($this->session->data['mollie_access_token'][$this->request->get['store_id']]);
				$profile = $mollie->profiles->get($profile->id);
				$profile->disableMethod($method);

				$this->session->data['success'] = $this->language->get('text_success');
				if (version_compare(VERSION, '2.3', '>=')) {
					$this->response->redirect($this->url->link('extension/payment/mollie', $this->token, true));
				} elseif (version_compare(VERSION, '2', '>=')) {
					$this->response->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
				} else {
					$this->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
				}

			}
			catch (Mollie\Api\Exceptions\ApiException $e)
			{
				$this->session->data['warning'] = $this->language->get('text_error');
				if (version_compare(VERSION, '2.3', '>=')) {
					$this->response->redirect($this->url->link('extension/payment/mollie', $this->token, true));
				} elseif (version_compare(VERSION, '2', '>=')) {
					$this->response->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
				} else {
					$this->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
				}
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

		//Add event to create shipment
		if (version_compare(VERSION, '2.2', '>=')) { // Events were added in OC2.2
			if ($this->mollieHelper->isOpenCart3x()) {
				$this->load->model('setting/event');
				$this->model_setting_event->deleteEventByCode('mollie_create_shipment');
				$this->model_setting_event->addEvent('mollie_create_shipment', 'catalog/model/checkout/order/addOrderHistory/after', 'payment/mollie/createShipment');

				$this->model_setting_event->deleteEventByCode('mollie_payment_controller');
				$this->model_setting_event->addEvent('mollie_payment_controller', 'catalog/controller/extension/payment/mollie_*/before', 'extension/payment/mollie/onMolliePaymentController');
			} else {
				$this->load->model('extension/event');
				$this->model_extension_event->deleteEvent('mollie_create_shipment');
				$this->model_extension_event->addEvent('mollie_create_shipment', 'catalog/model/checkout/order/addOrderHistory/after', 'payment/mollie/createShipment');

				if (version_compare(VERSION, '2.3.0.2', '==')) {
					$this->model_extension_event->deleteEvent('mollie_payment_controller');
					$this->model_extension_event->addEvent('mollie_payment_controller', 'catalog/controller/extension/payment/mollie_*/before', 'extension/payment/mollie/onMolliePaymentController');
				} else {
					$this->model_extension_event->deleteEvent('mollie_payment_controller');
					$this->model_extension_event->addEvent('mollie_payment_controller', 'catalog/controller/payment/mollie_*/before', 'payment/mollie/onMolliePaymentController');
				}				
			}
		}

		// Create mollie payments table
		$this->db->query(
			sprintf(
				"CREATE TABLE IF NOT EXISTS `%smollie_payments` (
					`order_id` INT(11) NOT NULL,
					`method` VARCHAR(32) NOT NULL,
					`mollie_order_id` VARCHAR(32) NOT NULL,
					`transaction_id` VARCHAR(32),
					`bank_account` VARCHAR(15),
					`bank_status` VARCHAR(20),
					`refund_id` VARCHAR(32),
					`subscription_id` VARCHAR(32),
					`order_recurring_id` INT(11),
					`next_payment` DATETIME,
					`subscription_end` DATETIME,
					`date_modified` DATETIME NOT NULL,
					`payment_attempt` INT(11) NOT NULL,
					PRIMARY KEY (`mollie_order_id`),
					UNIQUE KEY `mollie_order_id` (`mollie_order_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8",
				DB_PREFIX
			)
		);

		$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` MODIFY `payment_method` VARCHAR(255) NOT NULL;");

		//Check if subscription fields exist
		if(!$this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "mollie_payments` LIKE 'subscription_id'")->row)
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "mollie_payments` ADD `subscription_id` VARCHAR(32), ADD `order_recurring_id` INT(11), ADD `next_payment` DATETIME, ADD `subscription_end` DATETIME");

		//Check if mollie_order_id field exists
		if(!$this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "mollie_payments` LIKE 'mollie_order_id'")->row)
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "mollie_payments` ADD `mollie_order_id` VARCHAR(32) UNIQUE");

		//Check if refund_id field exists
		if(!$this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "mollie_payments` LIKE 'refund_id'")->row)
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "mollie_payments` ADD `refund_id` VARCHAR(32)");

		//Check if date_modified field exists
		if(!$this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "mollie_payments` LIKE 'date_modified'")->row)
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "mollie_payments` ADD `date_modified` DATETIME NOT NULL");

		//Check if payment_attempt field exists
		if(!$this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "mollie_payments` LIKE 'payment_attempt'")->row)
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "mollie_payments` ADD `payment_attempt` INT(11) NOT NULL");

		// Update primary key
		$query = $this->db->query("SHOW INDEX FROM `" .DB_PREFIX. "mollie_payments` where Key_name = 'PRIMARY'");
		if($query->num_rows > 0 && $query->row['Column_name'] != 'mollie_order_id') {
			$this->db->query("DELETE FROM `" .DB_PREFIX. "mollie_payments` where mollie_order_id IS NULL OR mollie_order_id = ''");
			$this->db->query("ALTER TABLE `" .DB_PREFIX. "mollie_payments` DROP PRIMARY KEY, ADD PRIMARY KEY (mollie_order_id)");
		}

		// Create mollie customers table
		$this->db->query(
			sprintf(
				"CREATE TABLE IF NOT EXISTS `%smollie_customers` (
					`id` INT(11) NOT NULL AUTO_INCREMENT,
					`mollie_customer_id` VARCHAR(32) NOT NULL,
					`customer_id` INT(11) NOT NULL,
					`email` VARCHAR(96) NOT NULL,
					`date_created` DATETIME NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8",
				DB_PREFIX
			)
		);

		// Create mollie recurring payments table
		$this->db->query(
			sprintf(
				"CREATE TABLE IF NOT EXISTS `%smollie_recurring_payments` (
					`id` INT(11) NOT NULL AUTO_INCREMENT,
					`transaction_id` VARCHAR(32),
					`order_recurring_id` INT(11),
					`subscription_id` VARCHAR(32) NOT NULL,
					`mollie_customer_id` VARCHAR(32) NOT NULL,
					`method` VARCHAR(32) NOT NULL,
					`status` VARCHAR(32) NOT NULL,
					`date_created` DATETIME NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8",
				DB_PREFIX
			)
		);

		// Set permissions
		$user_id = $this->getUserId();
		$this->model_user_user_group->addPermission($user_id, "access", "payment/mollie");
		$this->model_user_user_group->addPermission($user_id, "access", "extension/payment/mollie");
		$this->model_user_user_group->addPermission($user_id, "modify", "payment/mollie");
		$this->model_user_user_group->addPermission($user_id, "modify", "extension/payment/mollie");

		// Manage OCMod and VQMod files
		if (version_compare(VERSION, '2.0', '>=')) {
			if (class_exists('VQMod') && is_file(DIR_SYSTEM.'../vqmod/xml/mollie.xml')) {
				if (is_file(DIR_SYSTEM.'../system/mollie.ocmod.xml')) {
					rename(DIR_SYSTEM.'../system/mollie.ocmod.xml', DIR_SYSTEM.'../system/mollie.ocmod.xml_');
				}
			} else {
				if (is_file(DIR_SYSTEM.'../vqmod/xml/mollie.xml')) {
					rename(DIR_SYSTEM.'../vqmod/xml/mollie.xml', DIR_SYSTEM.'../vqmod/xml/mollie.xml_');
				}
			}
		} else {
			if (is_file(DIR_SYSTEM.'../system/mollie.ocmod.xml')) {
				rename(DIR_SYSTEM.'../system/mollie.ocmod.xml', DIR_SYSTEM.'../system/mollie.ocmod.xml_');
			}
		}
	}
	
	/**
	 * Clean up files that are not needed for the running version of OC.
	 */
	public function cleanUp() {
		$adminThemeDir = DIR_APPLICATION . 'view/template/';
		$catalogThemeDir = DIR_CATALOG . 'view/theme/default/template/';

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

		foreach ($this->mollieHelper->MODULE_NAMES as $method) {
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

		if ($this->mollieHelper->isOpenCart3x()) {
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
			
		} elseif ($this->mollieHelper->isOpenCart2x()) {
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

		// Remove patch
		if (is_dir(DIR_APPLICATION . 'patch')) {
			$this->delTree(DIR_APPLICATION . 'patch');
		}
		if (is_dir($adminControllerDir . 'payment/mollie')) {
			$this->delTree($adminControllerDir . 'payment/mollie');
		}
		if (is_dir($catalogControllerDir . 'payment/mollie')) {
			$this->delTree($catalogControllerDir . 'payment/mollie');
		}
		if (is_dir($catalogModelDir . 'payment/mollie')) {
			$this->delTree($catalogModelDir . 'payment/mollie');
		}
	}

	public function delTree($dir) {
		$files = array_diff(scandir($dir), array('.','..'));
	    foreach ($files as $file) {
	      (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
	    }
	    return rmdir($dir);
	}

	private function getStores() {
		// multi-stores management
		$this->load->model('setting/store');
		$stores = array();
		$stores[] = array(
			'store_id' => 0,
			'name'     => $this->config->get('config_name')
		);

		$_stores = $this->model_setting_store->getStores();

		foreach ($_stores as $store) {
			$stores[] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		return $stores;
	}

	/**
	 * Insert variables that are added in later versions.
	*/
	public function updateSettings() {
		$code = $this->mollieHelper->getModuleCode();
        $stores = $this->getStores();
        $vars = array(
        	'default_currency' => 'DEF' // variable => default value
        );
        foreach($stores as $store) {
        	$storeData = array();
        	$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '".$store['store_id']."'");
			
			foreach ($query->rows as $setting) {
				if (!$setting['serialized']) {
					$storeData[$setting["key"]] = $setting['value'];
				} else if (version_compare(VERSION, '2.1', '>=')) {
					$storeData[$setting["key"]] = json_decode($setting['value'], true);
				} else {
					$storeData[$setting["key"]] = unserialize($setting['value']);
				}
			}
        	foreach($vars as $key=>$value) {
        		if (!isset($storeData[$code . '_' . $key])) {
					if (version_compare(VERSION, '2', '>=')) {
			            $code = 'code';
			        } else {
			            $code = 'group';
			        }

			        if (!is_array($value)) {
			            $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store['store_id'] . "', `" . $code . "` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($code . '_' . $key) . "', `value` = '" . $this->db->escape($value) . "'");
			        } else {
			            $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store['store_id'] . "', `" . $code . "` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($code . '_' . $key) . "', `value` = '" . $this->db->escape(json_encode($value, true)) . "', serialized = '1'");
			        }
				}
        	}
        }
	}

	/**
	 * Trigger installation of all Mollie modules.
	 */
	protected function installAllModules () {
		// First uninstall all modules to make sure "mollie" is on the top of the modules list and other modules "mollie_*" are after it.
		$this->uninstallAllModules();

		// Load models.
		if(version_compare(VERSION, '3.0', '>=') || version_compare(VERSION, '2.0', '<')) {
			$this->load->model('setting/extension');
			$model = 'model_setting_extension';
		} else {
			$this->load->model('extension/extension');
			$model = 'model_extension_extension';
		}
		
		$user_id = $this->getUserId();

		foreach ($this->mollieHelper->MODULE_NAMES as $module_name) {
			// Install extension.
			$this->{$model}->install("payment", "mollie_" . $module_name);
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
		if(version_compare(VERSION, '3.0', '>=') || version_compare(VERSION, '2.0', '<')) {
			$this->load->model('setting/extension');
			$model = 'model_setting_extension';
		} else {
			$this->load->model('extension/extension');
			$model = 'model_extension_extension';
		}

		foreach ($this->mollieHelper->MODULE_NAMES as $module_name) {
			$this->{$model}->uninstall("payment", "mollie_" . $module_name);
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
	public function index () {
		// We need to remove the new mollie module added in v10
		if(version_compare(VERSION, '3.0', '>=') || version_compare(VERSION, '2.0', '<')) {
			$this->load->model('setting/extension');
			$model = 'model_setting_extension';
		} else {
			$this->load->model('extension/extension');
			$model = 'model_extension_extension';
		}

		// Preserve old settings
		$this->load->model('setting/setting');
		$oldSettings = array();
		foreach($this->getStores() as $store) {
			$oldSettings[$store['store_id']] = $this->model_setting_setting->getSetting($this->mollieHelper->getModuleCode(), $store['store_id']);
		}
		$this->session->data['mollie_settings'] = $oldSettings;

		$extensions = $this->{$model}->getInstalled('payment');
		if (in_array('mollie', $extensions)) {
			$this->{$model}->uninstall("payment", "mollie");
		}

		$adminControllerDir   = DIR_APPLICATION . 'controller/';
		if (file_exists($adminControllerDir . 'extension/payment/mollie.php')) {
			unlink($adminControllerDir . 'extension/payment/mollie.php');
		}
		if (file_exists($adminControllerDir . 'payment/mollie.php')) {
			unlink($adminControllerDir . 'payment/mollie.php');
		}

		// Install new modules (originally old modules)
		$user_id = $this->getUserId();
		foreach ($this->mollieHelper->MODULE_NAMES as $module_name) {
			// Install extension.
			$this->{$model}->install("payment", "mollie_" . $module_name);

			// Set permissions.
			$this->model_user_user_group->addPermission($user_id, "access", "payment/mollie_" . $module_name);
			$this->model_user_user_group->addPermission($user_id, "access", "extension/payment/mollie_" . $module_name);
			$this->model_user_user_group->addPermission($user_id, "modify", "payment/mollie_" . $module_name);
			$this->model_user_user_group->addPermission($user_id, "modify", "extension/payment/mollie_" . $module_name);
		}

        if (version_compare(VERSION, '2.3', '>=')) {
			$this->response->redirect($this->url->link('extension/payment/mollie_applepay', $this->token, true));
		} elseif (version_compare(VERSION, '2', '>=')) {
			$this->response->redirect($this->url->link('payment/mollie_applepay', $this->token, 'SSL'));
		} else {
			$this->redirect($this->url->link('payment/mollie_applepay', $this->token, 'SSL'));
		}


		// Double check for database and permissions
		$this->install();
		// Load essential models
		$this->load->model("localisation/order_status");
		$this->load->model("localisation/geo_zone");
		$this->load->model("localisation/language");
		$this->load->model("localisation/currency");
		$this->load->model('setting/setting');
		// Double-check if clean-up has been done - For upgrades
		if (null === $this->config->get($this->mollieHelper->getModuleCode() . '_version')) {
			if(version_compare(VERSION, '1.5.6.4', '<=')) {
	            $code = 'group';
	        } else {
	            $code = 'code';
	        }
			$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `" . $code . "` = '" . $this->db->escape($this->mollieHelper->getModuleCode()) . "', `key` = '" . $this->db->escape($this->mollieHelper->getModuleCode() . '_version') . "', `value` = '" . $this->db->escape(MOLLIE_VERSION) . "'");
		} elseif (version_compare($this->config->get($this->mollieHelper->getModuleCode() . '_version'), MOLLIE_VERSION, '<')) {
			$this->model_setting_setting->editSettingValue($this->mollieHelper->getModuleCode(), $this->mollieHelper->getModuleCode() . '_version', MOLLIE_VERSION);
		}

		//Also delete data related to deprecated modules from settings
		$this->clearData();

		// Update settings with newly added variables
		$this->updateSettings();

		//Load language data
		$data = array("version" => MOLLIE_RELEASE);

		if (version_compare(VERSION, '2.3', '>=')) {
	      $this->load->language('extension/payment/mollie');
	    } else {
	      $this->load->language('payment/mollie');
	    }
		$this->data = $data;		
		$this->load->library("mollieHttpClient");
		$code = $this->mollieHelper->getModuleCode();

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$code = $this->mollieHelper->getModuleCode();
			$redirect = true;
            $stores = $this->getStores();
            foreach ($stores as $store) {
            	if(count($stores) > 1) {
	                $this->model_setting_setting->editSetting($this->mollieHelper->getModuleCode(), $this->removePrefix($this->request->post, $store["store_id"] . "_"), $store["store_id"]);
            	} else {
            		if ($this->validate($store["store_id"])) {
		                $this->model_setting_setting->editSetting($this->mollieHelper->getModuleCode(), $this->removePrefix($this->request->post, $store["store_id"] . "_"), $store["store_id"]);
	            	}
	            	else {
	            		$redirect = false;
	            	}
            	}

            	// Remove refresh token if app credentials are changed
            	$removeToken = false;
            	$settingData = $this->removePrefix($this->request->post, $store["store_id"] . "_");
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
            	$this->session->data['success'] = $this->language->get('text_success');
            	if (version_compare(VERSION, '3', '>=')) {
					$this->response->redirect($this->url->link('marketplace/extension', 'type=payment&' . $this->token, 'SSL'));
				} elseif (version_compare(VERSION, '2.3', '>=')) {
					$this->response->redirect($this->url->link('extension/extension', 'type=payment&' . $this->token, 'SSL'));
				} else {
					$this->redirect($this->url->link('extension/payment', $this->token, 'SSL'));
				}
            }
        }

        $this->document->setTitle(strip_tags($this->language->get('heading_title')));
        //Set form variables
        $paymentStatus = array();
        $paymentSortOrder = array();
        $paymentGeoZone = array();

        foreach ($this->mollieHelper->MODULE_NAMES as $module_name) {
        	$paymentStatus[] 	= $code . '_' . $module_name . '_title';
        	$paymentStatus[] 	= $code . '_' . $module_name . '_image';
        	$paymentStatus[] 	= $code . '_' . $module_name . '_status';
        	$paymentSortOrder[] = $code . '_' . $module_name . '_sort_order';
        	$paymentGeoZone[] 	= $code . '_' . $module_name . '_geo_zone';
		}

        $fields = array("show_icons", "show_order_canceled_page", "ideal_description", "api_key", "client_id", "client_secret", "refresh_token", "ideal_processing_status_id", "ideal_expired_status_id", "ideal_canceled_status_id", "ideal_failed_status_id", "ideal_pending_status_id", "ideal_shipping_status_id", "create_shipment_status_id", "ideal_refund_status_id", "create_shipment", "payment_screen_language", "debug_mode", "mollie_component", "mollie_component_css_base", "mollie_component_css_valid", "mollie_component_css_invalid", "default_currency", "recurring_email", "status");

        $settingFields = $this->addPrefix($code . '_', $fields);

        $storeFormFields = array_merge($settingFields, $paymentStatus, $paymentSortOrder, $paymentGeoZone);

        $data['stores'] = $this->getStores();

        // Generate access token
        foreach ($data['stores'] as &$store) {
            $accessToken = $this->mollieHelper->generateAccessToken($store["store_id"]);
            $this->session->data['mollie_access_token'][$store["store_id"]] = $accessToken;
        }

        //API key not required for multistores
        $data['api_required'] = true;
        
        if(count($data['stores']) > 1) {
        	$data['api_required'] = false;
        }

        $data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
        'text'      => $this->language->get('text_home'),
        'href'      => $this->url->link('common/home', $this->token, 'SSL'),
      	'separator' => false
   		);

      if (version_compare(VERSION, '3', '>=')) {
        $extension_link = $this->url->link('marketplace/extension', 'type=payment&' . $this->token, 'SSL');
      } elseif (version_compare(VERSION, '2.3', '>=')) {
        $extension_link = $this->url->link('extension/extension', 'type=payment&' . $this->token, 'SSL');
		} else {
			$extension_link = $this->url->link('extension/payment', $this->token, 'SSL');
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_payment'] = $this->language->get('text_payment');
		$data['text_enable_payment_method'] = $this->language->get('text_enable_payment_method');
		$data['text_activate_payment_method'] = $this->language->get('text_activate_payment_method');
		$data['text_no_status_id'] = $this->language->get('text_no_status_id');
		$data['text_creditcard_required'] = $this->language->get('text_creditcard_required');
		$data['text_mollie_api'] = $this->language->get('text_mollie_api');
		$data['text_mollie_app'] = $this->language->get('text_mollie_app');
		$data['text_general'] = $this->language->get('text_general');
		$data['text_enquiry'] = $this->language->get('text_enquiry');
		$data['text_update_message'] = $this->language->get('text_update_message');
		$data['text_default_currency'] = $this->language->get('text_default_currency');
		$data['text_custom_css'] = $this->language->get('text_custom_css');
		$data['text_contact_us'] = $this->language->get('text_contact_us');
		$data['text_bg_color'] = $this->language->get('text_bg_color');
		$data['text_color'] = $this->language->get('text_color');
		$data['text_font_size'] = $this->language->get('text_font_size');
		$data['text_other_css'] = $this->language->get('text_other_css');
		$data['text_module_by'] = $this->language->get('text_module_by');
		$data['text_mollie_support'] = $this->language->get('text_mollie_support');
		$data['text_contact'] = $this->language->get('text_contact');
		$data['text_allowed_variables'] = $this->language->get('text_allowed_variables');
		$data['text_browse'] = $this->language->get('text_browse');
		$data['text_clear'] = $this->language->get('text_clear');
		$data['text_image_manager'] = $this->language->get('text_image_manager');
		$data['text_create_shipment_automatically'] = $this->language->get('text_create_shipment_automatically');
		$data['text_create_shipment_on_status'] = $this->language->get('text_create_shipment_on_status');
		$data['text_create_shipment_on_order_complete'] = $this->language->get('text_create_shipment_on_order_complete');
		$data['text_log_success'] = $this->language->get('text_log_success');
		$data['text_log_list'] = $this->language->get('text_log_list');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_missing_api_key'] = $this->language->get('text_missing_api_key');

		$data['title_global_options'] = $this->language->get('title_global_options');
		$data['title_payment_status'] = $this->language->get('title_payment_status');
		$data['title_mod_about'] = $this->language->get('title_mod_about');
		$data['footer_text'] = $this->language->get('footer_text');
		$data['title_mail'] = $this->language->get('title_mail');

		$data['name_mollie_banktransfer'] = $this->language->get('name_mollie_banktransfer');
		$data['name_mollie_belfius'] = $this->language->get('name_mollie_belfius');
		$data['name_mollie_creditcard'] = $this->language->get('name_mollie_creditcard');
		$data['name_mollie_ideal'] = $this->language->get('name_mollie_ideal');
		$data['name_mollie_kbc'] = $this->language->get('name_mollie_kbc');
		$data['name_mollie_bancontact'] = $this->language->get('name_mollie_bancontact');
		$data['name_mollie_paypal'] = $this->language->get('name_mollie_paypal');
		$data['name_mollie_paysafecard'] = $this->language->get('name_mollie_paysafecard');
		$data['name_mollie_sofort'] = $this->language->get('name_mollie_sofort');
		$data['name_mollie_giftcard'] = $this->language->get('name_mollie_giftcard');
		$data['name_mollie_eps'] = $this->language->get('name_mollie_eps');
		$data['name_mollie_giropay'] = $this->language->get('name_mollie_giropay');
		$data['name_mollie_klarnapaylater'] = $this->language->get('name_mollie_klarnapaylater');
		$data['name_mollie_klarnasliceit'] = $this->language->get('name_mollie_klarnasliceit');
		$data['name_mollie_przelewy24'] = $this->language->get('name_mollie_przelewy24');
		$data['name_mollie_applepay'] = $this->language->get('name_mollie_applepay');
		// Deprecated names
		$data['name_mollie_bitcoin'] = $this->language->get('name_mollie_bitcoin');
		$data['name_mollie_mistercash'] = $this->language->get('name_mollie_mistercash');

		$data['entry_payment_method'] = $this->language->get('entry_payment_method');
		$data['entry_activate'] = $this->language->get('entry_activate');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_api_key'] = $this->language->get('entry_api_key');
		$data['entry_description'] = $this->language->get('entry_description');
		$data['entry_show_icons'] = $this->language->get('entry_show_icons');
		$data['entry_show_order_canceled_page'] = $this->language->get('entry_show_order_canceled_page');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_client_id'] = $this->language->get('entry_client_id');
		$data['entry_client_secret'] = $this->language->get('entry_client_secret');
		$data['entry_redirect_uri'] = $this->language->get('entry_redirect_uri');
		$data['entry_payment_screen_language'] = $this->language->get('entry_payment_screen_language');
		$data['entry_mollie_connect'] = $this->language->get('entry_mollie_connect');
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_email'] = $this->language->get('entry_email');
		$data['entry_subject'] = $this->language->get('entry_subject');
		$data['entry_enquiry'] = $this->language->get('entry_enquiry');
		$data['entry_debug_mode'] = $this->language->get('entry_debug_mode');
		$data['entry_mollie_component'] = $this->language->get('entry_mollie_component');
		$data['entry_test_mode'] = $this->language->get('entry_test_mode');
		$data['entry_mollie_component_base'] = $this->language->get('entry_mollie_component_base');
		$data['entry_mollie_component_valid'] = $this->language->get('entry_mollie_component_valid');
		$data['entry_mollie_component_invalid'] = $this->language->get('entry_mollie_component_invalid');
		$data['entry_default_currency'] = $this->language->get('entry_default_currency');
		$data['entry_email_subject'] = $this->language->get('entry_email_subject');
		$data['entry_email_body'] = $this->language->get('entry_email_body');
		$data['entry_title'] = $this->language->get('entry_title');
		$data['entry_image'] = $this->language->get('entry_image');
		$data['entry_module'] = $this->language->get('entry_module');
		$data['entry_mod_status'] = $this->language->get('entry_mod_status');
		$data['entry_comm_status'] = $this->language->get('entry_comm_status');
		$data['entry_support'] = $this->language->get('entry_support');
		$data['entry_pending_status'] = $this->language->get('entry_pending_status');
		$data['entry_failed_status'] = $this->language->get('entry_failed_status');
		$data['entry_canceled_status'] = $this->language->get('entry_canceled_status');
		$data['entry_expired_status'] = $this->language->get('entry_expired_status');
		$data['entry_processing_status'] = $this->language->get('entry_processing_status');
		$data['entry_refund_status'] = $this->language->get('entry_refund_status');
		$data['entry_shipping_status'] = $this->language->get('entry_shipping_status');
		$data['entry_shipment'] = $this->language->get('entry_shipment');
		$data['entry_create_shipment_status'] = $this->language->get('entry_create_shipment_status');
		$data['entry_create_shipment_on_order_complete'] = $this->language->get('entry_create_shipment_on_order_complete');
		$data['entry_status'] = $this->language->get('entry_status');
		
		$data['help_view_profile'] = $this->language->get('help_view_profile');
		$data['help_status'] = $this->language->get('help_status');
		$data['help_api_key'] = $this->language->get('help_api_key');
		$data['help_description'] = $this->language->get('help_description');
		$data['help_show_icons'] = $this->language->get('help_show_icons');
		$data['help_show_order_canceled_page'] = $this->language->get('help_show_order_canceled_page');
		$data['help_redirect_uri'] = $this->language->get('help_redirect_uri');
		$data['help_mollie_app'] = $this->language->get('help_mollie_app');
		$data['help_apple_pay'] = $this->language->get('help_apple_pay');
		$data['help_mollie_component'] = $this->language->get('help_mollie_component');
		$data['help_shipment'] = $this->language->get('help_shipment');
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_update'] = $this->language->get('button_update');
		$data['button_mollie_connect'] = $this->language->get('button_mollie_connect');
		$data['button_download'] = $this->language->get('button_download');
		$data['button_clear'] = $this->language->get('button_clear');
		$data['button_submit'] = $this->language->get('button_submit');
      
   		$data['breadcrumbs'][] = array(
       	'text'      => $this->language->get('text_payment'),
        'href'      => $extension_link,
      	'separator' => ' :: '
   		);
		
   		$data['breadcrumbs'][] = array(
       	'text'      => strip_tags($this->language->get('heading_title')),
        'href'      => (version_compare(VERSION, '2.3', '>=')) ? $this->url->link('extension/payment/mollie', $this->token, true) : $this->url->link('payment/mollie', $this->token, 'SSL'),
        'separator' => ' :: '
   		);
		
		$data['action'] = (version_compare(VERSION, '2.3', '>=')) ? $this->url->link('extension/payment/mollie', $this->token, true) : $this->url->link('payment/mollie', $this->token, 'SSL');
		
		$data['cancel'] = $extension_link;

		// Set data for template
        $data['module_name']        = 'mollie';
        $data['api_check_url']      = $this->url->link("payment/mollie/validate_api_key", $this->token, 'SSL');
        $data['entry_version']      = $this->language->get("entry_version") . " " . MOLLIE_VERSION;
        $data['code']               = $code;
		$data['token']          	= $this->token;
		$data['update_url']         = ($this->getUpdateUrl()) ? $this->getUpdateUrl()['updateUrl'] : '';
        $data['text_update']        = ($this->getUpdateUrl()) ? sprintf($this->language->get('text_update_message'), $this->getUpdateUrl()['updateVersion'], $data['update_url']) : '';
		$data['geo_zones']			= $this->model_localisation_geo_zone->getGeoZones();
		$data['order_statuses']		= $this->model_localisation_order_status->getOrderStatuses();
		$data['languages']			= $this->model_localisation_language->getLanguages();
		$data['currencies']			= $this->model_localisation_currency->getCurrencies();

		$this->load->model('tool/image');
		if (is_file(DIR_IMAGE . 'mollie_connect.png')) {
			$data['image'] = $this->model_tool_image->resize('mollie_connect.png', 400, 90);
		} else {
			$data['image'] = '';
		}

		if(version_compare(VERSION, '2.0.2.0', '>=')) {
			$no_image = 'no_image.png';
		} else {
			$no_image = 'no_image.jpg';
		}

		$data['placeholder'] = $this->model_tool_image->resize($no_image, 100, 100);

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

		if(isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		// Load global settings. Some are prefixed with mollie_ideal_ for legacy reasons.
		$settings = array(
			$code . "_status"                    				=> TRUE,
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
			$code . "_recurring_email"  		  				=> array(),
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

		if((null == $this->config->get('config_complete_status')) && ($this->config->get('config_complete_status_id')) == '') {
			$data['is_order_complete_status'] = false;
		}

		foreach($data['stores'] as &$store) {
			$this->data = $this->model_setting_setting->getSetting($code, $store['store_id']);
			foreach ($settings as $setting_name => $default_value) {
				// Attempt to read from post
				if (isset($this->request->post[$store['store_id'] . '_' . $setting_name])) {
					$data['stores'][$store['store_id']][$setting_name] = $this->request->post[$store['store_id'] . '_' . $setting_name];
				} else { // Otherwise, attempt to get the setting from the database
					// same as $this->config->get() 
					$stored_setting = null;
					if(isset($this->data[$setting_name])) {
						$stored_setting = $this->data[$setting_name];						
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
			if(isset($this->data[$this->mollieHelper->getModuleCode() . '_refresh_token']) && !empty($this->data[$this->mollieHelper->getModuleCode() . '_refresh_token'])) {
				$data['stores'][$store['store_id']]['mollie_connection'] = true;
			}

			if(isset($this->session->data['mollie_access_token'][$store['store_id']]) && !empty($this->session->data['mollie_access_token'][$store['store_id']])) {
				$data['stores'][$store['store_id']]['show_mollie_connect_button'] = false;
			}

			if(isset($this->data[$this->mollieHelper->getModuleCode() . '_client_id']) && !empty($this->data[$this->mollieHelper->getModuleCode() . '_client_id'])) {
				if(version_compare(VERSION, '2.3', '>=')) {
					$route = 'extension/payment/mollie/mollieConnect';
				} else {
					$route = 'payment/mollie/mollieConnect';
				}
				$data['stores'][$store['store_id']]['mollie_connect'] = $this->url->link($route, $this->token . "&client_id=" . $this->data[$this->mollieHelper->getModuleCode() . '_client_id'] . "&store_id=" . $store['store_id']);
			} else {
				$data['stores'][$store['store_id']]['mollie_connect'] = '';
			}

			if(version_compare(VERSION, '2.3', '>=')) {
				$data['stores'][$store['store_id']]['redirect_uri'] = HTTPS_SERVER . 'index.php?route=extension/payment/mollie/mollieConnectCallback';
			} else {
				$data['stores'][$store['store_id']]['redirect_uri'] = HTTPS_SERVER . 'index.php?route=payment/mollie/mollieConnectCallback';
			}

			// Check which payment methods we can use with the current API key.
			$allowed_methods = array();
			try {
				$api_methods = $this->getAPIClient($store['store_id'])->methods->allAvailable();
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

			foreach ($this->mollieHelper->MODULE_NAMES as $module_name) {
				$payment_method = array();

				$payment_method['name']    = $this->language->get("name_mollie_" . $module_name);
				$payment_method['disable']    = $this->url->link("payment/mollie/disablePaymentMethod", "method=" . $module_name . "&store_id=" . $store['store_id']);
				$payment_method['enable']     = $this->url->link("payment/mollie/enablePaymentMethod", "method=" . $module_name . "&store_id=" . $store['store_id']);
				$payment_method['icon']    = "../image/mollie/" . $module_name . "2x.png";
				$payment_method['allowed'] = in_array($module_name, $allowed_methods);

				if(($module_name == 'creditcard') && $payment_method['allowed']) {
					$data['store_data']['creditCardEnabled'] = true;
				}

				// Load module specific settings.
				if (isset($this->data[$store['store_id'] . '_' . $code . '_' . $module_name . '_status'])) {
					$payment_method['status'] = ($this->data[$store['store_id'] . '_' . $code . '_' . $module_name . '_status'] == "on");
				} else {
					$payment_method['status'] = (bool) isset($this->data[$code . "_" . $module_name . "_status"]) ? $this->data[$code . "_" . $module_name . "_status"] : null;
				}

				if (isset($this->data[$store['store_id'] . '_' . $code . '_' . $module_name . '_title'])) {
					$payment_method['title'] = $this->data[$store['store_id'] . '_' . $code . '_' . $module_name . '_title'];
				} else {
					$payment_method['title'] = isset($this->data[$code . "_" . $module_name . "_title"]) ? $this->data[$code . "_" . $module_name . "_title"] : null;
				}

				if (isset($this->data[$store['store_id'] . '_' . $code . '_' . $module_name . '_image'])) {
					$payment_method['image'] = $this->data[$store['store_id'] . '_' . $code . '_' . $module_name . '_image'];
					if(!empty($this->data[$store['store_id'] . '_' . $code . '_' . $module_name . '_image'])) {
						$payment_method['thumb'] = $this->model_tool_image->resize($this->data[$store['store_id'] . '_' . $code . '_' . $module_name . '_image'], 100, 100);
					} else {
						$payment_method['thumb'] = $this->model_tool_image->resize($no_image, 100, 100);
					}					
				} else {
					$payment_method['image'] = isset($this->data[$code . "_" . $module_name . "_image"]) ? $this->data[$code . "_" . $module_name . "_image"] : null;
					$payment_method['thumb'] = (isset($this->data[$code . "_" . $module_name . "_image"]) && !empty($this->data[$code . "_" . $module_name . "_image"])) ? $this->model_tool_image->resize($this->data[$code . "_" . $module_name . "_image"], 100, 100) : $this->model_tool_image->resize($no_image, 100, 100);
				}

				if (isset($this->data[$store['store_id'] . '_' . $code . '_' . $module_name . '_sort_order'])) {
					$payment_method['sort_order'] = $this->data[$store['store_id'] . '_' . $code . '_' . $module_name . '_sort_order'];
				} else {
					$payment_method['sort_order'] = isset($this->data[$code . "_" . $module_name . "_sort_order"]) ? $this->data[$code . "_" . $module_name . "_sort_order"] : null;
				}

				if (isset($this->data[$store['store_id'] . '_' . $code . '_' . $module_name . '_geo_zone'])) {
					$payment_method['geo_zone'] = $this->data[$store['store_id'] . '_' . $code . '_' . $module_name . '_geo_zone'];
				} else {
					$payment_method['geo_zone'] = isset($this->data[$code . "_" . $module_name . "_geo_zone"]) ? $this->data[$code . "_" . $module_name . "_geo_zone"] : null;
				}

				$data['store_data'][$store['store_id'] . '_' . $code . '_payment_methods'][$module_name] = $payment_method;
			}

			$data['stores'][$store['store_id']]['entry_cstatus'] = $this->checkCommunicationStatus(isset($this->data[$code . '_api_key']) ? $this->data[$code . '_api_key'] : null);

			if(isset($this->error[$store['store_id']]['api_key'])) {
				$data['stores'][$store['store_id']]['error_api_key'] = $this->error[$store['store_id']]['api_key'];
			} else {
				$data['stores'][$store['store_id']]['error_api_key'] = '';
			}
			
		}

		$data['download'] = $this->url->link("payment/mollie/download", $this->token, 'SSL');
		$data['clear'] = $this->url->link("payment/mollie/clear", $this->token, 'SSL');

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

		// Save client_id and client_secret in the session and remove refresh_token from the setting if these cerdentials are changed after save
		$appData = array();

		foreach($data['stores'] as $store_id=>$setting_data) {
			$appData[$store_id] = array(
				'client_id' => $setting_data[$code . '_client_id'],
				'client_secret' => $setting_data[$code . '_client_secret']
			);
		}
		
		$this->session->data['app_data'] = $appData;
		$data['store_email'] = $this->config->get('config_email');

		if (version_compare(VERSION, '2', '>=')) {
			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');
			
			if (version_compare(VERSION, '3', '>=')) {
				$this->config->set('template_engine', 'template');
				$this->response->setOutput($this->load->view('payment/mollie', $data));
			} else {
				$this->response->setOutput($this->load->view('payment/mollie.tpl', $data));
			}
		} else {
			$data['column_left'] = '';
			$this->data = &$data;
			$this->template = 'payment/mollie(max_1.5.6.4).tpl';
			$this->children = array(
				'common/header',
				'common/footer'
			);
      
			$this->response->setOutput($this->render());
		}
	}

    /**
     *
     */
    public function validate_api_key() {
    	if (version_compare(VERSION, '2.3', '>=')) {
	      $this->load->language('extension/payment/mollie');
	    } else {
	      $this->load->language('payment/mollie');
	    }
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
		$route = (version_compare(VERSION, '2.3', '>=')) ? 'extension/payment/mollie' : 'payment/mollie';
		if (!$this->user->hasPermission("modify", $route)) {
			$this->error['warning'] = $this->language->get("error_permission");
		}

		if (!$this->request->post[$store . '_' . $this->mollieHelper->getModuleCode() . '_api_key']) {
			$this->error[$store]['api_key'] = $this->language->get("error_api_key");
		}
		
		return (count($this->error) == 0);
	}

	/**
	 * @param string|null
	 * @return string
	 */
	protected function checkCommunicationStatus ($api_key = null) {
		if (version_compare(VERSION, '2.3', '>=')) {
	      $this->load->language('extension/payment/mollie');
	    } else {
	      $this->load->language('payment/mollie');
	    }
		if (empty($api_key)) {
			return '<span style="color:red">' .  $this->language->get('error_no_api_key') . '</span>';
		}

		try {
			$client = $this->mollieHelper->getAPIClientForKey($api_key);

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
		$this->load->model('setting/setting');
		$store_id = $_POST['store_id'];
		$code = $this->mollieHelper->getModuleCode();

		$data = $this->model_setting_setting->getSetting($code, $store_id);
		$data[$code.'_api_key'] = $_POST['api_key'];
		
		$this->model_setting_setting->editSetting($code, $data, $store_id);
		return true;
	}

	public function saveAppData() {
		$json = array();
		$this->load->model('setting/setting');
		$store_id = $_POST['store_id'];
		$code = $this->mollieHelper->getModuleCode();

		$data = $this->model_setting_setting->getSetting($code, $store_id);
		$data[$code.'_client_id'] = $_POST['client_id'];
		$data[$code.'_client_secret'] = $_POST['client_secret'];
		
		$this->model_setting_setting->editSetting($code, $data, $store_id);

		$json['connect_url'] = $this->url->link("payment/mollie/mollieConnect", $this->token . "&client_id=" . $_POST['client_id'] . "&store_id=" . $store_id);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function getUpdateUrl() {
        $client = new mollieHttpClient();
        $info = $client->get(MOLLIE_VERSION_URL);
        if (isset($info["tag_name"]) && ($info["tag_name"] != MOLLIE_VERSION) && version_compare(MOLLIE_VERSION, $info["tag_name"], "<")) {
            $updateUrl = array(
                "updateUrl" => $this->url->link("payment/mollie/update", $this->token, 'SSL'),
                "updateVersion" => $info["tag_name"]
            );

            return $updateUrl;
        }
        return false;
    }

    function update() {
        $this->load->library("mollieHttpClient");

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
            if (version_compare(VERSION, '2.3', '>=')) {
		      $this->load->language('extension/payment/mollie');
		    } else {
		      $this->load->language('payment/mollie');
		    }
            $this->session->data['success'] = sprintf($this->language->get('text_update_success'), MOLLIE_RELEASE);
        }

        //go back
        if (version_compare(VERSION, '2.3', '>=')) {
			$this->response->redirect($this->url->link('extension/payment/mollie', $this->token, true));
		} elseif (version_compare(VERSION, '2', '>=')) {
			$this->response->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
		} else {
			$this->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
		}
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
		if (version_compare(VERSION, '2.3', '>=')) {
	      $this->load->language('extension/payment/mollie');
	    } else {
	      $this->load->language('payment/mollie');
	    }

		$file = DIR_LOGS . 'Mollie.log';

		if (file_exists($file) && filesize($file) > 0) {
			$this->response->addHeader('Pragma:public');
			$this->response->addHeader('Expires:0');
			$this->response->addHeader('Content-Description:File Transfer');
			$this->response->addHeader('Content-Type:application/octet-stream');
			$this->response->addHeader('Content-Disposition:attachment; filename="' . $this->config->get('config_name') . '_' . date('Y-m-d_H-i-s', time()) . '_mollie_error.log"');
			$this->response->addHeader('Content-Transfer-Encoding:binary');

			$this->response->setOutput(file_get_contents($file, FILE_USE_INCLUDE_PATH, null));
		} else {
			$this->session->data['warning'] = sprintf($this->language->get('error_log_warning'), basename($file), '0B');

			if (version_compare(VERSION, '2.3', '>=')) {
				$this->response->redirect($this->url->link('extension/payment/mollie', $this->token, true));
			} elseif (version_compare(VERSION, '2', '>=')) {
				$this->response->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
			} else {
				$this->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
			}
		}
	}
	
	public function clear() {
		if (version_compare(VERSION, '2.3', '>=')) {
	      $this->load->language('extension/payment/mollie');
	    } else {
	      $this->load->language('payment/mollie');
	    }

		$file = DIR_LOGS . 'Mollie.log';

		$handle = fopen($file, 'w+');

		fclose($handle);

		$this->session->data['success'] = $this->language->get('text_log_success');

		if (version_compare(VERSION, '2.3', '>=')) {
			$this->response->redirect($this->url->link('extension/payment/mollie', $this->token, true));
		} elseif (version_compare(VERSION, '2', '>=')) {
			$this->response->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
		} else {
			$this->redirect($this->url->link('payment/mollie', $this->token, 'SSL'));
		}
	}

	public function sendMessage() {
		if (version_compare(VERSION, '2.3', '>=')) {
	      $this->load->language('extension/payment/mollie');
	    } else {
	      $this->load->language('payment/mollie');
	    }

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
				if(version_compare(VERSION, '2', '<')) {
					$enquiry .= "<br>VQMod version : " . VQ_VERSION;
				}				
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