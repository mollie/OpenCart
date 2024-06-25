<?php

if (class_exists('VQMod')) {
	if (function_exists('modification')) {
		require_once(VQMod::modCheck(modification(DIR_APPLICATION . "model/total/mollie_payment_fee.php")));
	} else {
		require_once(VQMod::modCheck(DIR_APPLICATION . "model/total/mollie_payment_fee.php"));
	}
} else {
	if (function_exists('modification')) {
		require_once(modification(DIR_APPLICATION . "model/total/mollie_payment_fee.php"));
	} else {
		require_once(DIR_APPLICATION . "model/total/mollie_payment_fee.php");
	}
}

class ModelExtensionTotalMolliePaymentFee extends ModelTotalMolliePaymentFee
{
}
