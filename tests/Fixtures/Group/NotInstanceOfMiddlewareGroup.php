<?php

namespace Tests\Fixtures\Group;

class NotInstanceOfMiddlewareGroup
{
    public string $name = 'empty';

    public string|array $routes = '(:all)';

    public array $group = [];
}
