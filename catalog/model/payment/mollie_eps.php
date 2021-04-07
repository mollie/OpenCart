<?php
require_once(DIR_APPLICATION . "model/payment/mollie/base.php");
class ModelPaymentMollieEPS extends ModelPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_EPS;

	public function recurringPayments() {
		
		return true;
	}
}
