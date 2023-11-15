<?php

use Mollie\Api\MollieApiClient;

class MollieHelper {

	const PLUGIN_VERSION = "13.0.0";

	const OUTH_URL = 'https://api.mollie.com/oauth2';

	const MIN_PHP_VERSION = "8.0.0";
	const NEXT_PHP_VERSION = "8.0.0"; // Maybe used in future

	// All available modules. These should correspond to the Mollie_API_Object_Method constants.
	const MODULE_NAME_BANKTRANSFER  	= "banktransfer";
	const MODULE_NAME_BELFIUS       	= "belfius";
	const MODULE_NAME_CREDITCARD    	= "creditcard";
	const MODULE_NAME_IDEAL         	= "ideal";
	const MODULE_NAME_BANCONTACT    	= "bancontact";
	const MODULE_NAME_PAYPAL        	= "paypal";
	const MODULE_NAME_PAYSAFECARD   	= "paysafecard";
	const MODULE_NAME_SOFORT        	= "sofort";
	const MODULE_NAME_KBC           	= "kbc";
	const MODULE_NAME_GIFTCARD      	= "giftcard";
	const MODULE_NAME_EPS           	= "eps";
	const MODULE_NAME_GIROPAY       	= "giropay";
	const MODULE_NAME_KLARNAPAYLATER 	= "klarnapaylater";
	const MODULE_NAME_KLARNAPAYNOW   	= "klarnapaynow";
	const MODULE_NAME_KLARNASLICEIT  	= "klarnasliceit";
	const MODULE_NAME_PRZELEWY24  	 	= "przelewy_24";
	const MODULE_NAME_APPLEPAY  	 	= "applepay";
	const MODULE_NAME_VOUCHER    	 	= "voucher";
	const MODULE_NAME_IN3    	 		= "in_3";
	const MODULE_NAME_MYBANK    	 	= "mybank";
	const MODULE_NAME_BILLIE    	 	= "billie";
	const MODULE_NAME_KLARNA    	 	= "klarna";


	// List of all available module names.
	public $MODULE_NAMES = array(
		self::MODULE_NAME_BANKTRANSFER,
		self::MODULE_NAME_BELFIUS,
		self::MODULE_NAME_CREDITCARD,
		self::MODULE_NAME_IDEAL,
		self::MODULE_NAME_BANCONTACT,
		self::MODULE_NAME_PAYPAL,
		self::MODULE_NAME_PAYSAFECARD,
		self::MODULE_NAME_SOFORT,
		self::MODULE_NAME_KBC,
		self::MODULE_NAME_GIFTCARD,
		self::MODULE_NAME_EPS,
		self::MODULE_NAME_GIROPAY,
		self::MODULE_NAME_KLARNAPAYLATER,
		self::MODULE_NAME_KLARNAPAYNOW,
		self::MODULE_NAME_KLARNASLICEIT,
		self::MODULE_NAME_PRZELEWY24,
		self::MODULE_NAME_APPLEPAY,
		self::MODULE_NAME_VOUCHER,
		self::MODULE_NAME_IN3,
		self::MODULE_NAME_MYBANK,
		self::MODULE_NAME_BILLIE,
		self::MODULE_NAME_KLARNA
	);

	protected $api_client;
    private object $db;

	public function __construct($registry) {
		$this->db = $registry->get('db');
	}

	/**
	 * @return bool
	 */
	public function apiClientFound ()
	{
		return file_exists(DIR_EXTENSION . "mollie/system/library/mollie/");
	}

	/**
	 * Get the Mollie client. Needs the Config object to retrieve the API key.
	 *
	 * @param Config $config
	 *
	 * @return MollieApiClient
	 */
	public function getAPIClient ($data)
	{
		if (!$this->api_client && $this->apiClientFound())
		{
			require_once(DIR_EXTENSION . "mollie/system/library/mollie/vendor/autoload.php");
			$mollie = new MollieApiClient;

			$mollie->setApiKey($data->get($this->getModuleCode() . '_api_key'));

			$mollie->addVersionString("OpenCart/" . VERSION);
			$mollie->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);

			$this->api_client = $mollie;
		}

		return $this->api_client;
	}

	/**
	 * Get the Mollie client. Needs the Config array for multishop to retrieve the API key.
	 *
	 * @param array $config
	 *
	 * @return MollieApiClient
	 */
	public function getAPIClientAdmin ($config)
	{
		require_once(DIR_EXTENSION . "mollie/system/library/mollie/vendor/autoload.php");
		$mollie = new MollieApiClient;

		$mollie->setApiKey(isset($config[$this->getModuleCode() . '_api_key']) ? $config[$this->getModuleCode() . '_api_key'] : null);

		$mollie->addVersionString("OpenCart/" . VERSION);
		$mollie->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);

		return $mollie;
	}

	public function getAPIClientForKey($key = null)
	{
		require_once(DIR_EXTENSION . "mollie/system/library/mollie/vendor/autoload.php");
		$mollie = new MollieApiClient;

		$mollie->setApiKey(!empty($key) ? $key : null);

		$mollie->addVersionString("OpenCart/" . VERSION);
		$mollie->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);

		return $mollie;
	}

	public function getAPIClientForAccessToken($accessToken) {
		require_once(DIR_EXTENSION . "mollie/system/library/mollie/vendor/autoload.php");
		$mollie = new MollieApiClient;

		$mollie->setAccessToken(!empty($accessToken) ? $accessToken : null);

		$mollie->addVersionString("OpenCart/" . VERSION);
		$mollie->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);

		return $mollie;
	}

	public function getApiKey($store) {
        return $this->getSettingValue($this->getModuleCode() . "_api_key", $store);
    }

	/**
	 * @return string
	 */
	public function getModuleCode()
	{
		return 'payment_mollie';
	}

	public function getSettingValue($key, $store_id = 0) {
		$result = $this->db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `key` = '" . $key . "'");

		if (isset($result->row['value'])) {
			return $result->row['value'];
		} else {
			return null;	
		}
	}

	public function curlRequest($resource, $data) {
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