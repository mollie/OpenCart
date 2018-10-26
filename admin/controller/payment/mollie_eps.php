<?php
require_once(dirname(__FILE__) . "/mollie/base.php");
class ControllerPaymentMollieEPS extends ControllerPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_EPS;
}
