<?php

namespace Egal\Core\Http;

use Closure;
use Illuminate\Http\Request;

class ForceJsonMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');
        return $next($request);
    }
}
