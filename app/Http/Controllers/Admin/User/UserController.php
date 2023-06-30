<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreRequest;
use App\Models\Profile;
use App\Models\User;
use App\Services\User\Service;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(){

        $users = User::orderBy('position_id', 'desc')->paginate(10);
        return view('admin.user.index', compact('users'));
    }

    public function create(){

        return view('admin.user.create');
    }

    public function store(StoreRequest $request, Service $service){

        $data = $request->validated();
        $data['phone'] = preg_replace('/\D/','', $data['phone']);
        $data['phone'] = '7'.$data['phone'];
        $service->store($data);
        return redirect()->route('admin.users.index');
    }
}
