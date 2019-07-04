<?php
namespace comercia;
class Log
{
    function write($message){
        Util::registry()->get("log")->write($message);
    }
}