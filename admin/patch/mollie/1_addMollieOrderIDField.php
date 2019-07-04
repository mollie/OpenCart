<?php
use comercia\Util;

return function () {
    Util::patch()->table("mollie_payments")
        ->addField("mollie_order_id", "VARCHAR(32)")
        ->update();
};