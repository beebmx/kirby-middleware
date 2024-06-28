<?php

namespace Tests\Fixtures\Middleware;

use Beebmx\KirbyMiddleware\Request;
use Closure;
use Kirby\Http\Response;

class EmptyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
