<?php
namespace util;
 use ReflectionClass;
 use ReflectionMethod;
 use Twig_SimpleFunction;

 class Twig{
     var $twig;
    function prepare($twig){
        $this->twig=$twig;

        //for backward compatibility purposes
        $this->twig->addFunction("html_variables",new Twig_SimpleFunction("html_variables",function(){
            return \util\Util::html()->variables();
        }));

        $class = new ReflectionClass("\\util\\Util");
        $methods=$class->getMethods(ReflectionMethod::IS_STATIC);
        foreach($methods as $method){
            $this->handleClass($method->name);
        }

    }

     private function handleClass($class)
     {
        if(file_exists(__DIR__."/".$class.".php")){
            include_once(__DIR__."/".$class.".php");
            $reflectionClass = new ReflectionClass("\\util\\".$class);
            $methods=$reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach($methods as $method){
                $this->handleMethod($class,$method->name);
            }
        }
     }

     private function handleMethod($class, $method)
     {
         $funcName=$class."_".$method;
         //should use
         $this->twig->addFunction("util_".$funcName,new Twig_SimpleFunction("util_".$funcName,[Util::$class(),$method]));
         //for compatibility
         $this->twig->addFunction("util_".$funcName,new Twig_SimpleFunction("util_".$funcName,[Util::$class(),$method]));
     }
 }
