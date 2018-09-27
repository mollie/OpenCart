<?php
namespace comercia;
class Breadcrumb
{
    var $data;
    var $key;

    function __construct(&$data, $key = "breadcrumbs")
    {
        $this->data =& $data;
        $this->key = $key;
        if (!isset($data[$key]) || !is_array($data[$key])) {
            $data[$key] = array();
        }
    }

    function add($name, $route, $params = "")
    {
        $text = Util::language()->$name;
        $href = Util::url()->link($route, $params);
        if (count($this->data[$this->key])) {
            $separator = "::";
        } else {
            $separator = "";
        }

        $entry = array(
            'text' => $text,
            'href' => $href,
            'separator' => $separator
        );
        $this->data[$this->key][] = $entry;
        return $this;
    }
}

?>