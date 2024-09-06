<?php

namespace Beebmx\KirbyMiddleware\MiddlewareGroups;

use Beebmx\KirbyMiddleware\Concerns\MergeRoutes;

class AuthMiddlewareGroup extends MiddlewareGroup
{
    use MergeRoutes;

    protected static array $groupRoutes = [];

    public array $group = [
        \Beebmx\KirbyMiddleware\Middlewares\Authenticate::class,
    ];

    public static function setRoutes(array $routes = []): void
    {
        static::$groupRoutes = $routes;
    }
}
