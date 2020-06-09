<?php
use util\Util;

return function () {
    Util::patch()->table("mollie_payments")
        ->addField("mollie_order_id", "VARCHAR(32)")
		->addUnique("mollie_order_id")
        ->update();
};