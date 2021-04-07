<?php
require_once(DIR_APPLICATION . "model/payment/mollie/base.php");

class ModelPaymentMollieSOFORT extends ModelPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_SOFORT;

	public function recurringPayments() {
		
		return true;
	}
}
