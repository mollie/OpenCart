<?php
use comercia\Util;

return function () {
    Util::patch()->table("mollie_payments")
        ->addField("date_modified", "DATETIME NOT NULL")
        ->update();
};