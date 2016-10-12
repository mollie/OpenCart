<?php
class MollieHelper
{
    // Plugin only for Opencart >= 2.3
	const PLUGIN_VERSION = "7.0.1";

	// All available modules. These should correspond to the Mollie_API_Object_Method constants.
	const MODULE_NAME_BANKTRANSFER = "banktransfer";
	const MODULE_NAME_BELFIUS      = "belfius";
	const MODULE_NAME_BITCOIN      = "bitcoin";
	const MODULE_NAME_CREDITCARD   = "creditcard";
	const MODULE_NAME_DIRECTDEBIT  = "directdebit";
	const MODULE_NAME_IDEAL        = "ideal";
	const MODULE_NAME_MISTERCASH   = "mistercash";
	const MODULE_NAME_PAYPAL       = "paypal";
	const MODULE_NAME_PAYSAFECARD  = "paysafecard";
	const MODULE_NAME_SOFORT       = "sofort";
	const MODULE_NAME_KBC          = "kbc";

	// List of all available module names.
	static public $MODULE_NAMES = array(
		self::MODULE_NAME_BANKTRANSFER,
		self::MODULE_NAME_BELFIUS,
		self::MODULE_NAME_BITCOIN,
		self::MODULE_NAME_CREDITCARD,
		self::MODULE_NAME_DIRECTDEBIT,
		self::MODULE_NAME_IDEAL,
		self::MODULE_NAME_MISTERCASH,
		self::MODULE_NAME_PAYPAL,
		self::MODULE_NAME_PAYSAFECARD,
		self::MODULE_NAME_SOFORT,
		self::MODULE_NAME_KBC,
	);

	static protected $api_client;

	/**
	 * @return bool
	 */
	public static function apiClientFound ()
	{
		return file_exists(realpath(DIR_SYSTEM . "/..") . "/catalog/controller/extension/payment/mollie-api-client/");
	}

	/**
	 * Get the Mollie client. Needs the Config object to retrieve the API key.
	 *
	 * @param Config $config
	 *
	 * @return Mollie_API_Client
	 */
	public static function getAPIClient ($config)
	{
		if (!self::$api_client && self::apiClientFound())
		{
			require_once(realpath(DIR_SYSTEM . "/..") . "/catalog/controller/extension/payment/mollie-api-client/src/Mollie/API/Autoloader.php");

			$mollie = new Mollie_API_Client;

			$mollie->setApiKey($config->get('mollie_api_key'));

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
	 * @return Mollie_API_Client
	 */
	public static function getAPIClientAdmin ($config)
	{
		require_once(realpath(DIR_SYSTEM . "/..") . "/catalog/controller/extension/payment/mollie-api-client/src/Mollie/API/Autoloader.php");

		$mollie = new Mollie_API_Client;

		$mollie->setApiKey(isset($config['mollie_api_key']) ? $config['mollie_api_key'] : null);

		$mollie->addVersionString("OpenCart/" . VERSION);
		$mollie->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);

		return $mollie;
	}
}
