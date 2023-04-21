<?php

namespace App\Utilities;

use Imagick;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ImageConvertToWebp
{

    public function convert($source, $removeOld = false, $quality = 100){

        $dir = pathinfo($source, PATHINFO_DIRNAME);
        $name = pathinfo($source, PATHINFO_FILENAME);
        $destination = $dir . '/' . $name . '.webp';
        $destination_jpg = $dir . '/' . $name . '.jpeg';
        $info = getimagesize($source);
        $isAlpha = false;
        $image_webp = false;
        if ($info['mime'] == 'image/jpeg')
            $image = imagecreatefromjpeg($source);
        elseif ($isAlpha = $info['mime'] == 'image/gif') {
            $image = imagecreatefromgif($source);
        } elseif ($isAlpha = $info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);
        } elseif ($isAlpha = $info['mime'] == 'image/webp') {
            $image_webp = imagecreatefromwebp($source);
        }
        else {
            return $source;
        }

        if ($image_webp){
            imagepalettetotruecolor($image_webp);
            imagealphablending($image_webp, true);
            imagesavealpha($image_webp, true);
            imagejpeg($image_webp, $destination_jpg, $quality);
        }
        else{
            if ($isAlpha) {
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
            }
            imagewebp($image, $destination, $quality);
        }
        if ($removeOld)
            unlink($source);

        if ($image_webp){
            return $destination_jpg;
        }
        return $destination;
    }

}