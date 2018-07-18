<?php
require_once(dirname(__FILE__) . "/mollie/base.php");

class ControllerExtensionPaymentMolliebancontact extends ControllerExtensionPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_BANCONTACT;
}
