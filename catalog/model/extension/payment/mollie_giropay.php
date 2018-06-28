<?php
require_once(dirname(__FILE__) . "/mollie/base.php");
class ModelExtensionPaymentMollieGIROPAY extends ModelExtensionPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_GIROPAY;
}
