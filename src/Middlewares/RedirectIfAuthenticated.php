<?php

namespace Beebmx\KirbyMiddleware\Middlewares;

use Beebmx\KirbyMiddleware\Concerns\HasRedirection;
use Beebmx\KirbyMiddleware\Request;
use Closure;

class RedirectIfAuthenticated
{
    use HasRedirection;

    protected static Closure|string $redirectTo;

    public function handle(Request $request, Closure $next)
    {
        if ($this->authenticated($request)) {
            return $this->redirectTo($request, redirection: 'auth');
        }

        return $next($request);
    }

    protected function authenticated(Request $request): bool
    {
        return ! empty($request->user());
    }
}
