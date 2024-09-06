<?php

namespace Beebmx\KirbyMiddleware\Concerns;

trait MergeRoutes
{
    public function routes(): string|array
    {
        return array_key_exists($this->name(), $this->getParseRouteKeys())
            ? $this->mergeRoutes($this->getRouteByName())
            : $this->mergeRoutes($this->routes);
    }

    protected function mergeRoutes(array|string $routes): array
    {
        $routes = is_string($routes)
            ? array_merge([$routes], static::$groupRoutes)
            : array_merge($routes, static::$groupRoutes);

        return array_filter($routes);
    }
}
