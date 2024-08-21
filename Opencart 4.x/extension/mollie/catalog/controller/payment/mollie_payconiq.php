<?php
namespace Opencart\Catalog\Controller\Extension\Mollie\Payment;

require_once(__DIR__ . "/../mollie.php");

class MolliePayconiq extends \Opencart\Catalog\Controller\Extension\Mollie\Mollie
{
	const MODULE_NAME = \MollieHelper::MODULE_NAME_PAYCONIQ;
}
