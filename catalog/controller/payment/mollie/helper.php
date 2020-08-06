<?php

use util\Util;
use Mollie\Api\MollieApiClient;

class MollieHelper
{
	const PLUGIN_VERSION = "9.4.0";
	const OUTH_URL = 'https://api.mollie.com/oauth2';

	// All available modules. These should correspond to the Mollie_API_Object_Method constants.
	const MODULE_NAME_BANKTRANSFER  = "banktransfer";
	const MODULE_NAME_BELFIUS       = "belfius";
	const MODULE_NAME_CREDITCARD    = "creditcard";
	const MODULE_NAME_DIRECTDEBIT   = "directdebit";
	const MODULE_NAME_IDEAL         = "ideal";
	const MODULE_NAME_BANCONTACT    = "bancontact";
	const MODULE_NAME_PAYPAL        = "paypal";
	const MODULE_NAME_PAYSAFECARD   = "paysafecard";
	const MODULE_NAME_SOFORT        = "sofort";
	const MODULE_NAME_KBC           = "kbc";
	const MODULE_NAME_GIFTCARD      = "giftcard";
	const MODULE_NAME_INGHOMEPAY    = "inghomepay";
	const MODULE_NAME_EPS           = "eps";
	const MODULE_NAME_GIROPAY       = "giropay";
	const MODULE_NAME_KLARNAPAYLATER = "klarnapaylater";
	const MODULE_NAME_KLARNASLICEIT  = "klarnasliceit";
	const MODULE_NAME_PRZELEWY24  	 = "przelewy24";
	const MODULE_NAME_APPLEPAY  	 = "applepay";


	// List of all available module names.
	static public $MODULE_NAMES = array(
		self::MODULE_NAME_BANKTRANSFER,
		self::MODULE_NAME_BELFIUS,
		self::MODULE_NAME_CREDITCARD,
		self::MODULE_NAME_DIRECTDEBIT,
		self::MODULE_NAME_IDEAL,
		self::MODULE_NAME_BANCONTACT,
		self::MODULE_NAME_PAYPAL,
		self::MODULE_NAME_PAYSAFECARD,
		self::MODULE_NAME_SOFORT,
		self::MODULE_NAME_KBC,
		self::MODULE_NAME_GIFTCARD,
		self::MODULE_NAME_INGHOMEPAY,
		self::MODULE_NAME_EPS,
		self::MODULE_NAME_GIROPAY,
		self::MODULE_NAME_KLARNAPAYLATER,
		self::MODULE_NAME_KLARNASLICEIT,
		self::MODULE_NAME_PRZELEWY24,
		self::MODULE_NAME_APPLEPAY
	);

	static protected $api_client;

	/**
	 * @return bool
	 */
	public static function apiClientFound ()
	{
		return file_exists(realpath(DIR_SYSTEM . "/..") . "/catalog/controller/payment/mollie-api-client/");
	}

	/**
	 * Get the Mollie client. Needs the Config object to retrieve the API key.
	 *
	 * @param Config $config
	 *
	 * @return MollieApiClient
	 */
	public static function getAPIClient ($data)
	{
		if (!self::$api_client && self::apiClientFound())
		{
			require_once(realpath(DIR_SYSTEM . "/..") . "/catalog/controller/payment/mollie-api-client/vendor/autoload.php");
			$mollie = new MollieApiClient;

			$mollie->setApiKey($data->get(self::getModuleCode() . '_api_key'));

			$mollie->addVersionString("OpenCart/" . VERSION);
			$mollie->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);

			self::$api_client = $mollie;
		}

