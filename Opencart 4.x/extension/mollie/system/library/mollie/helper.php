<?php

use Mollie\Api\MollieApiClient;

class MollieHelper {

	const PLUGIN_VERSION = "14.1.0";
	const OUTH_URL = 'https://api.mollie.com/oauth2';

	const MIN_PHP_VERSION = "8.0.0";
	const NEXT_PHP_VERSION = "8.2.0"; // Maybe used in future

	// All available modules. These should correspond to the Mollie_API_Object_Method constants.
	const MODULE_NAME_BANKTRANSFER   = "banktransfer";
	const MODULE_NAME_BELFIUS        = "belfius";
	const MODULE_NAME_CREDITCARD     = "creditcard";
	const MODULE_NAME_IDEAL          = "ideal";
	const MODULE_NAME_BANCONTACT     = "bancontact";
	const MODULE_NAME_PAYPAL         = "paypal";
	const MODULE_NAME_KBC            = "kbc";
	const MODULE_NAME_GIFTCARD       = "giftcard";
	const MODULE_NAME_EPS            = "eps";
	const MODULE_NAME_KLARNAPAYLATER = "klarnapaylater";
	const MODULE_NAME_KLARNAPAYNOW   = "klarnapaynow";
	const MODULE_NAME_KLARNASLICEIT  = "klarnasliceit";
	const MODULE_NAME_PRZELEWY24     = "przelewy_24";
	const MODULE_NAME_APPLEPAY       = "applepay";
	const MODULE_NAME_VOUCHER        = "voucher";
	const MODULE_NAME_IN3            = "in_3";
	const MODULE_NAME_MYBANK         = "mybank";
	const MODULE_NAME_BILLIE         = "billie";
	const MODULE_NAME_KLARNA         = "klarna";
	const MODULE_NAME_TWINT          = "twint";
	const MODULE_NAME_BLIK           = "blik";
	const MODULE_NAME_BANCOMATPAY    = "bancomatpay";
	const MODULE_NAME_TRUSTLY        = "trustly";
	const MODULE_NAME_ALMA           = "alma";
	const MODULE_NAME_RIVERTY        = "riverty";
	const MODULE_NAME_PAYCONIQ       = "payconiq";
	const MODULE_NAME_SATISPAY       = "satispay";
    const MODULE_NAME_MULTIBANCO   	 = "multibanco";
	const MODULE_NAME_BIZUM   	 	 = "bizum";
	const MODULE_NAME_MBWAY   	 	 = "mbway";
	const MODULE_NAME_PAYBYBANK	 	 = "paybybank";
	const MODULE_NAME_SWISH 	 	 = "swish";
    const MODULE_NAME_WERO 	 	     = "wero";

	/**
	 * List of all available module names.
	 * @var array
	 */
	public array $MODULE_NAMES = [
		self::MODULE_NAME_BANKTRANSFER,
		self::MODULE_NAME_BELFIUS,
		self::MODULE_NAME_CREDITCARD,
		self::MODULE_NAME_IDEAL,
		self::MODULE_NAME_BANCONTACT,
		self::MODULE_NAME_PAYPAL,
		self::MODULE_NAME_KBC,
		self::MODULE_NAME_GIFTCARD,
		self::MODULE_NAME_EPS,
		self::MODULE_NAME_KLARNAPAYLATER,
		self::MODULE_NAME_KLARNAPAYNOW,
		self::MODULE_NAME_KLARNASLICEIT,
		self::MODULE_NAME_PRZELEWY24,
		self::MODULE_NAME_APPLEPAY,
		self::MODULE_NAME_VOUCHER,
		self::MODULE_NAME_IN3,
		self::MODULE_NAME_MYBANK,
		self::MODULE_NAME_BILLIE,
		self::MODULE_NAME_KLARNA,
		self::MODULE_NAME_TWINT,
		self::MODULE_NAME_BLIK,
		self::MODULE_NAME_BANCOMATPAY,
		self::MODULE_NAME_TRUSTLY,
		self::MODULE_NAME_ALMA,
		self::MODULE_NAME_RIVERTY,
		self::MODULE_NAME_PAYCONIQ,
		self::MODULE_NAME_SATISPAY,
        self::MODULE_NAME_MULTIBANCO,
		self::MODULE_NAME_BIZUM,
		self::MODULE_NAME_MBWAY,
		self::MODULE_NAME_PAYBYBANK,
		self::MODULE_NAME_SWISH,
		self::MODULE_NAME_WERO
	];

