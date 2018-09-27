<?php

namespace comercia;
class Info
{
    function IsInAdmin()
    {
        global $application_context;
        return $application_context !== null && $application_context == "admin" ||
            (defined("HTTPS_CATALOG") && HTTPS_CATALOG != HTTPS_SERVER || defined("HTTP_CATALOG") && HTTP_CATALOG != HTTPS_SERVER);
    }

    function theme()
    {
        return Util::config()->config_template;
    }

    function stores()
    {
        static $stores = false;
        if (!$stores) {
            $stores = array_merge(
                [
                    [
                        'store_id' => 0,
                        'name' => Util::config()->config_name . Util::language()->text_default,
                        'url' => Util::url()->getCatalogUrl()
                    ]
                ],
                Util::load()->model("setting/store")->getStores()
            );

            usort($stores, function ($a, $b) {
                return $a["store_id"] - $b["store_id"];
            });
            $stores = array_values($stores);
        }
        return $stores;
    }
}

?>