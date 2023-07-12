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
        'api/v1/users/*',
        'users/login',
        'users/register',
        'users/logout',
        'api/v1/baskets/create',
        'api/v1/favorites/create',
        'api/v1/compares/create',
        'api/v1/orders/create',
        'api/v1/comments/*',
    ];
}
