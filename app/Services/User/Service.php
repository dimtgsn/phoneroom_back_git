<?php

namespace App\Services\User;

use App\Models\Address;
use App\Models\Position;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Service
{
    public function store($data)
    {

      if ($data['first_name'] and $data['phone']) {
          return DB::transaction(function() use ($data) {

              $user = (new \App\Models\User)->firstOrCreate([
                  'first_name' => $data['first_name'],
                  'email' => $data['email'] ?? null,
                  'phone' => $data['phone'],
                  'password' => isset($data['password']) ? Hash::make($data['password']) : null,
                  'position_id' => 2
              ]);

//            return $user;

              $profile = Profile::firstOrCreate([
                  'user_id' => $user->id,
                  'middle_name' => $data['middle_name'] ?? null ,
                  'last_name' => $data['last_name'] ?? null,
              ]);

              Address::firstOrCreate([
                  'fullAddress' => $data['fullAddress'] ?? null,
                  'profile_id' => $profile->id,
              ]);

              return $user;
          });

      }

//        return $user;
    }
    public function storeClient($data)
    {
        if ($data['first_name'] and $data['phone']) {
            return DB::transaction(function() use ($data) {
                $user = (new \App\Models\User)->firstOrCreate([
                    'first_name' => $data['first_name'],
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'],
                    'password' => isset($data['password']) ? Hash::make($data['password']) : null,
                    'position_id' => 1
                ]);

                $profile = Profile::firstOrCreate([
                    'user_id' => $user->id,
                    'middle_name' => $data['middle_name'] ?? null ,
                    'last_name' => $data['last_name'] ?? null,
                ]);

                Address::firstOrCreate([
                    'fullAddress' => $data['fullAddress'] ?? null,
                    'profile_id' => $profile->id,
                ]);

                return $user;
            });

        }

//        return $user;
    }

    public function update($profile, $data)
    {
        if (isset($data['first_name'])){
            DB::table('users')
                ->where('users.profile_id', $profile->id)
                ->update($data['first_name']);
        }

        if (isset($data['email'])){
            DB::table('users')
                ->where('users.profile_id', $profile->id)
                ->update($data['email']);
        }
        unset($data['first_name']);
        unset($data['email']);


        DB::table('profiles')
            ->where('profiles.id', $profile->id)
            ->update([
                'middle_name' => $data['middle_name'] ?? $profile->middle_name,
                'last_name' => $data['last_name'] ?? $profile->last_name,
                'phone' => $data['phone'] ?? $profile->phone,
                'address' => $data['address'] ?? $profile->address,
            ]);

//        if (isset($data['discount'])){
//            $product->update([
//                'discount' => true,
//            ]);
//            unset($data['discount']);
//        }
//        else{
//            $product->update([
//                'discount' => false,
//            ]);
//        }
//
//        if (isset($data['tags_id'])){
//            $tags = $data['tags_id'];
//            unset($data['tags_id']);
//            $product->tags()->sync($tags);
//        }
//
//        $product->update($data);
//
        return $profile;
    }
}