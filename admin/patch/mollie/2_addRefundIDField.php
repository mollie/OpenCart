<?php
use util\Util;

return function () {
    Util::patch()->table("mollie_payments")
        ->addField("refund_id", "VARCHAR(32)")
        ->update();
};