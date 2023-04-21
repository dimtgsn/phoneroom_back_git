<?php

namespace App\Http\Controllers\Admin\Logo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Logo\UpdateRequest;
use App\Models\Logo;
use App\Services\Logo\Service;

class LogoController  extends Controller
{

    public function edit(){
        $image = Logo::all()->first();
        return view('admin.logo.edit', compact('image'));
    }

    public function update(Logo $logo, UpdateRequest $request, Service $service){
        $data = $request->validated();
        $service->update($data, $logo);
        return redirect()->route('admin.index');
    }
}