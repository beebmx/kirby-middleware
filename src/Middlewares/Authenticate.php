<?php

namespace Beebmx\KirbyMiddleware\Middlewares;

use Beebmx\KirbyMiddleware\Concerns\HasRedirection;
use Beebmx\KirbyMiddleware\Request;
use Closure;

class Authenticate
{
    use HasRedirection;

    protected static Closure|string $redirectTo;

    public function handle(Request $request, Closure $next)
    {
        if ($this->unauthenticated($request)) {
            return $this->redirectTo($request, redirection: 'guest');
        }

        return $next($request);
    }

    protected function unauthenticated(Request $request): bool
    {
        return empty($request->user());
    }
}
