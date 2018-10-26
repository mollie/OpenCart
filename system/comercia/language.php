<?php
namespace comercia;
class Language
{
    private $language;

    function __construct($language=false)
    {
        if(!$language){
            $this->language = Util::registry()->get("language");
        }else{
            $this->language=new \Language($language);
        }
    }

    function __get($name)
    {
        return $this->get($name);
    }

    function get($name)
    {
        return @$this->language->get($name) ?: "";
    }

    function load($file){
        $arr=[];
        return Util::load()->language($file,$arr,$this->language);
    }


}

?>