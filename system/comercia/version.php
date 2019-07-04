<?php
namespace comercia;
class Version
{
    private $version;

    function __construct()
    {
        $this->version = VERSION;
    }

    function get()
    {
        return $this->version;
    }

    function isMinimal($version)
    {
        return version_compare($version, $this->version, "<=");
    }

    function isMaximal($version)
    {
        return version_compare($version, $this->version, ">=");
    }
}