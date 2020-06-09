<?php
use util\Util;

return function () {
    Util::patch()->table("mollie_payments")
        ->editField("method", "VARCHAR(32) NOT NULL")
        ->editField("transaction_id", "VARCHAR(32)")
        ->editField("bank_account", "VARCHAR(15)")
        ->editField("bank_status", "VARCHAR(20)")
        ->editField("refund_id", "VARCHAR(32)")
        ->update();
};