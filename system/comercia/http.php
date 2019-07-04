<?php
namespace comercia;
class Http
{
    function getPathFor($path)
    {
        if (!(Util::stringHelper()->startsWith($path, "http://") ||
            Util::stringHelper()->startsWith($path, "https://") ||
            Util::stringHelper()->startsWith($path, "//"))
        ) {
            $path = $this->applicationPath() . $path;
        }
        return $path;
    }

    function applicationPath()
    {
        $path = HTTPS_SERVER ? HTTPS_SERVER : HTTP_SERVER;
        if (!Util::info()->IsInAdmin()) {
            $filePath = DIR_APPLICATION;
            $exp = explode("/", $filePath);
            $dir = $exp[count($exp) - 2];
            $path .= $dir . "/";
        }
        return $path;
    }
}

?>