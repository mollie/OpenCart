<?php
require_once(dirname(__FILE__) . "/mollie/base.php");

class ModelPaymentMollieSOFORT extends ModelPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_SOFORT;
}
