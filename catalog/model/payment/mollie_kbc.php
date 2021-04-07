<?php
require_once(DIR_APPLICATION . "model/payment/mollie/base.php");

class ModelPaymentMollieKbc extends ModelPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_KBC;

	public function recurringPayments() {
		
		return true;
	}
}
