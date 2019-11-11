<?php
use comercia\Util;

return function () {
    Util::patch()->table("mollie_payments")
        ->addField("refund_id", "VARCHAR(32)")
        ->update();
};