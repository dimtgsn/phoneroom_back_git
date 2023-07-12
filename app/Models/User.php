<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Leeto\PhoneAuth\Casts\PhoneCast;
use Leeto\PhoneAuth\Models\Traits\PhoneVerification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, PhoneVerification;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'email',
        'password',
        'phone',
        'position_id',
        'telegram_user_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $with = [
        'profile',
        'position',
        'messages',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone' => PhoneCast::class
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function basket(): HasOne
    {
        return $this->hasOne(Basket::class);
    }

    public function favorite(): HasOne
    {
        return $this->hasOne(Favorite::class);
    }

    public function compare(): HasOne
    {
        return $this->hasOne(Compare::class);
    }

    public function messages(): HasMany{
        return $this->hasMany(Message::class);
    }

    public function orders(): HasMany{
        return $this->hasMany(Order::class);
    }

    public function comments(): HasMany{
        return $this->hasMany(Comment::class);
    }

    /**
     * Route notifications for the Telegram channel.
     *
     * @return int
     */
    public function routeNotificationForTelegram()
    {
        return $this->telegram_user_id;
    }
}
