<?php

namespace Beebmx\KirbyMiddleware\MiddlewareGroups;

class WebMiddlewareGroup extends MiddlewareGroup
{
    public string|array $routes = '(:all)';

    public array $group = [
        \Beebmx\KirbyMiddleware\Http\ValidateCsrfToken::class,
    ];
}
