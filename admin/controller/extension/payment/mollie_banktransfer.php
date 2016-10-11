<?php
require_once(dirname(__FILE__) . "/mollie/base.php");

class ControllerExtensionPaymentMollieBankTransfer extends ControllerExtensionPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_BANKTRANSFER;
}
