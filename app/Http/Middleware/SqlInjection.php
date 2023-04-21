<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SqlInjection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(preg_match('/\b{drop}\b/', strtolower($request))
            || preg_match('/\bdatabase\b/', strtolower($request))
            || preg_match('/\bselect\b/', strtolower($request))
            || preg_match('/\bfrom\b/', strtolower($request))
            || preg_match('/\bwhere\b/', strtolower($request))
            || preg_match('/\bdelete\b/', strtolower($request))
            || preg_match('/\binsert\b/', strtolower($request))
            || preg_match('/\bupdate\b/', strtolower($request))
            || preg_match('/\bgroup\b/', strtolower($request))
            || preg_match('/\bopder\b/', strtolower($request))
            || preg_match('/\bby\b/', strtolower($request))
            || preg_match('/\blimit\b/', strtolower($request))
            || preg_match('/\blike\b/', strtolower($request))
            || preg_match('/\b"\b/', strtolower($request))
            || preg_match('/\b\'\b/', strtolower($request))
            || preg_match('/\bdescribe\b/', strtolower($request))
            || preg_match('/\bjoin\b/', strtolower($request))
            || preg_match('/\bview\b/', strtolower($request))
            || preg_match('/\bhaving\b/', strtolower($request))
            || preg_match('/\bbetween\b/', strtolower($request))){
            return $request;
            abort(404);
        }
        else{
            return 11;
            return $next($request);
        }
    }
}
