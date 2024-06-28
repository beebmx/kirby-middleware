<?php

namespace Beebmx\KirbyMiddleware\Facades;

use Beebmx\KirbyMiddleware\Middleware as M;
use Kirby\Toolkit\Facade;

class Middleware extends Facade
{
    public static function instance(): M
    {
        return M::instance();
    }
}
