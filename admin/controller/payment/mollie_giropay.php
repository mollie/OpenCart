<?php
require_once(DIR_APPLICATION . "controller/payment/mollie/base.php");

class ControllerPaymentMollieGIROPAY extends ControllerPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_GIROPAY;
}
