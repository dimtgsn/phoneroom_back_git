<?php

namespace App\Utilities;

use Illuminate\Support\Facades\Storage;
use Imagick;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ImageConvertToWebp
{

    static function convert($source, $removeOld = false, $quality = 100){

        $dir = pathinfo($source, PATHINFO_DIRNAME);
        $name = pathinfo($source, PATHINFO_FILENAME);
        $destination = $dir . '/' . $name . '.webp';
        $destination_jpg = $dir . '/' . $name . '.jpeg';
        if (str_contains($source, asset(''))){
            // TODO изменить нижележащую строку на url с доменом
            $destination_jpg = Storage::path(str_replace(asset('').'storage/', '', $destination_jpg));
            $destination = Storage::path(str_replace(asset('').'storage/', '', $destination));
        }
        $info = getimagesize($source);
        $isAlpha = false;
        $image_webp = false;
        $image_avif = false;
        if ($info['mime'] == 'image/jpeg')
            $image = imagecreatefromjpeg($source);
        elseif ($isAlpha = $info['mime'] == 'image/gif') {
            $image = imagecreatefromgif($source);
        } elseif ($isAlpha = $info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);
        } elseif ($isAlpha = $info['mime'] == 'image/webp') {
            $image_webp = imagecreatefromwebp($source);
        } elseif ($isAlpha = $info['mime'] == 'image/avif') {
            $image_avif = imagecreatefromavif($source);
        }
        else {
            return $source;
        }

        if ($image_webp || $image_avif){
            if ($image_webp){
                imagepalettetotruecolor($image_webp);
                imagealphablending($image_webp, true);
                imagesavealpha($image_webp, true);
                imagejpeg($image_webp, $destination_jpg, $quality);
            }
            else{
                imagepalettetotruecolor($image_avif);
                imagealphablending($image_avif, true);
                imagesavealpha($image_avif, true);
                imagejpeg($image_avif, $destination_jpg, $quality);
            }
        }
        else{
            if ($isAlpha) {
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
            }
            imagewebp($image, $destination, $quality);
        }
        if ($removeOld){
            if (str_contains($source, asset(''))){
                unlink(Storage::path(str_replace(asset('').'storage/', '', $source)));
            }
            else{
                unlink($source);
            }
        }

        if ($image_webp || $image_avif){
            return $destination_jpg;
        }
        return $destination;
    }

}