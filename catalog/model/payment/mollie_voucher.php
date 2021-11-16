<?php
require_once(DIR_APPLICATION . "model/payment/mollie/base.php");

class ModelPaymentMollieVoucher extends ModelPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_VOUCHER;

	public function recurringPayments() {
		
		return true;
	}
}
