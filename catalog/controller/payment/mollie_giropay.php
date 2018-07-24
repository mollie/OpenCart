<?php
require_once(dirname(__FILE__) . "/mollie/base.php");
class ControllerPaymentMollieGiropay extends ControllerPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_GIROPAY;
}
