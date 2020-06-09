<?php
namespace util;
class ControllerHelper
{
    function moduleSettingsController($name)
    {
        require_once __DIR__ . "/controllers/moduleSettings.php";
        return new ModuleSettings($name);
    }
}
