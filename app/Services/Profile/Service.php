<?php

namespace App\Services\Profile;

use App\Models\Profile;
use Illuminate\Support\Facades\DB;

class Service
{

    public function update($data, $profile, $user)
    {
        $user->update([
            'first_name' => $data['first_name'] ?? $user->fist_name,
            'email' => $data['email'] ?? $user->email,
//            'phone' => $data['phone'] ?? $user->phone,
        ]);
        $profile->update([
            'middle_name' => $data['middle_name'] ?? $profile->middle_name,
            'last_name' => $data['last_name'] ?? $profile->last_name,
        ]);
        $profile->address->update([
            'fullAddress' => $data['fullAddress'] ?? $profile->address->fullAddress,
        ]);

        return $profile;
    }
}