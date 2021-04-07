<?php
require_once(DIR_APPLICATION . "model/payment/mollie/base.php");

class ModelPaymentMolliePayPal extends ModelPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_PAYPAL;

	public function recurringPayments() {
		
		return true;
	}
}
