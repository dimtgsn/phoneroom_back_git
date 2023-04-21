<?php

namespace App\Http\Controllers\Api\Message;

use App\Http\Controllers\Controller;
use App\Http\Requests\Message\StoreRequest;
use App\Models\User;
use App\Services\Message\Service;


class MessageController extends Controller
{
    public function create(StoreRequest $request, User $user, Service $service){
        $data = $request->validated();
        $message = $service->store($data, $user);
//        return new MessageCollection($user->messages);
    }
}
