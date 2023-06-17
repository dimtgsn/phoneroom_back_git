<?php

namespace App\Utilities;

use Imagick;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ImageConvertToBase64
{

    public function convert($file){

        $path = pathinfo($file);
        $ext = mb_strtolower($path['extension']);

        if (in_array($ext, array('jpeg', 'jpg', 'gif', 'png', 'webp', 'svg', 'avif'))) {
            $img = base64_encode(file_get_contents($file));
        }
        return $img;
    }

}