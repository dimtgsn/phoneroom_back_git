<?php

namespace App\Services\MainImage;

use App\Models\MainImage;
use App\Utilities\ImageConvertToWebp;
use Illuminate\Support\Facades\Storage;

class Service
{
    public function create($data)
    {
        $imageConvert = new ImageConvertToWebp();
        \DB::transaction(function () use ($data, $imageConvert){
            foreach ($data['paths'] as $i => $path){
                $data['paths'][$i] = \Storage::disk('public')->put('images/main_images', $path);
                if(str_ends_with($data['paths'][$i], "jpg") || str_ends_with($data['paths'][$i], "png")
                    || str_ends_with($data['paths'][$i], "gif") || str_ends_with($data['paths'][$i], "jpeg")){
                    $data['paths'][$i] = $imageConvert->convert('storage/'.$data['paths'][$i], true);
                }
            }
            foreach ($data['paths'] as $path){
                $positionLast = MainImage::count();
                MainImage::firstOrCreate([
                    'path' => 'storage/'.$path,
                    'position' => $positionLast + 1 ,
                ]);
            }
        });
    }

    public function update($data)
    {
        $imageConvert = new ImageConvertToWebp();

        \DB::transaction(function() use ($data, $imageConvert) {
            if (isset($data['paths'])){
                foreach ($data['paths'] as $i => $path){
                    $data['paths'][$i] = \Storage::disk('public')->put('images/main_images', $path);
                    if(str_ends_with($data['paths'][$i], "jpg") || str_ends_with($data['paths'][$i], "png")
                        || str_ends_with($data['paths'][$i], "gif") || str_ends_with($data['paths'][$i], "jpeg")){
                        $data['paths'][$i] = $imageConvert->convert('storage/'.$data['paths'][$i], true);
                    }
                }
            }
            foreach ($data['positions'] as $id => $position){
                MainImage::where('id', $id)->update([
                    'position' => $position,
                ]);
            }
            if (isset($data['paths'])){
                foreach ($data['paths'] as $id => $path){
                    Storage::disk('public')->delete(substr(MainImage::where('id', $id)->select('path')->first()['path'], 8));
                    MainImage::where('id', $id)->update([
                        'path' => 'storage/'.$path,
                    ]);
                }
            }
        });

    }
}