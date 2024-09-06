<?php

use Beebmx\KirbyMiddleware\Middleware;
use Beebmx\KirbyMiddleware\MiddlewareGroups\AuthMiddlewareGroup;
use Beebmx\KirbyMiddleware\Middlewares\Authenticate;
use Beebmx\KirbyMiddleware\Request;
use Tests\Fixtures\Middleware\SomeMiddleware;

beforeEach(function () {
    Middleware::destroy();
});

it('exists an auth middleware group', function () {
    expect((new Middleware)->getMiddlewareGroups())
        ->toHaveKey('auth');
});

it('can add a middleware to the auth middleware group', function () {
    App(options: [
        'beebmx.kirby-middleware.auth' => [
            SomeMiddleware::class,
        ],
    ]);

    expect((new Middleware)->getMiddlewareGroupsBy('auth')['auth'])
        ->toContain(Authenticate::class)
        ->toContain(SomeMiddleware::class);
});

it('can be added a closure to the auth middleware group', function () {
    App(options: [
        'beebmx.kirby-middleware.auth' => [
            function (Request $request, Closure $next) {
                return $next($request);
            },
        ],
    ]);

    $auth = (new Middleware)->getMiddlewareGroupsBy('auth')['auth'];

    expect(current($auth))
        ->toContain(Authenticate::class)
        ->and(end($auth))
        ->toBeInstanceOf(Closure::class);
});

it('can set routes without options', function () {
    AuthMiddlewareGroup::setRoutes([
        'demo/(:all)',
    ]);

    expect(new AuthMiddlewareGroup)
        ->routes()
        ->toContain('demo/(:all)');
});
