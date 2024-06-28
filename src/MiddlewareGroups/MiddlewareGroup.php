<?php

namespace Beebmx\KirbyMiddleware\MiddlewareGroups;

use Beebmx\KirbyMiddleware\Concerns\MiddlewareGroup as Concern;
use Beebmx\KirbyMiddleware\Contracts\MiddlewareGroup as Contract;

abstract class MiddlewareGroup implements Contract
{
    use Concern;
}
