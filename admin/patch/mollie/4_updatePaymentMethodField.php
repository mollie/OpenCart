<?php
use util\Util;

return function () {
    Util::patch()->table("order")
        ->editField("payment_method", "VARCHAR(255)")
        ->update();
};