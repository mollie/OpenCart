<?php

define("DIR_TEMPLATE", dirname(dirname(__FILE__)) . "/catalog/view/theme");

spl_autoload_register(function($className)
{
	$project_dir = dirname(dirname(__FILE__));

	$map = array(
		"iDEAL_Payment" => "$project_dir/catalog/controller/payment/ideal.class.php",
		"ControllerPaymentMollieIdeal" => "$project_dir/catalog/controller/payment/mollie_ideal.php",
	);

	if (isset($map[$className]))
	{
		include $map[$className];
		return;
	}

	class_alias("stub", $className);
});

class stub {}

class Mollie_OpenCart_TestCase extends PHPUnit_Framework_TestCase
{
	const CONFIG_PARTNER_ID = 1001;
	const CONFIG_PROFILE_KEY = "decafbad";
	const CONFIG_TESTMODE = TRUE;

	const URL_PAYMENT = "https://opencart.local/url/payment";

	protected static $banks = array(
		'1234' => 'Test bank 1',
		'0678' => 'Test bank 2'
	);
}