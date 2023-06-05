<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\StoreRequest;
use App\Http\Resources\User\UserResource;
use App\Models\Profile;
use App\Models\User;
use App\Services\User\Service;
use App\Utilities\TranslationIntoLatin;
use Illuminate\Http\Request;
use MoveMoveIo\DaData\Facades\DaDataAddress;


class UserController extends Controller
{
    public function index(User $user){
        return new UserResource($user);
    }

    public function update(UpdateRequest $request, \App\Services\Profile\Service $service, User $user){
        $data = $request->validated();
        $data['phone'] = preg_replace('/\D/','', $data['phone']);
        $data['phone'][0] = '7';
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
        return $sms->send($request->phone, str('Ваш код подтверждения - '.$pass), 'SMS Aero');
    }

    public function register(StoreRequest $request, Service $service, \App\Services\Profile\Service $service_profile){
        $data = $request->validated();
        $data['phone'] = preg_replace('/\D/','', $data['phone']);
        $data['phone'][0] = '7';
        $user = User::where('phone', $data['phone'])->first();
        if ($user && isset($data['last_name'])){
            $profile = Profile::find($user->profile->id);
            $service_profile->update($data, $profile, $user);
        }
        elseif ($user){
            abort(404);
        }
        else{
            $user = $service->storeClient($data);
        }
        $token = $user->createToken($data['phone']);
        return [
            'data' => [
                'id' => $user['id'],
                'first_name' => $user['first_name'],
            ],
            'token' => $token->plainTextToken,
        ];
    }

    public function login(User $user){
        $token = $user->createToken($user['phone']);
        return [
            'data' => [
                'id' => $user['id'],
                'first_name' => $user['first_name'],
            ],
            'token' => $token->plainTextToken
        ];
    }

    public function logout(User $user){
        $user->tokens()->delete();
    }

    public function getIpInfo() {
        $dadata = DaDataAddress::iplocate(\Request::ip(), 2);
        return $dadata['location']['data']['city'] ?? $dadata['location'];
    }
}


// 11|tlZyQro4yaFgmzWU1463RzRIXpsVhrgl0i1ZjjZu