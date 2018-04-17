<?php
class MollieHelper
{
	const PLUGIN_VERSION = "8.3.0";

	// All available modules. These should correspond to the Mollie_API_Object_Method constants.
	const MODULE_NAME_BANKTRANSFER  = "banktransfer";
	const MODULE_NAME_BELFIUS       = "belfius";
	const MODULE_NAME_BITCOIN       = "bitcoin";
	const MODULE_NAME_CREDITCARD    = "creditcard";
	const MODULE_NAME_DIRECTDEBIT   = "directdebit";
	const MODULE_NAME_IDEAL         = "ideal";
	const MODULE_NAME_MISTERCASH    = "mistercash";
	const MODULE_NAME_PAYPAL        = "paypal";
	const MODULE_NAME_PAYSAFECARD   = "paysafecard";
	const MODULE_NAME_SOFORT        = "sofort";
	const MODULE_NAME_KBC           = "kbc";
	const MODULE_NAME_GIFTCARD      = "giftcard";
	const MODULE_NAME_INGHOMEPAY    = "inghomepay";


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
		self::MODULE_NAME_GIFTCARD,
		self::MODULE_NAME_INGHOMEPAY
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

			$mollie->setApiKey($config->get(self::getModuleCode() . '_api_key'));

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

		$mollie->setApiKey(isset($config[self::getModuleCode() . '_api_key']) ? $config[self::getModuleCode() . '_api_key'] : null);

		$mollie->addVersionString("OpenCart/" . VERSION);
		$mollie->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);

		return $mollie;
	}

	public static function getAPIClientForKey($key = null)
	{
		require_once(realpath(DIR_SYSTEM . "/..") . "/catalog/controller/extension/payment/mollie-api-client/src/Mollie/API/Autoloader.php");

		$mollie = new Mollie_API_Client;

		$mollie->setApiKey(!empty($key) ? $key : null);

		$mollie->addVersionString("OpenCart/" . VERSION);
		$mollie->addVersionString("MollieOpenCart/" . self::PLUGIN_VERSION);

		return $mollie;
	}

	/**
	 * @return string
	 */
	public static function getModuleCode()
	{
		if (self::isOpenCart3x()) {
			return 'payment_mollie';
		}

		return 'mollie';
	}

	/**
	 * @return bool
	 */
	public static function isOpenCart3x()
	{
		return version_compare(VERSION, '3.0.0', '>=');
	}

	/**
	 * @return bool
	 */
	public static function isOpenCart23x()
	{
		return version_compare(VERSION, '2.3.0', '>=');
	}

	/**
	 * @return bool
	 */
	public static function isOpenCart2x()
	{
		return version_compare(VERSION, '2', '>=');
	}
}
