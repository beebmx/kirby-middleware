<?php

namespace Tests\Fixtures\Group;

use Beebmx\KirbyMiddleware\MiddlewareGroups\MiddlewareGroup;

class TestMiddlewareGroup extends MiddlewareGroup
{
    public string $name = 'empty';

    public string|array $routes = [
        'test/(:all)',
        'tests/(:num?)',
        'testing/specific/page',
    ];

    public array $group = [
        \Tests\Fixtures\Middleware\EmptyMiddleware::class,
        \Tests\Fixtures\Middleware\OtherMiddleware::class,
    ];
}
