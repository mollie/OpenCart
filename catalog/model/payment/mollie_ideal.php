<?php
require_once(DIR_APPLICATION . "model/payment/mollie/base.php");

class ModelPaymentMollieIDEAL extends ModelPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_IDEAL;

	public function recurringPayments() {
		
		return true;
	}
}
