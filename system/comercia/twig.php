<?php
namespace comercia;
 use ReflectionClass;
 use ReflectionMethod;
 use Twig_SimpleFunction;

 class Twig{
     var $twig;
    function prepare($twig){
        $this->twig=$twig;

        //for backward compatibility purposes
        $this->twig->addFunction("html_variables",new Twig_SimpleFunction("html_variables",function(){
            return \comercia\Util::html()->variables();
        }));

        $class = new ReflectionClass("\\comercia\\Util");
        $methods=$class->getMethods(ReflectionMethod::IS_STATIC);
        foreach($methods as $method){
            $this->handleClass($method->name);
        }

    }

     private function handleClass($class)
     {
        if(file_exists(__DIR__."/".$class.".php")){
            include_once(__DIR__."/".$class.".php");
            $reflectionClass = new ReflectionClass("\\comercia\\".$class);
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
         $this->twig->addFunction("comercia_".$funcName,new Twig_SimpleFunction("comercia_".$funcName,[Util::$class(),$method]));
         //for compatibility
         $this->twig->addFunction("util_".$funcName,new Twig_SimpleFunction("util_".$funcName,[Util::$class(),$method]));
     }
 }
