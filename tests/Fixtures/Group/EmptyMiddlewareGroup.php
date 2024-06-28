<?php

namespace Tests\Fixtures\Group;

use Beebmx\KirbyMiddleware\MiddlewareGroups\MiddlewareGroup;

class EmptyMiddlewareGroup extends MiddlewareGroup
{
    public string $name = 'empty';

    public string|array $routes = '(:all)';

    public array $group = [];
}
