<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/v1/users/register',
        'api/v1/users/phone_auth',
        'api/v1/baskets/create',
        'api/v1/favorites/create',
        'api/v1/compares/create',
    ];
}
