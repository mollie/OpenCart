<?php
namespace comercia;
class ArrayHelper
{

    function keepPrefix($prefix, $array)
    {
        foreach ($array as $key => $value) {
            if (!Util::stringHelper()->startsWith($key, $prefix)) {
                unset($array[$key]);
            }
        }
        return $array;
    }

    function keyValuePairs($array,$keyField,$valueField){
        $result=[];
        foreach($array as $arrayItem){
            $result[$arrayItem[$keyField]]=$arrayItem[$valueField];
        }
        return $result;
    }

    function keyToVal($data)
    {
        $new = array();
        foreach ($data as $key => $val) {
            $new[$key] = $key;
        }
        return $new;
    }
    function allPrefixed($input,$prefix, $removePrefix = true)
    {
        $result = [];
        $prefixLen = strlen($prefix);
        foreach ($input as $key => $val) {
            if (substr($key, 0, $prefixLen) == $prefix) {
                if ($removePrefix) {
                    $key = substr($key, $prefixLen);
                }
                $result[$key] = $val;
            }
        }
        return $result;
    }

    function prefixAllValues($prefix, $input) {
        $result = [];
        foreach ($input as $val) {
            $result[] = $prefix . $val;
        }
        return $result;
    }
}

?>