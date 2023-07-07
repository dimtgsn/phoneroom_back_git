<?php

namespace App\Services\BannerImage;

use App\Models\BannerImage;
use App\Utilities\ImageConvertToWebp;
use Illuminate\Support\Facades\Storage;

class Service
{
    public function create($data)
    {
        \DB::transaction(function () use ($data){
            foreach ($data['paths'] as $i => $path){
                $data['paths'][$i] = \Storage::disk('public')->put('images/banner_images', $path);
                if(str_ends_with($data['paths'][$i], "jpg") || str_ends_with($data['paths'][$i], "png")
                    || str_ends_with($data['paths'][$i], "gif") || str_ends_with($data['paths'][$i], "jpeg")){
                    $data['paths'][$i] = ImageConvertToWebp::convert('storage/'.$data['paths'][$i], true);
                }
                else{
                    $data['paths'][$i] = 'storage/'.$data['paths'][$i];
                }
            }
            foreach ($data['paths'] as $path){
                $positionLast = BannerImage::count();
                BannerImage::firstOrCreate([
                    'path' => $path,
                    'position' => $positionLast + 1 ,
                ]);
            }
        });
    }

    public function update($data)
    {
        \DB::transaction(function() use ($data) {
            if (isset($data['paths'])){
                foreach ($data['paths'] as $i => $path){
                    $data['paths'][$i] = \Storage::disk('public')->put('images/banner_images', $path);
                    if(str_ends_with($data['paths'][$i], "jpg") || str_ends_with($data['paths'][$i], "png")
                        || str_ends_with($data['paths'][$i], "gif") || str_ends_with($data['paths'][$i], "jpeg")){
                        $data['paths'][$i] = ImageConvertToWebp::convert('storage/'.$data['paths'][$i], true);
                    }
                    else{
                        $data['paths'][$i] = 'storage/'.$data['paths'][$i];
                    }
                }
            }
            foreach ($data['positions'] as $id => $position){
                BannerImage::where('id', $id)->update([
                    'position' => $position,
                ]);
            }
            if (isset($data['paths'])){
                foreach ($data['paths'] as $id => $path){
                    Storage::disk('public')->delete(substr(BannerImage::where('id', $id)->select('path')->first()['path'], 8));
                    BannerImage::where('id', $id)->update([
                        'path' => $path,
                    ]);
                }
            }
        });

    }
}