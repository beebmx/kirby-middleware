<?php

use Beebmx\KirbyMiddleware\Http\ValidateCsrfToken;
use Beebmx\KirbyMiddleware\Middleware;
use Beebmx\KirbyMiddleware\Request;
use Tests\Fixtures\Middleware\SomeMiddleware;

beforeEach(function () {
    Middleware::destroy();
});

it('can add a middleware to the web middleware group', function () {
    App(options: [
        'beebmx.kirby-middleware.web' => [
            SomeMiddleware::class,
        ],
    ]);

    expect((new Middleware)->getMiddlewareGroupsBy('web')['web'])
        ->toContain(ValidateCsrfToken::class)
        ->toContain(SomeMiddleware::class);
});

it('can be added a closure to the web middleware group', function () {
    App(options: [
        'beebmx.kirby-middleware.web' => [
            function (Request $request, Closure $next) {
                return $next($request);
            },
        ],
    ]);

    $web = (new Middleware)->getMiddlewareGroupsBy('web')['web'];

    expect(current($web))
        ->toContain(ValidateCsrfToken::class)
        ->and(end($web))
        ->toBeInstanceOf(Closure::class);
});
