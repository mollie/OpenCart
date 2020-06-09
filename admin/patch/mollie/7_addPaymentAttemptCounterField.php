<?php
use util\Util;

return function () {
    Util::patch()->table("mollie_payments")
        ->addField("payment_attempt", "INT(11) NOT NULL")
        ->update();
};