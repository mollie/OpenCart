<?php
namespace comercia;
class Filesystem
{
    function removeDirectory($path)
    {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            is_dir($file) ? $this->removeDirectory($file) : unlink($file);
        }
        rmdir($path);
    }

    function getLatestVersion($before,$after){
        $posibilities = glob(DIR_APPLICATION.$before."*".$after);
        arsort($posibilities);
        reset($posibilities);
        if(count($posibilities)) {
            return str_replace(DIR_APPLICATION,"",$posibilities[0]);
        }else{
            return "";
        }
    }

    function search($dir, $pattern){
        return glob($dir . $pattern . ".*");
    }
}
