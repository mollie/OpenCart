<?php
namespace comercia;

class selectboxOptions
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

    function add($key, $value)
    {
        $text = Util::language()->$key;
        $entry = array(
            'text' => $text,
            'value' => $value,
        );
        $this->data[$this->key][] = $entry;
        return $this;
    }
}

?>