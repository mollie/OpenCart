<?php
require_once(dirname(__FILE__) . "/mollie/base.php");

class ModelPaymentMollieApplepay extends ModelPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_APPLEPAY;
}
