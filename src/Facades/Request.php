<?php

namespace Beebmx\KirbyMiddleware\Facades;

use Beebmx\KirbyMiddleware\Request as R;
use Kirby\Toolkit\Facade;

class Request extends Facade
{
    public static function instance(): R
    {
        return R::instance();
    }
}
