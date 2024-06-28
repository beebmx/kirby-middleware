<?php

namespace Beebmx\KirbyMiddleware\Http;

use Beebmx\KirbyMiddleware\Request;
use Closure;
use Kirby\Cms\App;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

class TrimStrings
{
    protected array $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    protected static array $neverTrim = [];

    public function handle(Request $request, Closure $next)
    {
        return $next($this->clean($request));
    }

    protected function clean(Request $request): Request
    {
        return $this->cleanBody(
            $this->cleanQuery($request)
        );
    }

    protected function cleanBody(Request $request): Request
    {
        $request->replaceBody(
            $this->cleanArray($request->body()->data())
        );

        return $request;
    }

    protected function cleanQuery(Request $request): Request
    {
        $request->replaceQuery(
            $this->cleanArray($request->query()->data())
        );

        return $request;
    }

    protected function cleanArray(array $data, $keyPrefix = ''): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->cleanValue($keyPrefix.$key, $value);
        }

        return $data;
    }

    protected function cleanValue($key, $value)
    {
        if (is_array($value)) {
            return $this->cleanArray($value, $key.'.');
        }

        return $this->transform($key, $value);
    }

    protected function transform($key, $value): mixed
    {
        $except = array_merge($this->exceptInputs(), static::$neverTrim);

        if ($this->shouldSkip($key, $except) || ! is_string($value)) {
            return $value;
        }

        return Str::trim($value);
    }

    protected function exceptInputs(): array
    {
        $exceptions = array_change_key_case(App::instance()->option('beebmx.kirby-middleware.exceptions', []));

        return array_key_exists('trim', $exceptions)
            ? $exceptions['trim']
            : $this->except;
    }

    protected function shouldSkip($key, $except): bool
    {
        return in_array($key, $except, true);
    }

    public static function except($attributes): void
    {
        static::$neverTrim = array_values(array_unique(
            array_merge(static::$neverTrim, A::wrap($attributes))
        ));
    }
}
