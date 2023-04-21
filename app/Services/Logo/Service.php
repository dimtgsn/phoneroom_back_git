<?php

namespace App\Services\Logo;

use App\Models\BannerImage;
use App\Utilities\ImageConvertToWebp;
use Illuminate\Support\Facades\Storage;

class Service
{

    public function update($data, $logo)
    {
        $imageConvert = new ImageConvertToWebp();
        \DB::transaction(function() use ($data, $logo, $imageConvert) {
            if (isset($data['path'])){
                $data['path'] = \Storage::disk('public')->put('images/logo', $data['path']);
                if(str_ends_with($data['path'], "jpg") || str_ends_with($data['path'], "png")
                    || str_ends_with($data['path'], "gif") || str_ends_with($data['path'], "jpeg")){
                    $data['path'] = $imageConvert->convert('storage/'.$data['path'], true);
                }
                Storage::disk('public')->delete(substr($logo->path, 8));
                $logo->update([
                    'path' => 'storage/'.$data['path'],
                ]);
            }
            if (isset($data['favicon'])){
                $data['favicon'] = \Storage::disk('public')->put('images/logo', $data['favicon']);
                if($logo->favicon){
                    Storage::disk('public')->delete(substr($logo->favicon, 8));
                }
                $logo->update([
                    'favicon' => 'storage/'.$data['favicon'],
                ]);
            }
        });

    }
}