	protected ?object $api_client = null;
	private object $db;

	/**
	 * Constructor
	 * * @param object $registry
	 */
	public function __construct(object $registry) {
		$this->db = $registry->get('db');
	}

	/**
	 * Check if the Mollie API Client directory exists
	 * * @return bool
	 */
	public function apiClientFound(): bool {
		return file_exists(DIR_EXTENSION . "mollie/system/library/mollie/");
	}

	/**
	 * Get the Mollie client. Needs the Config object to retrieve the API key.
	 *
	 * @param object $data
	 * @return MollieApiClient
	 */
	public function getAPIClient(object $data): MollieApiClient {
		if (!$this->api_client && $this->apiClientFound()) {
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
	 * @return MollieApiClient
	 */
	public function getAPIClientAdmin(array $config): MollieApiClient {
		require_once(DIR_EXTENSION . "mollie/system/library/mollie/vendor/autoload.php");
		$mollie = new MollieApiClient;

		$mollie->setApiKey($config[$this->getModuleCode() . '_api_key'] ?? null);

		$mollie->addVersionString("OpenCart/" . VERSION);
		$mollie->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);

		return $mollie;
	}

	/**
	 * Get the Mollie API Client using a specific key
	 * * @param string|null $key
	 * @return MollieApiClient
	 */
	public function getAPIClientForKey(?string $key = null): MollieApiClient {
		require_once(DIR_EXTENSION . "mollie/system/library/mollie/vendor/autoload.php");
		$mollie = new MollieApiClient;

		$mollie->setApiKey(!empty($key) ? $key : null);

		$mollie->addVersionString("OpenCart/" . VERSION);
		$mollie->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);

		return $mollie;
	}

	/**
	 * Get the Mollie API Client using an access token
	 * * @param string|null $accessToken
	 * @return MollieApiClient
	 */
	public function getAPIClientForAccessToken(?string $accessToken): MollieApiClient {
		require_once(DIR_EXTENSION . "mollie/system/library/mollie/vendor/autoload.php");
		$mollie = new MollieApiClient;

		$mollie->setAccessToken(!empty($accessToken) ? $accessToken : null);

		$mollie->addVersionString("OpenCart/" . VERSION);
		$mollie->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);

		return $mollie;
	}

	/**
	 * Get API key for a specific store
	 * * @param int|string $store
	 * @return string|null
	 */
	public function getApiKey(int|string $store): ?string {
        return $this->getSettingValue($this->getModuleCode() . "_api_key", (int)$store);
    }

	/**
	 * Get the module code
	 * * @return string
	 */
	public function getModuleCode(): string {
		return 'payment_mollie';
	}

	/**
	 * Retrieve a specific setting value from the database securely
	 * * @param string $key
	 * @param int $store_id
	 * @return string|null
	 */
	public function getSettingValue(string $key, int $store_id = 0): ?string {
		// Properly escaped $key to prevent SQL Injection
		$result = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' AND `key` = '" . $this->db->escape($key) . "'");

		return $result->row['value'] ?? null;
	}

	/**
	 * Execute a CURL request to Mollie's OAuth endpoint
	 * * @param string $resource
	 * @param mixed $data
	 * @return mixed
	 * @throws \RuntimeException
	 */
	public function curlRequest(string $resource, mixed $data): mixed {
        // Clean up the url
        $url = rtrim(self::OUTH_URL, '/ ');

        if (!function_exists('curl_init')) {
			throw new \RuntimeException('Mollie Helper Error: CURL extension is not loaded in PHP.');
		}

        // Define a final API request
        $api = $url . '/' . $resource;

        $ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $api);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$response = curl_exec($ch);
		$error = curl_error($ch);

        if (!$response) {
            throw new \RuntimeException('Mollie Helper Error: Nothing was returned from CURL request. Error: ' . $error);
        }

        // This line takes the response and breaks it into an array using JSON decoder
        return json_decode($response);
    }
}
