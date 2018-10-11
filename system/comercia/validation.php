<?php
namespace comercia;
class Validation
{
    var $data;
    var $store_id;
    var $error;


    function __construct(&$data, $store_id, $error)
    {
        $this->data =& $data;
        $this->store_id = $store_id;
        $this->error = $error;
    }

    function notIsset($error_key, $value) {

        $error = $this->error;
        if (!isset($this->data[$error_key]) && isset($error[$this->store_id][$value])) {
            $this->data[$error_key] = $error[$this->store_id][$value];
        }
        else {
            $this->data[$error_key] = '';
        }
        return $this;
    }
}

?>