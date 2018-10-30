<?php
namespace comercia;
class Html
{
    function variables()
    {
        $variables = Util::document()->variables;
        foreach ($variables as $key => $val) {
            $variables[$key] = json_encode($val);
        }
        return $this->component("variables", array("variables" => $variables));
    }

    function component($_name, $_data = array())
    {
        ob_start();
        foreach ($_data as $key => $val) {
            ${$key} = $val;
        }
        include(__DIR__ . "/component/" . $_name . ".php");
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    function selectbox($name, $value, $options, $class = "")
    {
        return $this->component("selectbox", array("value" => $value, "name" => $name, "options" => $options, "class" => $class));
    }

    function breadcrumb($breadcrumbs)
    {
        return $this->component("breadcrumb", array("breadcrumbs" => $breadcrumbs));
    }
}

?>