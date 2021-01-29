<?php
include_once(__DIR__."/../../payment/mollie_creditcard.php");
class ModelExtensionPaymentMollieCreditcard extends ModelPaymentMollieCreditcard
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_CREDITCARD;
}
