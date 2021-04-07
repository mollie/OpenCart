<?php
require_once(DIR_APPLICATION . "model/payment/mollie/base.php");

class ModelPaymentMolliebancontact extends ModelPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_BANCONTACT;

	public function recurringPayments() {
		
		return true;
	}
}
