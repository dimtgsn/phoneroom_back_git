<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    public function __invoke()
    {
        $userRegularCount = User::where('position_id', 1)->count();
        $orderCount = Order::all()->count();

        return view('admin.index', compact('userRegularCount', 'orderCount'));
    }
}
