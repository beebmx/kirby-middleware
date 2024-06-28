<?php

namespace Beebmx\KirbyMiddleware\Contracts;

interface MiddlewareGroup
{
    public function name(): string;

    public function routes(): string|array;

    public function group(): array;
}
