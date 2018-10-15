<?php
namespace comercia;
class StringHelper
{
    function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
    function ccToUnderline($subject){
        return strtolower(preg_replace('/\B([A-Z])/', '_$1', lcfirst($subject)));
    }

    function rewriteForVersion($string,$words){
        foreach($words as $word){
            $match=false;
            $versionMatch=@isset($word[0])?:array_values($word)[0];
            foreach ($word as $version=>$newWord){
                if(strpos($string, $newWord) !== false){
                    $match=$newWord;
                }
                if($version>0 && Util::version()->isMinimal($version)){
                    $versionMatch=$newWord;
                }
            }

            if($match){
                $string= str_replace($match,$versionMatch,$string);
            }
        }
        return $string;
    }

}

?>
