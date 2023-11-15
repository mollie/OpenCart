<?php
require_once(DIR_APPLICATION . "model/payment/mollie/base.php");

class ModelPaymentMollieCreditcard extends ModelPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_CREDITCARD;

	public function recurringPayments() {
		
		return true;
	}
}
