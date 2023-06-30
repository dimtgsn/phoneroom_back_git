<?php

namespace App\Http\Controllers\Admin\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateRequest;
use App\Models\Address;
use App\Models\Profile;
use App\Models\User;
use App\Services\Profile\Service;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index(Profile $profile){

        $user = User::find($profile->user_id);

        return view('admin.profile.index', compact('user', 'profile'));
    }

    public function update(UpdateRequest $request, Service $service, Profile $profile, User $user){

        $data = $request->validated();
        $data['phone'] = preg_replace('/\D/','', $data['phone']);
        $data['phone'] = '7'.$data['phone'];
        $profile = $service->update($data, $profile, $user);
        return redirect()->route('admin.profiles.index', $profile->slug);
    }
}
