<?php

use Beebmx\KirbyMiddleware\Middleware;
use Kirby\Exception\ErrorPageException;
use Tests\Fixtures\Middleware\ErrorPageMiddleware;

beforeEach(function () {
    Middleware::destroy();
});

describe('basic', function () {
    it('middlewares when page is rendered', function () {
        $kirby = App(children: [
            ['slug' => 'home'],
        ]);

        Middleware::instance()
            ->append(ErrorPageMiddleware::class);

        $kirby->page('home')->render();
    })->throws(ErrorPageException::class);
});

describe('options', function () {
    it('can disabled middleware functionality', function () {
        $kirby = App(options: [
            'beebmx.kirby-middleware.enabled' => false,
        ], children: [
            ['slug' => 'home'],
        ]);

        Middleware::instance()
            ->append(ErrorPageMiddleware::class);

        $kirby->page('home')->render();
    })->throwsNoExceptions();
});
