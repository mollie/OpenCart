<?php
require_once(DIR_APPLICATION . "model/payment/mollie/base.php");

class ModelPaymentMollieSatispay extends ModelPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_SATISPAY;
}
