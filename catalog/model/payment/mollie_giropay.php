<?php
require_once(DIR_APPLICATION . "model/payment/mollie/base.php");
class ModelPaymentMollieGIROPAY extends ModelPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_GIROPAY;

	public function recurringPayments() {
		
		return true;
	}
}
