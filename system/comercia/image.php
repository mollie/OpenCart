<?php
namespace comercia;
class Image
{
    function gdImage($file)
    {
        $file=DIR_IMAGE.$file;
        $info = getimagesize($file);
        $mime = isset($info['mime']) ? $info['mime'] : '';
        if (file_exists($file)) {

            if ($mime == 'image/gif') {
                return imagecreatefromgif($file);
            } elseif ($mime == 'image/png') {
                return imagecreatefrompng($file);
            } elseif ($mime == 'image/jpeg') {
                return imagecreatefromjpeg($file);
            }
        }

        return false;
    }

    function drawWatermark($mainImage,$watermarkImage,$width,$position){
        $mainWidth=imagesx($mainImage);
        $mainHeight=imagesy($mainImage);

        $watermarkWidth=imagesx($watermarkImage);
        $watermarkHeight=imagesy($watermarkImage);

        $realWidth=$mainWidth/100*$width;
        $realHeight=$watermarkHeight/$watermarkWidth*$realWidth;

        $watermarkImage=imagescale($watermarkImage,$realWidth,$realHeight);
        $realWidth=imagesx($watermarkImage);
        $realHeight=imagesy($watermarkImage);

        if($position==0){
            $offsetX=0;
            $offsetY=0;
        }
        elseif($position==1){
            $offsetX=$mainWidth-$realWidth;
            $offsetY=0;
        }
        elseif($position==2){
            $offsetX=$mainWidth-$realWidth;
            $offsetY=$mainHeight-$realHeight;
        }
        elseif($position==3){
            $offsetX=0;
            $offsetY=$mainHeight-$realHeight;
        }

        imagecopy($mainImage,$watermarkImage,$offsetX,$offsetY,0,0,$realWidth,$realHeight);

    }

    function clearCache(){
        Util::filesystem()->removeDirectory(DIR_IMAGE."cache");
        mkdir(DIR_IMAGE."cache");
        return;
    }

}

?>