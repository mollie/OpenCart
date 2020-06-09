<?php
use util\Util;

return function () {
    Util::patch()->table("mollie_payments")
        ->addField("order_id", "INT(11) NOT NULL")
        ->addField("method", "VARCHAR(32) NOT NULL")
        ->addField("mollie_order_id", "VARCHAR(32) NOT NULL", "primary")
        ->addField("transaction_id", "VARCHAR(32)")
        ->addField("bank_account", "VARCHAR(15)")
        ->addField("bank_status", "VARCHAR(20)")
        ->addField("refund_id", "VARCHAR(32)")
        ->addUnique("mollie_order_id")
        ->create();
};