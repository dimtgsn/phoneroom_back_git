<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\Client\StoreRequest;
use App\Http\Resources\User\UserResource;
use App\Models\Profile;
use App\Models\User;
use App\Services\User\Service;
use App\Utilities\DateFormatting;
use App\Utilities\TranslationIntoLatin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MoveMoveIo\DaData\Facades\DaDataAddress;


class UserController extends Controller
{
    public function index(User $user){
        return new UserResource($user);
    }

    public function update(UpdateRequest $request, \App\Services\Profile\Service $service, User $user){
        $data = $request->validated();
        $data['phone'] = preg_replace('/\D/','', $data['phone']);
        if ($data['phone'][0] === '8'){
            $data['phone'][0] = '7';
        }
        $profile = Profile::find($user->profile->id);
        $service->update($data, $profile, $user);
        return new UserResource($user);
    }

    public function phone_auth(\App\Http\Requests\PhoneAuth\StoreRequest $request){
        $settings = config('phone_auth.sms_service.settings');
        $host = 'https://'.$settings['email'].':'.$settings['api_key'].'@gate.smsaero.ru/v2/sms/send';
        $sms = new \Leeto\PhoneAuth\SmsServiceExample($host);
        $sms->settings($settings);
        $pass = mt_rand(100000, 999999);
        // TODO раскомментировать
//        return $sms->send($request->phone, str('Ваш код подтверждения - '.$pass), 'SMS Aero');
    }

    public function register(StoreRequest $request, Service $service, \App\Services\Profile\Service $service_profile){
        $data = $request->validated();
        $credentials_data = [];
        $data['phone'] = preg_replace('/\D/','', $data['phone']);
        if ($data['phone'][0] === '8'){
            $data['phone'][0] = '7';
        }
        $credentials_data['first_name'] = $data['first_name'];
        $credentials_data['phone'] = $data['phone'];
        $credentials_data['password'] = $data['password'];
        $user = User::where('phone', $data['phone'])->first();
        if (!$user){
            $user = $service->storeClient($data);
        }
        if (Auth::attempt($credentials_data)) {
            $request->session()->regenerate();
            return [
                'data' => [
                    'id' => auth()->user()->id,
                    'first_name' => auth()->user()->first_name,
                ],
            ];
        }
        return ':(';
    }

    public function login(Request $request){
        $credentials = $request->validate([
            'password' => ['required', 'string'],
            'phone' => ['required', 'phone', 'string'],
        ]);
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return [
                'data' => [
                    'id' => auth()->user()->id,
                    'first_name' => auth()->user()->first_name,
                ],
            ];
        }
        return ':(';
    }

    public function logout(Request $request){
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return 'logout';
    }

    public function getIpInfo() {
        $dadata = DaDataAddress::iplocate(\Request::ip(), 2);
        return $dadata['location']['data']['city'] ?? $dadata['location'];
//        return '';
    }
}