		return self::$api_client;
	}

	/**
	 * Get the Mollie client. Needs the Config array for multishop to retrieve the API key.
	 *
	 * @param array $config
	 *
	 * @return MollieApiClient
	 */
	public static function getAPIClientAdmin ($config)
	{
		require_once(realpath(DIR_SYSTEM . "/..") . "/catalog/controller/payment/mollie-api-client/vendor/autoload.php");
		$mollie = new MollieApiClient;

		$mollie->setApiKey(isset($config[self::getModuleCode() . '_api_key']) ? $config[self::getModuleCode() . '_api_key'] : null);

		$mollie->addVersionString("OpenCart/" . VERSION);
		$mollie->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);

		return $mollie;
	}

	public static function getAPIClientForKey($key = null)
	{
		require_once(realpath(DIR_SYSTEM . "/..") . "/catalog/controller/payment/mollie-api-client/vendor/autoload.php");
		$mollie = new MollieApiClient;

		$mollie->setApiKey(!empty($key) ? $key : null);

		$mollie->addVersionString("OpenCart/" . VERSION);
		$mollie->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);

		return $mollie;
	}

	public static function getAPIClientForAccessToken($accessToken) {
		require_once(realpath(DIR_SYSTEM . "/..") . "/catalog/controller/payment/mollie-api-client/vendor/autoload.php");
		$mollie = new MollieApiClient;

		$mollie->setAccessToken(!empty($accessToken) ? $accessToken : null);

		$mollie->addVersionString("OpenCart/" . VERSION);
		$mollie->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);

		return $mollie;
	}

	public static function getApiKey($store) {
        return self::getSettingValue(self::getModuleCode() . "_api_key", $store);
    }

	/**
	 * @return string
	 */
	public static function getModuleCode()
	{
		return Util::info()->getModuleCode('mollie', 'payment');
	}

	/**
	 * @return bool
	 */
	public static function isOpenCart3x()
	{
		return Util::version()->isMinimal('3.0.0');
	}

	/**
	 * @return bool
	 */
	public static function isOpenCart23x()
	{
		return Util::version()->isMinimal('2.3.0');
	}

	/**
	 * @return bool
	 */
	public static function isOpenCart2x()
	{
		return Util::version()->isMinimal('2');
	}

	public static function generateAccessToken($store_id = 0) {

		$client_id = self::getSettingValue(self::getModuleCode() . '_client_id', $store_id);
		$client_secret = self::getSettingValue(self::getModuleCode() . '_client_secret', $store_id);
		$refresh_token = self::getSettingValue(self::getModuleCode() . '_refresh_token', $store_id);
		if(version_compare(VERSION, '2.3.0.2', '>=') == true) {
			$redirect_uri = HTTPS_SERVER . 'index.php?route=extension/payment/mollie_bancontact/mollieConnectCallback';
		} else {
			$redirect_uri = HTTPS_SERVER . 'index.php?route=payment/mollie_bancontact/mollieConnectCallback';
		}

		if(!empty($client_id) && !empty($client_secret) && !empty($refresh_token)) {
			$data = array(
				'client_id' => $client_id,
	            'client_secret' => $client_secret,
	            'refresh_token' => $refresh_token,
				'grant_type' => 'refresh_token',
				'redirect_uri'		=> $redirect_uri
			);

			$result = self::curlRequest('tokens', $data);

			return isset($result->access_token) ? $result->access_token : null;
		} else {
			return null;
		}
	}

	public static function getSettingValue($key, $store_id = 0) {
		$result = Util::db()->query("SELECT value FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `key` = '" . $key . "'");

		if (isset($result[0]['value'])) {
			return $result[0]['value'];
		} else {
			return null;	
		}
	}

	public static function curlRequest($resource, $data) {
        // clean up the url
        $url = rtrim(self::OUTH_URL, '/ ');

        if ( !function_exists('curl_init') ) die('CURL not supported. (introduced in PHP 4.0.2)');

        // define a final API request
        $api = $url . '/' . $resource;

        $ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $api);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$response = curl_exec($ch);

		curl_close ($ch);

        if ( !$response ) {
            die('Nothing was returned.');
        }

        // This line takes the response and breaks it into an array using:
        // JSON decoder
        $result = json_decode($response);

        return $result;
    }
}
