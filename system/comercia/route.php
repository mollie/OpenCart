<?php
namespace comercia;
class Route
{
    function extension($extensionName = false)
    {
        if ($extensionName) {
            if(Util::version()->isMinimal("2.3"))
            {
                return 'extension/module/'.$extensionName;
            }
            return 'extension/module/'.$extensionName;
        }

        if (Util::version()->isMinimal("3.0")) {
            return "marketplace/extension";
        } elseif (Util::version()->isMinimal("2.3")) {
            return 'extension/extension';
        }
        return 'extension/module';
    }
}

?>