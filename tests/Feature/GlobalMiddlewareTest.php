<?php

use Beebmx\KirbyMiddleware\Http\TrimStrings;
use Beebmx\KirbyMiddleware\Middleware;
use Beebmx\KirbyMiddleware\Request;
use Tests\Fixtures\Middleware\SomeMiddleware;

beforeEach(function () {
    Middleware::destroy();
});

it('can add a middleware to the web middleware group', function () {
    App(options: [
        'beebmx.kirby-middleware.global' => [
            SomeMiddleware::class,
        ],
    ]);

    expect((new Middleware)->getGlobalMiddleware())
        ->toContain(TrimStrings::class)
        ->toContain(SomeMiddleware::class);
});

it('can be added a closure to the web middleware group', function () {
    App(options: [
        'beebmx.kirby-middleware.global' => [
            function (Request $request, Closure $next) {
                return $next($request);
            },
        ],
    ]);

    $globals = (new Middleware)->getGlobalMiddleware();

    expect(current($globals))
        ->toContain(TrimStrings::class)
        ->and(end($globals))
        ->toBeInstanceOf(Closure::class);
});
