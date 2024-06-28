<?php

namespace Beebmx\KirbyMiddleware;

use Kirby\Http\Route as BaseRoute;

class Route extends BaseRoute
{
    protected string $pattern;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public function resolve(string $path): array|false
    {
        return $this->parse($this->pattern, $path);
    }

    public function matches(string $path): bool
    {
        return is_array($this->resolve($path));
    }
}
