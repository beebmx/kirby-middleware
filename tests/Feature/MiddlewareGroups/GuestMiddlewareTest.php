<?php

use Beebmx\KirbyMiddleware\Middleware;
use Beebmx\KirbyMiddleware\MiddlewareGroups\GuestMiddlewareGroup;
use Beebmx\KirbyMiddleware\Middlewares\RedirectIfAuthenticated;
use Beebmx\KirbyMiddleware\Request;
use Tests\Fixtures\Middleware\SomeMiddleware;

beforeEach(function () {
    Middleware::destroy();
});

it('exists an guest middleware group', function () {
    expect((new Middleware)->getMiddlewareGroups())
        ->toHaveKey('guest');
});

it('can add a middleware to the guest middleware group', function () {
    App(options: [
        'beebmx.kirby-middleware.guest' => [
            SomeMiddleware::class,
        ],
    ]);

    expect((new Middleware)->getMiddlewareGroupsBy('guest')['guest'])
        ->toContain(RedirectIfAuthenticated::class)
        ->toContain(SomeMiddleware::class);
});

it('can be added a closure to the guest middleware group', function () {
    App(options: [
        'beebmx.kirby-middleware.guest' => [
            function (Request $request, Closure $next) {
                return $next($request);
            },
        ],
    ]);

    $guest = (new Middleware)->getMiddlewareGroupsBy('guest')['guest'];

    expect(current($guest))
        ->toContain(RedirectIfAuthenticated::class)
        ->and(end($guest))
        ->toBeInstanceOf(Closure::class);
});

it('can set routes without options', function () {
    GuestMiddlewareGroup::setRoutes([
        'demo/(:all)',
    ]);

    expect(new GuestMiddlewareGroup)
        ->routes()
        ->toContain('demo/(:all)');
});
