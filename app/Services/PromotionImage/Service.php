<?php

namespace App\Services\PromotionImage;

use App\Models\PromotionImage;
use App\Utilities\ImageConvertToWebp;
use Illuminate\Support\Facades\Storage;

class Service
{
    public function create($data)
    {
        $imageConvert = new ImageConvertToWebp();
        \DB::transaction(function () use ($data, $imageConvert){
            foreach ($data['paths'] as $i => $path){
                $data['paths'][$i] = \Storage::disk('public')->put('images/promotion_images', $path);
                if(str_ends_with($data['paths'][$i], "jpg") || str_ends_with($data['paths'][$i], "png")
                    || str_ends_with($data['paths'][$i], "gif") || str_ends_with($data['paths'][$i], "jpeg")){
                    $data['paths'][$i] = $imageConvert->convert('storage/'.$data['paths'][$i], true);
                }
                else{
                    $data['paths'][$i] = 'storage/'.$data['paths'][$i];
                }
            }
            foreach ($data['paths'] as $path){
                $positionLast = PromotionImage::count();
                PromotionImage::firstOrCreate([
                    'path' => $path,
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
                    $data['paths'][$i] = \Storage::disk('public')->put('images/promotion_images', $path);
                    if(str_ends_with($data['paths'][$i], "jpg") || str_ends_with($data['paths'][$i], "png")
                        || str_ends_with($data['paths'][$i], "gif") || str_ends_with($data['paths'][$i], "jpeg")){
                        $data['paths'][$i] = $imageConvert->convert('storage/'.$data['paths'][$i], true);
                    }
                    else{
                        $data['paths'][$i] = 'storage/'.$data['paths'][$i];
                    }
                }
            }
            foreach ($data['positions'] as $id => $position){
                PromotionImage::where('id', $id)->update([
                    'position' => $position,
                ]);
            }
            if (isset($data['paths'])){
                foreach ($data['paths'] as $id => $path){
                    Storage::disk('public')->delete(substr(PromotionImage::where('id', $id)->select('path')->first()['path'], 8));
                    PromotionImage::where('id', $id)->update([
                        'path' => $path,
                    ]);
                }
            }
        });

    }
}