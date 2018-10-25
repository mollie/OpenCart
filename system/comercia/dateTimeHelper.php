<?php
namespace comercia;
class DateTimeHelper
{

    function toTimestamp($data)
    {
        $regexVal=preg_replace("/\.[0-9]*/i","",$data);
        if (is_numeric($data)) {
           return $data;
        }elseif($dateTime=\DateTime::createFromFormat(\DateTime::W3C,$regexVal)){
            return $dateTime->getTimestamp();
        }elseif($dateTime=\DateTime::createFromFormat("Y-m-d",$data)){
            return $dateTime->getTimestamp();
        }elseif($dateTime=\DateTime::createFromFormat(\DateTime::ATOM,$regexVal)){
            return $dateTime->getTimestamp();
        }
        return 0;
    }


}

?>