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
        'my-warehouse/webhooks',
        'users/register',
        'users/logout',
        'api/v1/baskets/*',
        'api/v1/favorites/*',
        'api/v1/compares/*',
        'api/v1/orders/create',
        'api/v1/comments/*',
        '1c-retail-integration/1c_exchange',
    ];
}
