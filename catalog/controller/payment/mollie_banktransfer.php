<?php
require_once(DIR_APPLICATION . "controller/payment/mollie/base.php");

class ControllerPaymentMollieBankTransfer extends ControllerPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_BANKTRANSFER;

	/**
	 * Bank transfers can't be cancelled. They need 'pending' as an initial order status.
	 *
	 * @return bool
	 */
	protected function startAsPending ()
	{
		return TRUE;
	}
}
