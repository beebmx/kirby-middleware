<?php

namespace Beebmx\KirbyMiddleware\MiddlewareGroups;

use Beebmx\KirbyMiddleware\Concerns\MergeRoutes;

class GuestMiddlewareGroup extends MiddlewareGroup
{
    use MergeRoutes;

    protected static array $groupRoutes = [];

    public array $group = [
        \Beebmx\KirbyMiddleware\Middlewares\RedirectIfAuthenticated::class,
    ];

    public static function setRoutes(array $routes = []): void
    {
        static::$groupRoutes = $routes;
    }
}
