<?php

namespace Beebmx\KirbyMiddleware;

use Kirby\Toolkit\Collection;

class RouteCollection
{
    protected array $routes;

    public function __construct(array $routes)
    {
        $this->routes = $this->setRoutes($routes);
    }

    protected function setRoutes(array $routes): array
    {
        return array_map(function ($route) {
            return is_string($route)
                ? new Route($route)
                : $this->setRoutes($route);
        }, $routes);
    }

    public function add(array $routes): static
    {
        $this->routes = array_merge(
            $this->routes, $this->setRoutes($routes)
        );

        return $this;
    }

    public function resolve(string $path): array
    {
        return $this->resolvePathCollection($path, $this->routes);
    }

    public function toArray(): array
    {
        return $this->routes;
    }

    public function all(): Collection
    {
        return new Collection($this->routes);
    }

    protected function resolvePathCollection(string $path, array $routes): array
    {
        return array_keys(
            array_filter(
                array_map(
                    fn ($route) => $route instanceof Route
                    ? $route->matches($path)
                    : $this->resolvePathCollection($path, $route), $routes
                )
            )
        );
    }
}
