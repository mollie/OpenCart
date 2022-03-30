<?php

if (class_exists('VQMod')) {
	if (function_exists('modification')) {
		require_once(VQMod::modCheck(modification(DIR_APPLICATION . "controller/payment/mollie/base.php")));
	} else {
		require_once(VQMod::modCheck(DIR_APPLICATION . "controller/payment/mollie/base.php"));
	}
} else {
	if (function_exists('modification')) {
		require_once(modification(DIR_APPLICATION . "controller/payment/mollie/base.php"));
	} else {
		require_once(DIR_APPLICATION . "controller/payment/mollie/base.php");
	}
}

class ControllerPaymentMollieKlarnapaylater extends ControllerPaymentMollieBase
{
	const MODULE_NAME = MollieHelper::MODULE_NAME_KLARNAPAYLATER;
}
