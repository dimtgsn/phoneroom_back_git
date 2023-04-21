<?php

namespace App\Services\Message;

use App\Models\Message;

class Service
{
    public function store($data, $user)
    {
      $message = Message::firstOrCreate([
          'theme' => $data['theme'],
          'message' => $data['message'],
          'user_id' => $user->id,
      ]);

      return $message;
    }
}