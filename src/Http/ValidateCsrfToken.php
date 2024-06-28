<?php

namespace Beebmx\KirbyMiddleware\Http;

use Beebmx\KirbyMiddleware\Exception\TokenMismatchException;
use Beebmx\KirbyMiddleware\Request;
use Closure;
use Kirby\Cms\App;

class ValidateCsrfToken
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->isReading($request) || $this->inExceptArray($request) || $this->tokensMatch($request)) {
            return $next($request);
        }

        throw new TokenMismatchException('CSRF token mismatch.');
    }

    protected function isReading(Request $request): bool
    {
        return in_array(strtoupper($request->method()), ['HEAD', 'GET', 'OPTIONS']);
    }

    protected function tokensMatch(Request $request): bool
    {
        $token = $this->getTokenFromRequest($request);

        return is_string(App::instance()->session()->get('kirby.csrf'))
            && is_string($token)
            && App::instance()->csrf($token);
    }

    protected function getTokenFromRequest(Request $request): ?string
    {
        return $this->getTokenFromFieldRequest($request) ?: $this->getTokenFromRequestHeader($request);
    }

    protected function getTokenFromFieldRequest(Request $request): ?string
    {
        $field = $this->guessTokenFieldFromRequest($request);

        return $field
            ? $request->get($field)
            : null;
    }

    protected function getTokenFromRequestHeader(Request $request): ?string
    {
        $header = $this->guessTokenFromRequestHeader($request);

        return $header
            ? $request->header($header)
            : null;
    }

    protected function guessTokenFieldFromRequest(Request $request): ?string
    {
        return match (true) {
            array_key_exists('csrf', $request->data()) => 'csrf',
            array_key_exists('csrf-token', $request->data()) => 'csrf-token',
            array_key_exists('x-csrf', $request->data()) => 'x-csrf',
            array_key_exists('x-csrf-token', $request->data()) => 'x-csrf-token',
            array_key_exists('_token', $request->data()) => '_token',
            default => null,
        };
    }

    protected function guessTokenFromRequestHeader(Request $request): ?string
    {
        return match (true) {
            array_key_exists('x-csrf', $request->headers()), => 'x-csrf',
            array_key_exists('X-Csrf', $request->headers()), => 'X-Csrf',
            array_key_exists('X-CSRF-TOKEN', $request->headers()) => 'X-CSRF-TOKEN',
            array_key_exists('X-Csrf-Token', $request->headers()) => 'X-Csrf-Token',
            array_key_exists('X-XSRF-TOKEN', $request->headers()) => 'X-XSRF-TOKEN',
            array_key_exists('X-Xsrf-Token', $request->headers()) => 'X-Xsrf-Token',
            default => null,
        };
    }

    protected function inExceptArray(Request $request): bool
    {
        foreach ($this->getExcludedPaths() as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            return $request->urlIs($except);
        }

        return false;
    }

    protected function getExcludedPaths(): array
    {
        $exceptions = array_change_key_case(App::instance()->option('beebmx.kirby-middleware.exceptions', []));

        return array_key_exists('csrf', $exceptions)
            ? $exceptions['csrf']
            : [];
    }
}
