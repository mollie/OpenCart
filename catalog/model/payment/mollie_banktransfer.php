<?php
require_once(DIR_APPLICATION . "model/payment/mollie/base.php");

class ModelPaymentMollieBankTransfer extends ModelPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_BANKTRANSFER;
}
