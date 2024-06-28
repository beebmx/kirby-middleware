<?php

namespace Tests\Fixtures\Middleware;

use Beebmx\KirbyMiddleware\Request;
use Closure;
use Kirby\Exception\ErrorPageException;
use Kirby\Http\Response;

class ErrorPageMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        return throw new ErrorPageException([
            'fallback' => 'Unauthorized',
            'httpCode' => 401,
        ]);
    }
}
