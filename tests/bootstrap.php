<?php
date_default_timezone_set('CET');

define("DIR_APPLICATION", realpath(dirname(__FILE__) . "/.."));
define("DIR_SYSTEM",      DIR_APPLICATION . "/system");
define("DIR_TEMPLATE",    DIR_APPLICATION . "/catalog/view/theme");
define("VERSION",         '9.0.0');


date_default_timezone_set("Europe/Amsterdam");

spl_autoload_register(function($className)
{
	$project_dir = dirname(dirname(__FILE__));

	$map = array(
		"MollieHelper"                         => "$project_dir/catalog/controller/payment/mollie/helper.php",
		"ControllerPaymentMollieBase" => "$project_dir/catalog/controller/payment/mollie/base.php",
		"ModelPaymentMollieBase"      => "$project_dir/catalog/model/payment/mollie/base.php",
	);

	if (isset($map[$className]))
	{
		include $map[$className];
		return;
	}

	class_alias("stub", $className);
});

class stub extends ArrayObject implements ArrayAccess {
	public function __isset($name)
	{
		return $this->getIterator()->__isset($name);
	}
	public function __unset($name)
	{
		return $this->getIterator()->__unset($name);
	}
	public function offsetSet ($key, $value)
	{
		return $this->getIterator()->offsetSet($key, $value);
	}
	public function offsetGet ($key)
	{
		return $this->getIterator()->offsetGet($key);
	}
}

define("DB_PREFIX", "prefix_");

class Mollie_OpenCart_TestCase extends PHPUnit_Framework_TestCase
{
	const CONFIG_PARTNER_ID = 1001;
	const CONFIG_PROFILE_KEY = "decafbad";
	const CONFIG_TESTMODE = TRUE;
	const CONFIG_DESCRIPTION = "Uw order %";
	const CONFIG_DESCRIPTION_FINAL = "Uw order 1337";
	const CONFIG_SORT_ORDER = 995;

	const URL_PAYMENT = "https://opencart.local/url/payment";

	const BANK_URL = "https://bankieren.mijnbank.example";

	const TRANSACTION_ID = "1bba1d8fdbd8103b46151634bdbe0a60";

	const ORDER_STATUS_SUCCESS_ID = 1;
	const ORDER_STATUS_FAILED_ID = 2;
	const ORDER_STATUS_PROCESSING_ID = 3;
	const ORDER_STATUS_CANCELED_ID = 4;
	const ORDER_STATUS_EXPIRED_ID = 5;

	const ORDER_ID = 1337;

	const BANK_ID = "0999";

	protected static $banks = array(
		'1234' => 'Test bank 1',
		'0678' => 'Test bank 2'
	);
}
