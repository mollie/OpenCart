<?php
require_once(DIR_APPLICATION . "model/payment/mollie/base.php");

class ModelPaymentMollieMybank extends ModelPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_MYBANK;

	public function recurringPayments() {
		
		return true;
	}
}
