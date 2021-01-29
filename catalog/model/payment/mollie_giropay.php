<?php
require_once(dirname(__FILE__) . "/mollie/base.php");
class ModelPaymentMollieGIROPAY extends ModelPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_GIROPAY;

	public function recurringPayments() {
		
		return true;
	}
}
