<?php
namespace comercia;
class Route
{
    function extension($extensionName = false, $type = 'module')
    {
        if ($extensionName) {
            if(Util::version()->isMinimal("2.3"))
            {
                return 'extension/'.$type.'/'.$extensionName;
            }
            return $type.'/'.$extensionName;
        }

        if (Util::version()->isMinimal("3.0")) {
            return "marketplace/extension";
        } elseif (Util::version()->isMinimal("2.3")) {
            return 'extension/extension';
        }
        return 'extension/'.$type;
    }
}

?>